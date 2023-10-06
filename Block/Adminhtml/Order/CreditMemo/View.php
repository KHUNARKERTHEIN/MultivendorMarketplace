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

namespace Ksolves\MultivendorMarketplace\Block\Adminhtml\Order\CreditMemo;

/**
 * View class
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
     * @var \Magento\Sales\Api\CreditmemoRepositoryInterface 
     */
    private $ksCreditmemoModel;
    
    /**
     * @param \Magento\Backend\Block\Widget\Context $ksContext
     * @param \Magento\Framework\Registry $ksRegistry
     * @param \Magento\Sales\Api\CreditmemoRepositoryInterface $ksCreditmemoModel
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $ksContext,
        \Magento\Framework\Registry $ksRegistry,
        \Magento\Sales\Api\CreditmemoRepositoryInterface $ksCreditmemoModel,
        array $data = []
    ) {
        $this->ksRegistry = $ksRegistry;
        $this->ksCreditmemoModel = $ksCreditmemoModel;
        parent::__construct($ksContext, $data);
    }

    /**
     * Add & remove control buttons
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();

        $this->buttonList->remove('save');
        $this->buttonList->remove('reset');
        $this->buttonList->remove('delete');

        if (!$this->getKsCreditmemo()) {
            return;
        }

        $ksCreditmemoId = $this->getKsCreditmemoId(); //Actual creditmemo Id
        if ($this->getKsCreditmemo()->getKsApprovalStatus() == \Ksolves\MultivendorMarketplace\Model\KsSalesCreditMemo::KS_STATUS_APPROVED) {
            $this->addButton(
                'send_notification',
                [
                    'label' => __('Send Email'),
                    'class' => 'send-email',
                    'onclick' => 'confirmSetLocation(\'' . __(
                        'Are you sure you want to send a credit memo email to customer?'
                    ) . '\', \'' . $this->getKsEmailUrl() . '\')'
                ]
            );

                $this->buttonList->add(
                    'print',
                    [
                        'label' => __('Print'),
                        'class' => 'print',
                        'onclick' => 'setLocation(\'' . $this->getKsPrintUrl() . '\')'
                    ]
                );
        }else {
            $this->buttonList->add(
                'approve',
                [
                    'label' => __('Approve'),
                    'class' => 'approve',
                    'onclick' => 'setLocation(\'' . $this->getKsApproveUrl() . '\')'
                ]
            );

            if ($this->getKsCreditmemo()->getKsApprovalStatus() != \Ksolves\MultivendorMarketplace\Model\KsSalesCreditMemo::KS_STATUS_REJECTED) {
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
     * Retrieve creditmemo model instance
     *
     * @return \Magento\Sales\Model\Order\Creditmemo
     */
    public function getKsCreditmemo()
    {
        return $this->ksRegistry->registry('current_creditmemo_request');
    }

     /**
     * Retrieve actual creditmemo id
     *
     * @return \Magento\Sales\Model\Order\Creditmemo
     */
    public function getKsCreditmemoId()
    {
        $creditmemo_increment_id = $this->getKsCreditmemo()->getKsCreditmemoIncrementId();
        $ksCreditmemoId = $this->ksCreditmemoModel->create()->getCollection()->addAttributeToSelect('entity_id')->addFieldToFilter('increment_id',$creditmemo_increment_id)->getFirstItem()->getEntityId();
        
        return $ksCreditmemoId;
    }

    /**
     * Retrieve text for header
     *
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {
        if ($this->getKsCreditmemo()->getEmailSent()) {
            $emailSent = __('The credit memo email was sent.');
        } else {
            $emailSent = __('The credit memo email wasn\'t sent.');
        }
        return __(
            'Credit Memo #%1 | %3 | %2 (%4)',
            $this->getKsCreditmemo()->getIncrementId(),
            $this->formatDate(
                $this->_localeDate->date(new \DateTime($this->getKsCreditmemo()->getCreatedAt())),
                \IntlDateFormatter::MEDIUM,
                true
            ),
            $this->getKsCreditmemo()->getStateName(),
            $emailSent
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
     * Retrieve email url
     *
     * @return string
     */
    public function getKsEmailUrl()
    {
        return $this->getUrl(
            'multivendor/*/email',
            [
                'ks_creditmemo_id' => $this->getKsCreditmemo()->getId(),
                'creditmemo_id' => $this->getKsCreditmemoId(),
                'order_id' => $this->getKsCreditmemo()->getOrderId()
            ]
        );
    }

    /**
     * Retrieve print url
     *
     * @return string
     */
    public function getKsPrintUrl()
    {
        return $this->getUrl('multivendor/*/print', ['creditmemo_id' => $this->getKsCreditmemoId()]);
    }

    /**
     * Get approve url
     *
     * @return string
     */
    public function getKsApproveUrl()
    {
        return $this->getUrl('multivendor/order_creditmemo/approve', ['entity_id' => $this->getKsCreditmemo()->getId()]);
    }

    /**
     * Get approve url
     *
     * @return string
     */
    public function getKsRejectUrl()
    {
        return $this->getUrl('multivendor/order_creditmemo/reject', ['entity_id' => $this->getKsCreditmemo()->getId()]);
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
            if ($this->getKsCreditmemo()->getBackUrl()) {
                return $this->buttonList->update(
                    'back',
                    'onclick',
                    'setLocation(\'' . $this->getKsCreditmemo()->getBackUrl() . '\')'
                );
            }
        }
        return $this;
    }
}
