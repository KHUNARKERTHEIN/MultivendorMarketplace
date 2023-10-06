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
    'mage/url',
    'Magento_Ui/js/form/element/ui-select'
], function ($ , url , Select) {
    'use strict';

    return Select.extend({

        /**
         * Toggle activity list element
         *
         * @param {Object} data - selected option data
         * @returns {Object} Chainable
         */
        toggleOptionSelected: function (data) {
             var isSelected = this.isSelected(data.value);

            if (this.lastSelectable && data.hasOwnProperty(this.separator)) {
                return this;
            }

            if (!this.multiple) {
                if (!isSelected) {
                    this.value(data.value);
                }
                this.listVisible(false);
            } else {
                if (!isSelected) { /*eslint no-lonely-if: 0*/
                    this.value.push(data.value);
                } else {
                    this.value(_.without(this.value(), data.value));
                }
            }

            var pattern = /(set\/)(\d)*?\//,
                change = '$1' + data.value + '/';

            var ksCurrentUrl = $(location).attr('href');

            if(ksCurrentUrl.indexOf("/set/") != -1){
                var ksNewUrl = ksCurrentUrl.replace(pattern, change);

            }  else{
                var ksNewUrl = ksCurrentUrl + "set/"+data.value+"/";
            }

            $.ajax({
                 showLoader: true,
                 url: url.build("multivendor/product_ajax/ksupdateattributeset"),
                 data: $("#ks_product_form").serialize(),
                 type: "POST",
            }).done(function (data) {
                window.location.href = ksNewUrl;
            });

        },
    });
});


