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
    'mage/template',
    'jquery/ui',
    'ksBaseImage'
], function ($, mageTemplate) {
    'use strict';

    /**
     * Formats incoming bytes value to a readable format.
     *
     * @param {Number} bytes
     * @returns {String}
     */
    function bytesToSize(bytes) {
        var sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'],
            i;

        if (bytes === 0) {
            return '0 Byte';
        }

        i = window.parseInt(Math.floor(Math.log(bytes) / Math.log(1024)));

        return Math.round(bytes / Math.pow(1024, i), 2) + ' ' + sizes[i];
    }


    /**
     * Product gallery widget
     */
    $.widget('mage.ksProductGallery', {
        options: {
            imageSelector: '[data-role=image]',
            imageElementSelector: '[data-role=image-element]',
            template: '[data-template=image]',
            imageSizeLabel: '[data-role=size]',
            types: null,
            initialized: false
        },

        /**
         * Gallery creation
         * @protected
         */
        _create: function () {
            this.options.types = this.options.types || this.element.data('types');
            this.options.images = this.options.images || this.element.data('images');

            this.imgTmpl = mageTemplate(this.element.find(this.options.template).html());

            this._bind();

            $.each(this.options.images, $.proxy(function (index, imageData) {
                this.element.trigger('addItem', imageData);
            }, this));

            this.options.initialized = true;
        },

        /**
         * Bind handler to elements
         * @protected
         */
        _bind: function () {
            var events = {
                openDialog: '_onOpenDialog',
                addItem: '_addItem',
                removeItem: '_removeItem',
                setImageType: '_setImageType',
                setPosition: '_setPosition',
                resort: '_resort',
                'mouseup [data-role=delete-button]': function (event) {
                    var $imageContainer;

                    event.preventDefault();
                    $imageContainer = $(event.currentTarget).closest(this.options.imageSelector);
                    this.element.find('[data-role=dialog]').trigger('close');
                    this.element.trigger('removeItem', $imageContainer.data('imageData'));
                }
            };

            this._on(events);
            this.element.sortable({
                distance: 8,
                items: this.options.imageSelector,
                tolerance: "pointer",
                cancel: 'input, button, .uploader',
                update: $.proxy(function () {
                    this.element.trigger('resort');
                }, this)
            });
        },

        /**
         * Set image as main
         * @param {Object} imageData
         * @private
         */
        setBase: function (imageData) {
            var baseImage = this.options.types.image;
            var sameImages = $.grep(
                $.map(this.options.types, function (el) {
                    return el;
                }),
                function (el) {
                    return el.value === baseImage.value;
                }
            );
            var isImageOpened = this.findElement(imageData).hasClass('active');

            $.each(sameImages, $.proxy(function (index, image) {
                this.element.trigger('setImageType', {
                    type: image.code,
                    imageData: imageData
                });

                if (isImageOpened) {
                    this.element.find('.ks-item').addClass('selected');
                    this.element.find('[data-role=type-selector]').prop({
                        'checked': true
                    });
                }
            }, this));
        },

        /**
         * Find element by fileName
         * @param {Object} data
         * @returns {Element}
         */
        findElement: function (data) {
            return this.element.find(this.options.imageSelector).filter(function () {
                return $(this).data('imageData').file === data.file;
            }).first();
        },

        /**
         * Add image
         * @param event
         * @param imageData
         * @private
         */
        _addItem: function (event, imageData) { 
            var count = this.element.find(this.options.imageSelector).length,
                element,
                imgElement;

            imageData = $.extend({
                file_id: Math.random().toString(33).substr(2, 18),
                disabled: imageData.disabled ? imageData.disabled : 0,
                position: count + 1,
                sizeLabel: bytesToSize(imageData.size)
            }, imageData);

            element = this.imgTmpl({
                data: imageData
            });

            element = $(element).data('imageData', imageData);
            if (count === 0) {
                element.prependTo(this.element);
            } else {
                element.insertAfter(this.element.find(this.options.imageSelector + ':last'));
            }

            if (!this.options.initialized &&
                this.options.images.length === 0 ||
                this.options.initialized &&
                this.element.find(this.options.imageSelector + ':not(.removed)').length === 1
            ) {
                this.setBase(imageData);
            }

            imgElement = element.find(this.options.imageElementSelector);

            imgElement.on('load', this._updateImageDimesions.bind(this, element));

            $.each(this.options.types, $.proxy(function (index, image) {
                if (imageData.file === image.value) {
                    this.element.trigger('setImageType', {
                        type: image.code,
                        imageData: imageData
                    });
                }
            }, this));

            this._updateImagesRoles();
        },

        /**
         * Returns a list of current images.
         *
         * @returns {jQueryCollection}
         */
        _getImages: function () {
            return this.element.find(this.options.imageSelector);
        },

        /**
         * Returns a list of images roles.
         *
         * @return {Object}
         */
        _getRoles: function () {
            return _.mapObject(this.options.types, function (data, key) {
                var elem = this.element.find('.ks-image-' + key);

                return {
                    index: key,
                    value: elem.val(),
                    elem: elem
                };
            }, this);
        },

        /**
         * Updates labels with roles information for each image.
         */
        _updateImagesRoles: function () {
            var $images = this._getImages().toArray(),
                roles = this._getRoles();

            $images.forEach(function (img) {
                var $img = $(img),
                    data = $img.data('imageData');

                $img.find('[data-role=roles-labels] li').each(function (index, elem) {
                    var $elem = $(elem),
                        roleCode = $elem.data('roleCode'),
                        role = roles[roleCode];

                    role.value === data.file  ?
                        $elem.show() :
                        $elem.hide();
                });

            });
        },

        /**
         * Updates image's dimensions information.
         *
         * @param {jQeuryCollection} imgContainer
         */
        _updateImageDimesions: function (imgContainer) {
            var $img = imgContainer.find(this.options.imageElementSelector)[0],
                $dimens = imgContainer.find('[data-role=image-dimens]');

            $dimens.text($img.naturalWidth + 'x' + $img.naturalHeight + ' px');
        },

        /**
         * Remove Image
         * @param {jQuery.Event} event
         * @param imageData
         * @private
         */
        _removeItem: function (event, imageData) {
            var $imageContainer = this.findElement(imageData);
            imageData.isRemoved = true;
            $imageContainer.addClass('removed').hide().find('.is-removed').val(1);
        },

        /**
         * Set image type
         * @param event
         * @param data
         * @private
         */
        _setImageType: function (event, data) {
            if (data.type === 'image') {
                this.element.find('.base-image').removeClass('base-image');
            }

            if (data.imageData) {
                this.options.types[data.type].value = data.imageData.file;

                if (data.type === 'image') {
                    this.findElement(data.imageData).addClass('base-image');
                }
            } else {
                this.options.types[data.type].value = 'no_selection';
            }
            this.element.find('.ks-image-' + data.type).val(this.options.types[data.type].value || 'no_selection');
            this._updateImagesRoles();
        },

        /**
         * Resort images
         * @private
         */
        _resort: function () {
            this.element.find('.position').each($.proxy(function (index, element) {
                var value = $(element).val();

                if (value != index) {
                    this.element.trigger('moveElement', {
                        imageData: $(element).closest(this.options.imageSelector).data('imageData'),
                        position: index
                    });
                    $(element).val(index);
                }
            }, this));
        },

        /**
         * Set image position
         * @param event
         * @param data
         * @private
         */
        _setPosition: function (event, data) {
            var $element = this.findElement(data.imageData);
            var curIndex = this.element.find(this.options.imageSelector).index($element);
            var newPosition = data.position + (curIndex > data.position ? -1 : 0);

            if (data.position != curIndex) {
                if (data.position === 0) {
                    this.element.prepend($element);
                } else {
                    $element.insertAfter(
                        this.element.find(this.options.imageSelector).eq(newPosition)
                    );
                }
                this.element.trigger('resort');
            }
        }
    });

    // Extension for mage.ksProductGallery - Add advanced settings block
    $.widget('mage.ksProductGallery', $.mage.ksProductGallery, {
        options: {
            dialogTemplate: '.ks-dialog-template'
        },

        _create: function () {
            this._super();
            var template = this.element.find(this.options.dialogTemplate);
            if (template.length) {
                this.dialogTmpl = mageTemplate(template.html());
            }
        },

        /**
         * Bind handler to elements
         * @protected
         */
        _bind: function () {
            this._super();
            var events = {};
            events['click [data-role=close-panel]'] = $.proxy(function () {
                this.element.find('[data-role=dialog]').trigger('close');
            }, this);
            events['mouseup ' + this.options.imageSelector] = function (event) {
                if (!$(event.currentTarget).is('.ui-sortable-helper')) {
                    $(event.currentTarget).addClass('active');
                    var itemId = $(event.currentTarget).find('input')[0].name.match(/\[([^\]]*)\]/g)[2];
                    $('#item_id').val(itemId);
                    var imageData = $(event.currentTarget).data('imageData');
                    var $imageContainer = this.findElement(imageData);
                    if ($imageContainer.is('.removed')) {
                        return;
                    }
                    this.element.trigger('openDialog', [imageData]);
                }
            };
            this._on(events);
            this.element.on('sortstart', $.proxy(function () {
                this.element.find('[data-role=dialog]').trigger('close');
            }, this));
        },

        /**
         *
         * Click by image handler
         *
         * @param e
         * @param imageData
         * @private
         */
        _onOpenDialog: function(e, imageData) {
            if (imageData.media_type && imageData.media_type != 'image') {
                return;
            }
            this._showDialog(imageData);
        },

        /**
         * Show dialog
         * @param imageData
         * @private
         */
        _showDialog: function (imageData) {
            var $imageContainer = this.findElement(imageData);
            var dialogElement = $imageContainer.data('dialog');
            if (!this.dialogTmpl) {
                alert('System problem!');
                return;
            }

            var $template = this.dialogTmpl({ data: imageData });
            dialogElement = $($template);
            dialogElement.modal({
                'type': 'slide',
                title: $.mage.__('Image Detail'),
                buttons: [],
                opened: function() {
                    dialogElement.trigger('open');
                },
                closed: function(e) {
                    dialogElement.trigger('close');
                }
            });
            dialogElement
                .data('imageContainer', $imageContainer)
                .on('open', $.proxy(function (event) { 
                    
                    var imageSizeKb = imageData.sizeLabel,
                        image = document.createElement('img'),
                        sizeSpan = dialogElement.find(".ks-image-size")
                            .find('[data-message]'),
                        resolutionSpan = dialogElement.find(".ks-image-resolution")
                            .find('[data-message]'),
                        sizeText = sizeSpan.attr('data-message').replace('{size}', imageSizeKb),
                        resolutionText;
                        
                    image.src = imageData.url;

                    resolutionText = resolutionSpan
                        .attr('data-message')
                        .replace('{width}^{height}', image.width + 'x' + image.height);

                    sizeSpan.text(sizeText); 
                    resolutionSpan.text(resolutionText);


                    $(event.target)
                        .find('[data-role=type-selector]')
                        .each($.proxy(function (index, checkbox) {
                            var $checkbox = $(checkbox),
                                parent = $checkbox.closest('.ks-item'),
                                selectedClass = 'selected',
                                isChecked = this.options.types[$checkbox.val()].value == imageData.file;
                            $checkbox.prop(
                                'checked',
                                isChecked
                            );
                            parent.toggleClass(selectedClass, isChecked);
                        }, this));

                }, this))
                .on('close', $.proxy(function (event) {
                    $imageContainer.removeClass('active');
                    $imageContainer.data('dialog', null);
                }, this));

            $imageContainer.data('dialog', dialogElement);


            var _changeDescription = function(e) {
                var target = jQuery(e.target);
                var targetName = target.attr('name');
                var desc = target.val();
                jQuery('input[type="hidden"][name="'+ targetName + '"]').val(desc);
                imageData.label = desc;
                imageData.label_default = desc;
            };

            dialogElement.on('change', '[data-role=type-selector]', function () {
                var parent = $(this).closest('.ks-item'),
                    selectedClass = 'selected';
                parent.toggleClass(selectedClass, $(this).prop('checked'));
            });
            dialogElement.on('change', '[data-role=type-selector]', $.proxy(this._notifyType, this));
            dialogElement.on('change', '[data-role=visibility-trigger]', $.proxy(function(e) {
                this._changeVisibility(e, imageData);
            }, this));
            dialogElement.on('change', '#image-description', _changeDescription);
            dialogElement.modal('openModal');
        },

        /**
         * Change visibility
         *
         * @param event
         * @private
         */
        _changeVisibility: function (event, imageData) {
            var $checkbox = $(event.currentTarget);
            var $imageContainer = $checkbox.closest('[data-role=dialog]').data('imageContainer');
            $imageContainer.toggleClass('hidden-for-front', $checkbox.is(':checked'));
            var checked = $checkbox.is(':checked') ? 1 : 0;
            $imageContainer.find('[name*="disabled"]').val(checked);
            imageData.disabled = checked;
        },

        /**
         * Set image
         * @param event
         * @private
         */
        _notifyType: function (event) {
            var $checkbox = $(event.currentTarget);
            var $imageContainer = $checkbox.closest('[data-role=dialog]').data('imageContainer');
            this.element.trigger('setImageType', {
                type: $checkbox.val(),
                imageData: $checkbox.is(':checked') ? $imageContainer.data('imageData') : null
            });

            this._updateImagesRoles();
        }
    });

    return $.mage.ksProductGallery;
});
