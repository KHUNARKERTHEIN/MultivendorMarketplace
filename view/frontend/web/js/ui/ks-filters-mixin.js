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
    'mage/backend/notification'
], function ($, notification) {
    'use strict';

    return function (Collection) {
        return Collection.extend({

            /**
             * Initializes filters component.
             *
             * @returns {Filters} Chainable.
             */
            initialize: function () {
                notification({}, $('body'));
                return this._super();
            }
        });
    }

});