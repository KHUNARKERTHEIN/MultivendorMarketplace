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

namespace Ksolves\MultivendorMarketplace\Model\HowItWorks;

use Ksolves\MultivendorMarketplace\Model\ResourceModel\KsHowItWorks\CollectionFactory;
use Magento\Store\Model\StoreManagerInterface;

/**
 * KsDataProvider
 */
class KsDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var StoreManagerInterface
     */
    protected $ksStoreManager;

    protected $collection;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param StoreManagerInterface $ksStoreManager
     * @param CollectionFactory $ksCollectionFactory
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        StoreManagerInterface $ksStoreManager,
        CollectionFactory $ksCollectionFactory,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $ksCollectionFactory->create();
        $this->ksStoreManager   = $ksStoreManager;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        $ksItems = $this->collection->getItems();
        $this->loadedData = [];
        foreach ($ksItems as $ksData) {
            $this->loadedData[$ksData->getId()]['howitworks'] = $ksData->getData();
            if ($ksData->getKsPicture()) {
                $ksImageUrl = $this->getMediaUrl().$ksData->getKsPicture();
                // This is used to overcome the error when someone with invalid ssl certificate
                // tried to access and server verfying ssl cerificate gets failed or not responding
                //  so we set verify ssl to false
                stream_context_set_default([
                'ssl' => [
                            'verify_peer' => false,
                            'verify_peer_name' => false,
                        ],
                ]);
                $ksImg = get_headers($ksImageUrl, true);
                $ksImage['ks_picture'][0]['name'] = $ksData->getKsPicture();
                $ksImage['ks_picture'][0]['url'] = $ksImageUrl;
                $ksImage['ks_picture'][0]['size'] = $ksImg["Content-Length"];
                $ksFullData = $this->loadedData;
                $this->loadedData[$ksData->getId()]['howitworks'] = array_merge($ksFullData[$ksData->getId()]['howitworks'], $ksImage);
            }
        }
        return $this->loadedData;
    }

    public function getMediaUrl()
    {
        $mediaUrl = $this->ksStoreManager->getStore()
            ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA).'ksolves/multivendor/';
        return $mediaUrl;
    }
}
