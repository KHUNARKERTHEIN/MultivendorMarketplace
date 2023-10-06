/**
* Ksolves
*
* @category  Ksolves
* @package   Ksolves_MultivendorMarketplace
* @author    Ksolves Team
* @copyright Copyright (c) Ksolves India Limited (https://www.ksolves.com/)
* @license   https://store.ksolves.com/magento-license
*/
require([
        "jquery",
        "mage/url",
        "mage/translate",
        'Magento_Customer/js/customer-data'
    ], function($, url, $t, customerData) { 
        $('body').on('click',".ks-add-to-cart-button",function(e){ 
            e.preventDefault();
            //get product data for add to cart
            var ksProductData = $(this).closest(".product_addtocart_form").serialize();
            $.ajax({
                type: 'GET', 
                showLoader: true,
                url: url.build("multivendor/pricecomparison/addtocart")+"?"+ksProductData,
                success: function (ksData) {
                    console.log(ksData);
                    var ksSections = ['cart'];
                    customerData.invalidate(ksSections);
                    customerData.reload(ksSections, true);
                },
                error: function (request, error)
                {
                    console.log("Error");
                }
            });
            return false;
        });
    });
