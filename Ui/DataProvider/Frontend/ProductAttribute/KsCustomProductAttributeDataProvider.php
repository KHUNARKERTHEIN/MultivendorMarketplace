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

namespace Ksolves\MultivendorMarketplace\Ui\DataProvider\Frontend\ProductAttribute;

use Magento\Catalog\Model\AttributeHandler;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory;
use Ksolves\MultivendorMarketplace\Helper\KsSellerHelper;

/**
 * KsCustomProductAttributeDataProvider DataProvider Class ForCustom Product Attribute
 */
class KsCustomProductAttributeDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     *
     * @var \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory
     */
    protected $collection;

    /**
     * @var KsSellerHelper
     */
    protected $ksSellerHelper;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $ksRequestFieldName
     * @param CollectionFactory $ksCollectionFactory
     * @param RequestInterface $ksRequest
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $ksCollectionFactory,
        KsSellerHelper $ksSellerHelper,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->ksSellerHelper = $ksSellerHelper;
        $ksSellerId = $this->ksSellerHelper->getKsCustomerId();
        $this->collection = $ksCollectionFactory->create();
        $ksJoinTable = $this->collection->getTable('catalog_eav_attribute');
        $this->collection->getSelect()->join(
            $ksJoinTable.' as ks_att',
            'main_table.attribute_id = ks_att.attribute_id'
        );
        $this->collection->addFieldToFilter('ks_seller_id', ['eq' => $ksSellerId]);
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        $ksItems = [];
        foreach ($this->getCollection()->getItems() as $ksAttribute) {
            $ksItems[] = $ksAttribute->toArray();
        }

        return [
            'totalRecords' => $this->collection->getSize(),
            'items' => $ksItems
        ];
    }
}
