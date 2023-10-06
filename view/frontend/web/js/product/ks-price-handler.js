/**
 * Ksolves
 *
 * @category  Ksolves
 * @package   Ksolves_MultivendorMarketplace
 * @author    Ksolves Team
 * @copyright Copyright (c) Ksolves India Limited (https://www.ksolves.com/)
 * @license   https://store.ksolves.com/magento-license
 */

/**
 * @api
 */
define([
    'jquery',
    'Ksolves_MultivendorMarketplace/js/product/ks-type-events'
], function ($, ksProductType) {
    'use strict';

    return {

        $ksPrice: $('input[name="product[price]'),
        $ksPriceType: $('input[name="product[price_type]'),
        $ksAdvancePriceLink: $("#ks_advanced_pricing_link"),
        $ksTaxField: $('select[name="product[tax_class_id]'),
        /**
         * Init
         */
        init: function () { 
            if(ksProductType.type.current == 'configurable'){
                this.$ksPrice.prop('disabled', true);
                this.$ksPrice.val('');
                this.$ksPrice.closest('.ks-dollor-sign').addClass('disabled');
                this.$ksPrice.addClass('ignore-validate');
                this.$ksAdvancePriceLink.hide();
            }
            else if(ksProductType.type.current == 'bundle'){
                if(this.$ksPriceType.is(":checked")){
                    this.$ksPrice.prop('disabled', true);
                    this.$ksPrice.val('');
                    this.$ksPrice.closest('.ks-dollor-sign').addClass('disabled');
                    this.$ksTaxField.prop('disabled',true);
                    this.$ksPrice.addClass('ignore-validate');
                    $('.ks-custom-option-action,.ks-custom-option-text').hide();
                    $('#dynamic-price-warning').show();
                }else{
                    this.$ksPrice.prop('disabled', false);
                    this.$ksPrice.removeClass('ignore-validate');
                    this.$ksPrice.closest('.ks-dollor-sign').removeClass('disabled');
                    this.$ksTaxField.prop('disabled',false);
                    $('.ks-custom-option-action,.ks-custom-option-text').show();
                    $('#dynamic-price-warning').hide();
                }
            }else{
                if(!this.$ksPrice.closest('.ks-use-default').find('.ks-use-default-control').is(":checked"))
                {
                    this.$ksPrice.prop('disabled', false);
                    this.$ksPrice.removeClass('ignore-validate');
                    this.$ksPrice.closest('.ks-dollor-sign').removeClass('disabled');
                    this.$ksAdvancePriceLink.show();
                }
                
            }
        }
    };
});
