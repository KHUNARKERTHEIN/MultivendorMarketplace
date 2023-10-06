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

namespace Ksolves\MultivendorMarketplace\Block\Order;

use Magento\Payment\Model\Info;

/**
 * KsPayment block
 */
class KsPayment extends \Magento\Framework\View\Element\Template
{
    /**
     * Payment data
     *
     * @var \Magento\Payment\Helper\Data
     */
    protected $ksPaymentData = null;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Payment\Helper\Data $ksPaymentData
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $ksContext,
        \Magento\Payment\Helper\Data $ksPaymentData,
        array $data = []
    ) {
        $this->ksPaymentData = $ksPaymentData;
        parent::__construct($ksContext, $data);
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
            throw new \Magento\Framework\Exception\LocalizedException(__('Invalid parent block for this block'));
        }
        $this->setKsPayment($this->getParentBlock()->getKsOrder()->getPayment());
        parent::_beforeToHtml();
    }

    /**
     * Set payment
     *
     * @param Info $payment
     * @return $this
     */
    public function setKsPayment($payment)
    {
        $paymentInfoBlock = $this->ksPaymentData->getInfoBlock($payment, $this->getLayout());
        $this->setChild('ks_info', $paymentInfoBlock);
        $this->setData('payment', $payment);
        return $this;
    }

    /**
     * Prepare html output
     *
     * @return string
     */
    protected function _toHtml()
    {
        return $this->getChildHtml('ks_info');
    }
}
