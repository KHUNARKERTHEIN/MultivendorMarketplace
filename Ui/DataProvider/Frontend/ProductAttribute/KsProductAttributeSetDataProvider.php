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
use Magento\Framework\App\RequestInterface;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory;
use Ksolves\MultivendorMarketplace\Helper\KsDataHelper;
use Ksolves\MultivendorMarketplace\Helper\KsSellerHelper;
use Magento\Framework\View\Element\UiComponent\DataProvider\FulltextFilter;

/**
 * KsProductAttributeSetDataProvider DataProvider Class For Seller Custom Product Attribute
 */
class KsProductAttributeSetDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var ksRequestInterface
     */
    protected $ksRequest;

    /**
     * @var KsDataHelper
     */
    protected $ksDataHelper;

    /**
     * @var KsDataHelper
     */
    protected $ksSellerHelper;

    /**
     * @var FulltextFilter
     */
    protected $ksFulltextFilter;

    /**
     * @var Collection
     */
    protected $collection;

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
        RequestInterface $ksRequest,
        CollectionFactory $ksCollectionFactory,
        FulltextFilter $ksFulltextFilter,
        KsSellerHelper $ksSellerHelper,
        KsDataHelper $ksDataHelper,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->ksRequest = $ksRequest;
        $this->ksFulltextFilter = $ksFulltextFilter;
        $this->ksDataHelper = $ksDataHelper;
        $this->ksSellerHelper = $ksSellerHelper;
        $ksSellerId = $this->ksSellerHelper->getKsCustomerId();
        $this->collection = $ksCollectionFactory->create()->addFieldToFilter('entity_type_id', 4)->addFieldToFilter('ks_seller_id', $ksSellerId);
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        $ksItems = [];
        // For Making Full Text Search work
        $ksSearch = $this->ksRequest->getParam('search');
        if ($ksSearch) {
            $this->getCollection()->addFieldToFilter('attribute_set_name', ['like'=> '%'.$ksSearch.'%']);
        }
        foreach ($this->getCollection()->getItems() as $ksAttribute) {
            $ksItems[] = $ksAttribute->toArray();
        }
        $ksData = [
            'totalRecords' => $this->collection->getSize(),
            'items' => $ksItems
        ];
        return $ksData;
    }

    /**
     * Add Filter
     * @param \Magento\Framework\Api\Filter $filter
     */
    public function addFilter(\Magento\Framework\Api\Filter $filter)
    {
        if ('fulltext' == $filter->getField()) {
            $this->ksFulltextFilter->apply($this->collection, $filter);
        } else {
            parent::addFilter($filter);
        }
    }
}
