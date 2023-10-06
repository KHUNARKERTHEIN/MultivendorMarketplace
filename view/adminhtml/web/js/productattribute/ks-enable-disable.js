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
    "mage/translate"
    ], function($, url ,$t) { 
          $('body').on('change','.ks-checkbox',function(){
            var ksUrl = url.build('multivendor/productattribute/disable');
            if(this.checked) {
                var ksUrl = url.build('multivendor/productattribute/enable');
            }
            $.ajax({
                type: "POST",
                url: ksUrl,
                showLoader : true,
                data: {
                    attribute_id: $(this).data('id'),
                },
                success: function (ksResponse) { 
                    location.reload();
                }
            });
        });
    });