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

namespace Ksolves\MultivendorMarketplace\Block\Order\CreditMemo\Create;

use Magento\Framework\App\ObjectManager;
use Magento\Shipping\Helper\Data as ShippingHelper;
use Magento\Tax\Helper\Data as TaxHelper;

/**
 * KsForm block
 */
class KsForm extends \Ksolves\MultivendorMarketplace\Block\Order\KsAbstractOrder
{
    /**
     * @param \Magento\Backend\Block\Template\Context $ksContext
     * @param \Magento\Framework\Registry $ksRegistry
     * @param \Magento\Sales\Helper\Admin $ksAdminHelper
     * @param \Magento\Sales\Model\OrderRepository $ksOrderRepository
     * @param array $ksData
     * @param ShippingHelper|null $ksShippingHelper
     * @param TaxHelper|null $ksTaxHelper
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $ksContext,
        \Magento\Framework\Registry $ksRegistry,
        \Magento\Sales\Helper\Admin $ksAdminHelper,
        \Magento\Sales\Model\OrderRepository $ksOrderRepository,
        array $ksData = [],
        ?ShippingHelper $ksShippingHelper = null,
        ?TaxHelper $ksTaxHelper = null
    ) {
        $ksData['taxHelper'] = $ksTaxHelper;
        parent::__construct($ksContext, $ksRegistry, $ksAdminHelper, $ksOrderRepository, $ksData, $ksShippingHelper, $ksTaxHelper);
    }


    /**
     * Retrieve creditmemo model instance
     *
     * @return \Magento\Sales\Model\Order\Creditmemo
     */
    public function getKsCreditmemo()
    {
        return $this->ksRegistry->registry('current_creditmemo');
    }
    
    /**
     * Get save url
     *
     * @return string
     */
    public function getKsSaveUrl()
    {
        return $this->getUrl('multivendor/order_creditmemo/save', ['order_id' => $this->getKsCreditmemo()->getOrderId()]);
    }

    /**
     * Get back url
     *
     * @return string
     */
    public function getKsBackUrl()
    {
        return sprintf("Javascript:history.back();");
    }

    /**
     * Get Reset url
     *
     * @return string
     */
    public function getKsResetUrl()
    {
        return $this->getUrl('multivendor/order_creditmemo/new/', ['order_id'=>$this->getKsCreditmemo()->getOrderId()]);
    }
}
