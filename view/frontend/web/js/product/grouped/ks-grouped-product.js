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
    'Ksolves_MultivendorMarketplace/js/product/ks-add-grid-item',
    'mage/template',
    'Magento_Ui/js/modal/modal',
    "mage/url",
    'uiRegistry',
    'ko',
    'mage/translate'
], function ($, ksGridItem , mageTemplate, modal, url,uiRegistry, ko) {
    'use strict';

    /**
     */
    $.widget('mage.ksGroupedProductDialog', {

        /**
         * * Fired when widget initialization start
         * @private
         */
        _create: function () {

            this.$grid = this.element.find('[data-role=grouped-product-grid]');

            this.productTmpl = mageTemplate('#ks_grouped_product_template');

            $.each(
                this.$grid.data('products'),
                $.proxy(function (index, product) {
                    ksGridItem._ksAdd(null,product,this.$grid, this.productTmpl);
                }, this)
            );
            this._ksOpenModal();
            this._remove();

            ksGridItem._ksSort(this.element, this.$grid);
            ksGridItem._ksUpdateGridVisibility(this.element);
            ksGridItem._ksUpdatePager(this.element);
            ksGridItem._ksPagingNumerUpdate(this.element);
            ksGridItem._ksChangePager(this.element);

            var self =this;
            $('body').on('blur', '[data-role=grouped-product-grid] [data-role=position]', function(event){
               ksGridItem._ksResort(self.$grid);
            }); 
        },

        /**
         * Remove product
         * @param {EventObject} event
         * @private
         */
        _remove: function () {
            var self= this;
            $('body').on('click', '[data-role=grouped-product-grid] [data-role=delete]', function(event){
                ksGridItem._ksRemove(event,this.element);
                ksGridItem._ksUpdateGridVisibility(self.element);
                ksGridItem._ksPagingNumerUpdate(self.element);
                ksGridItem._ksResort(self.$grid);
            });

        },

        /**
         * Open Modal
         *
         * @param options
         * @param cookie
         * @private
         */
        _ksOpenModal: function () {

            var self= this,
                selectedProductList = {};

            $(".ks-add-grouped-product").on('click',function(){
                var ksTitle = $(this).data('title');
                ko.cleanNode($("#ks_grouped_product_modal")[0]);

                var options = {
                    type: 'slide',
                    responsive: true,
                    innerScroll: true,
                    title: ksTitle,
                    closed: function() {
                        $("#ks_grouped_product_modal").html("");
                    },
                    buttons: [{
                        text: $.mage.__('Add Selected Products'),
                        class: 'ks-action-btn ks-primary',
                        click: function () {
                            this.closeModal();
                            self._ksAddSelectedProducts();
                            ksGridItem._ksPagingNumerUpdate(self.element);
                        }
                    },
                        {
                            text: $.mage.__('Cancel'),
                            class: 'ks-action-btn',
                            click: function () {
                                this.closeModal();
                            }
                        }]
                };

                $.ajax({
                    type: "POST",
                    url: url.build("multivendor/product_ajax/ksaddgroupedproduct"),
                    showLoader: true,
                    data: [],
                    success: function (ksResponse) {
                        uiRegistry.get(function(component){
                            if(component.name != undefined){
                                if (component.name.indexOf('ks_grouped_product_listing') != -1) {
                                    uiRegistry.remove(component.name);
                                }
                            }
                        });
                        $("#ks_grouped_product_modal").html(ksResponse);
                        $("#ks_grouped_product_modal").trigger('contentUpdated');
                        $("#ks_grouped_product_modal").applyBindings();

                    }
                });

                modal(options, $("#ks_grouped_product_modal"));
                $("#ks_grouped_product_modal").modal("openModal");
            });

        },

        /**
         * Add selected product
         *
         * @private
         */
        _ksAddSelectedProducts: function () {
            
            var self = this;
            var ksTableRow = this.element.find("tbody tr");
            var indexValue = ksTableRow.length;

            $("#ks_grouped_product_modal .data-grid-checkbox-cell input[type='checkbox']:checked").each(function(){

                var trElement = $(this).closest('.data-row');
                var ksid = $(this).val();

                var product =  {
                    index: indexValue,
                    id: ksid,
                    thumbnail: trElement.find('.data-grid-thumbnail-cell').find('img').attr('src'),
                    name: trElement.find('.ks-product-name').find('div').text(),
                    status: trElement.find('.ks-product-status').find('div').text(),
                    attributeset: trElement.find('.ks-product-attr-set').find('div').text(),
                    sku: trElement.find('.ks-product-sku').find('div').text(),
                    price: trElement.find('.ks-product-price').find('div').text(),
                    position: indexValue
                }

                indexValue++;

                ksGridItem._ksAdd(null,product,self.$grid, self.productTmpl);

            });

            ksGridItem._ksSort(self.element, self.$grid);
            ksGridItem._ksUpdateGridVisibility(self.element);
        },

    });


    return $.mage.ksGroupedProductDialog;
});
