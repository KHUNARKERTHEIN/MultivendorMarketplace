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

namespace Ksolves\MultivendorMarketplace\Block\Adminhtml\Order;

/**
 * Adminhtml order totals block
 */
class KsTotals extends \Ksolves\MultivendorMarketplace\Block\Adminhtml\KsTotals
{
    /**
     * @var \Ksolves\MultivendorMarketplace\Helper\KsOrderHelper
     */
    protected $ksOrderHelper;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $ksContext
     * @param \Magento\Framework\Registry $ksRegistry
     * @param \Magento\Sales\Helper\Admin $ksAdminHelper
     * @param \Ksolves\MultivendorMarketplace\Helper\KsOrderHelper $ksOrderHelper
     * @param array $ksData
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $ksContext,
        \Magento\Framework\Registry $ksRegistry,
        \Magento\Sales\Helper\Admin $ksAdminHelper,
        \Ksolves\MultivendorMarketplace\Helper\KsOrderHelper $ksOrderHelper,
        array $ksData = []
    ) {
        $this->_adminHelper = $ksAdminHelper;
        $this->ksOrderHelper = $ksOrderHelper;
        parent::__construct($ksContext, $ksRegistry, $ksAdminHelper, $ksData);
    }

    /**
     * Initialize order totals array
     *
     * @return $this
     */
    protected function _initTotals()
    {
        parent::_initTotals();
        $this->_totals['commission'] = new \Magento\Framework\DataObject(
            [
                'code' => 'commission',
                'strong' => true,
                'value' => $this->ksOrderHelper->getKsTotalOrderCommission($this->getSource()->getRealOrderId()),
                'base_value' => $this->ksOrderHelper->getKsTotalOrderCommission($this->getSource()->getRealOrderId()),
                'label' => __('Total Commission'),
                'area' => 'footer',
            ]
        );
        $this->_totals['paid'] = new \Magento\Framework\DataObject(
            [
                'code' => 'paid',
                'strong' => true,
                'value' => $this->getSource()->getTotalPaid(),
                'base_value' => $this->getSource()->getBaseTotalPaid(),
                'label' => __('Total Paid'),
                'area' => 'footer',
            ]
        );
        $this->_totals['refunded'] = new \Magento\Framework\DataObject(
            [
                'code' => 'refunded',
                'strong' => true,
                'value' => $this->getSource()->getTotalRefunded(),
                'base_value' => $this->getSource()->getBaseTotalRefunded(),
                'label' => __('Total Refunded'),
                'area' => 'footer',
            ]
        );
        $code = 'due';
        $label = 'Total Due';
        $value = $this->getSource()->getTotalDue();
        $baseValue = $this->getSource()->getBaseTotalDue();
        if ($this->getSource()->getTotalCanceled() > 0 && $this->getSource()->getBaseTotalCanceled() > 0) {
            $code = 'canceled';
            $label = 'Total Canceled';
            $value = $this->getSource()->getTotalCanceled();
            $baseValue = $this->getSource()->getBaseTotalCanceled();
        }
        $this->_totals[$code] = new \Magento\Framework\DataObject(
            [
                'code' => 'due',
                'strong' => true,
                'value' => $value,
                'base_value' => $baseValue,
                'label' => __($label),
                'area' => 'footer',
            ]
        );
        return $this;
    }
}
