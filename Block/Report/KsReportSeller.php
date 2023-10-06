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
use Ksolves\MultivendorMarketplace\Helper\KsReportSellerHelper;
use Ksolves\MultivendorMarketplace\Helper\KsSellerHelper;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\ResourceConnection;

/**
 * KsReportSeller Block Class
 */
class KsReportSeller extends Template
{
    const KS_STATUS_ENABLED = 1;

    /**
     * @var KsDataHelper
     */
    protected $ksHelperData;

    /**
     * @var KsReportSellerHelper
     */
    protected $ksReportHelper;

    /**
     * @var KsSellerHelper
     */
    protected $ksSellerHelper;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $ksRegistry;

    /**
     * @var Magento\Framework\App\ResourceConnection
     */
    protected $ksResourceConnection;

    /**
     * @var \Ksolves\MultivendorMarketplace\Model\ResourceModel\KsProduct\CollectionFactory
     */
    protected $ksProductCollectionFactory;

    /**
     * @param Context $ksContext
     * @param KsDataHelper $ksHelperData
     * @param KsReportSellerHelper $ksReportHelper
     * @param KsSellerHelper $ksSellerHelper
     * @param \Magento\Framework\Registry $ksRegistry
     * @param \Magento\Framework\App\ResourceConnection $ksResourceConnection
     * @param \Ksolves\MultivendorMarketplace\Model\ResourceModel\KsProduct\CollectionFactory $ksProductCollectionFactory
     */
    public function __construct(
        Context $ksContext,
        KsDataHelper $ksHelperData,
        KsReportSellerHelper $ksReportHelper,
        KsSellerHelper $ksSellerHelper,
        \Magento\Framework\Registry $ksRegistry,
        ResourceConnection $ksResourceConnection,
        \Ksolves\MultivendorMarketplace\Model\ResourceModel\KsProduct\CollectionFactory $ksProductCollectionFactory
    ) {
        $this->ksHelperData = $ksHelperData;
        $this->ksReportHelper = $ksReportHelper;
        $this->ksSellerHelper = $ksSellerHelper;
        $this->ksRegistry = $ksRegistry;
        $this->ksResourceConnection = $ksResourceConnection;
        $this->ksProductCollectionFactory = $ksProductCollectionFactory;
        parent::__construct($ksContext);
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
     * get current seller
     * @return collection
     */
    public function getKsCurrentSellerId()
    {
        if ($this->getRequest()->getParam('seller_id')) {
            return $this->getRequest()->getParam('seller_id');
        }
        $ksProductId = $this->ksHelperData->getKsCurrentProductId();
        $ksSellerDetails = $this->ksProductCollectionFactory->create()->addFieldToFilter('ks_product_id', $ksProductId);
        if ($ksSellerDetails->getSize()>0) {
            return $ksSellerDetails->getFirstItem()->getKsSellerId();
        } else {
            return 0;
        }
    }

    /**
     * get current seller name
     * @return String
     */
    public function getKsCurrentSellerName()
    {
        return $this->ksSellerHelper->getKsSellerName($this->getKsCurrentSellerId());
    }

    /**
     * get available and enabled reason
     * @return collection
     */
    public function getKsReasons()
    {
        $ksQuery = $this->ksReportHelper->getKsSellerReportReasons()->getSelect()->group('ks_reason');
        return $this->ksResourceConnection->getConnection()->query($ksQuery->__toString())->fetchAll();
    }

    /**
     * get enable status of report seller
     * @return int
     */
    public function isEnabled()
    {
        return $this->getKsConfigValue(
            'ks_marketplace_report/ks_report_seller/ks_report_seller_enable',
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
            'ks_marketplace_report/ks_report_seller/ks_report_seller_text',
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
            'ks_marketplace_report/ks_report_seller/ks_allow_reason',
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
            'ks_marketplace_report/ks_report_seller/ks_reason_text',
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
            'ks_marketplace_report/ks_report_seller/ks_reason_placeholder',
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
            'ks_marketplace_report/ks_report_seller/ks_allow_sub_reason',
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
            'ks_marketplace_report/ks_report_seller/ks_sub_reason_text',
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
            'ks_marketplace_report/ks_report_seller/ks_sub_reason_placeholder',
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
            'ks_marketplace_report/ks_report_seller/ks_allow_comments',
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
            'ks_marketplace_report/ks_report_seller/ks_comments_text',
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
            'ks_marketplace_report/ks_report_seller/ks_comments_placeholder',
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
            'ks_marketplace_report/ks_report_seller/ks_allow_guests',
            $this->ksHelperData->getKsCurrentStoreView()
        );
    }

    /**
     * check if logged in account is of the same seller
     *
     * @return Boolean
     */
    public function getKsCanReportSeller()
    {
        $ksSellerId = $this->getRequest()->getParam('seller_id');
        if ($this->getKsCurrentCustomerId()==$ksSellerId) {
            return false;
        }
        return true;
    }

    /**
     * Get seller profile redirect url
     * @param $ksSellerId
     * @return url
     */
    public function getKsSellerProfileUrl($ksSellerId)
    {
        return $this->ksSellerHelper->getKsSellerProfileUrl($ksSellerId);
    }
}
