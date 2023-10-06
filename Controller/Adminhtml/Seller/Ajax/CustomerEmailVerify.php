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
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * CustomerEmailVerify Controller class
 */
class CustomerEmailVerify extends \Magento\Backend\App\Action
{
    /**
     * @var CollectionFactory
     */
    protected $ksSellerCollectionFactory;

    /**
     * @var CustomerFactory
     */
    protected $ksCustomerFactory;

    /**
     * @var ScopeConfigInterface
     */
    protected $ksScopeConfig;

    /**
     * Initialize dependencies
     *
     * @param \Magento\Backend\App\Action\Context $ksContext
     * @param CustomerFactory $ksCustomerFactory
     * @param ScopeConfigInterface $ksScopeConfig
     */
    public function __construct(
        Context $ksContext,
        CustomerFactory $ksCustomerFactory,
        ScopeConfigInterface $ksScopeConfig
    ) {
        $this->ksCustomerFactory = $ksCustomerFactory;
        $this->ksScopeConfig = $ksScopeConfig;
        parent::__construct($ksContext);
    }

    /**
     * Verify customer email exists or not
     *
     * @return \Magento\Framework\App\Response\Http
     */
    public function execute()
    {
        $ksStoreStore = 0;
        $ksWebsiteId = $this->getRequest()->getPost("ks_website_id");
        $ksCustomerEmail = trim($this->getRequest()->getPost("ks_customer_email", ""));
       
        // filter the customer collection according to website id
        $ksCustomerCollection = $this->ksCustomerFactory->create()->getCollection()
                                ->addFieldToFilter('email', $ksCustomerEmail);
        
        if ($this->ksScopeConfig->getValue('customer/account_share/scope')) {
            $ksCustomerCollection->addFieldToFilter("website_id", $ksWebsiteId);
        }

        if ($ksCustomerCollection->getSize() > 0) {
            $ksStoreStore = [
                            'ks_message_type' => 'error' ,
                            'ks_message' => '',
                        ];
        } else {
            $ksStoreStore = [
                            'ks_message_type' => 'success' ,
                            'ks_message' => __('email is available'),
                        ];
        }

        $ksResponse = $this->resultFactory
        ->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON)
        ->setData($ksStoreStore);

        return $ksResponse;
    }
}
