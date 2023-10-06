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
], function($, url, $t) {
    $('body').on('change', '.ks-checkbox', function() {
        var ksUrl = url.build('multivendor/seller_ajax/disable');
        if (this.checked) {
            var ksUrl = url.build('multivendor/seller_ajax/enable');
        }
        $.ajax({
            type: "POST",
            url: ksUrl,
            showLoader: true,
            data: {
                id: $(this).data('id'),
            },
            success: function(ksResponse) {
                location.reload();
            }
        });
    });

    $('body').on('click','.ks-reload',function(){
        $("body").trigger('processStart');
    });
});