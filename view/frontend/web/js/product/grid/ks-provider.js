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
    'Magento_Ui/js/grid/provider'
], function ($, provider) {
    'use strict';

    return provider.extend({

        /**
         * Reloads data with current parameters.
         *
         * @returns {Promise} Reload promise object.
         */
        reload: function (options) {

            if (this.params.namespace=='ks_product_review_listing') {
                this.params.current_product_id = $("input[name='id']").val();
            }else if(this.params.namespace=='ks_related_product_listing'){
                this.params.current_product_id = $("input[name='id']").val();

                var  ksExcludeRelatedIds = [];
                $("[data-role=related-product-grid] [data-role=id]").each(function() {
                    ksExcludeRelatedIds.push($(this).val());
                });

                this.params.filters_modifier = {"entity_id":{"condition_type":"nin","value":ksExcludeRelatedIds}};


            }else if(this.params.namespace=='ks_upsell_product_listing'){
                this.params.current_product_id = $("input[name='id']").val();

                var  ksExcludeUpsellIds = [];
                $("[data-role=upsell-product-grid] [data-role=id]").each(function() {
                    ksExcludeUpsellIds.push($(this).val());
                });

                this.params.filters_modifier = {"entity_id":{"condition_type":"nin","value":ksExcludeUpsellIds}};

            }else if(this.params.namespace=='ks_crosssell_product_listing'){
                this.params.current_product_id = $("input[name='id']").val();

                var  ksExcludeCrosssellIds = [];
                $("[data-role=crosssell-product-grid] [data-role=id]").each(function() {
                    ksExcludeCrosssellIds.push($(this).val());
                });

                this.params.filters_modifier = {"entity_id":{"condition_type":"nin","value":ksExcludeCrosssellIds}};
            }else if(this.params.namespace=='ks_inventory_source_listing'){

                var  ksExcludeSources = [];
                $("[data-role=assign-source-grid] [data-role=source_code]").each(function() {
                    ksExcludeSources.push($(this).val());
                });

                this.params.filters_modifier = {"source_code":{"condition_type":"nin","value":ksExcludeSources}};
            }else if(this.params.namespace=='ks_product_custom_options_listing'){
                this.params.current_product_id = $("input[name='id']").val();
            }else if(this.params.namespace=='ks_grouped_product_listing'){
                this.params.current_product_id = $("input[name='id']").val();

                var  ksExcludeGroupedProductIds = [];
                $("[data-role=grouped-product-grid] [data-role=id]").each(function() {
                    ksExcludeGroupedProductIds.push($(this).val());
                });
                this.params.filters_modifier = {"entity_id":{"condition_type":"nin","value":ksExcludeGroupedProductIds}};
            }else if(this.params.namespace=='ks_bundle_product_listing'){
                this.params.current_product_id = $("input[name='id']").val();
            }

            return this._super();
        },
    });
});
