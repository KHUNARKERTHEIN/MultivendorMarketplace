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

namespace Ksolves\MultivendorMarketplace\Block\Order\View;

use Ksolves\MultivendorMarketplace\Helper\KsOrderHelper as KsOrderHelper;

/**
 * KsHistory block
 */
class KsHistory extends \Magento\Framework\View\Element\Template
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $ksCoreRegistry = null;

    /**
     * Sales data
     *
     * @var \Magento\Sales\Helper\Data
     */
    protected $ksSalesData = null;

    /**
     * @var \Magento\Sales\Helper\Admin
     */
    protected $ksAdminHelper;

    /**
     * @var KsOrderHelper
     */
    protected $ksOrderHelper;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Sales\Helper\Data $ksSalesData
     * @param \Magento\Framework\Registry $ksCoreRegistry
     * @param \Magento\Sales\Helper\Admin $ksAdminHelper
     * @param KsOrderHeler $ksOrderHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Sales\Helper\Data $ksSalesData,
        \Magento\Framework\Registry $ksCoreRegistry,
        \Magento\Sales\Helper\Admin $ksAdminHelper,
        KsOrderHelper $ksOrderHelper,
        array $data = []
    ) {
        $this->ksCoreRegistry = $ksCoreRegistry;
        $this->ksSalesData    = $ksSalesData;
        $this->ksOrderHelper  = $ksOrderHelper;
        parent::__construct($context, $data);
        $this->ksAdminHelper  = $ksAdminHelper;
    }

    /**
     * Get stat uses
     *
     * @return array
     */
    public function getStatuses()
    {
        $state = $this->getKsOrder()->getState();
        $statuses = $this->getKsOrder()->getConfig()->getStateStatuses($state);
        return $statuses;
    }

    /**
     * Check allow to send order comment email
     *
     * @return bool
     */
    public function canSendCommentEmail()
    {
        return true;
    }

    /**
     * Retrieve order model
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getKsOrder()
    {
        return $this->ksCoreRegistry->registry('sales_order');
    }

    /**
     * Check allow to add comment
     *
     * @return bool
     */
    public function canAddComment()
    {
        return true;
    }

    /**
     * Submit URL getter
     *
     * @return string
     */
    public function getSubmitUrl()
    {
        return $this->getUrl('multivendor/order/addComment', ['order_id' => $this->getKsOrder()->getId()]);
    }

    /**
     * Customer Notification Applicable check method
     *
     * @param  \Magento\Sales\Model\Order\Status\History $history
     * @return bool
     */
    public function isCustomerNotificationNotApplicable(\Magento\Sales\Model\Order\Status\History $history)
    {
        return $history->isCustomerNotificationNotApplicable();
    }

    /**
     * Replace links in string
     *
     * @param array|string $data
     * @param null|array $allowedTags
     * @return string
     */
    public function escapeHtml($data, $allowedTags = null)
    {
        return $this->ksAdminHelper->escapeHtmlWithLinks($data, $allowedTags);
    }

    /**
     * @return string
     */
    public function checkNotesAndNotify()
    {
        return $this->ksOrderHelper->checkNotesAndNotify();
    }
}
