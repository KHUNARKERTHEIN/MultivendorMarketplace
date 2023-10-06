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
    'mage/url',
    'Magento_Ui/js/form/element/select',
    'uiRegistry'
], function ($, url, Select, uiRegistry) {
    var self;

    return Select.extend({
        initialize: function () {
            self = this;
            this._super();

            self.value.subscribe(function () {
                self.updateValueOptions();
            });

            return this;
        },

        /**
         * Update options in value select.
         */
        updateValueOptions: function () {
            
            uiRegistry.get('index = ks_seller_id').clear();
            uiRegistry.get('index = ks_seller_id').options([]);
            uiRegistry.get('index = ks_seller_id').updateOption([]);
            uiRegistry.get('index = ks_seller_id')._setItemsQuantity(false);
            uiRegistry.get('index = ks_seller_id').filterInputValue('');

            $("[data-index='ks_owner_fieldset']").css("display","none");
        }
    });
});