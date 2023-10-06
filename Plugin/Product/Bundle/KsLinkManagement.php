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

namespace Ksolves\MultivendorMarketplace\Plugin\Product\Bundle;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Bundle\Api\Data\LinkInterface;
use Magento\Bundle\Api\Data\LinkInterfaceFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Bundle\Model\LinkManagement;
use Magento\Catalog\Model\Product;
use Magento\Bundle\Model\ResourceModel\BundleFactory;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class KsLinkManagement
 */
class KsLinkManagement
{
    /**
     * @var ProductRepositoryInterface
     */
    protected $ksProductRepository;

    /**
     * @var MetadataPool
     */
    private $ksMetadataPool;

    /**
     * @var BundleFactory
     */
    protected $ksBundleFactory;

    /**
     * @var LinkInterfaceFactory
     */
    protected $ksLinkFactory;

    /**
     * @var DataObjectHelper
     */
    protected $ksDataObjectHelper;

    /**
     * @param ProductRepositoryInterface $ksProductRepository
     * @param BundleFactory $ksBundleFactory
     * @param LinkInterfaceFactory $ksLinkFactory
     * @param DataObjectHelper $ksDataObjectHelper
     * @param MetadataPool $ksMetadataPool
     */
    public function __construct(
        ProductRepositoryInterface $ksProductRepository,
        BundleFactory $ksBundleFactory,
        LinkInterfaceFactory $ksLinkFactory,
        DataObjectHelper $ksDataObjectHelper,
        MetadataPool $ksMetadataPool
    ) {
        $this->ksProductRepository = $ksProductRepository;
        $this->ksBundleFactory = $ksBundleFactory;
        $this->ksLinkFactory = $ksLinkFactory;
        $this->ksDataObjectHelper = $ksDataObjectHelper;
        $this->ksMetadataPool = $ksMetadataPool;
    }

    /**
     * @inheritDoc
     */
    public function aroundGetChildren(
        LinkManagement $ksSubject,
        callable $proceed,
        $ksProductSku,
        $ksOptionId = null
    ) {
        $ksProduct = $this->ksProductRepository->get($ksProductSku, true);
        if ($ksProduct->getTypeId() != Product\Type::TYPE_BUNDLE) {
            throw new InputException(__('This is implemented for bundle products only.'));
        }

        $ksChildrenList = [];
        foreach ($this->getKsOptions($ksProduct) as $ksOption) {
            if (!$ksOption->getSelections() || ($ksOptionId !== null && $ksOption->getOptionId() != $ksOptionId)) {
                continue;
            }
            /** @var Product $selection */
            foreach ($ksOption->getSelections() as $ksSelection) {
                $ksChildrenList[] = $this->buildLink($ksSelection, $ksProduct);
            }
        }
        return $ksChildrenList;
    }

    /**
     * @inheritDoc
     */
    public function aroundRemoveChild(
        LinkManagement $ksSubject,
        callable $proceed,
        $ksSku,
        $ksOptionId,
        $ksChildSku
    ) {
        $ksProduct = $this->ksProductRepository->get($ksSku, true);

        if ($ksProduct->getTypeId() != Product\Type::TYPE_BUNDLE) {
            throw new InputException(__('The product with the "%1" SKU isn\'t a bundle product.', $ksSku));
        }

        $ksExcludeSelectionIds = [];
        $ksUsedProductIds = [];
        $ksRemoveSelectionIds = [];
        foreach ($this->getKsOptions($ksProduct) as $ksOption) {
            /** @var Selection $selection */
            foreach ($ksOption->getSelections() as $ksSelection) {
                if ((strcasecmp($ksSelection->getSku(), $ksChildSku) == 0) && ($ksSelection->getOptionId() == $ksOptionId)) {
                    $ksRemoveSelectionIds[] = $ksSelection->getSelectionId();
                    $ksUsedProductIds[] = $ksSelection->getProductId();
                    continue;
                }
                $ksExcludeSelectionIds[] = $ksSelection->getSelectionId();
            }
        }

        if (empty($ksRemoveSelectionIds)) {
            throw new NoSuchEntityException(
                __("The bundle product doesn't exist. Review your request and try again.")
            );
        }
        $ksLinkField = $this->ksMetadataPool->getMetadata(ProductInterface::class)->getLinkField();

        /* @var $resource Bundle */
        $resource = $this->ksBundleFactory->create();
        $resource->dropAllUnneededSelections($ksProduct->getData($ksLinkField), $ksExcludeSelectionIds);
        $resource->removeProductRelations($ksProduct->getData($ksLinkField), array_unique($ksUsedProductIds));

        return true;
    }

    /**
     * Build bundle link between two products
     *
     * @param Product $ksSelection
     * @param Product $ksProduct
     *
     * @return LinkInterface
     */
    private function buildLink(Product $ksSelection, Product $ksProduct)
    {
        $ksSelectionPriceType = $ksSelectionPrice = null;

        /** @var Selection $ksProduct */
        if ($ksProduct->getPriceType()) {
            $ksSelectionPriceType = $ksSelection->getSelectionPriceType();
            $ksSelectionPrice = $ksSelection->getSelectionPriceValue();
        }

        /** @var LinkInterface $link */
        $ksLink = $this->ksLinkFactory->create();
        $this->ksDataObjectHelper->populateWithArray(
            $ksLink,
            $ksSelection->getData(),
            LinkInterface::class
        );
        $ksLink->setIsDefault($ksSelection->getIsDefault())
            ->setId($ksSelection->getSelectionId())
            ->setQty($ksSelection->getSelectionQty())
            ->setCanChangeQuantity($ksSelection->getSelectionCanChangeQty())
            ->setPrice($ksSelectionPrice)
            ->setPriceType($ksSelectionPriceType);

        return $ksLink;
    }

    /**
     * Get bundle product options
     *
     * @param ProductInterface $ksProduct
     *
     * @return OptionInterface[]
     */
    private function getKsOptions(ProductInterface $ksProduct)
    {
        /** @var Type $productTypeInstance */
        $ksProductTypeInstance = $ksProduct->getTypeInstance();
        $ksProductTypeInstance->setStoreFilter(
            $ksProduct->getStoreId(),
            $ksProduct
        );
        $ksOptionCollection = $ksProductTypeInstance->getOptionsCollection($ksProduct);

        $ksSelectionCollection = $ksProductTypeInstance->getSelectionsCollection(
            $ksProductTypeInstance->getOptionsIds($ksProduct),
            $ksProduct
        );
        $ksSelectionCollection->setFlag('has_stock_status_filter', true);
        $ksSelectionCollection->setFlag('seller_approved_status_filter', true);

        return $ksOptionCollection->appendSelections($ksSelectionCollection, true);
    }
}
