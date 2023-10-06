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

            var ksCurrentUrl = $(location).attr('href');

            if(ksCurrentUrl.indexOf("/id/") != -1){
                var ksPattern = /(id\/)(\d)*?\//;
                var ksResult = ksCurrentUrl.match(ksPattern);
                var ksProductId = ksResult[0].split(/(\d+)/);

                if (this.params.namespace=='grouped_product_listing') {
                    this.params.current_product_id = ksProductId[1];
                }

                if (this.params.namespace=='bundle_product_listing') {
                    this.params.current_product_id = ksProductId[1];
                }

                if (this.params.namespace=='configurable_associated_product_listing') {
                    this.params.current_product_id = ksProductId[1];
                }
            }  
            return this._super();
        },
    });
});
