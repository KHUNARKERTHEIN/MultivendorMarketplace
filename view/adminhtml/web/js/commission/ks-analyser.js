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
    'Magento_Ui/js/form/element/select',
    "mage/url",
    'ko'
], function($, uiRegistry, Select, url, ko) {

    'use strict';


    return Select.extend({


        /**
         * Initializes UISelect component.
         *
         * @returns {UISelect} Chainable.
         */
        initialize: function() {
            return this._super();

        },

        /**
         * On value change handler.
         *
         * @param {String} value
         */
        onUpdate: function(value) {
            var ksIsUpdateCommissionList = false;
            uiRegistry.get("index = ks_products", function(input) {
                    if(input.value() != null && input.value().constructor != Array) {
                        ksIsUpdateCommissionList = true;
                    }
                    input.disabled(true);
                    input.value('');
                    input.filterInputValue('');
                    input.setCaption();
            }.bind(this));
            uiRegistry.get("index = ks_sellers", function(input) {
                    input.disabled(true);
                    input.filterInputValue('');
                    input.value('');
            }.bind(this));
            if (this.value() == 'null') {
                uiRegistry.get("index = ks_category_ids", function(input) {
                    input.disabled(true);
                    input.value('');
                }.bind(this));
                
            } else {
                $.ajax({
                    type: "POST",
                    url: url.build("multivendor/commissionrule_ajax/analyserdata"),
                    data: {
                        form_key: window.FORM_KEY,
                        ksWebsiteId: this.value(),
                    },
                    success: function(ksResponseData) {
                        uiRegistry.get("index = ks_category_ids", function(input) {
                            input.cacheOptions.options = ksResponseData.output;
                            input.cacheOptions.tree = ksResponseData.output;
                            input.options(ksResponseData.output);
                            input.disabled(false);
                            input.value('');
                        }.bind(this));
                        
                    }
                });
            }
            if (ksIsUpdateCommissionList) {
                this.ksUpdateCommissionsList();
            }
           
        },

        /**
         * Update rules list
         */
        ksUpdateCommissionsList: function() {
            var ks_seller_id = uiRegistry.get('index = ks_sellers').value();
            var ksProductId = uiRegistry.get('index = ks_products').value();
            var ksQuantity = uiRegistry.get('index = ks_quantity').value();
            var ksTax = uiRegistry.get('index = ks_tax').value();
            if (typeof uiRegistry.get("index = ks_subtotal").value() == "string" && isNaN(uiRegistry.get("index = ks_subtotal").value().charAt(0))) {
                var ksDiscount = Number(uiRegistry.get("index = ks_discount").value().replace(/[^0-9.-]+/g,""));
                var ksPrice = Number(uiRegistry.get('index = ks_price').value().replace(/[^0-9.-]+/g,""));
            } else {
                var ksDiscount = uiRegistry.get('index = ks_discount').value();
                var ksPrice = uiRegistry.get('index = ks_price').value();
            }
            if (uiRegistry.get('index = ks_quantity').validate().valid == true) {
                $.ajax({
                    type: "POST",
                    url: url.build("multivendor/commissionrule_ajax/commissionruleslist"),
                    data: {
                        form_key: window.FORM_KEY,
                        ksProductId: ksProductId,
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
        }
    });
});
