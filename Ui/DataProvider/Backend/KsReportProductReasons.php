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

namespace Ksolves\MultivendorMarketplace\Ui\DataProvider\Backend;

use Ksolves\MultivendorMarketplace\Model\ResourceModel\KsReportProductReasons\CollectionFactory;

/**
 * Class KsReportProductReason
 */
class KsReportProductReasons extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var array
     */
    protected $loadedData;

    protected $collection;

    /**
     * DataProvider Constructor
     *
     * @param string                                                        $name
     * @param string                                                        $PrimaryFieldName
     * @param string                                                        $RequestFieldName
     * @param CollectionFactory                                             $groupCollectionFactory
     * @param array                                                         $Meta
     * @param array                                                         $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $groupCollectionFactory,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $groupCollectionFactory->create();
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }
    
    /**
     * @return array
     */
    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        $items = $this->collection->getItems();
        foreach ($items as $model) {
            $this->loadedData[$model->getId()] = $model->getData();
        }
        return $this->loadedData;
    }
}
