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

namespace Ksolves\MultivendorMarketplace\Block\Product\Form;

use Ksolves\MultivendorMarketplace\Helper\KsDataHelper;
use Ksolves\MultivendorMarketplace\Helper\KsProductHelper;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template\Context;

/**
 * KsTabs block class
 */
class KsTabs extends \Magento\Framework\View\Element\Template
{
    /**
     * @var string
     */
    protected $_template = 'Ksolves_MultivendorMarketplace::product/form/ks-tabs.phtml';

    /**
     * @var Registry
     */
    protected $ksRegistry = null;

    /**
     * Tabs structure
     *
     * @var array
     */
    protected $_ksTabs = [];

    /**
     * @var KsDataHelper
     */
    protected $ksDataHelper;

    /**
     * @var KsProductHelper
     */
    protected $ksProductHelper;

    /**
     * @var DataPersistorInterface
     */
    protected $ksDataPersistor;

    /**
     * @param Context $ksContext
     * @param Registry $ksRegistry
     * @param KsDataHelper $ksDataHelper
     * @param KsProductHelper $ksProductHelper
     * @param DataPersistorInterface $ksDataPersistor
     * @param array $ksData
     */
    public function __construct(
        Context $ksContext,
        Registry $ksRegistry,
        KsDataHelper $ksDataHelper,
        KsProductHelper $ksProductHelper,
        DataPersistorInterface $ksDataPersistor,
        array $ksData = []
    ) {
        $this->ksRegistry           = $ksRegistry;
        $this->ksDataHelper         = $ksDataHelper;
        $this->ksProductHelper      = $ksProductHelper;
        $this->ksDataPersistor      = $ksDataPersistor;
        parent::__construct($ksContext, $ksData);
    }

    /**
     * Return current Product
     *
     * @return Product
     */
    public function getKsProduct()
    {
        return $this->ksRegistry->registry('product');
    }

    /**
     * Get Product Persistor Data
     *
     * @param   string $ksFieldName
     * @return array
     */
    public function getKsPersistorProductData()
    {
        $ksPersistorProductData = (array)$this->ksDataPersistor->get('ks_seller_product');

        return $ksPersistorProductData;
    }

    /**
     * Get Product Persistor Data
     *
     * @param   string $ksFieldName
     * @return array
     */
    public function clearKsPersistorProductData()
    {
        $this->ksDataPersistor->clear('ks_seller_product');
    }

    /**
     * Add new tab after another
     *
     * @param   string $tabId new ksTabId
     * @param   array|\Magento\Framework\DataObject $ksTab
     * @param   string $ksAfterTabId
     * @return  void
     */
    public function addKsTabAfter($ksTabId, $ksTab, $ksAfterTabId)
    {
        $this->addKsTab($ksTabId, $ksTab);
        $ksAfterTabIndex = array_search($ksAfterTabId, array_keys($this->_ksTabs));

        $this->_ksTabs = $this->ksArrayMove($ksTabId, $ksAfterTabIndex+1, $this->_ksTabs);
    }

    /**
     * Change array index
     *
     * @param   string $ksKey
     * @param   int ksNewIndex
     * @param   array $ksArray
     * @return  void
     */
    public function ksArrayMove($ksKey, $ksNewIndex, $ksArray)
    {
        if ($ksNewIndex < 0) {
            return;
        }
        if ($ksNewIndex >= count($ksArray)) {
            return;
        }
        if (!array_key_exists($ksKey, $ksArray)) {
            return;
        }

        $ksNewArray = [];
        $ksIndex = 0;
        foreach ($ksArray as $k => $v) {
            if ($ksNewIndex == $ksIndex) {
                $ksNewArray[$ksKey] = $ksArray[$ksKey];
                $ksIndex++;
            }
            if ($k != $ksKey) {
                $ksNewArray[$k] = $v;
                $ksIndex++;
            }
        }
        // one last check for end indexes
        if ($ksNewIndex == $ksIndex) {
            $ksNewArray[$ksKey] = $ksArray[$ksKey];
        }

        return $ksNewArray;
    }

    /**
     * Get tab label
     *
     * @param \Magento\Framework\DataObject|TabInterface $ksTab
     * @return string
     */
    public function getKsTabLabel($ksTab)
    {
        if ($ksTab instanceof TabInterface) {
            return $ksTab->getTabLabel();
        }
        return $ksTab->getLabel();
    }

    /**
     * Get tab label
     *
     * @param \Magento\Framework\DataObject|TabInterface $ksTab
     * @return string
     */
    public function getKsUicomponent($ksTab)
    {
        if ($ksTab->getUicomponent()) {
            return $ksTab->getUicomponent();
        }
        return;
    }

    /**
     * Get tab content
     *
     * @param \Magento\Framework\DataObject|TabInterface $ksTab
     * @return string
     */
    public function getKsTabContent($ksTab)
    {
        if ($ksTab instanceof TabInterface) {
            if ($ksTab->getSkipGenerateContent()) {
                return '';
            }
            return $ksTab->toHtml();
        }
        return $ksTab->getContent();
    }

    /**
     * Add new tab
     *
     * @param   string $ksTabId
     * @param   array|\Magento\Framework\DataObject|string $ksTab
     * @return  $this
     * @throws  \Exception
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function addKsTab($ksTabId, $ksTab)
    {
        if (empty($ksTabId)) {
            throw new \Exception(__('Please correct the tab configuration and try again. Tab Id should be not empty'));
        }

        if (is_array($ksTab)) {
            $this->_ksTabs[$ksTabId] = new \Magento\Framework\DataObject($ksTab);
        } elseif ($ksTab instanceof \Magento\Framework\DataObject) {
            $this->_ksTabs[$ksTabId] = $ksTab;
            if (!$this->_ksTabs[$ksTabId]->hasTabId()) {
                $this->_ksTabs[$ksTabId]->setTabId($ksTabId);
            }
        } elseif (is_string($ksTab)) {
            $this->_ksAddTabByName($ksTab, $ksTabId);
        } else {
            throw new \Exception(__('Please correct the tab configuration and try again.'));
        }
    }

    /**
     * Removes tab with passed id from tabs block
     *
     * @param string $ksTabId
     * @return $this
     */
    public function removeKsTab($ksTabId)
    {
        if (isset($this->_ksTabs[$ksTabId])) {
            unset($this->_ksTabs[$ksTabId]);
        }
        return $this;
    }

    /**
     * get all Tab
     *
     * @return array
     */
    public function getKsProductTabs()
    {
        $ksConfigurableProductChecker = $ksDownloadableProductChecker = false;
        $ksCustomerScope = $this->ksDataHelper->getKsConfigCustomerScopeField("scope");
        if ($ksCustomerScope==1) {
            $this->removeKsTab('product-websites');
        }

        $ksAlertPriceAllow = $this->ksDataHelper->getKsConfigValue('catalog/productalert/allow_price');
        $ksAlertStockAllow = $this->ksDataHelper->getKsConfigValue('catalog/productalert/allow_stock');

        if (!$ksAlertPriceAllow && !$ksAlertStockAllow) {
            $this->removeKsTab('product-alert');
        }

        $ksEnableRelated = $this->ksDataHelper->getKsConfigProductSetting('ks_related_product');
        $ksEnableUpsell = $this->ksDataHelper->getKsConfigProductSetting('ks_up_sell_product');
        $ksEnableCrossSell = $this->ksDataHelper->getKsConfigProductSetting('ks_cross_sell_product');

        if (!$ksEnableRelated && !$ksEnableUpsell && !$ksEnableCrossSell) {
            $this->removeKsTab('product-related');
        }

        if (!$this->getKsProduct()->getId()) {
            $this->removeKsTab('product-scalable-quantity');
            $this->removeKsTab('product-reviews');
        }

        if ($this->getKsProduct()->getTypeId() != 'grouped') {
            $this->removeKsTab('product-grouped');
        }

        if ($this->getKsProduct()->getTypeId() == 'grouped') {
            $this->removeKsTab('product-custom-options');
        }

        if ($this->getKsProduct()->getTypeId() == 'grouped' ||
            $this->getKsProduct()->getTypeId() == 'bundle') {
            $this->removeKsTab('product-configurable');
            $this->removeKsTab('product-scalable-quantity');
            $this->removeKsTab('downloadable-product');
        }

        if ($this->getKsProduct()->getTypeId() == 'configurable') {
            $this->removeKsTab('product-scalable-quantity');
            $this->removeKsTab('downloadable-product');
        }
        $ksAllowedProductTypes = $this->ksProductHelper->ksSellerAllowedProductType();
        foreach ($ksAllowedProductTypes as $ksAllowedProductType) {
            if ($ksAllowedProductType == 'configurable') {
                $ksConfigurableProductChecker = true;
            } elseif ($ksAllowedProductType == 'downloadable') {
                $ksDownloadableProductChecker = true;
            }
        }

        if (!$ksConfigurableProductChecker) {
            $this->removeKsTab('product-configurable');
        }
        if (!$ksDownloadableProductChecker) {
            $this->removeKsTab('downloadable-product');
        }
        return  $this->_ksTabs;
    }

    /**
     * Add tab by tab block name
     *
     * @param string $ksTab
     * @param string $ksTabId
     * @return void
     * @throws \Exception
     */
    protected function _ksAddTabByName($ksTab, $ksTabId)
    {
        if ($this->getChildBlock($ksTab)) {
            $KsTabContent = new \Magento\Framework\DataObject();
            $ksUicomponent = ($this->getChildBlock($ksTab)->getUicomponent()) ? $this->getChildBlock($ksTab)->getUicomponent() : '';

            $KsTabContent->setData(
                [
                    'label' => $this->getChildBlock($ksTab)->getLabel(),
                    'content' => ($ksTabId!='product-configurable') ? $this->getChildBlock($ksTab)->toHtml() : '',
                    'uicomponent' => $ksUicomponent,
                    'group_code' => $ksTabId,
                ]
            );
            $this->_ksTabs[$ksTabId] = $KsTabContent;
        } else {
            $this->_ksTabs[$ksTabId] = null;
        }
    }

    /**
     * check inventory single source mode
     * @return boolean
     */
    public function ksIsSingleSourceMode()
    {
        $ksProductType = $this->getKsProduct()->getTypeId();
        return $this->ksProductHelper->ksIsSingleSourceMode($ksProductType);
    }
}
