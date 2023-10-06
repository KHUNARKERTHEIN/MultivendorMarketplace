/**
 * Ksolves
 *
 * @category  Ksolves
 * @package   Ksolves_MultivendorMarketplace
 * @author    Ksolves Team
 * @copyright Copyright (c) Ksolves India Limited (https://www.ksolves.com/)
 * @license   https://store.ksolves.com/magento-license
 */

/*global FORM_KEY*/
/*global bSelection*/
/*global $H*/
define([
    'jquery',
    'mage/template',
    "mage/url",
    'Magento_Ui/js/modal/modal',
    'uiRegistry',
    'ko',
    'Ksolves_MultivendorMarketplace/js/product/ks-add-grid-item',
    'accordion'
], function ($, mageTemplate, url, modal, uiRegistry, ko, ksGridItem) {
    'use strict';

    $.widget('mage.ksBundleProduct', {
        /** @inheritdoc */
        _create: function () {
            var self = this;
            this.ksSelectionType = 'radio';
            this.ksDynamicPrice = $('#product_price_type').val();
            this.baseTmpl = mageTemplate('#bundle-option-template');
            this.productTmpl = mageTemplate('#bundle-option-selection-row-template');

            $('#ks_product_has_weight').hide();

            if ($('#product_weight_type').val() == 0) {
                $('input[name="product[weight]"]').prop('disabled',true);
            } else {
                $('input[name="product[weight]"]').prop('disabled',false);
            }

            $('#product_weight_type').change(function () {
                if ($(this).val() == 0) {
                    $('input[name="product[weight]"]').prop('disabled',true);
                } else {
                    $('input[name="product[weight]"]').prop('disabled',false);
                }
            });

            ksGridItem._ksUpdatePager(this.element);
            ksGridItem._ksChangePager(this.element);
            this.ksShowPager();

            this._initOptionBoxes();


            $('#product_price_type').change(function () {
                self.ksDynamicPrice = $(this).val();

                var ksTableRow = $('#ks_product_bundle_container').find('.ks-bundle-option-product-grid table'),
                    ksPrice = $('input[name="product[price]');

                if (self.ksDynamicPrice == 0) {
                    ksTableRow.find('thead tr th.col-price').hide();
                    ksTableRow.find('tbody tr td.col-price').hide();
                    ksTableRow.find('tbody tr td.col-price input').addClass('ignore-validate');
                    ksPrice.prop('disabled',true);
                    ksPrice.addClass('ignore-validate');
                    ksPrice.closest('.ks-dollor-sign').addClass('disabled');
                    $('select[name="product[tax_class_id]').prop('disabled',true);
                    $('.ks-custom-option-action,.ks-custom-option-text').hide();
                    $('#dynamic-price-warning').show();
                } else {
                    ksTableRow.find('thead tr th.col-price').show();
                    ksTableRow.find('tbody tr td.col-price').show();
                    ksTableRow.find('tbody tr td.col-price input').removeClass('ignore-validate');
                    ksPrice.prop('disabled',false);
                    ksPrice.removeClass('ignore-validate');
                    ksPrice.closest('.ks-dollor-sign').removeClass('disabled');
                    $('select[name="product[tax_class_id]').prop('disabled',false);
                    $('.ks-custom-option-action,.ks-custom-option-text').show();
                    $('#dynamic-price-warning').hide();
                }
            })
        },

        _initOptionBoxes: function () {
            var ksSyncOptionTitle;

            this.element.sortable({
                axis: 'y',
                handle: '[data-role=draggable-handle]',
                items: '#ks_product_bundle_container_top > div',
                update: this._updateOptionBoxPositions,
                tolerance: 'pointer'
            });

            /**
             * @param {jQuery.Event} event
             */
            ksSyncOptionTitle = function (event) {
                var ksCurrentValue = $(event.target).val(),
                    ksOptionBoxTitle = $(
                        '.admin__collapsible-title > span',
                        $(event.target).closest('.fieldset-wrapper')
                    ),
                    ksNewOptionTitle = $.mage.__('New Option');

                ksOptionBoxTitle.text(ksCurrentValue === '' ? ksNewOptionTitle : ksCurrentValue);
            };

            this._on({

                /**
                 * Add new bundle option
                 */
                'click #ks-bundle-options': function (event) {
                    this.ksAddOption(event);
                },

                /**
                 * get bundle options from products
                 */
                'click input[id^=bundle_selection_][id$=_is_default]': function (event) {
                    var ksParentId = $(event.target).attr('id');
                    if (this.ksSelectionType == 'radio') {
                        $(event.target).closest('.option-box').find('.ks-bundle-option-product-grid tbody tr td.col-default').each(function () {
                            if (ksParentId != $(this).find('.default').attr('id')) {
                                $(this).find('.default').val("0");
                                $(this).find('.default').prop("checked","");
                            }
                        });
                    }
                },

                /**
                 * get bundle options from products
                 */
                'click a[id^=bundle_option_][id$=_add_bundle_product]': function (event) {

                    var self = this;
                    var ksParentElement = $(event.target).closest('.option-box');
                    var ksExcludeBundleProductIds= [];
                    ksParentElement.find("[data-role=bundle-product-grid] [data-role=id]").each(function() {
                        ksExcludeBundleProductIds.push($(this).val());
                    });
                    ko.cleanNode($("#ks_bundle_options_product_modal")[0]);
                    var options = {
                        type: 'slide',
                        responsive: true,
                        innerScroll: true,
                        title: $.mage.__('Add Products to Options'),
                        closed: function() {
                            $("#ks_bundle_options_product_modal").html("");
                        },
                        buttons: [{
                            text: $.mage.__('Add Selected Products'),
                            class: 'ks-action-btn ks-primary',
                            click: function () {
                                this.closeModal();
                                self._ksAddSelectedProducts(ksParentElement);
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
                        url: url.build("multivendor/product_ajax/ksaddbundleproduct"),
                        showLoader: true,
                        data: {"ksExcludeProductIds": JSON.stringify(ksExcludeBundleProductIds)},
                        success: function (ksResponse) {
                            uiRegistry.get(function (component) {
                                if (component.name != undefined) {
                                    if (component.name.indexOf('ks_bundle_product_listing') != -1) {
                                        uiRegistry.remove(component.name);
                                    }
                                }
                            });
                            $("#ks_bundle_options_product_modal").html(ksResponse);
                            $("#ks_bundle_options_product_modal").trigger('contentUpdated');
                            $("#ks_bundle_options_product_modal").applyBindings();

                        }
                    });

                    modal(options, $("#ks_bundle_options_product_modal"));
                    $("#ks_bundle_options_product_modal").modal("openModal");
                },

                /**
                 * Remove bundle option or option row for 'select' type of bundle option
                 */
                'click button[id^=bundle_option_][id$=_delete]': function (event) {
                    var element = $(event.target).closest('.option-box > div.fieldset-wrapper,tr');
                    if (element.length) {
                        $('#bundle_option_' + element.attr('id').replace('bundle_option_', '') + '_is_delete').val(1);
                        element.addClass('ignore-validate').hide();
                        $(event.target).closest('.option-box').addClass('remove-row');
                        ksGridItem._ksPagingNumerUpdate(this.element);
                        this.ksShowPager();
                    }
                },

                /**
                 * Remove Selected Table Data
                 */
                'click button[id^=bundle_selection_][id$=_delete]': function (event) {

                    var element = $(event.target).closest('.bundle-option-selection-table');
                    var ksSelectTable = $(event.target).closest('.ks-selected-option-table');
                    
                    element.remove();
                    
                    var ksSelectRowCount = ksSelectTable.find('tr').length - 1;
                    if (ksSelectRowCount==0) {
                        ksSelectTable.remove();
                    }
                },

                'change select[id^=bundle_option_][id$=_type]': function (event) {
                    var self = this;
                    if ($(event.target).val() == 'multi' || $(event.target).val() == 'checkbox') {
                        self.ksSelectionType = 'checkbox';
                        $(event.target).closest('.option-box').find('.ks-bundle-option-product-grid thead tr th.col-uqty').hide();
                        $(event.target).closest('.option-box').find('.ks-bundle-option-product-grid tbody tr').each(function () {
                            $(this).find('td.col-uqty').hide();
                        });
                    } else {
                        self.ksSelectionType = 'radio';
                        $(event.target).closest('.option-box').find('.ks-bundle-option-product-grid thead tr th.col-uqty').show();
                        $(event.target).closest('.option-box').find('.ks-bundle-option-product-grid tbody tr').each(function () {
                            $(this).find('td.col-uqty').show();
                            $(this).find('td.col-default input.default').val(0);
                            $(this).find('td.col-default input.default').prop('checked',"");
                        });
                    }
                    $(event.target).closest('.option-box').find('.ks-bundle-option-product-grid tbody tr td.col-default').each(function () {
                        $(this).find('.default').prop('type', self.ksSelectionType);
                    });
                },

                //Sync title
                'change .field-option-title > .control > input[id$="_title"]': ksSyncOptionTitle,
                'keyup .field-option-title > .control > input[id$="_title"]': ksSyncOptionTitle,
                'paste .field-option-title > .control > input[id$="_title"]': ksSyncOptionTitle
            })
        },

        /**
         * @return {Object}
         * @private
         */

        _updateOptionBoxPositions: function () {
            $(this).find('[name^=bundle_options][name$="[position]"]').each(function (index) {
                $(this).val(index);
            });

            return this;
        },

        /**
         * Add selected product
         *
         * @private
         */
        _ksAddSelectedProducts: function (ksParentElement) { 
            if (ksParentElement.find('tbody tr').length == 'undefined') {
                var indexValue = 0;
            } else {
                var indexValue = ksParentElement.find('tbody tr').length;
            }
            var self = this;
            var tmpl;

            if (!ksParentElement.find('.ks-bundle-option-product-grid table').length &&
                $("#ks_bundle_options_product_modal .data-grid-checkbox-cell input[type='checkbox']:checked").length) {

                var ksTableGrid = mageTemplate('#bundle-option-selection-box-template');
                var ksTableTmpl = ksTableGrid({});

                $(ksTableTmpl).appendTo(ksParentElement.find('.ks-bundle-option-product-grid'));
            }

            var ksTableRow = ksParentElement.find('.ks-bundle-option-product-grid table');
            var ksOptionType = ksParentElement.find('.select-product-option-type').val();

            var ksOptionSelectType = 'radio';
            if (ksOptionType == 'multi' || ksOptionType == 'checkbox') {
                ksOptionSelectType = 'checkbox';
            }else{
                ksOptionSelectType = 'radio';
            }

            $("#ks_bundle_options_product_modal .data-grid-checkbox-cell input[type='checkbox']:checked").each(function () {

                var trElement = $(this).closest('.data-row');
                var ksid = $(this).val();
                var product = {
                    index: indexValue,
                    option_type: ksOptionSelectType,
                    id: ksid,
                    product_id: ksid,
                    checked: '',
                    selection_qty: 1,
                    parentIndex: ksParentElement.find('.ks-bundle-option-product-grid').data("parent-index"),
                    name: trElement.find('.ks-product-name').find('div').text(),
                    sku: trElement.find('.ks-product-sku').find('div').text(),
                    position: indexValue
                }
               
                indexValue++;

                tmpl = self.productTmpl({
                    data: product
                });

                ksGridItem._ksAdd(null, product, ksTableRow, self.productTmpl);

                if (self.ksSelectionType == 'checkbox') {
                    ksTableRow.find('thead tr th.col-uqty').hide()
                    ksTableRow.find('tbody tr').each(function () {
                        $(this).find('td.col-uqty').hide();
                    });
                } else {
                    ksTableRow.find('thead tr th.col-uqty').show()
                    ksTableRow.find('tbody tr').each(function () {
                        $(this).find('td.col-uqty').show();
                    });
                }

            });

            if (self.ksDynamicPrice == 0) {
                ksTableRow.find('thead tr th.col-price').hide();
                ksTableRow.find('tbody tr td.col-price').hide();
                ksTableRow.find('tbody tr td.col-price input').addClass('ignore-validate');
            } else {
                ksTableRow.find('thead tr th.col-price').show();
                ksTableRow.find('tbody tr td.col-price').show();
                ksTableRow.find('tbody tr td.col-price input').removeClass('ignore-validate');
            }

            ksGridItem._ksSort(self.element, ksTableRow);
        },

        /**
         * Add custom option
         */
        ksAddOption: function (event) {
            var data = {},
                element = event.target || event.srcElement || event.currentTarget,
                baseTmpl;

            if (typeof element !== 'undefined') {
                data.id = this.options.itemCount;
                data.type = '';
                data['option_id'] = 0;
                data['required'] = 1;
            } else {
                data = event;
                data.id = this.options.itemCount;
            }
            baseTmpl = this.baseTmpl({
                data: data
            });

            $(baseTmpl)
                .appendTo(this.element.find('#ks_product_bundle_container_top'));

            $(this.element.find('#bundle_option_'+data.id+'_header_title')).text(data.title)

            //set selected type value if set
            if (data.type) {
                $('#' + this.options.fieldId + '_' + data.id + '_type').val(data.type).trigger('change', data);
            }

            //set selected is_require value if set
            if (data['is_require']) {
                $('#' + this.options.fieldId + '_' + data.id + '_is_require').val(data['is_require']).trigger('change');
            }

            if (data.selections) {
                if (data.selections.length > 0) {
                    this._ksAddSelectedProductsRow(this.element.find('#bundle_option_'+data.id),data);
                }
            }
            this.options.itemCount++;
            $('#' + this.options.fieldId + '_' + data.id + '_title').trigger('change');

            ksGridItem._ksPagingNumerUpdate(this.element);
            this.ksShowPager();
        },

        /**
         * Create Selection grid on the basis of option selected data
         */
        _ksAddSelectedProductsRow: function (ksParentElement,data) {
            var self = this;
            var tmpl;
            var ksSelectionData = data.selections;

            if (!ksParentElement.find('.ks-bundle-option-product-grid table').length) {

                var ksTableGrid = mageTemplate('#bundle-option-selection-box-template');
                var ksTableTmpl = ksTableGrid({});

                $(ksTableTmpl).appendTo(ksParentElement.find('.ks-bundle-option-product-grid'));
            }

            var ksTableRow = ksParentElement.find('.ks-bundle-option-product-grid table');

            $.each(ksSelectionData,function (index,ksSelection) {
                var product = {
                    index: index,
                    option_type: self.ksSelectionType,
                    id: ksSelection.entity_id,
                    product_id: ksSelection.entity_id,
                    selection_id: ksSelection.selection_id,
                    option_id: ksSelection.option_id,
                    is_default: ksSelection.is_default,
                    selection_qty: ksSelection.selection_qty,
                    selection_price_value: ksSelection.selection_price_value,
                    selection_price_type: ksSelection.selection_price_type,
                    selection_can_change_qty: ksSelection.selection_can_change_qty,
                    parentIndex: ksParentElement.find('.ks-bundle-option-product-grid').data("parent-index"),
                    name: ksSelection.name,
                    sku: ksSelection.sku,
                    position: index
                }
                tmpl = self.productTmpl({
                    data: product
                });
                ksGridItem._ksAdd(null, product, ksTableRow, self.productTmpl);
            });

            if (self.ksDynamicPrice == 0) {
                ksTableRow.find('thead tr th.col-price').hide();
                ksTableRow.find('tbody tr td.col-price').hide();
                ksTableRow.find('tbody tr td.col-price input').addClass('ignore-validate');
            } else {
                ksTableRow.find('thead tr th.col-price').show();
                ksTableRow.find('tbody tr td.col-price').show();
                ksTableRow.find('tbody tr td.col-price input').removeClass('ignore-validate');
            }

            if (this.ksSelectionType == 'checkbox') {

                ksTableRow.find('thead tr th.col-uqty').hide()
                ksTableRow.find('tbody tr').each(function () {
                    $(this).find('td.col-uqty').hide();
                });
            } else {
                ksTableRow.find('thead tr th.col-uqty').show()
                ksTableRow.find('tbody tr').each(function () {
                    $(this).find('td.col-uqty').show();
                });
            }

            $('#product_price_type').prop('disabled',true);

            ksGridItem._ksSort(self.element, ksTableRow);
        },

        /**
         * show pager
         */
        ksShowPager: function () {
            var ksElementRow = this.element.find(".ks-gridrow").not(".remove-row");
            if(ksElementRow.length > 0){
                this.element.find(".ks-grid-pager").show();
            }else{
                this.element.find(".ks-grid-pager").hide();
            }
        },
    });
    return $.mage.ksBundleProduct;
});
