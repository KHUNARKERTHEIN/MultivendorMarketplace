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
    'Magento_Ui/js/form/element/ui-select',
    'Magento_Catalog/js/price-utils',
    'ko'
], function($, uiRegistry, url, Select, priceUtils, ko) {
    'use strict';

    /**
     * Processing options list
     *
     * @param {Array} array - Property array
     * @param {String} separator - Level separator
     * @param {Array} created - list to add new options
     *
     * @return {Array} Plain options list
     */
    function flattenCollection(array, separator, created) {
        var i = 0,
            length,
            childCollection;

        array = _.compact(array);
        length = array.length;
        created = created || [];

        for (i; i < length; i++) {
            created.push(array[i]);

            if (array[i].hasOwnProperty(separator)) {
                childCollection = array[i][separator];
                delete array[i][separator];
                flattenCollection.call(this, childCollection, separator, created);
            }
        }

        return created;
    }

    /**
     * Set levels to options list
     *
     * @param {Array} array - Property array
     * @param {String} separator - Level separator
     * @param {Number} level - Starting level
     * @param {String} path - path to root
     *
     * @returns {Array} Array with levels
     */
    function setProperty(array, separator, level, path) {
        var i = 0,
            length,
            nextLevel,
            nextPath;

        array = _.compact(array);
        length = array.length;
        level = level || 0;
        path = path || '';

        for (i; i < length; i++) {
            if (array[i]) {
                _.extend(array[i], {
                    level: level,
                    path: path
                });
            }

            if (array[i].hasOwnProperty(separator)) {
                nextLevel = level + 1;
                nextPath = path ? path + '.' + array[i].label : array[i].label;
                setProperty.call(this, array[i][separator], separator, nextLevel, nextPath);
            }
        }

        return array;
    }

    return Select.extend({

        /**
         * update option
         */
        updateOption: function(ksOptions) {
            var caption,
                value,
                cacheNodes,
                copyNodes;

            ksOptions = setProperty(ksOptions, 'optgroup');
            copyNodes = JSON.parse(JSON.stringify(ksOptions));
            cacheNodes = flattenCollection(copyNodes, 'optgroup');

            ksOptions = _.map(ksOptions, function(node) {
                value = node.value;

                if (value == null || value === '') {
                    if (_.isUndefined(caption)) {
                        caption = node.label;
                    }
                } else {
                    return node;
                }
            });
            this.cacheOptions.options = _.compact(ksOptions);
            this.cacheOptions.plain = _.compact(cacheNodes);
            this.cacheOptions.tree = _.compact(ksOptions);
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
         * Initializes UISelect component.
         *
         * @returns {UISelect} Chainable.
         */
        initialize: function() {
            return this._super();

        },

        /**
         * Set caption
         */
        setCaption: function() {
            var length, caption = '';
            var ksproducts = this.value();
            uiRegistry.get('index=ks_sellers', function(input) {
                if (ksproducts.constructor != Array && ksproducts != '') {
                    input.disabled(false);
                }
            });
            if (!_.isArray(this.value()) && this.value() && this.value() != 0) {
                length = 1;
            } else if (this.value() && this.value() != 0) {
                length = this.value().length;
            } else {
                this.value([]);
                length = 0;
            }
            this.warn(caption);
            var ksProductId = this.value();
            var ksTaxRate = uiRegistry.get('index=ks_tax_rate').value();
            if (ksProductId != null) {
                $.ajax({
                    type: "POST",
                    url: url.build("multivendor/commissionrule_ajax/productdata"),
                    data: {
                        form_key: window.FORM_KEY,
                        ksProductId: this.value(),
                        ksRate: ksTaxRate
                    },
                    success: function(ksResponse) {
                        if (ksResponse.output != null) {
                        var ksPriceFormat = {
                            decimalSymbol: '.',
                            groupLength: 3,
                            groupSymbol: ",",
                            integerRequired: false,
                            pattern: window.currency.concat("%s"),
                            precision: 2,
                            requiredPrecision: 2
                        };
                            if (ksResponse.output.hasOwnProperty('price')) {
                                ksResponse.output.price = parseFloat(ksResponse.output.price.replace(/,/g, ''));    
                            }
                            var ksQuantity = uiRegistry.get("index = ks_quantity");
                            uiRegistry.get("index = ks_price", function(input) {
                                input.value(priceUtils.formatPrice(ksResponse.output.price, ksPriceFormat));
                            }.bind(this));
                            uiRegistry.get("index = ks_tax_rate", function(input) {
                                input.disabled(false);
                            }.bind(this));
                            uiRegistry.get("index = ks_tax", function(input) {
                                input.value(ksResponse.Rate);
                            }.bind(this));
                            
                            if (ksQuantity.validate().valid == true) {
                                uiRegistry.get("index = ks_discount", function(input) {
                                    input.value(priceUtils.formatPrice(ksResponse.discount * ksQuantity.value(), ksPriceFormat));
                                }.bind(this));
                                uiRegistry.get("index = ks_subtotal", function(input) {
                                    input.value(priceUtils.formatPrice(ksResponse.output.price * ksQuantity.value(), ksPriceFormat));
                                }.bind(this));
                                uiRegistry.get("index = ks_grandtotal", function(input) {
                                    var ksTax = (ksResponse.output.price - parseFloat(ksResponse.discount.replace(/,/g, ''))) * ksResponse.Rate / 100;
                                    var ksGrTotal = ((ksResponse.output.price - parseFloat(ksResponse.discount.replace(/,/g, '')) + parseFloat(ksTax.toFixed(3))) * ksQuantity.value());
                                    input.value(priceUtils.formatPrice(ksGrTotal.toFixed(3)), ksPriceFormat);
                                }.bind(this));
                            }
                        } else {
                            uiRegistry.get("index = ks_price", function(input) {
                                input.value('');
                            }.bind(this));
                            uiRegistry.get("index = ks_tax_rate", function(input) {
                                input.disabled(true);
                            }.bind(this));
                            uiRegistry.get("index = ks_discount", function(input) {
                                input.value('');
                            }.bind(this));
                            uiRegistry.get("index = ks_subtotal", function(input) {
                                input.value('');
                            }.bind(this));
                            uiRegistry.get("index = ks_grandtotal", function(input) {
                                input.value('');
                            }.bind(this));
                            uiRegistry.get("index = ks_discount", function(input) {
                                input.value('');
                            }.bind(this));
                        }

                        $.ajax({
                            type: "POST",
                            url: url.build("multivendor/commissionrule_ajax/productattributes"),
                            data: {
                                form_key: window.FORM_KEY,
                                id: ksProductId,

                            },
                            success: function(ksResponseData) {
                                ko.cleanNode($('.ks-product-attributes')[0]);
                                $('.ks-product-attributes').html(ksResponseData);
                                $(".ks-product-attributes").trigger('contentUpdated');
                                $('.ks-product-attributes').applyBindings();
                            }
                        });
                    }
                });

                if (length > 1) {
                    this.placeholder(length + ' ' + this.selectedPlaceholders.lotPlaceholders);
                } else if (length && this.getSelected().length) {
                    this.placeholder(this.getSelected()[0].label);
                } else {
                    this.placeholder(this.selectedPlaceholders.defaultPlaceholder);
                }

                return this.placeholder();
            }
        },


        /**
         * Filtered options list by value from filter options list
         */
        filterOptionsList: function() {
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

                var self = this;
                var ksCategory = uiRegistry.get("index=ks_category_ids").value();
                $.ajax({
                    type: "POST",
                    url: url.build("multivendor/commissionrule_ajax/productlist"),
                    data: {
                        form_key: window.FORM_KEY,
                        ks_search_key: value,
                        ks_category: ksCategory
                    },
                    success: function(ksResponse) {
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
