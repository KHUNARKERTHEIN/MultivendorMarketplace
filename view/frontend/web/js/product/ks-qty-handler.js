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

        $ksQty: $("#ks-qty"),

        /**
         * Init
         */
        init: function () { 
            if(ksProductType.type.current == 'configurable' 
                || ksProductType.type.current =='grouped'
                || ksProductType.type.current =='bundle'){

                this.$ksQty.prop('disabled', true);
                this.$ksQty.addClass('ignore-validate');

                if(ksProductType.type.current == 'configurable' 
                    || ksProductType.type.current =='bundle'){
                    this.$ksQty.val('');
                }
            }else{
                this.$ksQty.prop('disabled', false);
                this.$ksQty.removeClass('ignore-validate');
            }
        }
    };
});
