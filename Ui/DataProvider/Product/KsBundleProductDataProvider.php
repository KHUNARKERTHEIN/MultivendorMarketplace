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

namespace Ksolves\MultivendorMarketplace\Ui\DataProvider\Product;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Ui\DataProvider\Modifier\PoolInterface;
use Magento\Framework\App\Request\DataPersistorInterface;

/**
 * Class KsBundleProductDataProvider
 * @package Ksolves\MultivendorMarketplace\Ui\DataProvider\Backend\Product\Listing
 */
class KsBundleProductDataProvider extends \Ksolves\MultivendorMarketplace\Ui\DataProvider\Backend\Product\Listing\KsCatalogProductDataProvider
{
    /**
     * @var \Magento\Bundle\Helper\Data
     */
    protected $ksDataHelper;

    /**
      * @var DataPersistorInterface
      */
    protected $ksDataPersistor;

    /**
     * KsBundleProductDataProvider constructor.
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $ksCollectionFactory
     * @param array $addFieldStrategies
     * @param array $addFilterStrategies
     * @param \Magento\Bundle\Helper\Data $ksDataHelper
     * @param DataPersistorInterface $ksDataPersistor
     * @param array $meta
     * @param array $data
     * @param PoolInterface|null $modifiersPool
     * @param \Magento\Framework\App\RequestInterface $ksRequest
     * @param \Ksolves\MultivendorMarketplace\Model\ResourceModel\KsProduct\CollectionFactory $ksProductFactory
     * @param \Magento\Customer\Model\SessionFactory $ksSession
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $ksCollectionFactory,
        \Magento\Bundle\Helper\Data $ksDataHelper,
        DataPersistorInterface $ksDataPersistor,
        \Magento\Framework\App\RequestInterface $ksRequest,
        \Ksolves\MultivendorMarketplace\Model\ResourceModel\KsProduct\CollectionFactory $ksProductFactory,
        \Magento\Customer\Model\SessionFactory $ksSession,
        \Ksolves\MultivendorMarketplace\Model\KsProductFactory $ksSellerProductFactory,
        array $addFieldStrategies = [],
        array $addFilterStrategies = [],
        array $meta = [],
        array $data = [],
        PoolInterface $modifiersPool = null
    ) {
        $this->ksDataHelper = $ksDataHelper;
        $this->ksDataPersistor = $ksDataPersistor;
        parent::__construct(
            $name,
            $primaryFieldName,
            $requestFieldName,
            $ksCollectionFactory,
            $ksRequest,
            $ksProductFactory,
            $ksSession,
            $ksSellerProductFactory,
            $addFieldStrategies,
            $addFilterStrategies,
            $meta,
            $data,
            $modifiersPool
        );

        if ($ksSession->create()->getId()) {
            $this->collection->addFieldToFilter(
                'ks_product_approval_status',
                ['eq'=>\Ksolves\MultivendorMarketplace\Model\KsProduct::KS_STATUS_APPROVED]
            );

            $ksExcludeProductIds = $this->ksDataPersistor->get('ks_selected_bundle_productids');

            if (!empty($ksExcludeProductIds)) {
                $this->collection->addFieldToFilter(
                    'entity_id',
                    ['nin'=> $ksExcludeProductIds]
                );
            }
        } else {
            $ksSellerId = $this->ksSellerProductFactory->create()
                        ->load($this->ksRequest->getParam('current_product_id'), 'ks_product_id')
                        ->getKsSellerId();

            if ($ksSellerId) {
                $this->collection->addFieldToFilter(
                    'ks_product_approval_status',
                    ['eq'=>\Ksolves\MultivendorMarketplace\Model\KsProduct::KS_STATUS_APPROVED]
                );
            }
        }
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        if (!$this->getCollection()->isLoaded()) {
            $this->getCollection()->addAttributeToFilter(
                'type_id',
                $this->ksDataHelper->getAllowedSelectionTypes()
            );
            $this->getCollection()->addFilterByRequiredOptions();
            $this->getCollection()->addStoreFilter(
                \Magento\Store\Model\Store::DEFAULT_STORE_ID
            );
            $this->getCollection()->load();
        }
        $items = $this->getCollection()->toArray();

        return [
            'totalRecords' => $this->getCollection()->getSize(),
            'items' => array_values($items),
        ];
    }
}
