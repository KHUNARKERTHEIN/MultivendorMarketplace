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
    'mage/template',
    'Magento_Ui/js/modal/modal',
    "mage/url",
    'uiRegistry',
    'ko',
    'mage/translate'
], function ($, mageTemplate, modal, url,uiRegistry, ko ) {
    'use strict';

    /**
     */
    $.widget('mage.ksAssignSourcesDialog', {

         /**
         * * Fired when widget initialization start
         * @private
         */
        _create: function () {
            this.$grid = this.element.find('[data-role=assign-source-grid]');

            this.sourceTmpl = mageTemplate('#ks_sources_template');

            $.each(
                this.$grid.data('sources'),
                $.proxy(function (index, sources) {
                    this._add(null, sources);
                }, this)
            );

            this._on({
                'add': '_add',
                'click [data-column=actions] [data-role=delete]': '_remove'
            });

            this._ksOpenModal();
            this._ksUpdateGridVisibility();
            this._ksOnChangeUseDefault();
            this._ksSourceFieldDisabled();

        },

        /**
         * Add sources to grid
         * @param {EventObject} event
         * @param {Object} sources
         * @private
         */
        _add: function (event, sources) {
            var tmpl,
                ksSourceExists;

            ksSourceExists = this.$grid.find('[data-role=source_code]')
                .filter(function (index, element) {
                    return $(element).val() == sources.source_code;
                }).length;

            if (!ksSourceExists) {
                tmpl = this.sourceTmpl({
                    data: sources
                });

                $(tmpl).appendTo(this.$grid.find('tbody'));
           }
        },

        /**
         * Remove sources
         * @param {EventObject} event
         * @private
         */
        _remove: function (event) {
            $(event.target).closest('[data-role=row]').remove();
            this._ksUpdateGridVisibility();
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

            $("#ks_assign_sources_link").on('click',function(){

                var options = {
                    type: 'slide',
                    responsive: true,
                    innerScroll: true,
                    title: $.mage.__('Assign Sources'),
                    closed: function() {
                        ko.cleanNode($("#ks_assign_sources_modal")[0]);
                        $("#ks_assign_sources_modal").html("");
                    },
                    buttons: [{
                        text: $.mage.__('Done'),
                        class: 'ks-action-btn ks-primary',
                        click: function () {
                            this.closeModal();
                            self._ksAddSelectedProducts();
                            self._ksSourceFieldDisabled();
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
                    url: url.build("multivendor/product_ajax/ksinventorysources"),
                    data: [],
                    showLoader: true,
                    success: function (ksResponse) {
                        uiRegistry.get(function(component){
                            if(component.name != undefined){
                               uiRegistry.remove(component.name);
                            }
                         });

                        $("#ks_assign_sources_modal").html(ksResponse);
                        $("#ks_assign_sources_modal").trigger('contentUpdated');
                        $("#ks_assign_sources_modal").applyBindings();

                    }
                });


                modal(options, $("#ks_assign_sources_modal"));
                $("#ks_assign_sources_modal").modal("openModal");
            });
        },

        /**
         * Add selected product
         *
         * @private
         */
        _ksAddSelectedProducts: function () {
            var indexValue = $('[data-index=assign-sources]').find('tbody tr').length;
            var self = this;

            $("#ks_assign_sources_modal .data-grid-checkbox-cell input[type='checkbox']:checked").each(function(){

                var trElement = $(this).closest('.data-row');
                var ksid = $(this).val();

                var sources =  {
                    index: indexValue,
                    source_code: trElement.find(".ks-source-code").find('div').text(),
                    name: trElement.find(".ks-source-name").find('div').text(),
                    sources_status: trElement.find(".ks-source-status").find('div').text(),
                    sources_item_status: 1,
                    qty: 0,
                    notify_stock_qty: $('#ks-default-notify').val(),
                    notify_stock_qty_use_default: 1
                }
                
                indexValue++;

                self._add(null, sources);

           });

           self._ksUpdateGridVisibility();
        },

        /**
         * Change Quantity field on tha basis of Use Default
         *  @private
         */
        _ksOnChangeUseDefault: function () {

            // for enable/disable on click use default button
            $('body').on('click','.ks-notify-use-default',function(){
                var ksNotifyStockQty = $(this).closest('tr').find('.ks-notify-stock-qty');
                if ($(this).is(":checked")) {
                    ksNotifyStockQty.val($('#ks-default-notify').val());
                    ksNotifyStockQty.prop('disabled',true);

                } else {
                    ksNotifyStockQty.prop('disabled',false);
                }
            });
        },

        /**
         * Show or hide message
         * @private
         */
        _ksUpdateGridVisibility: function () {
            var showGrid = this.element.find('[data-role=source_code]').length > 0;
            this.element.find('.grid-container').toggle(showGrid);
        },

        /**
         * * onchange value set disabled source tab data
         * @private
         */
        _ksSourceFieldDisabled: function () {
            if($('[data-role=assign-source-grid]').length >0 ){
                if($("#ks_inventory_manage_stock").val()!=1){
                    $('[data-role=assign-source-grid]').find("select,:input").not('.action-delete').prop('disabled',true);
                }else{
                    $('[data-role=assign-source-grid]').find("select, :input").not('.action-delete').prop('disabled',false);
                }
            }
        },

    });


    return $.mage.ksAssignSourcesDialog;
});
