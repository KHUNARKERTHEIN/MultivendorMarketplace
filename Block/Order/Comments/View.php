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

namespace Ksolves\MultivendorMarketplace\Block\Adminhtml\Order\Comments;

/**
 * Comments view  comments form
 */
class View extends \Magento\Backend\Block\Template
{
    /**
     * Sales data
     *
     * @var \Magento\Sales\Helper\Data
     */
    protected $_salesData = null;

    /**
     * @var \Magento\Sales\Helper\Admin
     */
    private $adminHelper;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Sales\Helper\Data $salesData
     * @param \Magento\Sales\Helper\Admin $adminHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Sales\Helper\Data $salesData,
        \Magento\Sales\Helper\Admin $adminHelper,
        array $data = []
    ) {
        $this->_salesData = $salesData;
        parent::__construct($context, $data);
        $this->adminHelper = $adminHelper;
    }

    /**
     * Retrieve required options from parent
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _beforeToHtml()
    {
        if (!$this->getParentBlock()) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Please correct the parent block for this block.')
            );
        }
        $this->setEntity($this->getParentBlock()->getSource());
        parent::_beforeToHtml();
    }

    /**
     * Get submit url
     *
     * @return string|true
     */
    public function getSubmitUrl()
    {
        return $this->getUrl('*/*/addComment', ['id' => $this->getEntity()->getId()]);
    }

    /**
     * @return bool
     */
    public function canSendCommentEmail()
    {
        switch ($this->getParentType()) {
            case 'invoice':
                return $this->_salesData->canSendInvoiceCommentEmail(
                    $this->getEntity()->getOrder()->getStore()->getId()
                );
            case 'shipment':
                return $this->_salesData->canSendShipmentCommentEmail(
                    $this->getEntity()->getOrder()->getStore()->getId()
                );
            case 'creditmemo':
                return $this->_salesData->canSendCreditmemoCommentEmail(
                    $this->getEntity()->getOrder()->getStore()->getId()
                );
        }
        return true;
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
        return $this->adminHelper->escapeHtmlWithLinks($data, $allowedTags);
    }
}
