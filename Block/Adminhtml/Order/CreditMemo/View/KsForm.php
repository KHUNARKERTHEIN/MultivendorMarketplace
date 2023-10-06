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

namespace Ksolves\MultivendorMarketplace\Block\Adminhtml\Order\CreditMemo\View;

/**
 * Creditmemo view form
 */
class KsForm extends \Ksolves\MultivendorMarketplace\Block\Adminhtml\Order\KsAbstractOrder
{
    /**
     * Retrieve invoice order
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return $this->_coreRegistry->registry('current_order');
    }

    /**
     * Retrieve source
     *
     * @return \Magento\Sales\Model\Order\Creditmemo
     */
    public function getSource()
    {
        return $this->getCreditMemo();
    }

    /**
     * Retrieve order totals block settings
     *
     * @return array
     */
    public function getOrderTotalData()
    {
        return ['grand_total_title' => __('Total Refund')];
    }

    /**
     * Retrieve creditmemo model instance
     *
     * @return \Magento\Sales\Model\Order\Creditmemo
     */
    public function getCreditMemo()
    {
        return $this->_coreRegistry->registry('current_creditmemo_request');
    }

    /**
     * Get order url
     *
     * @return string
     */
    public function getOrderUrl()
    {
        return $this->getUrl('sales/order/view', ['order_id' => $this->getCreditmemo()->getOrderId()]);
    }

    /**
     * Get reject url
     *
     * @return string
     */
    public function getKsRejectUrl()
    {
        return $this->getUrl('multivendor/order_creditmemo/reject');
    }
}
