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
    'Magento_Ui/js/modal/modal',
    'mage/translate'
], function ($, modal) {
    'use strict';

    /**
     */
    $.widget('mage.ksInventoryDialog', {

         /**
         * * Fired when widget initialization start
         * @private
         */
        _create: function () {
            this._ksOpenModal();
            this._ksChangeInventory();
            this._ksChangeManageStockOption();
            this._ksApplyEnableQtyIncrements();
            this._ksCustomizeMinQty();
            this._ksUpdateStockValue();
        },

        /**
         * Open Modal 
         *
         * @param options
         * @param cookie
         * @private
         */
        _ksOpenModal: function () {  
            var ksSelf = this;

            var options = {
                type: 'slide',
                responsive: true,
                innerScroll: true,
                title: $.mage.__("Advanced Inventory"),
                buttons: [{
                    text: $.mage.__('Done'),
                    class: 'ks-action-btn ks-primary',
                    click: function () { 
                        if(ksSelf._ksStopCloseModal()){
                            return false;
                        }
                        this.closeModal();
                    }
                }]
             };
                
            modal(options, $('#ks_advanced_inventory_modal'));

            $("#ks_advanced_inventory_link").on('click',function(){ 
                $("#ks_advanced_inventory_modal").modal("openModal");
                ksSelf._ksSetInventoryCheckbox();
            });
        },

        /**
         * close Modal 
         *
         * @private
         */
        _ksStopCloseModal: function () {  
             
            var ksNotValidate =0;
            $('#ks_advanced_inventory_modal label.mage-error').remove();
            $("#ks_advanced_inventory_modal :input[type='text'],#ks_advanced_inventory_modal select").not(':hidden').each(function(index) {
               if(!$.validator.validateSingleElement($(this))){
                    ksNotValidate++;
                }
            });
            
            return ksNotValidate;
        },

         /**
         * * set disable inventory field from popup
         * @private
         */
        _ksSetInventoryCheckbox: function () {
            $('#ks_advanced_inventory_modal input[type="checkbox"]').click(function () {
                var ksField = $(this).closest('.ks-field-control');

                if ($(this).prop('checked') === true) {
                    var ksDefaultValue = ksField.find('input[type="text"],select').not('.ks-minqty').data('defaultval');

                    ksField.find('input[type="text"],select').not('.ks-minqty').val(ksDefaultValue);

                    ksField.find('input[type="text"],select').not('.ks-minqty').prop('disabled',true);
                    ksField.find('.ks-minqty-field').hide();
                    ksField.find('.ks-minqty-table').show();
                    
                } else {  
                    ksField.find('input[type="text"],select').not('.ks-minqty').prop('disabled',false);
                    ksField.find('.ks-minqty-field').show();
                    ksField.find('.ks-minqty-table').hide();
                }
            });
        },


         /**
         * * Manage Stock Option
         * @private
         */
        _ksChangeManageStockOption: function () {

            var ksManageStock = $('#ks_inventory_use_config_manage_stock').checked
                ? $('#ks_inventory_manage_stock').data('defaultval')
                : $('#ks_inventory_manage_stock').val();
            var ksInventoryNotManageStockFields = new Array(
                "ks_inventory_manage_stock",
                "inventory_min_sale_qty_table",
                "inventory_min_sale_qty",
                "inventory_max_sale_qty",
                "ks_inventory_enable_qty_increments",
                "inventory_qty_increments"
            );
           
            $('#ks_table_cataloginventory > div').each(function() {

                var ksFieldId = $(this).find("input[type='text'],select,table").attr('id');

                if($.inArray(ksFieldId, ksInventoryNotManageStockFields) == -1) {
                     if(ksManageStock==1){ 
                        $(this).show();
                    }else{ 
                        $(this).hide();
                    }
                }
            });

        },

        /**
         * * Apply Enable Qty Increments
         * @private
         */
        _ksApplyEnableQtyIncrements: function () {
            var ksEnableQtyIncrements = $('#ks_inventory_use_config_enable_qty_increments').checked
            ? $('#ks_inventory_use_config_enable_qty_increments').data('defaultval')
            : $('#ks_inventory_enable_qty_increments').val();

            if(ksEnableQtyIncrements==1){
                $('#inventory_qty_increments').closest('.ks-form-field').show();
            }else{
                $('#inventory_qty_increments').closest('.ks-form-field').hide();
            }
        },

        /**
         * * Change Inventory
         * @private
         */
        _ksChangeInventory: function () {
            var self = this;
            $('#ks_inventory_manage_stock, #ks_inventory_use_config_manage_stock').on('change', function() {
                self._ksChangeManageStockOption();
                self._ksHideOutofStockStockOption();
                self._ksAssignSourceFieldDisabled();
            });

            $('#ks_inventory_enable_qty_increments, #ks_inventory_use_config_enable_qty_increments').on('change', function() {
                self._ksApplyEnableQtyIncrements();
            });
        },

        /**
         * * hide min qty field
         * @private
         */
        _ksCustomizeMinQty: function () {
            $('.ks-minqty-field, .ks-minqty-table').hide();
            $('.ks-minqty-textfield, .ks-minqty-container').show();
        },

        /**
         * * hide out of stock qty field
         * @private
         */
        _ksHideOutofStockStockOption: function () {
            if($('#ks_inventory_manage_stock').val()==1){
                $(".ks-out-stock-field").show();
            }else{
                $(".ks-out-stock-field").hide();
            }
        },

        /**
         * * onchange value update out of stock
         * @private
         */
        _ksUpdateStockValue: function () {
            $(".ks-out-stock-field select").change(function(){
                $("#inventory_stock_availability").val($(this).val());
            })
        },

        /**
         * * onchange value set disabled source tab data
         * @private
         */
        _ksAssignSourceFieldDisabled: function () {
            if($('[data-role=assign-source-grid]').length >0 ){
                if($("#ks_inventory_manage_stock").val()!=1){
                    $('[data-role=assign-source-grid]').find("select,:input").not('.action-delete').prop('disabled',true);
                }else{
                    $('[data-role=assign-source-grid]').find("select, :input").not('.action-delete').prop('disabled',false);
                }
            }
        },


    });

    return $.mage.ksInventoryDialog;
});
