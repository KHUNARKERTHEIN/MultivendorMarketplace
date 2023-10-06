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

namespace Ksolves\MultivendorMarketplace\Block\Adminhtml\Order\Invoice;

/**
 * Invoice view form
 */
class View extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $ksRegistry = null;

    /**
     * @var \Magento\Sales\Model\Order\Invoice
     */
    private $ksInvoice;

    /**
     * @param \Magento\Backend\Block\Widget\Context $ksContext
     * @param \Magento\Framework\Registry $ksRegistry
     * @param \Magento\Sales\Model\Order\Invoice $ksInvoice
     * @param array $ksData
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $ksContext,
        \Magento\Framework\Registry $ksRegistry,
        \Magento\Sales\Model\Order\Invoice $ksInvoice,
        array $ksData = []
    ) {
        $this->ksRegistry = $ksRegistry;
        $this->ksInvoice = $ksInvoice;
        parent::__construct($ksContext, $ksData);
    }

    /**
     * Constructor
     *
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _construct()
    {
        $this->_objectId = 'invoice_id';

        parent::_construct();

        $this->buttonList->remove('save');
        $this->buttonList->remove('reset');
        $this->buttonList->remove('delete');

        if (!$this->getKsInvoice()) {
            return 0;
        }       
         
        $ksInvoiceId = $this->getKsInvoiceId(); //Actual Invoice Id
        if($this->getKsInvoice()->getKsApprovalStatus() == \Ksolves\MultivendorMarketplace\Model\KsSalesInvoice::KS_STATUS_APPROVED){

            $this->addButton(
                'send_notification',
                [
                    'label' => __('Send Email'),
                    'class' => 'send-email',
                    'onclick' => 'confirmSetLocation(\'' . __(
                        'Are you sure you want to send an invoice email to customer?'
                    ) . '\', \'' . $this->getEmailUrl() . '\')'
                ]
            );
            
            $this->buttonList->add(
                'print',
                [
                    'label' => __('Print'),
                    'class' => 'print',
                    'onclick' => 'setLocation(\'' . $this->getPrintUrl() . '\')'
                ]
            );

        }else{
            $this->buttonList->add(
                'approve',
                [
                    'label' => __('Approve'),
                    'class' => 'approve',
                    'onclick' => 'setLocation(\'' . $this->getKsApproveUrl() . '\')'
                ]
            );
            if ($this->getKsInvoice()->getKsApprovalStatus()!= \Ksolves\MultivendorMarketplace\Model\KsSalesInvoice::KS_STATUS_REJECTED) {
                $this->buttonList->add(
                    'reject',
                    [
                        'label' => __('Reject'),
                        'class' => 'ks-reject-view'                ]
                );
            }
        }
    }

   
    /**
     * Retrieve invoice model instance
     *
     * @return \Magento\Sales\Model\Order\Invoice
     */
    public function getKsInvoice()
    {
        return $this->ksRegistry->registry('current_invoice_request');
    }

    /**
     * Retrieve actual invoice id
     *
     * @return \Magento\Sales\Model\Order\Invoice
     */
    public function getKsInvoiceId()
    {
        $ksInvoiceIncrId = $this->getKsInvoice()->getKsInvoiceIncrementId();
        $ksInvoiceId = $this->ksInvoice->loadByIncrementId($ksInvoiceIncrId)->getId();
        return $ksInvoiceId;
    }

    /**
     * Get header text
     *
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {
        if ($this->getKsInvoice()->getEmailSent()) {
            $emailSent = __('The invoice email was sent.');
        } else {
            $emailSent = __('The invoice email wasn\'t sent.');
        }
        return __(
            'Invoice #%1 | %2 | %4 (%3)',
            $this->getInvoice()->getIncrementId(),
            $this->getInvoice()->getStateName(),
            $emailSent,
            $this->formatDate(
                $this->_localeDate->date(new \DateTime($this->getInvoice()->getCreatedAt())),
                \IntlDateFormatter::MEDIUM,
                true
            )
        );
    }

    /**
     * Get back url
     *
     * @return string
     */
    public function getBackUrl()
    {
        return sprintf("Javascript:history.back();");
    }   

    /**
     * Get email url
     *
     * @return string
     */
    public function getEmailUrl()
    {
        return $this->getUrl(
            'multivendor/order_invoice/email',
            ['order_id' => $this->getKsInvoice()->getKsOrderId(), 'ks_invoice_id' => $this->getKsInvoice()->getId(), 'invoice_id' => $this->getKsInvoiceId()]
        );
    }

  
    /**
     * Get print url
     *
     * @return string
     */
    public function getPrintUrl()
    {
        return $this->getUrl('multivendor/order_invoice/print', ['invoice_id' => $this->getKsInvoiceId()]);
    }

    /**
     * Get approve url
     *
     * @return string
     */
    public function getKsApproveUrl()
    {
        return $this->getUrl('multivendor/order_invoice/approve', ['id' => $this->getKsInvoice()->getId()]);
    }

    /**
     * Update back button url
     *
     * @param bool $flag
     * @return $this
     */
    public function updateBackButtonUrl($flag)
    {
        if ($flag) {
            if ($this->getKsInvoice()->getBackUrl()) {
                return $this->buttonList->update(
                    'back',
                    'onclick',
                    'setLocation(\'' . $this->getKsInvoice()->getBackUrl() . '\')'
                );
            }
        }
        return $this;
    }
}