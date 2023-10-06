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
    'Magento_Ui/js/form/element/abstract',
    'mage/url'
], function($ ,Abstract , url) {
    "use strict";

    /**
    * Return the UI Component
    */
    return Abstract.extend({
        
        /**
         * Initialization method
         */
        initialize: function () {

            this._super(); 
            this.valueUpdate = 'keyup';
            
        },

        /**
         * Callback that fires when 'value' property is updated.
         */
        onUpdate: function () {
            var ksSellerStoreNameVal = this.value();
            var ksSellerStoreUrlVal= ksSellerStoreNameVal.replace(/[^a-z^A-Z^0-9\.\-]/g,'-').toLowerCase();
            $("input[name$='[ks_store_url]").val(ksSellerStoreUrlVal);
            $("input[name$='[ks_store_url]").trigger('change');
            this._super();
        }

    });
});