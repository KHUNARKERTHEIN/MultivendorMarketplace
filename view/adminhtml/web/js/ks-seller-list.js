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
    'Magento_Ui/js/form/element/ui-select'
], function ($ , uiRegistry , url , Select) {
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
        updateOption: function (ksOptions) {
            var caption,
                value,
                cacheNodes,
                copyNodes;

            ksOptions = setProperty(ksOptions, 'optgroup');
            copyNodes = JSON.parse(JSON.stringify(ksOptions));
            cacheNodes = flattenCollection(copyNodes, 'optgroup');

            ksOptions = _.map(ksOptions, function (node) {
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
            this.cacheOptions.plain =  _.compact(cacheNodes);
            this.cacheOptions.tree =  _.compact(ksOptions);
        },

        /**
         * Initializes UISelect component.
         *
         * @returns {UISelect} Chainable.
         */
        initialize: function () {
            return this._super();
        
        },

        /**
         * Set caption
         */
        setCaption: function () { 
            var length, caption = '';
           
            if (!_.isArray(this.value()) && this.value() && this.value()!= 0) {
                length = 1;
            } else if (this.value() && this.value()!= 0) {
                length = this.value().length;
            } else {
                this.value([]);
                length = 0;
            }
            this.warn(caption);
             
            //check if option was removed
            if (this.isDisplayMissingValuePlaceholder && length && !this.getSelected().length) {
                var self= this;
                $.ajax({
                    type: "POST",
                    showLoader: true,
                    url: url.build("multivendor/seller_ajax/selectedseller"),
                    data: {
                        form_key: window.FORM_KEY,
                        ks_seller_id: this.value
                    },
                    success: function (ksResponse) {
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
            

            return this.placeholder();
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

                var self = this;
                $.ajax({
                    type: "POST",
                    url: url.build("multivendor/seller_ajax/sellerlist"),
                    data: {
                        form_key: window.FORM_KEY,
                        ks_search_key: value
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

