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

namespace Ksolves\MultivendorMarketplace\Observer;

use Ksolves\MultivendorMarketplace\Helper\KsDataHelper;
use Ksolves\MultivendorMarketplace\Helper\KsProductHelper;
use Ksolves\MultivendorMarketplace\Helper\KsSellerHelper;
use Ksolves\MultivendorMarketplace\Model\KsProductFactory;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSellerCategories\CollectionFactory as KsSellerCategoryCollectionFactory;

/**
 * Class KsBeforeProductSave
 * @package Ksolves\MultivendorMarketplace\Observer
 */
class KsBeforeProductSave implements ObserverInterface
{
    /**
     * @var Http
     */
    protected $ksRequest;

    /**
     * @var KsDataHelper
     */
    protected $ksDataHelper;

    /**
     * @var KsProductHelper
     */
    protected $ksProductHelper;

    /**
     * @var KsSellerHelper
     */
    protected $ksSellerHelper;

    /**
     * @var KsProductFactory
     */
    protected $ksSellerProductFactory;

    /**
     * @var SerializerInterface
     */
    protected $ksSerialize;

    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    protected $ksProductRepository;

    /**
     * @var  \Ksolves\MultivendorMarketplace\Model\ResourceModel\KsProductCategoryBackup\CollectionFactory
     */
    protected $ksProductCategoryCollectionFactory;

    /**
     * @var KsSellerCategoryCollectionFactory
     */
    protected $ksSellerCategoryCollection;

    /**
     * KsBeforeProductSave constructor.
     * @param Http $ksRequest
     * @param KsDataHelper $ksDataHelper
     * @param KsProductHelper $ksProductHelper
     * @param KsSellerHelper $ksSellerHelper
     * @param KsProductFactory $ksSellerProductFactory
     * @param SerializerInterface $ksSerialize
     * @param \Magento\Catalog\Model\ProductRepository $ksProductRepository
     * @param Ksolves\MultivendorMarketplace\Model\ResourceModel\KsProductCategoryBackup\CollectionFactory $ksProductCategoryCollectionFactory
     * @param KsSellerCategoryCollectionFactory $ksSellerCategoryCollection
     */
    public function __construct(
        Http $ksRequest,
        KsDataHelper $ksDataHelper,
        KsProductHelper $ksProductHelper,
        KsSellerHelper $ksSellerHelper,
        KsProductFactory $ksSellerProductFactory,
        SerializerInterface $ksSerialize,
        \Magento\Catalog\Model\ProductRepository $ksProductRepository,
        \Ksolves\MultivendorMarketplace\Model\ResourceModel\KsProductCategoryBackup\CollectionFactory $ksProductCategoryCollectionFactory,
        KsSellerCategoryCollectionFactory $ksSellerCategoryCollection
    ) {
        $this->ksRequest               = $ksRequest;
        $this->ksDataHelper            = $ksDataHelper;
        $this->ksProductHelper         = $ksProductHelper;
        $this->ksSellerHelper          = $ksSellerHelper;
        $this->ksSellerProductFactory  = $ksSellerProductFactory;
        $this->ksSerialize             = $ksSerialize;
        $this->ksProductRepository     = $ksProductRepository;
        $this->ksProductCategoryCollectionFactory = $ksProductCategoryCollectionFactory;
        $this->ksSellerCategoryCollection = $ksSellerCategoryCollection;
    }

    /**
     * @param Observer $ksObserver
     */
    public function execute(Observer $ksObserver)
    {
        $ksProductSellerId = 0;

        //get Data
        $ksData = $this->ksRequest->getParams();

        try {
            $ksProduct = $ksObserver->getProduct();

            //check product
            if (!empty($ksProduct) && $this->ksRequest->getFullActionName()=="catalog_product_save") {
                if (isset($ksData['product']['ks_seller_id'])) {
                    $ksSellerId = $ksData['product']['ks_seller_id'];

                    $ksProductCount = $this->ksSellerProductFactory->create()
                        ->getCollection()
                        ->addFieldToFilter('ks_seller_id', $ksSellerId)
                        ->getSize();

                    if ($ksProduct->getId()) {
                        $ksProductData = $this->ksSellerProductFactory->create()
                            ->getCollection()
                            ->addFieldToFilter('ks_product_id', $ksProduct->getId())
                            ->getData();
                        if (count($ksProductData)) {
                            $ksProductSellerId = $ksProductData[0]['ks_seller_id'];
                        }
                    }

                    if ($ksProductSellerId == 0 || $ksProductSellerId == $ksSellerId) {
                        if ($ksData['type'] == 'configurable') {
                            $ksAssociatedProductIds = $this->ksSerialize
                                ->unserialize($ksData['associated_product_ids_serialized']);

                            foreach ($ksAssociatedProductIds as $ksAssociatedProductId) {
                                $ksSellerProductData = $this->ksSellerProductFactory->create()
                                    ->load($ksAssociatedProductId, 'ks_product_id');

                                if (isset($ksSellerProductData)) {
                                    $ksProductSellerId = $ksSellerProductData->getKsSellerId();
                                    if ($ksProductSellerId!=$ksSellerId) {
                                        throw new \Magento\Framework\Exception\LocalizedException(
                                            __('You cannot add admin and different seller product.')
                                        );
                                    }
                                }
                            }
                        }
                        //For Check Attribute Set is Allowed for the Seller or Not
                        if ($this->ksProductHelper->ksCheckForSellerAttributeSet($ksSellerId, 4, $ksProduct->getAttributeSetId())) {
                            if ($ksProductSellerId == 0 || $ksSellerId != $ksProductSellerId) {
                                if ($ksProductCount < $this->ksDataHelper->getKsConfigProductSetting('ks_seller_product_limit')) {
                                    if (isset($ksData['type']) && $ksData['type']) {
                                        $this->ksProductHelper->ksCheckForBundleAndGroupedProduct($ksSellerId, $ksData, $ksProduct);
                                        $this->ksProductHelper->ksCheckForRelatedUpSellCrossSellProduct($ksData, $ksSellerId);
                                    }
                                } else {
                                    throw new \Magento\Framework\Exception\LocalizedException(
                                        __('Reached the seller product limit.')
                                    );
                                }
                            } else {
                                if (isset($ksData['type']) && $ksData['type']) {
                                    $this->ksProductHelper->ksCheckForBundleAndGroupedProduct($ksSellerId, $ksData, $ksProduct);
                                    $this->ksProductHelper->ksCheckForRelatedUpSellCrossSellProduct($ksData, $ksSellerId);
                                }
                            }
                        } else {
                            throw new \Magento\Framework\Exception\LocalizedException(
                                __('Selected Attribute Set is not allowed for Selected Seller.')
                            );
                        }
                    } else {
                        throw new \Magento\Framework\Exception\LocalizedException(
                            __('This product type is not allowed to the seller.')
                        );
                    }

                    if (!$this->ksCheckForCategories($ksSellerId, $ksProduct)) {
                        throw new \Magento\Framework\Exception\LocalizedException(
                            __('Selected Categories has not allowed to this seller.')
                        );
                    }
                } else {
                    $ksSellerId = 0;

                    if (isset($ksData['type']) && $ksData['type']) {
                        $this->ksProductHelper->ksCheckForBundleAndGroupedProduct($ksSellerId, $ksData, $ksProduct);

                        $this->ksProductHelper->ksCheckForRelatedUpSellCrossSellProduct($ksData, $ksSellerId);

                        if ($ksData['type'] == 'configurable') {
                            $ksAssociatedProductIds = $this->ksSerialize
                                ->unserialize($ksData['associated_product_ids_serialized']);
                            foreach ($ksAssociatedProductIds as $ksAssociatedProductId) {
                                $ksSellerProductData = $this->ksSellerProductFactory->create()
                                    ->load($ksAssociatedProductId, 'ks_product_id');

                                if (isset($ksSellerProductData)) {
                                    $ksProductSellerId = $ksSellerProductData->getKsSellerId();
                                    if ($ksProductSellerId!=$ksSellerId) {
                                        throw new \Magento\Framework\Exception\LocalizedException(
                                            __('You cannot add seller product.')
                                        );
                                    }
                                }
                            }
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __($e->getMessage())
            );
        }
    }

    /**
     * @param $ksSellerId
     * @param $ksData
     * @param $ksProduct
     */
    private function ksCheckForCategories($ksSellerId, $ksProduct)
    {
        if (!empty($ksProduct->getCategoryIds())) {
            $ksCateoryIds = $ksProduct->getCategoryIds();

            $ksCategoryData = $this->ksProductHelper->ksGetCategoryIds($ksSellerId);
            $ksDiffCatIds = array_diff($ksCateoryIds, $ksCategoryData);
            foreach($ksDiffCatIds as $ksKey => $ksCatId){
                $ksCollection = $this->ksSellerCategoryCollection->create()
                ->addFieldToFilter('ks_seller_id', $ksSellerId)
                ->addFieldToFilter('ks_category_id',$ksCatId);
                if($ksCollection->getSize() > 0){
                    $ksProductCategoryCollection = $this->ksProductCategoryCollectionFactory->create()->addFieldToFilter('ks_product_id',$ksProduct->getId())->addFieldToFilter('ks_category_id',$ksCatId);
                    if($ksProductCategoryCollection->getSize() > 0){
                        unset($ksDiffCatIds[$ksKey]);
                    }
                }
            }
            if (count($ksDiffCatIds) == 0) {
                return true;
            } else {
                return false;
            }
        } else {
            return true;
        }
    }
}
