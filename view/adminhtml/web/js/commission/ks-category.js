/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'mage/url',
    'Magento_Ui/js/form/element/ui-select',
    'uiRegistry',
    'ko'
], function($, url, Select, uiRegistry, ko) {
    'use strict';

    return Select.extend({

        /**
         * Parse data and set it to options.
         *
         * @param {Object} data - Response data object.
         * @returns {Object}
         */
        setParsed: function(data) {
            var option = this.parseData(data);

            if (data.error) {
                return this;
            }

            this.options([]);
            this.setOption(option);
            this.set('newOption', option);
        },

        /**
         * Set caption
         */
        setCaption: function() {
            var length, caption = '';
            var ksCategory = this.value();
            uiRegistry.get("index = ks_products", function(input) {
                input.value('');
                input.filterInputValue('');
                if (this.value().constructor != Array && this.value() != '') {
                    input.disabled(false);
                } else {
                    input.disabled(true);
                }
            }.bind(this));
            uiRegistry.get("index = ks_sellers", function(input) {
                input.value('');
                input.disabled(true);
                input.filterInputValue('');
            }.bind(this));

            this.ksUpdateCommissionsList();

            if (!_.isArray(this.value()) && this.value()) {
                length = 1;
            } else if (this.value()) {
                length = this.value().length;
            } else {
                this.value([]);
                length = 0;
            }
            this.warn(caption);

            //check if option was removed
            if (this.isDisplayMissingValuePlaceholder && length && !this.getSelected().length) {
                caption = this.missingValuePlaceholder.replace('%s', this.value());
                this.placeholder(caption);
                this.warn(caption);

                return this.placeholder();
            }

            if (length > 1) {
                this.placeholder(length + ' ' + this.selectedPlaceholders.lotPlaceholders);
            } else if (length && this.getSelected().length) {
                this.placeholder(this.getSelected()[0].label);
            } else {
                this.placeholder(this.selectedPlaceholders.defaultPlaceholder);
            }

            return this.placeholder();
        },

        /**
         * Normalize option object.
         *
         * @param {Object} data - Option object.
         * @returns {Object}
         */
        parseData: function(data) {
            return {
                'is_active': data.category['is_active'],
                level: data.category.level,
                value: data.category['entity_id'],
                label: data.category.name,
                parent: data.category.parent
            };
        },

        /**
         * Toggle list visibility
         *
         * @returns {Object} Chainable
         */
        toggleListVisible: function () {
            if (!this.disabled()) {
                this.listVisible(!this.listVisible());
            }

            return this;
        },


        /**
         * Update rules list
         */
        ksUpdateCommissionsList: function() {
            $.ajax({
                type: "POST",
                url: url.build("multivendor/commissionrule_ajax/commissionruleslist"),
                data: {
                    form_key: window.FORM_KEY,
                    ksProductId: null,
                    ksSellerId: null,
                    ksPrice: null,
                    ksDiscount: null,
                    ksQuantity: null,
                    ksTax: null,
                },
                success: function(ksResponseData) {
                    uiRegistry.get(function(component) {

                        if (component.name != undefined) {
                            if (component.name.indexOf('ks_marketplace_seller_product_commission_listing') != -1) {
                                uiRegistry.remove(component.name);
                            }
                        }
                    });
                    ko.cleanNode($('.rules-container')[0]);
                    $('.rules-container').html(ksResponseData);
                    $(".rules-container").trigger('contentUpdated');
                    $('.rules-container').applyBindings();
                }
            });
        }
    });
});
