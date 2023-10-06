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
    'underscore',
    'uiRegistry',
    'Magento_Ui/js/form/element/select',
    'Magento_Ui/js/modal/modal'
], function(_, uiRegistry, select, modal) {
    'use strict';
    return select.extend({

        initialize: function() {
            var ksType = this._super().initialValue;
            setTimeout(function() {
                if (ksType == 'to_percent') {
                    uiRegistry.get("index = ks_min_price", function(input) {
                        input.required(false);
                        input.validation['required-entry'] = false;
                    }.bind(this));

                    uiRegistry.get("index = ks_commission_value", function(input) {
                        input.validation['validate-digits-range'] = '0-100';
                    }.bind(this));
                } else {
                    uiRegistry.get("index = ks_min_price", function(input) {
                        input.required(true);
                        input.validation['required-entry'] = true;
                    }.bind(this));
                }
                return this;
            }, 1);
        },

        /**
         * On value change handler.
         *
         * @param {String} value
         */
        onUpdate: function(value) {
            var ksType = uiRegistry.get("index = ks_commission_type").value();
            if (ksType == 'to_percent') {
                uiRegistry.get("index = ks_min_price", function(input) {
                    input.required(false);
                    input.validation['required-entry'] = false;
                }.bind(this));

                uiRegistry.get("index = ks_commission_value", function(input) {
                    input.validation['validate-digits-range'] = '0-100';
                }.bind(this));
            } else {
                uiRegistry.get("index = ks_min_price", function(input) {
                    input.required(true);
                    input.validation['required-entry'] = true;
                }.bind(this));

                uiRegistry.get("index = ks_commission_value", function(input) {
                    input.validation['validate-digits-range'] = '';
                }.bind(this));
            }
            return this._super();
        },
    });
});
