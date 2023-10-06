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

use Magento\Framework\Api\Filter;
use Magento\Framework\App\RequestInterface;
use Magento\Review\Model\ResourceModel\Review\Product\CollectionFactory;

/**
 * Class KsProductReview
 * @package Ksolves\MultivendorMarketplace\Ui\DataProvider\Frontend\Product
 */
class KsProductReview extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var CollectionFactory
     * @since 100.1.0
     */
    protected $ksCollectionFactory;

    /**
     * @var RequestInterface
     * @since 100.1.0
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
        $this->ksCollectionFactory = $ksCollectionFactory;
        $this->collection = $this->ksCollectionFactory->create();
        $this->ksRequest = $ksRequest;
    }

    /**
     * {@inheritdoc}
     * @since 100.1.0
     */
    public function getData()
    {
        $this->getCollection()->addEntityFilter($this->ksRequest->getParam('current_product_id', 0))
            ->addStoreData();

        $arrItems = [
            'totalRecords' => $this->getCollection()->getSize(),
            'items' => [],
        ];

        foreach ($this->getCollection() as $item) {
            $arrItems['items'][] = $item->toArray([]);
        }
        return $arrItems;
    }

    /**
     * Returns prepared field name
     *
     * @param string $name
     * @return string
     */
    private function getPreparedField(string $name): string
    {
        $preparedName = '';

        if (in_array($name, ['review_id', 'created_at', 'status_id'])) {
            $preparedName = 'rt.' . $name;
        } elseif (in_array($name, ['title', 'nickname', 'detail'])) {
            $preparedName = 'rdt.' . $name;
        } elseif ($name === 'review_created_at') {
            $preparedName = 'rt.created_at';
        }

        return $preparedName ?: $name;
    }

    /**
     * @inheritDoc
     */
    public function addOrder($field, $direction)
    {
        $this->getCollection()->setOrder($this->getPreparedField($field), $direction);
    }

    /**
     * @inheritdoc
     * @since 100.1.0
     */
    public function addFilter(Filter $filter)
    {
        $field = $filter->getField();
        $filter->setField($this->getPreparedField($field));

        parent::addFilter($filter);
    }
}
