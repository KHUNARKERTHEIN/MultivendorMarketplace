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
    "Magento_Ui/js/modal/modal",
    "mage/url",
    'uiRegistry',
    'ko'
], function($, modal, url, uiRegistry, ko) {
    var options = {
        type: 'slide',
        responsive: true,
        innerScroll: true,
        title: 'Related Products List',
        closed: function(e) {
            $('.ks-commission-action').removeClass('disabled');
        },
        closeText: $.mage.__('Close'),
        buttons: [],

    };

    $('body').on('click', '.ks-commission-action', function() {
        $('.ks-commission-action').addClass('disabled');
        $.ajax({
            type: "POST",
            url: url.build("multivendor/commissionrule/productslist"),
            showLoader: true,
            data: {
                form_key: window.FORM_KEY,
                ksRuleId: $(this).data('id'),

            },
            success: function(ksResponse) {

                uiRegistry.get(function(component) {

                    if (component.name != undefined) {
                        if (component.name.indexOf('ks_marketplace_commission_products_listing') != -1) {
                            uiRegistry.remove(component.name);
                        }
                    }
                });

                ko.cleanNode($('.product-container')[0]);
                $('.product-container').html(ksResponse);
                $(".product-container").trigger('contentUpdated');
                $('.product-container').applyBindings();
                $('.product-container').modal(options).modal('openModal');
            }
        });

    });
});
