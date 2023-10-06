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

/**
 * Class SelectedSeller. Controller
 */
class SelectedSeller extends \Ksolves\MultivendorMarketplace\Controller\Adminhtml\Seller\Ajax\CustomerList
{
    /**
     * execute action
     */
    public function execute()
    {
        // get customer id
        $ksCustomerId = $this->getRequest()->getParam('ks_seller_id');

        // load the customer
        $ksCustomer = $this->ksCustomerFactory->create()->load($ksCustomerId);
        if ($ksCustomerId) {
            $ksSelectedCustomer = $ksCustomer->getName().' ('.$ksCustomer->getEmail().')';
        } else {
            $ksSelectedCustomer = '';
        }
        // set response data
        $ksResponse = $this->resultFactory
        ->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON)
        ->setData([
            'output' => $ksSelectedCustomer
        ]);
        return $ksResponse;
    }
}
