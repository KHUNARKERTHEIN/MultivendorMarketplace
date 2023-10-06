/**
 * Ksolves
 *
 * @category  Ksolves
 * @package   Ksolves_MultivendorMarketplace
 * @author    Ksolves Team
 * @copyright Copyright (c) Ksolves India Limited (https://www.ksolves.com/)
 * @license   https://store.ksolves.com/magento-license
 */
/*jshint browser:true jquery:true*/
define([
    'jquery',
    'ksProductGallery',
    'jquery/ui',
    'Magento_Ui/js/modal/modal',
    'mage/translate',
    'mage/backend/tree-suggest',
    'mage/backend/validation'
], function ($, ksProductGallery) {
    'use strict';

    $.widget('mage.ksProductGallery', ksProductGallery, {

        /**
         * * Fired when widget initialization start
         * @private
         */
        _create: function () {
            this._bind();
        },

        /**
         * Bind events
         * @private
         */
        _bind: function () {
            $(this.element).on('click', this.showModal.bind(this));
            $('.gallery.ui-sortable').on('openDialog', $.proxy(this._onOpenDialog, this));
        },

        /**
         * Open dialog for external video
         * @private
         */
        _onOpenDialog: function (e, imageData) {

            if (imageData['media_type'] !== 'external-video') {
                return;
            }
            this.showModal();
        },

        /**
         * Fired on trigger "openModal"
         */
        showModal: function () {

            $('#ks_video_popup').modal('openModal');
        }
    });

    return $.mage.ksProductGallery;
});
