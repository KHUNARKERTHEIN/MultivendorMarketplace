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

use Magento\Bundle\Model\Product\LinksList;

/**
 * Class KsLinksList
 */
class KsLinksList
{
    /**
     * @var \Magento\Bundle\Api\Data\LinkInterfaceFactory
     */
    protected $ksLinkFactory;

    /**
     * @var Type
     */
    protected $ksType;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper
     */
    protected $ksDataObjectHelper;

    /**
     * @param \Magento\Bundle\Api\Data\LinkInterfaceFactory $linkFactory
     * @param Type $type
     * @param \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
     */
    public function __construct(
        \Magento\Bundle\Api\Data\LinkInterfaceFactory $ksLinkFactory,
        \Magento\Bundle\Model\Product\Type $ksType,
        \Magento\Framework\Api\DataObjectHelper $ksDataObjectHelper
    ) {
        $this->ksLinkFactory = $ksLinkFactory;
        $this->ksType = $ksType;
        $this->ksDataObjectHelper = $ksDataObjectHelper;
    }

    /**
     * Bundle Product Items Data
     *
     * @param \Magento\Catalog\Api\Data\ProductInterface $ksProduct
     * @param int $ksOptionId
     * @return \Magento\Bundle\Api\Data\LinkInterface[]
     */
    public function aroundGetItems(
        LinksList $ksSubject,
        callable $proceed,
        \Magento\Catalog\Api\Data\ProductInterface $ksProduct,
        $ksOptionId
    ) {
        $ksSelectionCollection = $this->ksType->getSelectionsCollection([$ksOptionId], $ksProduct);
        $ksSelectionCollection->setFlag('has_stock_status_filter', true);
        $ksProductLinks = [];
        /** @var \Magento\Catalog\Model\Product $ksSelection */
        foreach ($ksSelectionCollection as $ksSelection) {
            $ksBundledProductPrice = $ksSelection->getSelectionPriceValue();
            if ($ksBundledProductPrice <= 0) {
                $ksBundledProductPrice = $ksSelection->getPrice();
            }
            $ksSelectionPriceType = $ksProduct->getPriceType() ? $ksSelection->getSelectionPriceType() : null;
            $ksSelectionPrice = $ksBundledProductPrice ? $ksBundledProductPrice : null;

            /** @var \Magento\Bundle\Api\Data\LinkInterface $ksProductLink */
            $ksProductLink = $this->ksLinkFactory->create();
            $this->ksDataObjectHelper->populateWithArray(
                $ksProductLink,
                $ksSelection->getData(),
                \Magento\Bundle\Api\Data\LinkInterface::class
            );
            $ksProductLink->setIsDefault($ksSelection->getIsDefault())
                ->setId($ksSelection->getSelectionId())
                ->setQty($ksSelection->getSelectionQty())
                ->setCanChangeQuantity($ksSelection->getSelectionCanChangeQty())
                ->setPrice($ksSelectionPrice)
                ->setPriceType($ksSelectionPriceType);
            $ksProductLinks[] = $ksProductLink;
        }
        return $ksProductLinks;
    }
}
