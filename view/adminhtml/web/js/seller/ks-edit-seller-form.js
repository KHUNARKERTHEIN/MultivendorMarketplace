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
    'uiRegistry',
    'Magento_Ui/js/form/form',
    'mage/url',
], function ($, uiRegistry, form, url) {
    'use strict';

    return form.extend({

        /**
         * Reset form.
         */
        reset: function () {
            $(".ks-available").html(" ");
            $(".ks-available").removeClass('admin__field-error');
            return this._super();
        }
    });
});
