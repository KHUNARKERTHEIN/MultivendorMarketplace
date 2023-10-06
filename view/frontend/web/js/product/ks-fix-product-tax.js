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
    'jquery/validate'
], function ($, mageTemplate, Validation) {
    'use strict';
    $.widget('mage.ksFixProductTax', {

        _create: function () {

            var widget = this;

            this._ksInitTaxRows();
            this._ksValidateTaxRows();

            $.each(this.options.itemsData, function () {
                widget._ksAddItems(this);
            });

        },

        /**
         * add Validation for tax row
         * @protected
         */
        _ksValidateTaxRows: function(event){

            $.validator.addMethod('validate-ks-fpt-group', function (value) { 

                if (value.indexOf('?') !== -1) {
                    return false;
                }
                return true;
            }, $.mage.__('Set unique country-state combinations within the same fixed product tax. ' +
                'Verify the combinations and try again.'));
        },

        /**
         * set Validation for tax field
         * @protected
         */
        _ksSetValidateTaxRows: function(event){
            var dup;
            dup = {};

            this.element.find('[data-role="ks-fix-product-tax-item-row"]').each(function (elem) {
                var country,
                    state,
                    key;
               
                country = $(this).find('.country');
                state = $(this).find('.state');
                
                key = country.val() + (state.val() > 0 ? state.val() : 0);
                
                dup[key]++; 
                
                if (!dup[key]) { 
                    dup[key] = 1;
                    $(this).find('.validate-ks-fpt-group').val('');
                } else { 
                    dup[key]++;
                    $(this).find('.validate-ks-fpt-group').val(country.val() + '?' + country.attr('name'));
                }
                
            });
        },

        /**
         * add tax row
         * @protected
         */
        _ksAddItems: function(event){

            var rowTmpl = mageTemplate(this.element.find('[data-role="ks-row-template"]').html());

            var data = {},
                currentElement = event.target || event.srcElement || event.currentTarget,
                tmpl;

            if (typeof currentElement !== 'undefined') {
                data['website_id'] = 0;
            } else {
                data = event;
            }

            data.index = this.element.find('[data-role="ks-fix-product-tax-item-row"]').length;

            tmpl = rowTmpl({
                data: data
            });

            $(tmpl).appendTo(this.element.find('[data-role="ks-fix-product-tax-item-container"]'));

            //set selected website_id value if set
            if (data['website_id']) {
                this.element.find('[data-role="select-website"][id$="_' + data.index + '_website"]')
                    .val(data['website_id']);
            }

            //set selected country value if set
            if (data.country) {
                this.element.find('[data-role="select-country"][id$="_' + data.index + '_country"]')
                    .val(data.country).trigger('change', data);
            }

        },

        /**
         * init tax row
         * @protected
         */
        _ksInitTaxRows: function () {

            var widget = this,
                isOriginalRequired = $(widget.element).hasClass('required');

            this._on({

                'click [data-action=add-fpt-item]': function (event) {
                    this._ksAddItems(event);
                },

                /**
                 * Delete tax item.
                 *
                 * @param {jQuery.Event} event
                 */
                'click [data-action=delete-fpt-item]': function (event) {
                    var parent = $(event.target).closest('[data-role="ks-fix-product-tax-item-row"]');
                    parent.find('[data-role="delete-fpt-item"]').val(1);
                    parent.addClass('ignore-validate').hide();
                },

                /**
                 * Change tax item country/state.
                 *
                 * @param {jQuery.Event} event
                 * @param {Object} data
                 */
                'change [data-role="select-country"]': function (event, data) {

                    var currentElement = event.target || event.srcElement || event.currentTarget,
                        parentElement = $(currentElement).closest('[data-role="ks-fix-product-tax-item-row"]'),
                        updater;

                    data = data || {};
                    updater = new RegionUpdater(
                        parentElement.find('[data-role="select-country"]').attr('id'), null,
                        parentElement.find('[data-role="select-state"]').attr('id'),
                        widget.options.region, 'disable', true
                    );
                    updater.update();
                    //set selected state value if set
                    if (data.state) {
                        parentElement.find('[data-role="select-state"]').val(data.state);
                    }

                    if (!isOriginalRequired && $(widget.element).hasClass('required')) {
                        $(widget.element).removeClass('required');
                    }

                    widget._ksSetValidateTaxRows();
                }
            });

            $(this.options.bundlePriceType).on('change', function (event) {
                var attributeItems = widget.element.find('[data-role="delete-fpt-item"]');

                if ($(event.target).val() === '0') {
                    widget.element.hide();
                    attributeItems.each(function () {
                        $(this).val(1);
                    });
                } else {
                    widget.element.show();
                    attributeItems.each(function () {
                        if ($(this).closest('[data-role="fpt-item-row"]').is(':visible')) {
                            $(this).val(0);
                        }
                    });
                }
            });
        }
    });

    return $.mage.ksFixProductTax;
});
