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

namespace Ksolves\MultivendorMarketplace\Block\Product\Form\Tabs\Bundle;

use Magento\Backend\Block\Template\Context;
use Magento\Bundle\Model\Source\Option\Type;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Config\Model\Config\Source\Yesno;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Framework\Registry;
use Magento\Bundle\Model\ResourceModel\Selection\CollectionFactory;
use Magento\Bundle\Model\ResourceModel\Selection\Collection\FilterApplier as SelectionCollectionFilterApplier;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\App\ObjectManager;

/**
 * Class KsBundleOptions
 * @package Ksolves\MultivendorMarketplace\Block\Product\Form\Tabs\Bundle
 */
class KsBundleOptions extends \Magento\Bundle\Block\Adminhtml\Catalog\Product\Edit\Tab\Bundle\Option
{
    /**
     * @var string
     */
    protected $_template = 'Ksolves_MultivendorMarketplace::product/form/tabs/bundle/ks-bundle-option.phtml';

    /**
     * @var ProductRepositoryInterface
     */
    protected $ksProductRepository;

    /**
     * True when selections appended
     *
     * @var bool
     */
    protected $_ksSelectionsAppended = false;

    /**
    * @var CollectionFactory
    */
    protected $ksBundleCollection;

    /**
     * @var SelectionCollectionFilterApplier
     */
    private $ksSelectionCollectionFilterApplier;

    /**
    * @var \Magento\Catalog\Helper\Data
    */
    private $ksCatalogData;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $ksStoreManager;

    /**
     * @var MetadataPool
     */
    private $ksMetadataPool;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Config\Model\Config\Source\Yesno $yesno
     * @param \Magento\Bundle\Model\Source\Option\Type $optionTypes
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     * @param JsonHelper|null $jsonHelper
     * @param ProductRepositoryInterface $ksProductRepository
     * @param CollectionFactory $ksBundleCollection
     * @param SelectionCollectionFilterApplier $ksSelectionCollectionFilterApplier = null
     * @param MetadataPool $ksMetadataPool = null
     * @param \Magento\Catalog\Helper\Data $ksCatalogData
     * @param \Magento\Store\Model\StoreManagerInterface $ksStoreManager
     */
    public function __construct(
        Context $ksContext,
        Yesno $ksYesno,
        Type $ksOptionTypes,
        Registry $ksRegistry,
        ProductRepositoryInterface $ksProductRepository,
        CollectionFactory $ksBundleCollection,
        \Magento\Catalog\Helper\Data $ksCatalogData,
        \Magento\Store\Model\StoreManagerInterface $ksStoreManager,
        array $ksData = [],
        ?JsonHelper $ksJsonHelper = null,
        SelectionCollectionFilterApplier $ksSelectionCollectionFilterApplier = null,
        MetadataPool $ksMetadataPool = null
    ) {
        $this->ksProductRepository = $ksProductRepository;
        $this->ksBundleCollection = $ksBundleCollection;
        $this->ksSelectionCollectionFilterApplier = $ksSelectionCollectionFilterApplier
            ?: ObjectManager::getInstance()->get(SelectionCollectionFilterApplier::class);
        $this->ksMetadataPool = $ksMetadataPool
            ?: ObjectManager::getInstance()->get(MetadataPool::class);
        $this->ksCatalogData = $ksCatalogData;
        $this->ksStoreManager = $ksStoreManager;
        parent::__construct($ksContext, $ksYesno, $ksOptionTypes, $ksRegistry, $ksData, $ksJsonHelper);
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getSelectionHtml()
    {
        return $this->getLayout()
            ->createBlock("Ksolves\MultivendorMarketplace\Block\Product\Form\Tabs\Bundle\KsSelection")
            ->setTemplate('Ksolves_MultivendorMarketplace::product/form/tabs/bundle/ks-selection.phtml')
            ->toHtml();
    }

    /**
     * Get Type Select Html
     *
     * @return mixed
     */
    public function getTypeSelectHtml()
    {
        $select = $this->getLayout()->createBlock(
            \Magento\Framework\View\Element\Html\Select::class
        )->setData(
            [
                'id' => $this->getFieldId() . '_<%- data.id %>_type',
                'class' => 'select select-product-option-type required-option-select',
            ]
        )->setName(
            $this->getFieldName() . '[' . $this->getFieldName() . ']' . '[<%- data.id %>][type]'
        )->setOptions(
            $this->_optionTypes->toOptionArray()
        );

        return $select->getHtml();
    }

    /**
     * Retrieve bundle selections collection based on used options
     * Skip here website/store filter
     * @param array $optionIds
     * @param \Magento\Catalog\Model\Product $product
     * @return \Magento\Bundle\Model\ResourceModel\Selection\Collection
     */
    public function getKsSelectionsCollection($ksOptionIds, $ksProduct)
    {
        $ksStoreId = $ksProduct->getStoreId();

        $ksMetadata = $this->ksMetadataPool->getMetadata(
            \Magento\Catalog\Api\Data\ProductInterface::class
        );

        /** @var Selections $ksSelectionsCollection */
        $ksSelectionsCollection = $this->ksBundleCollection->create();
        $ksSelectionsCollection
        ->addAttributeToSelect('tax_class_id') //used for calculation item taxes in Bundle with Dynamic Price
        ->setFlag('product_children', true)
        ->setPositionOrder()
        //->addStoreFilter($this->getStoreFilter($ksProduct))
        ->setStoreId($ksStoreId)
        ->addFilterByRequiredOptions()
        ->setOptionIdsFilter($ksOptionIds);

        $this->ksSelectionCollectionFilterApplier->apply(
            $ksSelectionsCollection,
            'parent_product_id',
            $ksProduct->getData($ksMetadata->getLinkField())
        );

        if (!$this->ksCatalogData->isPriceGlobal() && $ksStoreId) {
            $ksWebsiteId = $this->ksStoreManager->getStore($ksStoreId)
                ->getWebsiteId();
            $ksSelectionsCollection->joinPrices($ksWebsiteId);
        }

        return $ksSelectionsCollection;
    }

    /**
     * Retrieve list of bundle product options
     *
     * @return array
     */
    public function getOptions()
    {
        if (!$this->_options) {
            /** @var \Magento\Bundle\Model\ResourceModel\Option\Collection $optionCollection */
            $ksOptionCollection = $this->getProduct()->getTypeInstance()->getOptionsCollection($this->getProduct());

            $ksSelectionsCollection = $this->getKsSelectionsCollection(
                $this->getProduct()->getTypeInstance()->getOptionsIds($this->getProduct()),
                $this->getProduct()
            );

            $this->_options = $this->appendSelections($ksSelectionsCollection, $ksOptionCollection);

            if ($this->getCanReadPrice() === false) {
                foreach ($this->_options as $option) {
                    if ($option->getSelections()) {
                        foreach ($option->getSelections() as $selection) {
                            $selection->setCanReadPrice($this->getCanReadPrice());
                            $selection->setCanEditPrice($this->getCanEditPrice());
                        }
                    }
                }
            }
        }
        return $this->_options;
    }

    /**
     * Append selection to options
     *
     * @param \Magento\Bundle\Model\ResourceModel\Selection\Collection $selectionsCollection
     * @param bool $stripBefore indicates to reload
     * @param bool $appendAll indicates do we need to filter by saleable and required custom options
     * @return \Magento\Framework\DataObject[]
     */
    public function appendSelections($ksSelectionsCollection, $ksOptionCollection, $ksAppendAll = true)
    {
        if (!$this->_ksSelectionsAppended) {
            foreach ($ksSelectionsCollection->getData() as $key => $ksSelection) {
                $ksOption = $ksOptionCollection->getItemById($ksSelection['option_id']);

                if ($ksOption) {
                    $ksStoreId = $this->getRequest()->getParam('store', 0);
                    $ksProduct = $this->ksProductRepository->getById($ksSelection['entity_id'], true, $ksStoreId);

                    $ksSelection['name'] = $ksProduct->getName();
                    if (isset($ksSelection['selection_price_value']) && $ksSelection['selection_price_value']) {
                        $ksSelection['selection_price_value'] = number_format((float)$ksSelection['selection_price_value'], 2, '.', '');
                    }
                    $ksSelection['selection_qty'] = number_format((float)$ksSelection['selection_qty'], 0, '.', '');

                    if (!$ksOption->hasData('selections')) {
                        $ksOption->setData('selections', []);
                    }
                    $ksSelections = $ksOption->getData('selections');
                    $ksSelections[] = $ksSelection;
                    $ksOption->setSelections($ksSelections);
                }
            }
            $this->_ksSelectionsAppended = true;
        }

        return $ksOptionCollection->getItems();
    }
}
