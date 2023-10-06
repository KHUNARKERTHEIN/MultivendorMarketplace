/**
 * Ksolves
 *
 * @category  Ksolves
 * @package   Ksolves_MultivendorMarketplace
 * @author    Ksolves Team
 * @copyright Copyright (c) Ksolves India Limited (https://www.ksolves.com/)
 * @license   https://store.ksolves.com/magento-license
 */
 
define([
    'jquery',
    'Magento_Ui/js/modal/modal',
    'jquery/validate',
    'Magento_Ui/js/lib/validation/utils',
    'mage/translate'
], function ($, modal, Validation, utils) {
    'use strict';

    /**
     */
    $.widget('mage.ksAdvancedPriceDialog', {

         /**
         * * Fired when widget initialization start
         * @private
         */
        _create: function () {

            // create custom validation method
            $.validator.addMethod('validate-positive-percent-decimal', function (value) {
                var numValue;

                if (utils.isEmptyNoTrim(value) || !/^\s*-?\d*(\.\d*)?\s*$/.test(value)) {
                    return false;
                }
                numValue = utils.parseNumber(value);
                if (isNaN(numValue)) {
                    return false;
                }
                return utils.isBetween(numValue, 0.01, 100);
            },
            $.mage.__('Please enter a valid percentage discount value greater than 0.'));
                
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
                title: $.mage.__("Advanced Pricing"),
                modalCloseBtnHandler: function() {
                    if(ksSelf._ksStopCloseModal()){
                        return false;
                    }
                    this.closeModal();
                },
                outerClickHandler: function() { 
                    if(ksSelf._ksStopCloseModal()){
                        return false;
                    }
                    $("#ks_advanced_pricing_modal").modal("closeModal");
                },
                buttons: [{
                    text: $.mage.__('Done'),
                    class: 'ks-action-btn ks-primary',
                    click: function () { 
                        if(ksSelf._ksStopCloseModal()){
                            return false;
                        }
                        this.closeModal();
                    }
                }]
             };
                
            modal(options, $('#ks_advanced_pricing_modal'));

            $("#ks_advanced_pricing_link").on('click',function(){
                $("#ks_advanced_pricing_modal .ks-field-control :input").attr('form', 'ks_product_form');
                $("#ks_advanced_pricing_modal").modal("openModal");
            });
        },

        /**
         * close Modal 
         *
         * @private
         */
        _ksStopCloseModal: function () {  
             
            var ksNotValidate =0;
            $('#ks_advanced_pricing_modal label.mage-error').remove();
            $('#ks_advanced_pricing_modal div.mage-error').remove();
            $("#ks_advanced_pricing_modal :input").not(':hidden').not('.ks-use-default-control').each(function(index) {
                
                if(!$.validator.validateSingleElement($(this))){
                    ksNotValidate++;
                }
            });
            
            return ksNotValidate;
        }

    });
    
    return $.mage.ksAdvancedPriceDialog;
});
