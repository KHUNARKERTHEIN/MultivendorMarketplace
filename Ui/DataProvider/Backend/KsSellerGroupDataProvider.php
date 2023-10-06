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

use Magento\Framework\App\Request\Http;

/**
 * Class DataProvider of KsSellerGroup
 */
class KsSellerGroupDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var \Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSellerGroup\CollectionFactory
     */
    protected $collection;

    /**
     * @var array
     */
    protected $ksLoadedData;

    /**
     * @var Http
     */
    protected $ksRequest;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param \Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSellerGroup\CollectionFactory $ksCollectionFactory
     * @param Http $ksRequest
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        \Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSellerGroup\CollectionFactory $ksCollectionFactory,
        Http $ksRequest,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $ksCollectionFactory->create();
        $this->ksRequest = $ksRequest;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * Get data
     * @return array
     */
    public function getData()
    {
        if (isset($this->ksLoadedData)) {
            return $this->ksLoadedData;
        }
        $ksItems = $this->collection->getItems();

        foreach ($ksItems as $ksData) {
            $this->ksLoadedData[$ksData->getId()]['ks_seller_group'] = $ksData->getData();
        }

        return $this->ksLoadedData;
    }

    /**
     * return disabled field for seller group name of general group
     * Get data
     * @return array
     */
    public function getMeta() : array
    {
        $ksMeta = parent::getMeta();

        // check if Some field should be editable
        
        if (($this->ksRequest->getParam('id') == 1)) {
            $ksIsEnabled = true;
        } else {
            $ksIsEnabled = false;
        }
        
        $ksMeta['ks_seller_group']['children']['ks_status']['arguments']['data']['config']['disabled'] = $ksIsEnabled;

        return $ksMeta;
    }
}
