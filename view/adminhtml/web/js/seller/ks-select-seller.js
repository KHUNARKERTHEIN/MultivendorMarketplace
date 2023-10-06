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
         * Set caption
         */
        setCaption: function () { 

            if(this.getSelected().length){
               var ksCustomerId = this.getSelected()[0].value;
                
                if(ksCustomerId){
                    $.ajax({
                        url: url.build("multivendor/seller_ajax/customerdata"),
                        showLoader: true,
                        data: {"ks_customer_id":ksCustomerId},
                        type: "POST",
                        dataType : "json",
                        success: function(result){
                            $("[data-index='ks_owner_fieldset'] .admin__fieldset-wrapper-content").html(result.output);
                            $("[data-index='ks_owner_fieldset']").css("display","block");

                            $('[data-index="ks_new_customer_fieldset"] .ks-customer-fields').each(function(){
                                var ksCustomerField = $(this).data('index');
                                uiRegistry.get('index='+ksCustomerField).disable(true);
                            });
                            $(".ks-check-customer").html(" ");
                            $(".ks-check-customer").removeClass('admin__field-error');
                            uiRegistry.get('ks_marketplace_seller_add.ks_marketplace_seller_add.ks_new_customer_fieldset').visible(false);
                        }
                    });
                }
            }
            return this._super();
           
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
                    url: url.build("multivendor/seller_ajax/customerlist"),
                    data: {
                        form_key: window.FORM_KEY,
                        ks_website_id: uiRegistry.get('index=website_id').value(),
                        ks_search_key: value
                    },
                    success: function (ksResponse) { 
                       var ksOptionList = ksResponse.output;
                       var ksOptionLength = ksResponse.ksLength;
                       self.updateOption(ksOptionList);

                        array = self.selectType === 'optgroup' ?
                            self._getFilteredArray(self.cacheOptions.lastOptions, value) :
                            self._getFilteredArray(self.cacheOptions.plain, value);

                        if (ksOptionLength) {
                            if (!value.length) {
                                self.options(self.cacheOptions.plain);
                                self._setItemsQuantity(self.cacheOptions.plain.length);
                            } else {
                                self.options(array);
                                self._setItemsQuantity(array.length);
                            }
                        } else {
                            self.options(self.cacheOptions.plain);
                            self._setItemsQuantity(0);
                        }
                    }
                });

                return false;
            }

            this.options([]);
        },
    });
});


