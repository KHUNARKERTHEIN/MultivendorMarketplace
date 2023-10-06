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

use Magento\Catalog\Model\AttributeHandler;
use Magento\Framework\App\RequestInterface;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory;

/**
 * Removing Seller Attribute From DataProvider for product attributes listing
 */
class KsRemoveProductAttributeList extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var RequestInterface
     */
    protected $ksRequest;

    protected $collection;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
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
        RequestInterface $ksRequest,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->ksRequest = $ksRequest;
        $this->collection = $ksCollectionFactory->create()
        ->addFieldToFilter('ks_seller_id', 0);
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        $this->collection->setExcludeSetFilter((int)$this->ksRequest->getParam('template_id', 0));
        $this->collection->getSelect()->setPart('order', []);

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
