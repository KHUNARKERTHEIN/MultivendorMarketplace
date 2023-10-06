/**
 * Ksolves
 *
 * @category  Ksolves
 * @package   Ksolves_Multivendor
 * @author    Ksolves Team
 * @copyright Copyright (c) Ksolves India Limited (https://www.ksolves.com/)
 * @license   https://store.ksolves.com/magento-license
 */
define([
    'jquery',
    'Magento_Ui/js/modal/modal',
    'mage/url',
    'mage/translate'
], function ($, modal, url) {
    'use strict';

    /**
     */
    $.widget('mage.ksEarningCalculatorDialog', {

         /**
         * * Fired when widget initialization start
         * @private
         */
        _create: function () {
            this._ksOpenModal();
        },

        /**
         * Open Modal 
         *
         * @param options
         * @param cookie
         * @private
         */
        _ksOpenModal: function () {  
            var ksSelf = this;

            var options = {
                type: 'slide',
                responsive: true,
                innerScroll: true,
                title: $.mage.__("Earnings Calculator"),
                buttons: []
            };
                
            modal(options, $('#ks_earning_calculator_modal'));
            $("#ks-doller-button.ks-tax").addClass('ks-selected-radio');

            $("#ks_earning_calculator_link").on('click',function(){
                var ksUrl = window.location.href;
                if (!ksUrl.includes('edit')) {
                    $("#ks_earning_calculator_modal .ks-field-control :input").attr('form', 'ks_product_form');
                    $("#ks_earning_calculator_modal").modal("openModal");
                    $.ajax({
                        type: "POST",
                        url: url.build("multivendor/product_ajax/KsEarningCalculator"),
                        data: {
                            form_key: window.FORM_KEY,
                            ksGetRulesData: true,
                            ksProductForm: $('#ks_product_form').serializeArray()
                        },
                        success: function(ksResponse) {
                            $('.ks_calculation_base_on').val(ksResponse.ksRule['ks_calculation_baseon']);
                            $('.ks_commission_value').val(ksResponse.ksRule['ks_commission_value']);
                            if(ksResponse.ksRule['ks_commission_type'] == 'to_fixed') {
                               $('.ksCommissionValue :input[type=radio]')[0].checked = true;
                               $("#ks-doller-button.ksCommissionValue").addClass('ks-selected-radio');
                               $("#ks-percent-button.ksCommissionValue").removeClass('ks-selected-radio')
                            } else {
                               $('.ksCommissionValue :input[type=radio]')[0].checked = false;
                               $("#ks-doller-button.ksCommissionValue").removeClass('ks-selected-radio');
                               $("#ks-percent-button.ksCommissionValue").addClass('ks-selected-radio'); 
                            } 
                        }
                    });
                } else {
                        $("#ks_earning_calculator_modal .ks-field-control :input").attr('form', 'ks_product_form');
                        $("#ks_earning_calculator_modal").modal("openModal");
                        if($('.ksCommissionValue :input[type=radio]:unchecked').val() == 'percent') {
                            $("#ks-doller-button.ksCommissionValue").addClass('ks-selected-radio');
                            $("#ks-percent-button.ksCommissionValue").removeClass('ks-selected-radio');
                        } else {
                            $("#ks-doller-button.ksCommissionValue").removeClass('ks-selected-radio');
                            $("#ks-percent-button.ksCommissionValue").addClass('ks-selected-radio');
                        }
                }
            });
        },
    });
    
    return $.mage.ksEarningCalculatorDialog;
});
