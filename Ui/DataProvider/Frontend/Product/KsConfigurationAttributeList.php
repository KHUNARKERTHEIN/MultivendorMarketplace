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

namespace Ksolves\MultivendorMarketplace\Ui\DataProvider\Frontend\Product;

/**
 * KsConfigurationAttributeList DataProvider Class
 */
class KsConfigurationAttributeList extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection
     */
    protected $collection;

    /**
     * @var \Magento\ConfigurableProduct\Model\ConfigurableAttributeHandler
     */
    protected $ksConfigurableAttributeHandler;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param \Magento\ConfigurableProduct\Model\ConfigurableAttributeHandler $ksConfigurableAttributeHandler
     * @param \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $ksAttributeColFactory
     * @param \Ksolves\MultivendorMarketplace\Helper\KsSellerHelper $ksSellerHelper,
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        \Magento\ConfigurableProduct\Model\ConfigurableAttributeHandler $ksConfigurableAttributeHandler,
        \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $ksAttributeColFactory,
        \Ksolves\MultivendorMarketplace\Helper\KsSellerHelper $ksSellerHelper,
        \Ksolves\MultivendorMarketplace\Helper\KsProductHelper $ksProductHelper,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->ksConfigurableAttributeHandler = $ksConfigurableAttributeHandler;
        // Get Current Seller Id
        $ksSellerId = $ksSellerHelper->getKsCustomerId();

        $ksAttributeArr = $ksProductHelper->ksGetAttribute($ksSellerId);
        // Get Collection
        $this->collection = $ksAttributeColFactory->create();
        // Filter Seller According to data
        $this->collection->addFieldToFilter(
            'frontend_input',
            'select'
        )->addFieldToFilter(
            'is_user_defined',
            1
        )->addFieldToFilter(
            'is_global',
            \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL
        )->addFieldToFilter(
            'main_table.attribute_id',
            ['in' => $ksAttributeArr]
        );
    }

    /**
     * @return \Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection
     */
    public function getCollection()
    {
        return $this->collection;
    }

    /**
     * Get Data
     */
    public function getData()
    {
        $ksItem = [];
        $ksSkippedItems = 0;
        foreach ($this->getCollection()->getItems() as $ksAttribute) {
            if ($this->ksConfigurableAttributeHandler->isAttributeApplicable($ksAttribute)) {
                $ksItem[] = $ksAttribute->toArray();
            } else {
                $ksSkippedItems++;
            }
        }
        return [
            'totalRecords' => $this->collection->getSize() - $ksSkippedItems,
            'items' => $ksItem
        ];
    }
}
