<?php
/**
 * Ksolves
 *
 * @category  Ksolves
 * @package   Ksolves_MultivendorMarketplace
 * @author    Ksolves Team
 * @copyright Copyright (c) Ksolves India Limited (https://www.ksolves.com/)
 * @license   https://store.ksolves.com/magento-license
 */

namespace Ksolves\MultivendorMarketplace\Controller\Adminhtml\Seller\Ajax;

use Magento\Backend\App\Action\Context;
use Magento\Customer\Model\CustomerFactory;
use Ksolves\MultivendorMarketplace\Model\KsSellerFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Ksolves\MultivendorMarketplace\Model\KsProductFactory;

/**
 * Class CustomerList Controller
 */
class CustomerList extends \Magento\Backend\App\Action
{
    /**
     * @var CustomerFactory
     */
    protected $ksCustomerFactory;

    /**
     * @var KsSellerFactory
     */
    protected $ksSellerFactory;

    /**
     * @var ScopeConfigInterface
     */
    protected $ksScopeConfig;

    /**
     * @var ProductFactory
     */
    protected $KsProductFactory;

    /**
     * @var KsProductFactory
     */
    protected $ksProductFactory;

    /**
     * Initialize Controller
     *
     * @param Context $ksContext
     * @param CustomerFactory $ksCustomerFactory
     * @param KsSellerFactory $ksSellerFactory
     * @param KsProductFactory $ksProductFactory
     * @param ScopeConfigInterface $ksScopeConfig
     */
    public function __construct(
        Context $ksContext,
        CustomerFactory $ksCustomerFactory,
        KsSellerFactory $ksSellerFactory,
        KsProductFactory $ksProductFactory,
        ScopeConfigInterface $ksScopeConfig
    ) {
        $this->ksCustomerFactory = $ksCustomerFactory;
        $this->ksSellerFactory = $ksSellerFactory;
        $this->ksProductFactory = $ksProductFactory;
        $this->ksScopeConfig = $ksScopeConfig;
        parent::__construct($ksContext);
    }

    /**
     * execute action
     */
    public function execute()
    {
        // get website id
        $ksWebsiteId = $this->getRequest()->getParam('ks_website_id');
        $ksSearchKey = $this->getRequest()->getParam('ks_search_key');

        // get seller collection
        $ksSellerList = $this->ksSellerFactory->create()
                        ->getCollection();
        $ksSellerIds = [];
        foreach ($ksSellerList as $ksSeller) {
            $ksSellerIds[] = $ksSeller->getKsSellerId();
        }

        // filter the customer collection according to website id
        $ksCustomerList = $this->ksCustomerFactory->create()
                            ->getCollection()
                            ->addNameToSelect()
                            ->addAttributeToFilter(
                                [
                                    ['attribute'=>'email', ['like' => '%'.$ksSearchKey.'%']],
                                    ['attribute'=>'prefix', ['like' => '%'.$ksSearchKey.'%']],
                                    ['attribute'=>'firstname', ['like' => '%'.$ksSearchKey.'%']],
                                    ['attribute'=>'middlename', ['like' => '%'.$ksSearchKey.'%']],
                                    ['attribute'=>'lastname', ['like' => '%'.$ksSearchKey.'%']],
                                    ['attribute'=>'suffix', ['like' => '%'.$ksSearchKey.'%']],
                                    ['attribute'=>'name', ['like' => '%'.$ksSearchKey.'%']],
                                ]
                            )
                            ->setOrder('firstname', 'ASC')
                            ->setPageSize(20)
                            ->setCurPage(1);
        
        if ($this->ksScopeConfig->getValue('customer/account_share/scope')) {
            $ksCustomerList->addAttributeToFilter("website_id", ["eq" => $ksWebsiteId]);
        }

        // get the customer list which is not a seller
        if (!empty($ksSellerIds)) {
            $ksCustomerList->addFieldToFilter('entity_id', ['nin'=>$ksSellerIds]);
        }

        // check size of customer collection
        if ($ksCustomerList->getSize() > 0) {
            foreach ($ksCustomerList as $ksCustomer) {
                $ksOptions[] = [
                    'value' => $ksCustomer->getId(),
                    'label' => $ksCustomer->getName().' ('.$ksCustomer->getEmail().')',
                ];
            }
            $ksOptionAvailable = 1;
        } else {
            $ksOptions[] = [
                'value' => '',
                'label' =>  "No customer available",
            ];
            $ksOptionAvailable = 0;
        }

        // set response data
        $ksResponse = $this->resultFactory
        ->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON)
        ->setData([
            'output' => $ksOptions,
            'ksLength' => $ksOptionAvailable
        ]);

        return $ksResponse;
    }
}
