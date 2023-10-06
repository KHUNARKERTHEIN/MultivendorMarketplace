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

namespace Ksolves\MultivendorMarketplace\Block\Product\Form\Tabs;

use Ksolves\MultivendorMarketplace\Helper\KsDataHelper;
use Ksolves\MultivendorMarketplace\Helper\KsProductHelper;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class KsFixedPrice
 * @package Ksolves\MultivendorMarketplace\Block\Product\Form\Tabs
 */
class KsFixedPrice extends \Ksolves\MultivendorMarketplace\Block\Product\Form\KsTabs
{

    /**
     * @var array|null
     */
    protected $ksWebsites = null;

    /**
     * @var null
     */
    protected $ksCountry = null;

    /**
     * @var \Magento\Directory\Helper\Data
     */
    protected $ksDirectoryHelper;

    /**
     * @var StoreManagerInterface
     */
    protected $ksStoreManager;

    /**
     * @var \Magento\Directory\Model\Config\Source\Country
     */
    protected $ksSourceCountry;

    /**
     * KsFixedPrice constructor.
     * @param Context $ksContext
     * @param Registry $ksRegistry
     * @param \Magento\Directory\Helper\Data $ksDirectoryHelper
     * @param \Magento\Directory\Model\Config\Source\Country $ksSourceCountry
     * @param StoreManagerInterface $ksStoreManager
     * @param KsDataHelper $ksDataHelper
     * @param KsProductHelper $ksProductHelper
     * @param DataPersistorInterface $ksDataPersistor
     * @param array $ksData
     */
    public function __construct(
        Context $ksContext,
        Registry $ksRegistry,
        \Magento\Directory\Helper\Data $ksDirectoryHelper,
        \Magento\Directory\Model\Config\Source\Country $ksSourceCountry,
        StoreManagerInterface $ksStoreManager,
        KsDataHelper $ksDataHelper,
        KsProductHelper $ksProductHelper,
        DataPersistorInterface $ksDataPersistor,
        array $ksData = []
    ) {
        $this->ksDirectoryHelper = $ksDirectoryHelper;
        $this->ksStoreManager = $ksStoreManager;
        $this->ksSourceCountry = $ksSourceCountry;
        parent::__construct(
            $ksContext,
            $ksRegistry,
            $ksDataHelper,
            $ksProductHelper,
            $ksDataPersistor,
            $ksData
        );
    }

    /**
     * Check group price attribute scope is global
     *
     * @return bool
     */
    public function isScopeGlobal($ksAttributeCode)
    {
        $objectManager =  \Magento\Framework\App\ObjectManager::getInstance();

        $ksAttribute = $objectManager->get('\Magento\Eav\Model\Config')
            ->getAttribute('catalog_product', $ksAttributeCode);

        return $ksAttribute->isScopeGlobal();
    }

    /**
     * @return array|null
     */
    public function getKsWebsites($ksAttributeCode)
    {
        if ($this->ksWebsites !== null) {
            return $this->ksWebsites;
        }

        $this->ksWebsites = [
            0 => ['name' => __('All Websites'), 'currency' => $this->ksDirectoryHelper->getBaseCurrencyCode()]
        ];

        if (!$this->isScopeGlobal($ksAttributeCode) && $this->getKsProduct()->getStoreId()) {
            /** @var $website \Magento\Store\Model\Website */
            $ksWebsite = $this->ksStoreManager->getStore($this->getKsProduct()->getStoreId())->getWebsite();

            $this->ksWebsites[$ksWebsite->getId()] = [
                'name' => $ksWebsite->getName(),
                'currency' => $ksWebsite->getBaseCurrencyCode()
            ];
        } elseif (!$this->isScopeGlobal($ksAttributeCode)) {
            $ksWebsites = $this->ksStoreManager->getWebsites();
            $ksProductWebsiteIds = $this->getKsProduct()->getWebsiteIds();
            foreach ($ksWebsites as $ksWebsite) {
                /** @var $website \Magento\Store\Model\Website */
                if (!in_array($ksWebsite->getId(), $ksProductWebsiteIds)) {
                    continue;
                }
                $this->ksWebsites[$ksWebsite->getId()] = [
                    'name' => $ksWebsite->getName(),
                    'currency' => $ksWebsite->getBaseCurrencyCode()
                ];
            }
        }

        return $this->ksWebsites;
    }

    /**
     * @return bool
     */
    public function isKsMultiWebsites()
    {
        return !$this->ksStoreManager->hasSingleStore();
    }

    /**
     * @return array|null
     */
    public function getKsCountries()
    {
        if (null === $this->ksCountry) {
            $this->ksCountry = $this->ksSourceCountry->toOptionArray();
        }

        return $this->ksCountry;
    }

    /**
     * @param $ksAttributeCode
     * @return array
     */
    public function getKsFixedProductValue($ksAttributeCode)
    {
        return $this->getKsProduct()->getCustomAttribute($ksAttributeCode) ? $this->getKsProduct()->getCustomAttribute($ksAttributeCode)->getValue() : [];
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getKsStateValue()
    {
        return $this->ksDirectoryHelper->getRegionJson();
    }
}
