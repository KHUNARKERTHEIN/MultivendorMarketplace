/**
 * Ksolves
 *
 * @category  Ksolves
 * @package   Ksolves_MultivendorMarketplace
 * @author    Ksolves Team
 * @copyright Copyright (c) Ksolves India Limited (https://www.ksolves.com/)
 * @license   https://store.ksolves.com/magento-license
 */
var config = {
    map: {
        '*': {
            ksVerifySellerStore    : 'Ksolves_MultivendorMarketplace/js/seller/ks-verify-seller-store',
            ksReportProduct        : 'Ksolves_MultivendorMarketplace/js/report/ks-report-product',
            ksReportSeller         : 'Ksolves_MultivendorMarketplace/js/report/ks-report-seller',
            KsProductForm          : 'Ksolves_MultivendorMarketplace/js/product/ks-product-form',
            ksProductImageUpload   : 'Ksolves_MultivendorMarketplace/js/product/gallery/ks-file-upload',
            ksProductGallery       : 'Ksolves_MultivendorMarketplace/js/product/gallery/ks-product-gallery',
            ksBaseImage            : 'Ksolves_MultivendorMarketplace/js/product/gallery/ks-base-image-uploader',
            ksNewVideoDialog       : 'Ksolves_MultivendorMarketplace/js/product/gallery/ks-new-video-dialog',
            ksOpenVideoModal       : 'Ksolves_MultivendorMarketplace/js/product/gallery/ks-video-modal',
            ksInventoryDialog      : 'Ksolves_MultivendorMarketplace/js/product/ks-inventory-modal',
            ksAssignSourcesDialog  : 'Ksolves_MultivendorMarketplace/js/product/ks-assign-source-modal',
            ksAdvancedPriceDialog  : 'Ksolves_MultivendorMarketplace/js/product/ks-advance-pricing-modal',
            ksEarningCalculatorDialog : 'Ksolves_MultivendorMarketplace/js/product/ks-earning-calculator-modal',
            ksEarningCalculator  : 'Ksolves_MultivendorMarketplace/js/product/ks-earning-calculator',
            ksAddGridItem          : 'Ksolves_MultivendorMarketplace/js/product/ks-add-grid-item',
            ksRelatedDialog        : 'Ksolves_MultivendorMarketplace/js/product/link-product/ks-related-modal',
            ksUpsellDialog         : 'Ksolves_MultivendorMarketplace/js/product/link-product/ks-upsell-modal',
            ksCrosssellDialog      : 'Ksolves_MultivendorMarketplace/js/product/link-product/ks-crosssell-modal',
            ksCustomOptions        : 'Ksolves_MultivendorMarketplace/js/product/ks-custom-options',
            ksFixProductTax        : 'Ksolves_MultivendorMarketplace/js/product/ks-fix-product-tax',
            ksGroupedProductDialog : 'Ksolves_MultivendorMarketplace/js/product/grouped/ks-grouped-product',
            ksBundleProduct        : 'Ksolves_MultivendorMarketplace/js/product/bundle/ks-bundle-product',
            owl_carousel           : 'Ksolves_MultivendorMarketplace/js/lib/owl.carousel',
            ksDashboard            : 'Ksolves_MultivendorMarketplace/js/dashboard/ks-dashboard',
            ksBundleSelection      : 'Ksolves_MultivendorMarketplace/js/product/bundle/ks-selection',
            ksDownloadableProduct  : 'Ksolves_MultivendorMarketplace/js/product/downloadable/ks-downloadable-product',
/*          ksPriceComparisonReportSeller  : 'Ksolves_MultivendorMarketplace/js/price-comparison/ks-price-comparison-report-seller',*/
            ksPriceComparisonProductList : 'Ksolves_MultivendorMarketplace/js/price-comparison/ks-pricecomparison-product-list'
        }
    },
    config: {
        mixins:
        {
            'Magento_Ui/js/grid/filters/filters': {
              'Ksolves_MultivendorMarketplace/js/ui/ks-filters-mixin': true
            }
        }
    },
    paths: {
        'bootstrap':'Ksolves_MultivendorMarketplace/js/bootstrap/bootstrap.bundle'
    } ,
    shim: {

        "extjs/ext-tree": [
            "prototype"
        ],
        "extjs/ext-tree-checkbox": [
            "extjs/ext-tree",
            "extjs/defaults"
        ],

        'bootstrap': {
            'deps': ['jquery']
        },

        owl_carousel: {
            deps: ['jquery']
        }
    }
};
