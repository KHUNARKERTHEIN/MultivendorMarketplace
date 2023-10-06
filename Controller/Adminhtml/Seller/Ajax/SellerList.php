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

/**
 * Class SellerList Controller
 */
class SellerList extends \Ksolves\MultivendorMarketplace\Controller\Adminhtml\Seller\Ajax\CustomerList
{

    /**
     * execute action
     */
    public function execute()
    {
        $ksSearchKey = $this->getRequest()->getParam('ks_search_key');
        $ksSellerId = null;
        if ($this->getRequest()->getParam('ks_product_id') != null) {
            $ksProductId = $this->getRequest()->getParam('ks_product_id');
            $ksProduct = $this->ksProductFactory->create()->getCollection()->addFieldToFilter('ks_product_id', $ksProductId);
            $ksSellerId = $ksProduct->getData()[0]['ks_seller_id'];
        }
        // get seller collection
        $ksSellerList = $this->ksSellerFactory->create()
                        ->getCollection()->addFieldToFilter('ks_seller_status', 1);
        $ksSellerIds = [];
        foreach ($ksSellerList as $ksSeller) {
            $ksSellerIds[] = $ksSeller->getKsSellerId();
        }
        if ($ksSellerId != null) {
            $ksSellerIds = [];
            $ksSellerIds[] = $ksSellerId;
        }
        
        // filter the customer collection
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

        $ksJoinTable = $ksCustomerList->getTable('ks_seller_details');
        $ksCustomerList->getSelect()->join(
            $ksJoinTable. ' as ks_cgf',
            'e.entity_id = ks_cgf.ks_seller_id',
            [
                'ks_seller_id'    =>'ks_seller_id'
            ]
        );

        // get the customer list which is not a seller
        if (!empty($ksSellerIds)) {
            $ksCustomerList->addFieldToFilter('entity_id', ['in'=>$ksSellerIds]);
        }


        // check size of customer collection
        if ($ksCustomerList->getSize() > 0) {
            foreach ($ksCustomerList as $ksCustomer) {
                $ksOptions[] = [
                    'value' => $ksCustomer->getId(),
                    'label' => $ksCustomer->getName().' ('.$ksCustomer->getEmail().')',
                ];
            }
        } else {
            $ksOptions[] = [
                'value' => '',
                'label' =>  "No Seller available",
            ];
        }

        // set response data
        $ksResponse = $this->resultFactory
        ->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON)
        ->setData([
            'output' => $ksOptions
        ]);

        return $ksResponse;
    }
}
