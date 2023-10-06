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
        var ksUrl = url.build('multivendor/product_ajax/ksdisable');
        if (this.checked) {
            var ksUrl = url.build('multivendor/product_ajax/ksenable');
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