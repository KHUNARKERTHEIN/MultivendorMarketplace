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
    'uiRegistry',
    'ko',
    'Ksolves_MultivendorMarketplace/js/product/variations/options/ks-price-type-handler',
    'Ksolves_MultivendorMarketplace/js/product/ks-add-grid-item',
    'jquery/ui',
    'jquery/validate'
], function ($, mageTemplate, modal,uiRegistry,ko , ksPriceTypeHandler, ksGridItem) {
    'use strict';

    $.widget('mage.ksCustomOptions', {

        options: {
            selectionItemCount: {}
        },

        /**
         * * Fired when widget initialization start
         * @private
         */
        _create: function () {
            this.baseTmpl = mageTemplate('#ks-custom-option-base-template');
            this.rowTmpl = mageTemplate('#ks-custom-option-select-type-row-template');

            this._initOptionBoxes();
            this._ksInitSortableSelections();
            this._ksBindCheckboxHandlers();
            this._ksBindReadOnlyMode();
            this._ksAddValidation();
            this._ksChangePriceType();
            this.ksShowPager();

            ksGridItem._ksUpdatePager(this.element);
            ksGridItem._ksChangePager(this.element);
        },

         /**
         * @private
         */
        _ksChangePriceType: function () {
            $('body').on('change', '.product-option-price-type', function(e){
                var trElement = $(this).closest('tr');
                var ksCurrencySymbol = trElement.find('.ks-dollor-sign .product-option-price').data('price-sysmbol');
                if($(this).val()=='percent'){
                    trElement.find('.ks-dollor-sign span').html("%");
                }else{
                    trElement.find('.ks-dollor-sign span').html(ksCurrencySymbol);
                }
            })
        },

         /**
         * @private
         */
        _ksAddValidation: function () {
            $.validator.addMethod(
                'required-option-select', function (value) {
                    return value !== '';
                }, $.mage.__('Select type of option.'));

            $.validator.addMethod(
                'required-option-select-type-rows', function (value, element) {
                    var optionContainerElm = element.up('div[id*=_type_]'),
                        selectTypesFlag = false,
                        selectTypeElements = $('#' + optionContainerElm.id + ' .select-type-title');

                    selectTypeElements.each(function () {
                        if (!$(this).closest('tr').hasClass('ignore-validate')) {
                            selectTypesFlag = true;
                        }
                    });

                    return selectTypesFlag;
                }, $.mage.__('Please add rows to option.'));
        },

        /**
         * @private
         */
        _initOptionBoxes: function () {
            var ksSyncOptionTitle;
            if (!this.options.isReadonly) {
                this.element.sortable({
                    axis: 'y',
                    handle: '[data-role=draggable-handle]',
                    items: '#ks_product_options_container_top > div',
                    update: this._ksUpdateOptionBoxPositions,
                    tolerance: 'pointer'
                });
            }

            /**
             * @param {jQuery.Event} event
             */
            ksSyncOptionTitle = function (event) { 
                var ksCurrentValue = $(event.target).val(),
                    ksOptionBoxTitle = $(
                        '.ks-tab-collapsible-title > span',
                        $(event.target).closest('.fieldset-wrapper')
                    ),
                    ksNewOptionTitle = $.mage.__('New Option');

                ksOptionBoxTitle.text(ksCurrentValue === '' ? ksNewOptionTitle : ksCurrentValue);
            };

            this._on({

                /**
                 * Remove custom option or option row for 'select' type of custom option
                 */
                'click button[id^=product_option_][id$=_delete]': function (event) { 
                    var element = $(event.target).closest('#ks_product_options_container_top > div.fieldset-wrapper,tr');

                    if (element.length) {
                        $('#product_' + element.attr('id').replace('product_', '') + '_is_delete').val(1);
                        element.addClass('ignore-validate remove-row').hide();
                        this.ksRefreshSortableElements();
                        ksGridItem._ksPagingNumerUpdate(this.element);
                        this.ksShowPager();
                    }
                    
                },

                /**
                 * Add new option row for 'select' type of custom option
                 */
                'click button[id^=product_option_][id$=_add_select_row]': function (event) {
                    this.ksAddSelection(event);
                },

                /**
                 * Add new custom option
                 */
                'click #ks-custom-options': function (event) {
                    this.ksAddOption(event);
                },

                /**
                 * Import custom options from products
                 */
                'click #ks-custom-import': function () {
                    var ksImportContainer = $('#ks-import-container'),
                        widget = this;
                    ko.cleanNode(ksImportContainer[0]);    
                    var options = {
                        type: 'slide',
                        responsive: true,
                        innerScroll: true,
                        title: $.mage.__('Select Product'),
                        closed: function() {
                            ksImportContainer.html("");
                        },
                        buttons: [{
                            text: $.mage.__('Import'),
                            class: 'ks-action-btn ks-primary',
                            click: function () {
                                this.closeModal();
                                widget._ksImportProductOptions();
                            }
                        }]
                     };

                    $.ajax({
                        type: "POST",
                        url: this.options.importProductGridUrl,
                        showLoader: true,
                        success: function (ksResponse) {
                            uiRegistry.get(function(component){
                                if(component.name != undefined){
                                    if (component.name.indexOf('ks_product_custom_options_listing') != -1) {
                                        uiRegistry.remove(component.name);
                                    }
                                }
                             });
                            
                            ksImportContainer.html(ksResponse);
                            ksImportContainer.trigger('contentUpdated');
                            ksImportContainer.applyBindings();

                        }
                    });

                    modal(options, ksImportContainer);
                    ksImportContainer.modal("openModal");

                },

                /**
                 * Change custom option type
                 */
                'change select[id^=product_option_][id$=_type]': function (event, data) {
                    var widget = this,
                        currentElement = $(event.target),
                        parentId = '#' + currentElement.closest('.fieldset-alt').attr('id'),
                        group = currentElement.find('[value="' + currentElement.val() + '"]')
                            .closest('optgroup').attr('data-optgroup-name'),
                        previousGroup = $(parentId + '_previous_group').val(),
                        previousBlock = $(parentId + '_type_' + previousGroup),
                        tmpl, disabledBlock, priceType;

                    data = data || {};

                    if (typeof group !== 'undefined') {
                        group = group.toLowerCase();
                    }

                    if (previousGroup !== group) {
                        if (previousBlock.length) {
                            previousBlock.addClass('ignore-validate').hide();
                        }
                        $(parentId + '_previous_group').val(group);

                        if (typeof group === 'undefined') {
                            return;
                        }
                        disabledBlock = $(parentId).find(parentId + '_type_' + group);

                        if (disabledBlock.length) {
                            disabledBlock.removeClass('ignore-validate').show();
                        } else {
                            if ($.isEmptyObject(data)) { //eslint-disable-line max-depth
                                data['option_id'] = $(parentId + '_custom_id').val();
                                data.price = data.sku = '';
                            }
                            data.group = group;

                            tmpl = widget.element.find('#ks-custom-option-' + group + '-type-template').html();

                            tmpl = mageTemplate(tmpl, {
                                data: data
                            });

                            $(tmpl).insertAfter($(parentId));

                            //hide percent for configurable
                            ksPriceTypeHandler.percentPriceTypeHandler();

                            if (data['price_type']) { //eslint-disable-line max-depth
                                priceType = $('#' + widget.options.fieldId + '_' + data['option_id'] + '_price_type');
                                priceType.val(data['price_type']).attr('data-store-label', data['price_type']);
                            }

                            //Add selections

                            if (data.optionValues) { //eslint-disable-line max-depth
                                data.optionValues.each(function (value) {
                                    widget.ksAddSelection(value);
                                });
                            }
                        }
                    }
                },

                //Sync title
                'change .field-option-title > .control > .ks-option-title': ksSyncOptionTitle,
                'keyup .field-option-title > .control > .ks-option-title': ksSyncOptionTitle,
                'paste .field-option-title > .control > .ks-option-title': ksSyncOptionTitle
            })
        },

        /**
         * @private
         */
        _ksInitSortableSelections: function () {
            if (!this.options.isReadonly) {
                this.element.find('[id^=product_option_][id$=_type_select] tbody').sortable({
                    axis: 'y',
                    handle: '[data-role=draggable-handle]',
                    /** @inheritdoc */
                    helper: function (event, ui) {
                        ui.children().each(function () {
                            $(this).width($(this).width());
                        });

                        return ui;
                    },
                    update: this._ksUpdateSelectionsPositions,
                    tolerance: 'pointer'
                });
            }
        },

         /**
         * Sync sort order checkbox with hidden dropdown
         */
        _ksBindCheckboxHandlers: function () {
            this._on({
                /**
                 * @param {jQuery.Event} event
                 */
                'change [id^=product_option_][id$=_required]': function (event) {
                    var $this = $(event.target);

                    $this.closest('#ks_product_options_container_top > div')
                        .find('input[type="checkbox"][name$="[is_require]"]').val($this.is(':checked') ? 1 : 0);
                }
            });
            this.element.find('[id^=product_option_][id$=_required]').each(function () {
                $(this).prop('checked', $(this).closest('#ks_product_options_container_top > div')
                        .find('input[type="checkbox"][name$="[is_require]"]').val() > 0);
            });
        },

        /**
         * Update Custom option position
         */
        _ksUpdateOptionBoxPositions: function () {
            $(this).find('div[id^=option_]:not(.ignore-validate) .fieldset-alt > [name$="[sort_order]"]').each(
                function (index) {
                    $(this).val(index);
                });
        },

        /**
         * Update selections positions for 'select' type of custom option
         */
        _ksUpdateSelectionsPositions: function () {
            $(this).find('tr:not(.ignore-validate) [name$="[sort_order]"]').each(function (index) {
                $(this).val(index);
            });
        },

        /**
         * Disable input data if "Read Only"
         */
        _ksBindReadOnlyMode: function () {
            if (this.options.isReadonly) {
                $('div.product-custom-options').find('button,input,select,textarea').each(function () {
                    $(this).prop('disabled', true);

                    if ($(this).is('button')) {
                        $(this).addClass('disabled');
                    }
                });
            }
        },

        /**
         * Add selection value for 'select' type of custom option
         */
        ksAddSelection: function (event) {
            var data = {},
                element = event.target || event.srcElement || event.currentTarget,
                rowTmpl, priceType;

            if (typeof element !== 'undefined') {
                data.id = $(element).closest('#ks_product_options_container_top > div')
                    .find('[name^="product[options]"][name$="[custom_id]"]').val();
                data['option_type_id'] = -1;

                if (!this.options.selectionItemCount[data.id]) {
                    this.options.selectionItemCount[data.id] = 1;
                }

                data['select_id'] = this.options.selectionItemCount[data.id];
                data.price = data.sku = '';
            } else {
                data = event;
                data.id = data['option_id'];
                data['select_id'] = data['option_type_id'];
                this.options.selectionItemCount[data.id] = data['item_count'];
            }

            rowTmpl = this.rowTmpl({
                data: data
            });

            $(rowTmpl).appendTo($('#select_option_type_row_' + data.id));

            //hide percent for configurable
            ksPriceTypeHandler.percentPriceTypeHandler();

            //set selected price_type value if set
            if (data['price_type']) {
                priceType = $('#' + this.options.fieldId + '_' + data.id + '_select_' + data['select_id'] +
                    '_price_type');
                priceType.val(data['price_type']).attr('data-store-label', data['price_type']);
            }

            
            this.ksRefreshSortableElements();
            this.options.selectionItemCount[data.id] = parseInt(this.options.selectionItemCount[data.id], 10) + 1;

            $('#' + this.options.fieldId + '_' + data.id + '_select_' + data['select_id'] + '_title').focus();
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
                data['is_require'] = 1;
            } else {
                data = event;
                this.options.itemCount = data['item_count'];
            }

            baseTmpl = this.baseTmpl({
                data: data
            });

            $(baseTmpl)
                .appendTo(this.element.find('#ks_product_options_container_top'));

            //set selected type value if set
            if (data.type) {
                $('#' + this.options.fieldId + '_' + data.id + '_type').val(data.type).trigger('change', data);
            }

            this.ksRefreshSortableElements();
            this._ksBindCheckboxHandlers();
            this._ksBindReadOnlyMode();
            this.options.itemCount++;
            $('#' + this.options.fieldId + '_' + data.id + '_title').trigger('change');

            ksGridItem._ksPagingNumerUpdate(this.element);
            this.ksShowPager();
        },

        /**
         * @return {Object}
         */
        ksRefreshSortableElements: function () {
            if (!this.options.isReadonly) {
                this.element.sortable('refresh');
                this._ksUpdateOptionBoxPositions.apply(this.element);
                this._ksUpdateSelectionsPositions.apply(this.element);
                this._ksInitSortableSelections();
            }

            return this;
        },

        /**
         * Import product Options
         */
        _ksImportProductOptions: function () {

            var ksRequest = [],
                widget  = this;

            $("#ks-import-container .data-grid-checkbox-cell input[type='checkbox']:checked").each(function(){
                ksRequest.push($(this).val());
            });

            $.post(widget.options.customOptionsUrl, {
                'products[]': ksRequest,
                'form_key': widget.options.formKey
            }, function ($ksData) {
                $.parseJSON($ksData).each(function (el) {
                    var i;

                    el.id = widget.getFreeOptionId(el.id);
                    el['option_id'] = el.id;

                    if (typeof el.optionValues !== 'undefined') {
                        for (i = 0; i < el.optionValues.length; i++) {
                            el.optionValues[i]['option_id'] = el.id;
                        }
                    }
                    //Adding option
                    widget.ksAddOption(el);
                    //Will save new option on server side
                    $('#product_option_' + el.id + '_option_id').val(0);
                    $('#option_' + el.id + ' input[name$="option_type_id]"]').val(-1);
                });
            });
        },

        /**
         * @param {String} id
         * @return {*}
         */
        getFreeOptionId: function (id) {
            return $('#' + this.options.fieldId + '_' + id).length ? this.getFreeOptionId(parseInt(id, 10) + 1) : id;
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

    return $.mage.ksCustomOptions;
});
