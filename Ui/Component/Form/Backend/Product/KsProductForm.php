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

namespace Ksolves\MultivendorMarketplace\Ui\Component\Form\Backend\Product;

use Ksolves\MultivendorMarketplace\Model\KsSellerFactory;
use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Ui\Component\Form\Element\Input;
use Magento\Ui\Component\Form\Element\Hidden;
use Magento\Ui\Component\Form\Field;
use Magento\Ui\Component\Form\Element\DataType\Text;
use Ksolves\MultivendorMarketplace\Helper\KsDataHelper;

/**
 * Class KsProductForm
 * @package Ksolves\MultivendorMarketplace\Ui\Component\Form\Backend\Product
 */
class KsProductForm extends \Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier
{
    /**
     * @var \Magento\Catalog\Model\Locator\LocatorInterface
     */
    protected $ksLocator;

    /**
     * @var CustomerFactory
     */
    protected $ksCustomerFactory;

    /**
     * @var KsSellerFactory
     */
    protected $ksSellerFactory;

    /**
     * @var KsSellerFactory
     */
    protected $ksStoreManager;

    /**
     * @var ScopeConfigInterface
     */
    protected $ksScopeConfig;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $ksCoreRegistry;

    /**
     * @var \Ksolves\MultivendorMarketplace\Model\KsProductFactory
     */
    protected $ksProductFactory;

    /**
     * @var \Ksolves\MultivendorMarketplace\Model\KsSeller
     */
    protected $ksSellerModel;

    /**
     * @var KsDataHelper
     */
    protected $ksDataHelper;

    /**
     * KsSeller constructor.
     * @param \Magento\Catalog\Model\Locator\LocatorInterface $ksLocator
     * @param CustomerFactory $ksCustomerFactory
     * @param KsSellerFactory $ksSellerFactory
     * @param StoreManagerInterface $ksStoreManager
     * @param ScopeConfigInterface $ksScopeConfig
     * @param \Magento\Framework\Registry $ksCoreRegistry
     * @param \Ksolves\MultivendorMarketplace\Model\KsProductFactory $ksProductFactory
     * @param \Ksolves\MultivendorMarketplace\Model\KsSellerFactory $ksSellerModel
     */
    public function __construct(
        \Magento\Catalog\Model\Locator\LocatorInterface $ksLocator,
        CustomerFactory $ksCustomerFactory,
        KsSellerFactory $ksSellerFactory,
        StoreManagerInterface $ksStoreManager,
        ScopeConfigInterface $ksScopeConfig,
        \Magento\Framework\Registry $ksCoreRegistry,
        \Ksolves\MultivendorMarketplace\Model\KsProductFactory $ksProductFactory,
        \Ksolves\MultivendorMarketplace\Model\KsSellerFactory $ksSellerModel,
        KsDataHelper $ksDataHelper
    ) {
        $this->ksLocator         = $ksLocator;
        $this->ksCoreRegistry    = $ksCoreRegistry;
        $this->ksCustomerFactory = $ksCustomerFactory;
        $this->ksSellerFactory   = $ksSellerFactory;
        $this->ksStoreManager    = $ksStoreManager;
        $this->ksScopeConfig     = $ksScopeConfig;
        $this->ksProductFactory  = $ksProductFactory;
        $this->ksSellerModel     = $ksSellerModel;
        $this->ksDataHelper      = $ksDataHelper;
    }

    /**
     * @param array $ksData
     * @return array|null
     */
    public function modifyData(array $ksData)
    {
        return array_replace_recursive(
            $ksData,
            [
                $this->ksLocator->getProduct()->getId() => [
                    self::DATA_SOURCE_DEFAULT => [
                        'ks_seller_id' => $this->ksProductFactory->create()->load($this->ksLocator->getProduct()->getId(), 'ks_product_id')->getKsSellerId()
                    ],
                ]
            ]
        );
    }

    /**
     * @param array $ksMeta
     * @return array
     */
    public function modifyMeta(array $ksMeta)
    {
        // // get price comaprison products collection
        // $ksProductCollection = $this->ksProductFactory->create()->getCollection()->addFieldToFilter('ks_parent_product_id', $this->ksLocator->getProduct()->getId())->addFieldToFilter('ks_parent_product_id', ['neq' => 0]);

        // // check the size of collection
        // if ($ksProductCollection->getSize() > 0) {
        //     $ksVisible = true;
        // } else {
        //     $ksVisible = false;
        // }
        // // hide or show the price comparison tab in product form
        // $ksMeta['ks_product_pricecomparison_section']['arguments']['data']['config']['visible'] = $ksVisible;

        $ksSellerId = $this->ksProductFactory->create()->load($this->ksLocator->getProduct()->getId(), 'ks_product_id')->getKsSellerId();

        if ($ksSellerId) {
            $ksEnableRelated = $this->ksDataHelper->getKsConfigProductSetting('ks_related_product');
            $ksEnableUpsell = $this->ksDataHelper->getKsConfigProductSetting('ks_up_sell_product');
            $ksEnableCrosssell = $this->ksDataHelper->getKsConfigProductSetting('ks_cross_sell_product');

            if (!$ksEnableRelated) {
                $ksMeta['related']['children']['related']['arguments']['data']['config']['visible'] = false;
            }

            if (!$ksEnableUpsell) {
                $ksMeta['related']['children']['upsell']['arguments']['data']['config']['visible'] = false;
            }

            if (!$ksEnableCrosssell) {
                $ksMeta['related']['children']['crosssell']['arguments']['data']['config']['visible'] = false;
            }

            if (!$ksEnableRelated && !$ksEnableUpsell && !$ksEnableCrosssell) {
                $ksMeta['related']['arguments']['data']['config']['visible'] = false;
            }
        }

        if (isset($ksSellerId) && $ksSellerId) {
            $ksSellerName = $this->ksCustomerFactory->create()->load($ksSellerId)->getName();
            $ksSellerEmail = $this->ksCustomerFactory->create()->load($ksSellerId)->getEmail();

            $ksSellerFullName = $ksSellerName." ( ".$ksSellerEmail." )";

            if ($ksName = $this->getGeneralPanelName($ksMeta)) {
                $ksMeta[$ksName]['children']['ks_seller_id']['arguments']['data']['config'] = [
                    'dataType' => Text::NAME,
                    'disableLabel' => true,
                    'filterOptions' => true,
                    'formElement' => Hidden::NAME,
                    'componentType' => Field::NAME,
                    'class'=> "Ksolves\MultivendorMarketplace\Ui\Component\Listing\Column\Backend\Product\KsSellerName",
                    'visible' => true,
                    'required' => 1,
                    'label' => __('Seller'),
                    'source' => $ksName,
                    'dataScope' => 'ks_seller_id',
                    'sortOrder' => $this->getNextAttributeSortOrder(
                        $ksMeta,
                        [ProductAttributeInterface::CODE_STATUS],
                        25
                    ),
                    'multiple' => false,
                    'disabled' =>true
                ];

                $ksMeta[$ksName]['children']['ks_seller_name']['arguments']['data']['config'] = [
                    'dataType' => Text::NAME,
                    'disableLabel' => true,
                    'filterOptions' => true,
                    'formElement' => Input::NAME,
                    'componentType' => Field::NAME,
                    'class'=> "Ksolves\MultivendorMarketplace\Ui\Component\Listing\Column\Backend\Product\KsSellerName",
                    'visible' => true,
                    'required' => 1,
                    'label' => __('Seller'),
                    'value' => $ksSellerFullName,
                    'sortOrder' => $this->getNextAttributeSortOrder(
                        $ksMeta,
                        [ProductAttributeInterface::CODE_STATUS],
                        25
                    ),
                    'multiple' => false,
                    'disabled' =>true
                ];
            }
        }
        return $ksMeta;
    }
}
