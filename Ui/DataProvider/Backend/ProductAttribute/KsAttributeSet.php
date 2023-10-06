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

namespace Ksolves\MultivendorMarketplace\Ui\DataProvider\Backend\ProductAttribute;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\UrlInterface;
use Magento\Ui\Component\Form\Field;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Ksolves\MultivendorMarketplace\Model\KsProductFactory;
use Ksolves\MultivendorMarketplace\Helper\KsDataHelper;

/**
 * KsAttributeSet Class for Modifing Seller Product Attribute Set
 */
class KsAttributeSet extends AbstractModifier
{
    /**
     * Sort order of "Attribute Set" field inside of fieldset
     */
    const KS_ATTRIBUTE_SET_FIELD_ORDER = 30;

    /**
     * @var Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory
     */
    protected $ksAttributeSetCollectionFactory;

    /**
     * @var Magento\Framework\UrlInterface
     */
    protected $ksUrlBuilder;

    /**
     * @var Magento\Catalog\Model\Locator\LocatorInterface
     */
    protected $ksLocator;

    /**
     * @var \Ksolves\MultivendorMarketplace\Model\KsProductFactory
     */
    protected $ksProductFactory;

    /**
     * @var \Ksolves\MultivendorMarketplace\Helper\KsDataHelper
     */
    protected $ksDataHelper;

    /**
     * @param LocatorInterface $ksLocator
     * @param CollectionFactory $ksAttributeSetCollectionFactory
     * @param UrlInterface $ksUrlBuilder
     * @param KsProductFactory $ksProductFactory
     * @param KsDataHelper $ksDataHelper
     */
    public function __construct(
        LocatorInterface $ksLocator,
        CollectionFactory $ksAttributeSetCollectionFactory,
        UrlInterface $ksUrlBuilder,
        KsProductFactory $ksProductFactory,
        KsDataHelper $ksDataHelper
    ) {
        $this->ksLocator = $ksLocator;
        $this->ksAttributeSetCollectionFactory = $ksAttributeSetCollectionFactory;
        $this->ksUrlBuilder = $ksUrlBuilder;
        $this->ksProductFactory = $ksProductFactory;
        $this->ksDataHelper = $ksDataHelper;
    }

    /**
     * Return options for select
     *
     * @return array
     * @since 101.0.0
     */
    public function getOptions()
    {
        /** @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection $collection */
        $ksCollection = $this->ksAttributeSetCollectionFactory->create();
        $ksSellerAttribute = $this->ksSellerAttributeSet();
        $ksCollection->setEntityTypeFilter($this->ksLocator->getProduct()->getResource()->getTypeId())
            ->addFieldToFilter('attribute_set_id', ['in' => $ksSellerAttribute])
            ->addFieldToSelect('attribute_set_id', 'value')
            ->addFieldToSelect('attribute_set_name', 'label')
            ->setOrder(
                'attribute_set_name',
                \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection::SORT_ORDER_ASC
            );

        $ksCollectionData = $ksCollection->getData() ?? [];
        // Get Seller Set Id
        $ksSellerSetId = $this->ksSellerSetId();
        // Get Seller id from Product Id
        $ksSellerId = $this->ksProductFactory->create()->load($this->ksLocator->getProduct()->getId(), 'ks_product_id')->getKsSellerId();
        // Check SellerId is not empty
        if ($ksSellerId) {
            foreach ($ksCollectionData as $ksKey => $ksRecord) {
                $ksValue = $ksRecord['value'];
                if (!in_array($ksValue, $ksSellerSetId)) {
                    $ksCollectionData[$ksKey]['value'] = $ksRecord['value'];
                    $ksCollectionData[$ksKey]['label'] = $ksRecord['label'].' [Admin]';
                }
            }
        }
        return $ksCollectionData;
    }

    /**
     * @inheritdoc
     * @since 101.0.0
     */
    public function modifyMeta(array $ksMeta)
    {
        if ($ksName = $this->getGeneralPanelName($ksMeta)) {
            $ksMeta[$ksName]['children']['attribute_set_id']['arguments']['data']['config']  = [
                'component' => 'Magento_Catalog/js/components/attribute-set-select',
                'disableLabel' => true,
                'filterOptions' => true,
                'elementTmpl' => 'ui/grid/filters/elements/ui-select',
                'formElement' => 'select',
                'componentType' => Field::NAME,
                'options' => $this->getOptions(),
                'visible' => 1,
                'required' => 1,
                'label' => __('Attribute Set'),
                'source' => $ksName,
                'dataScope' => 'attribute_set_id',
                'filterUrl' => $this->ksUrlBuilder->getUrl('catalog/product/suggestAttributeSets', ['isAjax' => 'true']),
                'sortOrder' => $this->getNextAttributeSortOrder(
                    $ksMeta,
                    [ProductAttributeInterface::CODE_STATUS],
                    self::KS_ATTRIBUTE_SET_FIELD_ORDER
                ),
                'multiple' => false,
                'disabled' => $this->ksLocator->getProduct()->isLockedAttribute('attribute_set_id'),
            ];
        }
        return $ksMeta;
    }

    /**
     *
     */
    public function modifyData(array $ksData)
    {
        return array_replace_recursive(
            $ksData,
            [
                $this->ksLocator->getProduct()->getId() => [
                    self::DATA_SOURCE_DEFAULT => [
                        'attribute_set_id' => $this->ksLocator->getProduct()->getAttributeSetId()
                    ],
                ]
            ]
        );
    }

    /**
     * Get Seller and Default Attribute Set Id
     */
    public function ksSellerAttributeSet()
    {
        // Get Seller id from Product Id
        $ksSellerId = $this->ksProductFactory->create()->load($this->ksLocator->getProduct()->getId(), 'ks_product_id')->getKsSellerId();
        // Create Collection
        $ksCollection = $this->ksAttributeSetCollectionFactory->create();
        // Array for storing value of attribute
        $ksArray = [];
        // Filter Collection
        $ksSellerAttributeArray = $ksCollection->setEntityTypeFilter($this->ksLocator->getProduct()->getResource()->getTypeId())
            ->addFieldToFilter('ks_seller_id', $ksSellerId);
        // Iterate collection
        foreach ($ksSellerAttributeArray as $ksValue) {
            $ksArray[] = $ksValue->getAttributeSetId();
        }
        // Get Default array
        $ksDefaultArray = $this->ksDataHelper->getKsDefaultAttributes();
        if (!empty($ksDefaultArray)) {
            foreach ($ksDefaultArray as $ksValue) {
                $ksArray[] = $ksValue;
            }
        }
        return $ksArray;
    }

    /**
     * Get Seller Set Id
     */
    public function ksSellerSetId()
    {
        // Get Seller id from Product Id
        $ksSellerId = $this->ksProductFactory->create()->load($this->ksLocator->getProduct()->getId(), 'ks_product_id')->getKsSellerId();
        // Create Collection
        $ksCollection = $this->ksAttributeSetCollectionFactory->create();
        // Array for storing value of attribute
        $ksArray = [];
        // Filter Collection
        $ksSellerAttributeArray = $ksCollection->setEntityTypeFilter($this->ksLocator->getProduct()->getResource()->getTypeId())
            ->addFieldToFilter('ks_seller_id', $ksSellerId);
        // Iterate collection
        foreach ($ksSellerAttributeArray as $ksValue) {
            $ksArray[] = $ksValue->getAttributeSetId();
        }
        return $ksArray;
    }
}
