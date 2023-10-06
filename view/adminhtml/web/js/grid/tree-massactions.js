/**
 * Ksolves
 *
 * @category  Ksolves
 * @package   Ksolves_MultivendorMarketplace
 * @author    Ksolves Team
 * @copyright Copyright (c) Ksolves India Limited (https://www.ksolves.com/)
 * @license   https://store.ksolves.com/magento-license
 */

/**
 * @api
 */
define([
    'jquery',
    'ko',
    'underscore',
    'Magento_Ui/js/grid/tree-massactions'
], function ($, ko, _, Treemassactions) {
    'use strict';

    return Treemassactions.extend({
       /**
         * Default action callback. Sends selections data
         * via POST request.
         *
         * @param {Object} action - Action data.
         * @param {Object} data - Selections data.
         */
        defaultCallback: function (action, data) {
            var ksCurrentUrl = $(location).attr('href');

            if(ksCurrentUrl.indexOf("/store/") != -1){
                var ksPattern = /(store\/)(\d)*?\//;
                var ksResult = ksCurrentUrl.match(ksPattern);
                var ksStoreId = ksResult[0].split(/(\d+)/);
                
                data.params.ks_store_id= ksStoreId[1];
            }  
            
            return this._super();
        }
    });
});
