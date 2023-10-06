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
    'Magento_Ui/js/grid/columns/actions',
    'Magento_Ui/js/modal/alert',
    'uiRegistry',
    'underscore',
    'jquery',
    'mage/translate',
], function (Actions, uiAlert, registry, _, $, $t) {
    'use strict';

    return Actions.extend({
        defaults: {
            ajaxSettings: {
                method: 'POST',
                dataType: 'json'
            },
            listens: {
                action: 'onAction'
            },
            ignoreTmpls: {
                fieldAction: true,
                options: true,
                action: true
            }
        },

        /**
         * Reload Assign Admin Product listing data source after actions perform
         *
         * @param {Object} data
         */
        onAction: function (data) {
            if (data.action == 'remove') {
                this.source().reload({
                    refresh: true
                });
            }
        },

        /**
         * Default action callback. Redirects to
         * the specified in action's data url.
         *
         * @param {String} actionIndex - Action's identifier.
         * @param {(Number|String)} recordId - Id of the record associated
         *      with a specified action.
         * @param {Object} action - Action's data.
         */
        defaultCallback: function (actionIndex, recordId, action) {
            if (action.isAjax) {
                this.request(action.href).done(function (response) {
                    var data;

                    if (!response.error) {
                        data = _.findWhere(this.rows, {
                            _rowIndex: action.rowIndex
                        });

                        this.trigger('action', {
                            action: actionIndex,
                            data: data
                        });
                    }
                }.bind(this));

            } else {
                this._super();
            }
        },

        /**
         * Send product type listing ajax request
         *
         * @param {String} href
         */
        request: function (href) {
            var settings = _.extend({}, this.ajaxSettings, {
                url: href,
                data: {
                    'form_key': window.FORM_KEY
                }
            });

            $('body').trigger('processStart');

            return $.ajax(settings)
                .done(function (response) {
                    if (response.error) {
                        uiAlert({
                            title: "Remove Assigned Product",
                            content: response.message
                        });
                    }
                    var params = [];
                    var ksTarget = registry.get('ks_marketplace_admin_product_listing.ks_marketplace_admin_product_listing_data_source');
                    if (ksTarget && typeof ksTarget === 'object') {                                     
                        ksTarget.set('params.t ', Date.now());                

                    }
                })
                .fail(function () {
                    uiAlert({
                        title: "Remove Assigned Product",
                        content: $t('Sorry, there has been an error processing your request. Please try again later.')
                    });
                })
                .always(function () {
                    $('body').trigger('processStop');
                });
        }
    });
});
