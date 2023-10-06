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
        var ksUrl = url.build('multivendor/product_ajax/disable');
        if (this.checked) {
            var ksUrl = url.build('multivendor/product_ajax/enable');
        }
        $.ajax({
            type: "POST",
            url: ksUrl,
            showLoader: true,
            data: {
                entity_id: $(this).data('id'),
                store_id: $(this).data('storeid'),
            },
            success: function(ksResponse) {
                location.reload();
            }
        });
    });
});