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
], function ($ ) {
    'use strict';

        return function (config) {
             $("<span class='ks-notifications-counter'>"+config.ksPendingSellerCount+"</span>").insertAfter(".item-seller-pending > a > span");
             $("<span class='ks-notifications-counter'>"+config.ksPendingCategoryRequestsCount+"</span>").insertAfter(".item-categoryrequests > a > span");
             $("<span class='ks-notifications-counter'>"+config.ksPendingProductCount+"</span>").insertAfter(".item-product-pending > a > span");
             $("<span class='ks-notifications-counter'>"+config.ksPendingPriceComparisonProductCount+"</span>").insertAfter(".item-price-comparison-product-pending > a > span");
        };
});