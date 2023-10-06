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

namespace Ksolves\MultivendorMarketplace\Block\Report;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Ksolves\MultivendorMarketplace\Helper\KsDataHelper;
use Ksolves\MultivendorMarketplace\Helper\KsProductHelper;
use Ksolves\MultivendorMarketplace\Helper\KsReportProductHelper;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\ResourceConnection;

/**
 * KsReportProduct Block Class
 */
class KsReportProduct extends Template
{
    const KS_STATUS_ENABLED = 1;

    /**
     * @var KsDataHelper
     */
    protected $ksHelperData;

    /**
     * @var KsProductHelper
     */
    protected $ksProductHelper;

    /**
     * @var KsReportProductHelper
     */
    protected $ksReportHelper;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $ksRegistry;

    /**
     * @var Magento\Framework\App\ResourceConnection
     */
    protected $ksResourceConnection;

    /**
     * @param Context $ksContext
     * @param KsDataHelper $ksHelperData
     * @param KsProductHelper $ksProductHelper
     * @param KsReportProductHelper $ksReportHelper
     * @param \Magento\Framework\Registry $ksRegistry
     * @param \Magento\Framework\App\ResourceConnection $ksResourceConnection
     */
    public function __construct(
        Context $ksContext,
        KsDataHelper $ksHelperData,
        KsReportProductHelper $ksReportHelper,
        KsProductHelper $ksProductHelper,
        \Magento\Framework\Registry $ksRegistry,
        ResourceConnection $ksResourceConnection
    ) {
        $this->ksHelperData = $ksHelperData;
        $this->ksProductHelper = $ksProductHelper;
        $this->ksReportHelper = $ksReportHelper;
        $this->ksRegistry = $ksRegistry;
        $this->ksResourceConnection = $ksResourceConnection;
        parent::__construct($ksContext);
    }
    
    /**
     * check if product is seller and logged in customer is the same seller
     *
     * @return Boolean
     */
    public function getKsCanReportProduct()
    {
        $ksSellerId = $this->ksProductHelper->getKsSellerId($this->getKsCurrentProduct()->getId());
        if ($ksSellerId!=0) {
            if ($this->getKsCurrentCustomerId()==$ksSellerId) {
                return false;
            }
            return true;
        }
        return false;
    }

    /**
     * get configuration values
     * @param $ksField
     * @param null $ksStoreId
     * @return mixed
     */
    public function getKsConfigValue($ksField, $ksStoreId = null)
    {
        return $this->ksHelperData->getKsConfigValue(
            $ksField,
            $ksStoreId
        );
    }

    /**
     * get current product
     * @return collection
     */
    public function getKsCurrentProduct()
    {
        return $this->ksRegistry->registry('current_product');
    }

    /**
     * get current product sku
     * @return String
     */
    public function getKsCurrentProductSku()
    {
        return $this->getKsCurrentProduct()->getSku();
    }

    /**
     * get current product name
     * @return String
     */
    public function getKsCurrentProductName()
    {
        return $this->getKsCurrentProduct()->getName();
    }

    /**
     * get available and enabled reason
     * @return collection
     */
    public function getKsReasons()
    {
        $ksQuery = $this->ksReportHelper->getKsProductReportReasons()->getSelect()->group('ks_reason');
        return $this->ksResourceConnection->getConnection()->query($ksQuery->__toString())->fetchAll();
    }

    /**
     * get enable status of report product
     * @return int
     */
    public function getKsIsEnabled()
    {
        return $this->getKsConfigValue(
            'ks_marketplace_report/ks_report_product/ks_report_product_enable',
            $this->ksHelperData->getKsCurrentStoreView()
        );
    }

    /**
     * get heading
     * @return String
     */
    public function getKsHeadingText()
    {
        return $this->getKsConfigValue(
            'ks_marketplace_report/ks_report_product/ks_report_product_text',
            $this->ksHelperData->getKsCurrentStoreView()
        );
    }

    /**
     * get reason status
     * @return Boolean
     */
    public function getKsIsReasonEnabled()
    {
        return $this->getKsConfigValue(
            'ks_marketplace_report/ks_report_product/ks_allow_reason',
            $this->ksHelperData->getKsCurrentStoreView()
        );
    }

    /**
     * get reason text
     * @return String
     */
    public function getKsReasonText()
    {
        return $this->getKsConfigValue(
            'ks_marketplace_report/ks_report_product/ks_reason_text',
            $this->ksHelperData->getKsCurrentStoreView()
        );
    }

    /**
     * get reason placeholder
     * @return String
     */
    public function getKsReasonPlaceholder()
    {
        return $this->getKsConfigValue(
            'ks_marketplace_report/ks_report_product/ks_reason_placeholder',
            $this->ksHelperData->getKsCurrentStoreView()
        );
    }

    /**
     * get sub reason status
     * @return Boolean
     */
    public function getKsIsSubReasonEnabled()
    {
        return $this->getKsConfigValue(
            'ks_marketplace_report/ks_report_product/ks_allow_sub_reason',
            $this->ksHelperData->getKsCurrentStoreView()
        );
    }

    /**
     * get sub reason text
     * @return String
     */
    public function getKsSubReasonText()
    {
        return $this->getKsConfigValue(
            'ks_marketplace_report/ks_report_product/ks_sub_reason_text',
            $this->ksHelperData->getKsCurrentStoreView()
        );
    }

    /**
     * get sub reason placeholder
     * @return String
     */
    public function getKsSubReasonPlaceholder()
    {
        return $this->getKsConfigValue(
            'ks_marketplace_report/ks_report_product/ks_sub_reason_placeholder',
            $this->ksHelperData->getKsCurrentStoreView()
        );
    }

    /**
     * get comments status
     * @return Boolean
     */
    public function getKsIsCommentsEnabled()
    {
        return $this->getKsConfigValue(
            'ks_marketplace_report/ks_report_product/ks_allow_comments',
            $this->ksHelperData->getKsCurrentStoreView()
        );
    }

    /**
     * get comments text
     * @return String
     */
    public function getKsCommentsText()
    {
        return $this->getKsConfigValue(
            'ks_marketplace_report/ks_report_product/ks_comments_text',
            $this->ksHelperData->getKsCurrentStoreView()
        );
    }

    /**
     * get comments placeholder
     * @return String
     */
    public function getKsCommentsPlaceholder()
    {
        return $this->getKsConfigValue(
            'ks_marketplace_report/ks_report_product/ks_comments_placeholder',
            $this->ksHelperData->getKsCurrentStoreView()
        );
    }

    /**
     * get current customer id
     * @return Int
     */
    public function getKsCurrentCustomerId()
    {
        return $this->ksReportHelper->getKsCurrentCustomerId();
    }

    /**
     * get guests report allowed flag
     * @return Int
     */
    public function getKsIsGuestAllowed()
    {
        return $this->getKsConfigValue(
            'ks_marketplace_report/ks_report_product/ks_allow_guests',
            $this->ksHelperData->getKsCurrentStoreView()
        );
    }
}
