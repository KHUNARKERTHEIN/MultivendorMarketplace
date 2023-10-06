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
    'uiRegistry',
    'mage/url',
    '../ks-seller-list',
    'ko'
], function($, uiRegistry, url, Sellerlist, ko) {
    'use strict';

    return Sellerlist.extend({

        /**
         * Initializes UISelect component.
         *
         * @returns {UISelect} Chainable.
         */
        initialize: function() {
            this.ksGetCommissionList(false, null);
            return this._super();

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
         * Set caption
         */
        setCaption: function() {
            var length, caption = '';

            if (!_.isArray(this.value()) && this.value() && this.value() != 0) {
                length = 1;
            } else if (this.value() && this.value() != 0) {
                length = this.value().length;
            } else {
                this.value([]);
                length = 0;
            }
            this.warn(caption);
            var ks_seller_id = this.value();
            var ksProductid;
            uiRegistry.get("index = ks_products", function(input) {
                ksProductid = input.value();
            }.bind(this));
            if (ksProductid.constructor != Array || ks_seller_id.constructor != Array) {
                this.ksGetCommissionList(true, ksProductid);

            }

            //check if option was removed
            if (this.isDisplayMissingValuePlaceholder && length && !this.getSelected().length) {
                var self = this;
                $.ajax({
                    type: "POST",
                    showLoader: true,
                    url: url.build("multivendor/seller_ajax/selectedseller"),
                    data: {
                        form_key: window.FORM_KEY,
                        ks_seller_id: this.value
                    },
                    success: function(ksResponse) {

                        caption = ksResponse.output;
                        self.placeholder(caption);
                        self.warn(caption);
                    }
                });
                return this.placeholder();

            }

            if (length > 1) {
                this.placeholder(length + ' ' + this.selectedPlaceholders.lotPlaceholders);
            } else if (length && this.getSelected().length) {
                this.placeholder(this.getSelected()[0].label);
            } else {
                this.placeholder(this.selectedPlaceholders.defaultPlaceholder);
            }

            return this._super();
        },

        ksGetCommissionList: function(ksUpdateList, ksProductId) {
            var ks_seller_id = null;
            var ksProductid = null;
            var ksPrice = null;
            var ksDiscount = null;
            var ksQuantity = null;
            var ksTax = null;
            if (ksUpdateList) {
                ks_seller_id = this.value();
                ksProductid = ksProductId;
                ksPrice = Number(uiRegistry.get("index = ks_price").value().replace(/[^0-9.-]+/g,""))
                ksDiscount = Number(uiRegistry.get("index = ks_discount").value().replace(/[^0-9.-]+/g,""))
                ksQuantity = uiRegistry.get('index = ks_quantity').value();
                ksTax = uiRegistry.get('index = ks_tax').value();
            }
            if ((ksUpdateList && uiRegistry.get('index = ks_quantity').validate().valid == true) || !ksUpdateList) {
                $.ajax({
                    type: "POST",
                    url: url.build("multivendor/commissionrule_ajax/commissionruleslist"),
                    data: {
                        form_key: window.FORM_KEY,
                        ksProductId: ksProductid,
                        ksSellerId: ks_seller_id,
                        ksPrice: ksPrice,
                        ksDiscount: ksDiscount,
                        ksQuantity: ksQuantity,
                        ksTax: ksTax,
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
        },

        /**
         * Filtered options list by value from filter options list
         */
        filterOptionsList: function () {
            var value = this.filterInputValue().trim().toLowerCase(),
                array = [];

            if (this.searchOptions) {
                return this.loadOptions(value);
            }

            this.cleanHoveredElement();

            if (!value) {
                this.renderPath = false;
                this.options([]);
                this._setItemsQuantity(false);

                return false;
            }

            this.showPath ? this.renderPath = true : false;

            if (this.filterInputValue()) {
                var ksProductId; 
                uiRegistry.get("index = ks_products", function(input) {
                    ksProductId = input.value();
                }.bind(this));
                var self = this;
                $.ajax({
                    type: "POST",
                    url: url.build("multivendor/seller_ajax/sellerlist"),
                    data: {
                        form_key: window.FORM_KEY,
                        ks_search_key: value,
                        ks_product_id: ksProductId,
                    },
                    success: function (ksResponse) { 
                       var ksOptionList = ksResponse.output;
                       self.updateOption(ksOptionList);

                        array = self.selectType === 'optgroup' ?
                            self._getFilteredArray(self.cacheOptions.lastOptions, value) :
                            self._getFilteredArray(self.cacheOptions.plain, value);

                        if (!value.length) {
                            self.options(self.cacheOptions.plain);
                            self._setItemsQuantity(self.cacheOptions.plain.length);
                        } else {
                            self.options(array);
                            self._setItemsQuantity(array.length);
                        }

                    }
                });

                return false;
            }

            this.options([]);
        },
    });
});
