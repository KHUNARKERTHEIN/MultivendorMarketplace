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

namespace Ksolves\MultivendorMarketplace\Block\Order\Invoice\View;

use \Magento\Sales\Model\ResourceModel\Order\Invoice\Comment\CollectionFactory;
use Ksolves\MultivendorMarketplace\Helper\KsOrderHelper;

/**
 * CommentsHistory block
 */
class CommentsHistory extends \Magento\Framework\View\Element\Template
{
    /**
     * Sales data
     *
     * @var \Magento\Sales\Helper\Data
     */
    protected $ksSalesData = null;

    /**
     * @var \Magento\Sales\Helper\Admin
     */
    private $ksAdminHelper;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Invoice\Comment\CollectionFactory
     */
    protected $ksCommentFactory;

    /**
     * @var KsOrderHelper
     */
    protected $ksOrderHelper;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $ksRegistry;

    /**
     * @param Magento\Framework\View\Element\Template\Context $ksContext
     * @param \Magento\Sales\Helper\Data $ksSalesData
     * @param \Magento\Sales\Helper\Admin $ksAdminHelper
     * @param \Magento\Framework\Registry $ksRegistry
     * @param \Magento\Sales\Model\ResourceModel\Order\Invoice\Comment\CollectionFactory $ksCommentFactory
     * @param KsOrderHelper $ksOrderHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $ksContext,
        \Magento\Sales\Helper\Data $ksSalesData,
        \Magento\Sales\Helper\Admin $ksAdminHelper,
        \Magento\Framework\Registry $ksRegistry,
        CollectionFactory $ksCommentFactory,
        KsOrderHelper $ksOrderHelper,
        array $data = []
    ) {
        $this->ksSalesData = $ksSalesData;
        $this->ksAdminHelper = $ksAdminHelper;
        $this->ksRegistry  = $ksRegistry;
        $this->ksCommentFactory = $ksCommentFactory;
        $this->ksOrderHelper = $ksOrderHelper;
        parent::__construct($ksContext, $data);
    }

    /**
     * Retrieve Invoice model instance
     *
     * @return \Ksolves\MultivendorMarketplace\Model\KsSalesInvoice
     */
    public function getInvoice()
    {
        return $this->ksRegistry->registry('current_invoice_request');
    }

    /**
     * Retrieve order model instance
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return $this->ksRegistry->registry('current_order');
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
     * @return bool
     */
    public function canKsSendCommentEmail()
    {
        return $this->ksSalesData->canSendInvoiceCommentEmail(
            $this->getOrder()->getStore()->getId()
        );
    }

    /**
     * Get comment collection for the Invoice
     *
     * @return Object
     */
    public function getKsComments()
    {
        if ($this->getInvoice()->getKsApprovalStatus()) {
            $ksEntityId = $this->ksOrderHelper->getInvoiceId($this->getInvoice()->getEntityId());
            return $this->ksCommentFactory->create()->addFieldToFilter('parent_id', $ksEntityId)->setOrder('entity_id', 'DESC');
        } else {
            return $this->getInvoice();
        }
    }

    /**
     * @return bool
     */
    public function checkNotesAndNotifyInvoice()
    {
        return $this->ksOrderHelper->checkNotesAndNotifyInvoice();
    }
}
