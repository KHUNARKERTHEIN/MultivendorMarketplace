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
        title: '',
        closed: function(e) {
            $('.ks-analyse').removeClass('disabled');
            $('.ks-calculate').removeClass('disabled');
        },
        closeText: $.mage.__('Close'),
        buttons: [],

    };

    $('body').on('click', '.ks-analyse', function() {
        $('.ks-analyse').addClass('disabled');
        options.title = "Commission Analyzer";
        $('.ks-analyser-form').modal(options).modal('openModal');
    });

    $('body').on('click', '.ks-calculate, .ks-calculation-button', function() {
        options.title = "Commission Calculator";
        setTimeout(function() {
            $('.ks-calculate').addClass('disabled');
            if ($(".ks_calculator_tax input[type='radio']:checked").val() == 'fixed') {
                $("#ks-doller-button.ks_calculator_tax").addClass('ks-selected-radio');
            }
            if ($(".ks_calculator_discount input[type='radio']:checked").val() == 'fixed') {
                $("#ks-doller-button.ks_calculator_discount").addClass('ks-selected-radio');
            }
            if ($(".ks_commission_value_calculator input[type='radio']:checked").val() == 'fixed') {
                $("#ks-doller-button.ks_commission_value_calculator").addClass('ks-selected-radio');
            }
            $('.ks-calculator-form').modal(options).modal('openModal');
        }, 1500);
    });

    $('body').on('click', '.save', function() {
        $('.ks-calculator-form').remove();
        $('#ks_marketplace_commission_calculator').remove();

    });
});
