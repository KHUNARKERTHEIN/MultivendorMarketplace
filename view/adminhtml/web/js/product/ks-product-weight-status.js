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
], function (_, uiRegistry, select) {
    'use strict';
    return select.extend({

        initialize: function () {
            this._super();

            this.ksDependentField(this.value());

            return this;
        },

        /**
         * On value change handler.
         *
         * @param {String} value
         */
        onUpdate: function (value) {
            this.ksDependentField(value);

            return this._super();
        },

        /**
         * logic for show or hide dependent field
         *
         * @param {String} value
         */
        ksDependentField: function (value) {
            setTimeout(function() { 
                var ksProductWeightField = uiRegistry.get('index = weight');

                if (value == 1) {
                    ksProductWeightField.enable();
                } else {
                    ksProductWeightField.disable();
                }
            }, 1);

            return this._super();
        }
    });
});
