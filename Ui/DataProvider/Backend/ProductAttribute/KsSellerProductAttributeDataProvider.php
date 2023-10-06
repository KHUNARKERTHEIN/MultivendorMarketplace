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
use Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory;
use Magento\Framework\App\Request\DataPersistorInterface;

/**
 * KsSellerProductAttributeDataProvider DataProvider Class For Seller Custom Product Attribute
 */
class KsSellerProductAttributeDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var ksRequestInterface
     */
    protected $ksRequest;

    /**
     * @var DataPersistorInterface
     */
    protected $ksDataPersistor;

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
        CollectionFactory $ksCollectionFactory,
        RequestInterface $ksRequest,
        DataPersistorInterface $ksDataPersistor,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->ksRequest = $ksRequest;
        $this->ksDataPersistor = $ksDataPersistor;
        // Get Seller Id
        $ksSellerId = $this->ksRequest->getParam('ks_seller_id');
        // Get Seller Id from DataPersistor
        $ksDataSellerId = $this->ksDataPersistor->get('ks_current_seller_id');
        // Terinary Operator to find seller id
        $ksCurrentSellerId = $ksSellerId ? $ksSellerId : $ksDataSellerId;
        $this->collection = $ksCollectionFactory->create()->addFieldToFilter('main_table.ks_seller_id', ['eq' => $ksCurrentSellerId])->addFieldToFilter('main_table.ks_attribute_approval_status', ['neq' => 4]);
        
        $ksJoinTable = $this->collection->getTable('catalog_eav_attribute');
        
        $this->collection->getSelect()->join(
            $ksJoinTable.' as ks_att',
            'main_table.attribute_id = ks_att.attribute_id'
        );
    }

    /**
     * Get Data
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
