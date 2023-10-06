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

namespace Ksolves\MultivendorMarketplace\Block\Order\CreditMemo\View;

use Magento\Framework\App\ObjectManager;
use Magento\Shipping\Helper\Data as ShippingHelper;
use Magento\Tax\Helper\Data as TaxHelper;
use Ksolves\MultivendorMarketplace\Helper\KsOrderHelper;

/**
 * KsForm block
 */
class KsForm extends \Ksolves\MultivendorMarketplace\Block\Order\KsAbstractOrder
{
    /**
     * KsOrderHelper
     */
    protected $ksOrderHelper;


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
        KsOrderHelper $ksOrderHelper,
        array $ksData = [],
        ?ShippingHelper $ksShippingHelper = null,
        ?TaxHelper $ksTaxHelper = null
    ) {
        $this->ksOrderHelper = $ksOrderHelper;
        parent::__construct($ksContext, $ksRegistry, $ksAdminHelper, $ksOrderRepository, $ksData, $ksShippingHelper, $ksTaxHelper);
    }

    /**
     * Retrieve creditmemo model instance
     *
     * @return Object
     */
    public function getKsCreditmemo()
    {
        if ($this->ksRegistry->registry('current_creditmemo')) {
            return $this->ksRegistry->registry('current_creditmemo');
        } else {
            return $this->ksRegistry->registry('current_creditmemo_request');
        }
    }

    /**
     * Retrieve creditmemo items
     *
     * @return Object
     */
    public function getKsCreditmemoItems()
    {
        return $this->ksRegistry->registry('current_creditmemo_items');
    }

    /**
     * Get print url
     *
     * @return string
     */
    public function getKsPrintUrl()
    {
        return $this->getUrl('multivendor/*/print', ['creditmemo_id' => $this->getKsCreditmemo()->getId()]);
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
     * @return string
     */
    public function getKsReSubmitUrl()
    {
        return $this->getUrl('multivendor/*/resubmit');
    }

    /**
     * @return bool
     */
    public function ksCanSendEmail()
    {
        return $this->ksOrderHelper->canKsSendMemoEmail();
    }

    /**
     * @return string
     */
    public function getKsSendEmailUrl()
    {
        return $this->getUrl('multivendor/order_creditmemo/sendemail', ['creditmemo_id' => $this->ksRegistry->registry('current_creditmemo_id')]);
    }
}
