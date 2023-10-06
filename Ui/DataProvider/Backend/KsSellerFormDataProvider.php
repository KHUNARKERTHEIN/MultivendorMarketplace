<?php

declare(strict_types=1);
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

use Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSeller\CollectionFactory as KsSellerCollectionFactory;
use Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSellerStore\CollectionFactory as KsSellerStoreCollectionFactory;
use Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSellerSitemap\CollectionFactory as KsSellerSitemapCollectionFactory;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Magento\Framework\App\Request\Http;
use Ksolves\MultivendorMarketplace\Helper\KsDataHelper;
use Magento\Store\Model\StoreManagerInterface;

/**
 * KsSellerFormDataProvider dataprovider class
 */
class KsSellerFormDataProvider extends AbstractDataProvider
{
    /**
     * @var KsSellerCollectionFactory
     */
    protected $ksSellerCollection;

    /**
     * @var array
     */
    protected $ksLoadedData;

    /**
     * @var KsDataHelper
     */
    protected $ksDataHelper;

    /**
     * @var StoreManagerInterface
     */
    protected $ksStoreManager;

    /**
     * @var KsSellerSitemapCollectionFactory
     */
    protected $ksSellerSitemapCollectionFactory;

    /**
    * @var Ksolves\MultivendorMarketplace\Helper\KsSellerDashboardMyProfileHelper
    */
    protected $KsSellerDashboardMyProfileHelper;

    /**
     * @var Http
     */
    protected $ksRequest;

    /**
     * @var KsSellerStoreCollectionFactory
     */
    protected $ksSellerStoreCollectionFactory;

    protected $collection;
    protected $meta;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param KsSellerCollectionFactory $ksCollectionFactory
     * @param KsSellerStoreCollectionFactory $ksSellerStoreCollectionFactory
     * @param Http $ksRequest
     * @param StoreManagerInterface $ksStoreManager
     * @param array $meta
     * @param array $data
     *  @param Ksolves\MultivendorMarketplace\Helper\KsSellerDashboardMyProfileHelper $KsSellerDashboardMyProfileHelper
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        KsSellerCollectionFactory $ksCollectionFactory,
        KsSellerStoreCollectionFactory $ksSellerStoreCollectionFactory,
        KsSellerSitemapCollectionFactory $ksSellerSitemapCollectionFactory,
        Http $ksRequest,
        KsDataHelper $ksDataHelper,
        StoreManagerInterface $ksStoreManager,
        \Ksolves\MultivendorMarketplace\Helper\KsSellerDashboardMyProfileHelper $KsSellerDashboardMyProfileHelper,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $ksCollectionFactory->create();
        $this->ksRequest = $ksRequest;
        $this->ksSellerStoreCollectionFactory = $ksSellerStoreCollectionFactory;
        $this->ksSellerSitemapCollectionFactory = $ksSellerSitemapCollectionFactory;
        $this->KsSellerDashboardMyProfileHelper = $KsSellerDashboardMyProfileHelper;
        $ksStoreId = $ksRequest->getParam('store') ? $ksRequest->getParam('store') : 0;

        $ksSellerId = $ksRequest->getParam('seller_id');
        $ksSellerStoreCollection = $ksSellerStoreCollectionFactory->create()->addFieldToFilter('ks_store_id', $ksStoreId)->addFieldToFilter('ks_seller_id', $ksSellerId);

        if ($ksSellerStoreCollection->getSize() > 0) {
            $where = "ks_store_id = ".$ksStoreId;
        } elseif ($ksSellerStoreCollectionFactory->create()->addFieldToFilter('ks_store_id', 0)->addFieldToFilter('ks_seller_id', $ksSellerId)->getSize() > 0) {
            $where = "ks_store_id = 0";
        } else {
            $where = "1 = 1";
        }

        $ksJoinTable = $this->collection->getTable('ks_seller_store_details');
        $this->collection->getSelect()->joinLeft(
            $ksJoinTable.' as ks_ssd',
            'main_table.ks_seller_id = ks_ssd.ks_seller_id',
            [
                'ks_store_id'                => 'ks_store_id',
                'ks_store_logo'              => 'ks_store_logo',
                'ks_store_banner'            => 'ks_store_banner  ',
                'ks_store_description'       => 'ks_store_description',
                'ks_support_contact'         => 'ks_support_contact',
                'ks_support_email'           => 'ks_support_email',
                'ks_twitter_id'              => 'ks_twitter_id',
                'ks_facebook_id'             => 'ks_facebook_id',
                'ks_instagram_id'            => 'ks_instagram_id',
                'ks_googleplus_id'           => 'ks_googleplus_id',
                'ks_youtube_id'              => 'ks_youtube_id',
                'ks_vimeo_id'                => 'ks_vimeo_id',
                'ks_pinterest_id'            => 'ks_pinterest_id',
                'ks_meta_keyword'            => 'ks_meta_keyword',
                'ks_meta_description'        => 'ks_meta_description',
                'ks_refund_policy'           => 'ks_refund_policy',
                'ks_privacy_policy'          => 'ks_privacy_policy',
                'ks_shipping_policy'         => 'ks_shipping_policy',
                'ks_terms_of_service'        => 'ks_terms_of_service',
                'ks_legal_notice'            => 'ks_legal_notice'
            ]
        )->where($where);

        $ksConfigJoinTable = $this->collection->getTable('ks_seller_config_data');
        $this->collection->getSelect()->joinLeft(
            $ksConfigJoinTable.' as ks_scd',
            'main_table.ks_seller_id = ks_scd.ks_seller_id',
            [
                'ks_show_banner'              => 'ks_show_banner',
                'ks_show_recently_products'   => 'ks_show_recently_products',
                'ks_recently_products_text'   => 'ks_recently_products_text',
                'ks_recently_products_count'  => 'ks_recently_products_count',
                'ks_show_best_products'       => 'ks_show_best_products',
                'ks_best_products_text'       => 'ks_best_products_text',
                'ks_best_products_count'      => 'ks_best_products_count',
                'ks_show_discount_products'   => 'ks_show_discount_products',
                'ks_discount_products_text'   => 'ks_discount_products_text',
                'ks_discount_products_count'  => 'ks_discount_products_count'
            ]
        );

        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->meta = $this->prepareMeta($this->meta);
        $this->ksRequest = $ksRequest;
        $this->ksDataHelper = $ksDataHelper;
        $this->ksStoreManager = $ksStoreManager;
    }

    /**
     * Prepares Meta
     *
     * @param  array $meta
     * @return array
     */
    public function prepareMeta(array $meta)
    {
        return $meta;
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        if (isset($this->ksLoadedData)) {
            return $this->ksLoadedData;
        }

        $ks_store_id=$this->ksRequest->getParam('store') ? $this->ksRequest->getParam('store') : 0;
        $ksSellerData = $this->collection->getItems();
        foreach ($ksSellerData as $ksItem) {
            $this->ksLoadedData[$ksItem->getId()]['ks_seller_account_details_tab'] = $this->getKsSellerAccountTabData($ksItem);
            $this->ksLoadedData[$ksItem->getId()]['ks_customer_support_tab'] = $this->getKsCustomerSupportTabData($ksItem);
            $this->ksLoadedData[$ksItem->getId()]['ks_social_media_tab'] = $this->getKsSocialMediaTabData($ksItem);
            $this->ksLoadedData[$ksItem->getId()]['ks_seo_details_tab'] = $this->getKsSeoTabData($ksItem);
            $this->ksLoadedData[$ksItem->getId()]['ks_store_policies_details_tab'] = $this->getKsStorePoliciesTabData($ksItem);
            $this->ksLoadedData[$ksItem->getId()]['ks_seller_product_type_tab'] = $this->getKsProductTypeTabData($ksItem);
            $this->ksLoadedData[$ksItem->getId()]['ks_seller_product_tab'] = $this->getKsProductTabData($ksItem);
            $this->ksLoadedData[$ksItem->getId()]['ks_assign_admin_product_tab']['ks_assign_admin_product_modal']['ks_seller_id'] = $ksItem->getKsSellerId();
            $this->ksLoadedData[$ksItem->getId()]['ks_seller_product_tab'] = $this->getKsProductTabData($ksItem);
            $this->ksLoadedData[$ksItem->getId()]['ks_seller_product_attribute_tab'] = $this->getKsProductAttributeTabData($ksItem);
            $this->ksLoadedData[$ksItem->getId()]['ks_homepage_tab'] = $this->getKsHomePageTabData($ksItem);
            $this->ksLoadedData[$ksItem->getId()]['ks_seller_account_details_tab']['ks_sitemap_section'] = $this->getKsSellerAccountTabSitemapSectionData($ksItem->getKsSellerId(), $ks_store_id);
            $this->ksLoadedData[$ksItem->getId()]['ks_assign_admin_product_tab']['ks_seller_id'] = $ksItem->getKsSellerId();
        }
        return $this->ksLoadedData;
    }

    /**
     * get seller account tab data
     * @param array $ksItem
     * @return array
     */
    public function getKsSellerAccountTabData($ksItem)
    {
        $ksData = [];
        // set seller public profile data
        $ksData['ks_public_profile_section'] = [
            'id' => $ksItem->getId(),
            'ks_seller_id' => $ksItem->getKsSellerId(),
            'ks_store_id' => $this->ksRequest->getParam('store'),
            'ks_seller_group_id' => $ksItem->getKsSellerGroupId(),
            'ks_store_name' => $ksItem->getKsStoreName(),
            'ks_store_url' => $ksItem->getKsStoreUrl(),
            'ks_store_status' => $ksItem->getKsStoreStatus(),
            'ks_store_description' => $ksItem->getKsStoreDescription(),
            'ks_store_available_countries' => $ksItem->getKsStoreAvailableCountries()
        ];

        $ksMediaUrl = $this->ksStoreManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);

        if ($ksItem->getKsStoreLogo()) {
            $ksImageUrl = $ksMediaUrl.'ksolves/multivendor/' . $ksItem->getKsStoreLogo();

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
            $ksData['ks_public_profile_section']['ks_store_logo'] = [
                    [
                        'name' => $ksItem->getKsStoreLogo(),
                        'url' => $ksImageUrl,
                        'size' => $ksImg["Content-Length"]
                    ],
                ];
        } else {
            $ksData['ks_public_profile_section']['ks_store_logo'] = null;
        }

        if ($ksItem->getKsStoreBanner()) {
            $ksImageUrl = $ksMediaUrl.'ksolves/multivendor/' . $ksItem->getKsStoreBanner();

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
            $ksData['ks_public_profile_section']['ks_store_banner'] = [
                   [
                       'name' => $ksItem->getKsStoreBanner(),
                       'url'  => $ksImageUrl,
                       'size' => $ksImg["Content-Length"]
                   ],
               ];
        } else {
            $ksData['ks_public_profile_section']['ks_store_banner'] = null;
        }

        // set seller company data
        $ksData['ks_company_details_section'] = [
            'ks_company_name' => $ksItem->getKsCompanyName(),
            'ks_company_contact_email' => $ksItem->getKsCompanyContactEmail(),
            'ks_company_contact_no' => $ksItem->getKsCompanyContactNo(),
            'ks_company_address' => $ksItem->getKsCompanyAddress(),
            'country_id' => $ksItem->getKsCompanyCountry(),
            'ks_company_state' => $ksItem->getKsCompanyState(),
            'ks_company_state_id' => $ksItem->getKsCompanyStateId(),
            'ks_company_postcode' => $ksItem->getKsCompanyPostcode(),
            'ks_company_taxvat_number' => $ksItem->getKsCompanyTaxvatNumber()
        ];
        // set approval data
        $ksData['ks_approval_section'] = [
            'ks_seller_status' => $ksItem->getKsSellerStatus(),
            'ks_rejection_reason' => $ksItem->getKsRejectionReason(),
            'ks_created_at' => $this->ksDataHelper->getKsDateTime($ksItem->getKsCreatedAt())
        ];

        return $ksData;
    }

    /**
     * get customer support tab data
     * @param array $ksItem
     * @return array
     */
    public function getKsCustomerSupportTabData($ksItem)
    {
        return [
                'ks_support_contact' => $ksItem->getKsSupportContact(),
                'ks_support_email' => $ksItem->getKsSupportEmail()
            ];
    }

    /**
     * get social media tab data
     * @param array $ksItem
     * @return array
     */
    public function getKsSocialMediaTabData($ksItem)
    {
        return [
                'ks_twitter_id' => $ksItem->getKsTwitterId(),
                'ks_facebook_id' => $ksItem->getKsFacebookId(),
                'ks_instagram_id' => $ksItem->getKsInstagramId(),
                'ks_googleplus_id' => $ksItem->getKsGoogleplusId(),
                'ks_youtube_id' => $ksItem->getKsYoutubeId(),
                'ks_vimeo_id' => $ksItem->getKsVimeoId(),
                'ks_pinterest_id' => $ksItem->getKsPinterestId()
            ];
    }

    /**
     * get SEO tab data
     * @param array $ksItem
     * @return array
     */
    public function getKsSeoTabData($ksItem)
    {
        return [
                'ks_meta_keyword' => $ksItem->getKsMetaKeyword(),
                'ks_meta_description' => $ksItem->getKsMetaDescription()
            ];
    }

    /**
     * get Store Policies tab data
     * @param array $ksItem
     * @return array
     */
    public function getKsStorePoliciesTabData($ksItem)
    {
        return [
                'ks_refund_policy' => $ksItem->getKsRefundPolicy(),
                'ks_privacy_policy' => $ksItem->getKsPrivacyPolicy(),
                'ks_shipping_policy' => $ksItem->getKsShippingPolicy(),
                'ks_terms_of_service' => $ksItem->getKsTermsOfService(),
                'ks_legal_notice' => $ksItem->getKsLegalNotice()
            ];
    }

    /**
     * get Product Type tab data
     * @param array $ksItem
     * @return array
     */
    public function getKsProductTypeTabData($ksItem)
    {
        return [
                'ks_seller_producttype_request_status' => $ksItem->getKsSellerProducttypeRequestStatus(),
                'ks_producttype_auto_approval_status' => $ksItem->getKsProducttypeAutoApprovalStatus()
            ];
    }

    private function getKsProductTabData($ksItem)
    {
        return [
            'ks_product_auto_approval' => $ksItem->getKsProductAutoApproval()
        ];
    }

    /**
     * Get Product Attribute tab data
     * @param array $ksItem
     * @return array
     */
    public function getKsProductAttributeTabData($ksItem)
    {
        return [
                'ks_product_attribute_request_allowed_status' => $ksItem->getKsProductAttributeRequestAllowedStatus(),
                'ks_product_attribute_auto_approval_status' => $ksItem->getKsProductAttributeAutoApprovalStatus()
            ];
    }

    /**
     * get Home Page tab data
     * @param array $ksItem
     * @return array
     */
    public function getKsHomePageTabData($ksItem)
    {
        return [
                'ks_show_banner'             => $ksItem->getKsShowBanner(),
                'ks_show_recently_products'  => $ksItem->getKsShowRecentlyProducts(),
                'ks_recently_products_text'  => $ksItem->getKsRecentlyProductsText(),
                'ks_recently_products_count' => $ksItem->getKsRecentlyProductsCount(),
                'ks_show_best_products'      => $ksItem->getKsShowBestProducts(),
                'ks_best_products_text'      => $ksItem->getKsBestProductsText(),
                'ks_best_products_count'     => $ksItem->getKsBestProductsCount(),
                'ks_show_discount_products'  => $ksItem->getKsShowDiscountProducts(),
                'ks_discount_products_text'  => $ksItem->getKsDiscountProductsText(),
                'ks_discount_products_count' => $ksItem->getKsDiscountProductsCount()
            ];
    }

    /**
     * Get Meta
     * @return array
     */
    public function getMeta()
    {
        $meta = parent::getMeta();
        $meta = $this->prepareMeta($meta);
        // Array for the values having store
        $ksFields = [
            'ks_store_policies_details_tab' =>  [
                                                   'ks_refund_policy',
                                                   'ks_privacy_policy',
                                                   'ks_shipping_policy',
                                                   'ks_legal_notice',
                                                   'ks_terms_of_service'
                                                ],
            'ks_social_media_tab'           =>  [
                                                   'ks_meta_description',
                                                   'ks_meta_keyword'
                                                ],
            'ks_social_media_tab'           =>  [
                                                    'ks_twitter_id',
                                                    'ks_facebook_id',
                                                    'ks_instagram_id',
                                                    'ks_googleplus_id',
                                                    'ks_youtube_id',
                                                    'ks_vimeo_id',
                                                    'ks_pinterest_id'
                                                ],
            'ks_seo_details_tab'            =>  [   'ks_meta_keyword',
                                                    'ks_meta_description'
                                                ],
            'ks_customer_support_tab'       =>  [
                                                    'ks_support_contact',
                                                    'ks_support_email'
                                                ],
            'ks_public_profile_section'     =>  [
                                                    'ks_store_logo',
                                                    'ks_store_banner',
                                                    'ks_store_description'
                                                ]
            ];
        // Get Current Seller Id
        $ksSellerId = $this->ksRequest->getParam('seller_id');
        $ksDefaultStore = $this->ksSellerStoreCollectionFactory->create()->addFieldToFilter('ks_store_id', 0)->addFieldToFilter('ks_seller_id', $ksSellerId)->getFirstItem()->getData();

        // check store is not default
        if ($this->ksRequest->getParam('store') != 0 && !empty($ksDefaultStore)) {
            // Get Default Values check array
            $ksDefaultValue = $this->getKsDefaultValue();
            // Iterate over the fields
            foreach ($ksFields as $ksKey => $ksRecord) {
                foreach ($ksRecord as $ksValue) {
                    if ($ksKey == 'ks_public_profile_section') {
                        $meta['ks_seller_account_details_tab']['children'][$ksKey]['children'][$ksValue]['arguments']['data']['config']['service']['template'] = 'ui/form/element/helper/service';
                        $meta['ks_seller_account_details_tab']['children'][$ksKey]['children'][$ksValue]['arguments']['data']['config']['disabled'] = $ksDefaultValue ? $ksDefaultValue[$ksValue] : true;
                    } else {
                        $meta[$ksKey]['children'][$ksValue]['arguments']['data']['config']['service']['template'] = 'ui/form/element/helper/service';
                        $meta[$ksKey]['children'][$ksValue]['arguments']['data']['config']['disabled'] = $ksDefaultValue ? $ksDefaultValue[$ksValue] : true;
                    }
                }
            }
        }
        /**
         * Sitemap functionality
         * Array for the values having sitemap
         */

        $ksSitemapFields=[
        'ks_sitemap_section'            =>  [
                                                'ks_included_sitemap_profile',
                                                'ks_included_sitemap_product'
                                            ]
        ];
        // Sitemap code for use default value
        $ksDefaultSitemapStore = $this->ksSellerSitemapCollectionFactory->create()->addFieldToFilter('ks_store_id', 0)->addFieldToFilter('ks_seller_id', $ksSellerId)->getFirstItem()->getData();
        if ($this->ksRequest->getParam('store') != 0 && !empty($ksDefaultSitemapStore)) {
            // Get Default Values check array
            $ksSitemapDefaultValue = $this->getKsSitemapDefaultValue();
            // Iterate over the fields
            foreach ($ksSitemapFields as $ksKey => $ksRecord) {
                foreach ($ksRecord as $ksValue) {
                    $meta['ks_seller_account_details_tab']['children'][$ksKey]['children'][$ksValue]['arguments']['data']['config']['service']['template'] = 'ui/form/element/helper/service';
                    $meta['ks_seller_account_details_tab']['children'][$ksKey]['children'][$ksValue]['arguments']['data']['config']['disabled'] = $ksSitemapDefaultValue ? $ksSitemapDefaultValue[$ksValue] : true;
                }
            }
        }
        //Store Policy Visibility
        $meta['ks_store_policies_details_tab']['children']['ks_refund_policy']['arguments']['data']['config']['visible'] = $this->KsSellerDashboardMyProfileHelper->getKsSellerConfigData('ks_refund_policy');
        $meta['ks_store_policies_details_tab']['children']['ks_privacy_policy']['arguments']['data']['config']['visible'] =  $this->KsSellerDashboardMyProfileHelper->getKsSellerConfigData('ks_privacy_policy');
        $meta['ks_store_policies_details_tab']['children']['ks_shipping_policy']['arguments']['data']['config']['visible'] =$this->KsSellerDashboardMyProfileHelper->getKsSellerConfigData('ks_shipping_policy');
        $meta['ks_store_policies_details_tab']['children']['ks_legal_notice']['arguments']['data']['config']['visible'] = $this->KsSellerDashboardMyProfileHelper->getKsSellerConfigData('ks_legal_notice');
        $meta['ks_store_policies_details_tab']['children']['ks_terms_of_service']['arguments']['data']['config']['visible'] = $this->KsSellerDashboardMyProfileHelper->getKsSellerConfigData('ks_terms_of_service');

        //Seller Since Visibility
        $meta['ks_seller_account_details_tab']['children']['ks_approval_section']['children']['ks_created_at']['arguments']['data']['config']['visible']= $this->KsSellerDashboardMyProfileHelper->getKsSellerConfigData('ks_seller_since');

        $ksStoreId = $this->ksRequest->getParam('store') ? $this->ksRequest->getParam('store') : 0;
        //Check sitemap status from configuration
        $meta['ks_seller_account_details_tab']['children']['ks_sitemap_section']['arguments']['data']['config']['visible'] = $this->ksSetSitemapVisibility();
        if ($this->ksSetSitemapVisibility() == '1') {
            $meta['ks_seller_account_details_tab']['children']['ks_sitemap_section']['children']['ks_included_sitemap_profile']['arguments']['data']['config']['visible']= $this->ksDataHelper->getKsConfigValue('ks_marketplace_sitemap/ks_sitemap/ks_include_seller_profile_url', $ksStoreId);

            $meta['ks_seller_account_details_tab']['children']['ks_sitemap_section']['children']['ks_included_sitemap_product']['arguments']['data']['config']['visible']= $this->ksDataHelper->getKsConfigValue('ks_marketplace_sitemap/ks_sitemap/ks_include_seller_product_url', $ksStoreId);
        }
        return $meta;
    }

    /**
     * function to check default values in the SellerSitemap table for specific id
     * @return array
     */

    public function getKsSitemapDefaultValue()
    {
        // Get Current Seller Id
        $ksSellerId = $this->ksRequest->getParam('seller_id');
        // Get current store id
        $ksCurrentStore = $this->ksRequest->getParam('store') ? $this->ksRequest->getParam('store') : 0;
        // Get Data of Default Store
        $ksDefaultStore = $this->ksSellerSitemapCollectionFactory->create()->addFieldToFilter('ks_store_id', 0)->addFieldToFilter('ks_seller_id', $ksSellerId)->getFirstItem()->getData();
        // Get Data of Current Store
        $ksCurrentStore = $this->ksSellerSitemapCollectionFactory->create()->addFieldToFilter('ks_store_id', $ksCurrentStore)->addFieldToFilter('ks_seller_id', $ksSellerId)->getFirstItem()->getData();

        // Initial an Array
        $ksDisable = [];
        if ($ksDefaultStore && $ksCurrentStore) {
            // Iterate over the Array
            foreach ($ksDefaultStore as $ksKey => $ksValue) {
                // If the Current Store Value matches with Deafult store true
                if ($ksDefaultStore[$ksKey] == $ksCurrentStore[$ksKey]) {
                    $ksDisable[$ksKey] = true;
                } else {
                    $ksDisable[$ksKey] = false;
                }
            }
        }
        return $ksDisable;
    }

    /**
     * Function to check default values in the Seller List
     * @return array
     */
    public function getKsDefaultValue()
    {
        // Get Current Seller Id
        $ksSellerId = $this->ksRequest->getParam('seller_id');
        // Get current store id
        $ksCurrentStore = $this->ksRequest->getParam('store');
        // Get Data of Default Store
        $ksDefaultStore = $this->ksSellerStoreCollectionFactory->create()->addFieldToFilter('ks_store_id', 0)->addFieldToFilter('ks_seller_id', $ksSellerId)->getFirstItem()->getData();
        // Get Data of Current Store
        $ksCurrentStore = $this->ksSellerStoreCollectionFactory->create()->addFieldToFilter('ks_store_id', $ksCurrentStore)->addFieldToFilter('ks_seller_id', $ksSellerId)->getFirstItem()->getData();
        // Initial an Array
        $ksDisable = [];

        // Check Values are not null
        if ($ksDefaultStore && $ksCurrentStore) {
            // Iterate over the Array
            foreach ($ksDefaultStore as $ksKey => $ksValue) {
                // If the Current Store Value matches with Deafult store true
                if ($ksDefaultStore[$ksKey] == $ksCurrentStore[$ksKey]) {
                    $ksDisable[$ksKey] = true;
                } else {
                    $ksDisable[$ksKey] = false;
                }
            }
        }
        return $ksDisable;
    }

    /**
     * function to check the Sitemap values from seller_sitemap table,
     * @return array
    */
    public function getKsSellerAccountTabSitemapSectionData($ksSellerId, $ksStoreId)
    {
        $ksItems=$this->ksSellerSitemapCollectionFactory->create()->addFieldToFilter('ks_seller_id', $ksSellerId)->addFieldToFilter('ks_store_id', $ksStoreId);
        $ksItemsDefault=$this->ksSellerSitemapCollectionFactory->create()->addFieldToFilter('ks_seller_id', $ksSellerId)->addFieldToFilter('ks_store_id', 0);
        $ksData = [];
        if ($ksItems->getSize()>0) {
            $ksItem=$ksItems->getFirstItem();
            $ksData['ks_seller_id'] = $ksSellerId;
            $ksData['ks_included_sitemap_profile'] = $ksItem->getKsIncludedSitemapProfile();
            $ksData['ks_included_sitemap_product'] = $ksItem->getKsIncludedSitemapProduct();
        } else {
            if ($ksStoreId!=0) {
                // if data is not found on particular store
                $ksItem=$ksItems->getFirstItem();
                $ksData['ks_seller_id'] = $ksSellerId;
                $ksData['ks_included_sitemap_profile'] = $ksItemsDefault->getFirstItem()->getKsIncludedSitemapProfile();
                $ksData['ks_included_sitemap_product'] = $ksItemsDefault->getFirstItem()->getKsIncludedSitemapProduct();
            } else {
                // if no data found than set the configuration value
                $ksData['ks_seller_id'] = $ksSellerId;
                $ksData['ks_included_sitemap_profile'] = $this->ksDataHelper->getKsConfigValue('ks_marketplace_sitemap/ks_sitemap/ks_include_seller_profile_url', $ksStoreId);
                $ksData['ks_included_sitemap_product'] = $this->ksDataHelper->getKsConfigValue('ks_marketplace_sitemap/ks_sitemap/ks_include_seller_product_url', $ksStoreId);
            }
        }
        return $ksData;
    }

    /**
     * Function to check the Sitemap tab will be visible or hidden
     * @return boolean;
    */
    public function ksSetSitemapVisibility()
    {
        $ks_store_id=$this->ksRequest->getParam('store') ? $this->ksRequest->getParam('store') : 0;
        if ($this->ksDataHelper->getKsConfigValue('ks_marketplace_sitemap/ks_sitemap/ks_enable_sitemap', $ks_store_id)=='1') {
            if ($this->ksDataHelper->getKsConfigValue('ks_marketplace_sitemap/ks_sitemap/ks_include_seller_profile_url', $ks_store_id)=='1' || $this->ksDataHelper->getKsConfigValue('ks_marketplace_sitemap/ks_sitemap/ks_include_seller_product_url', $ks_store_id)=='1') {
                return 1;
            }
        };
    }
}
