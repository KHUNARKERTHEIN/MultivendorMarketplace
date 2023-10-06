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
    'uiElement',
    'uiRegistry',
    'uiLayout',
    'mageUtils',
    'underscore'
], function (Element, registry, layout, utils, _) {
    'use strict';

    return Element.extend({
        defaults: {
            buttonClasses: {},
            additionalClasses: {},
            displayArea: 'outsideGroup',
            displayAsLink: false,
            elementTmpl: 'ui/form/element/button',
            template: 'ui/form/components/button/simple',
            visible: true,
            disabled: false,
            title: '',
            buttonTextId: '',
            ariLabelledby: ''
        },

        /**
         * Initializes component.
         *
         * @returns {Object} Chainable.
         */
        initialize: function () {
            return this._super()
                ._setClasses()
                ._setButtonClasses();
        },

        /** @inheritdoc */
        initObservable: function () {
            return this._super()
                .observe([
                    'visible',
                    'disabled',
                    'title',
                    'childError'
                ]);
        },

        /**
         * Pass Black Function
         */
        action: function () {
            
        },

        /**
         * Create target component from template
         *
         * @param {Object} ksTargetName - name of component,
         * that supposed to be a template and need to be initialized
         */
        getFromTemplate: function (ksTargetName) {
            var ksParentName = ksTargetName.split('.'),
                ksIndex = ksParentName.pop(),
                ksChild;

            ksParentName = ksParentName.join('.');
            ksChild = utils.template({
                parent: ksParentName,
                name: ksIndex,
                nodeTemplate: ksTargetName
            });
            layout([ksChild]);
        },

        /**
         * Extends 'additionalClasses' object.
         *
         * @returns {Object} Chainable.
         */
        _setClasses: function () {
            if (typeof this.additionalClasses === 'string') {
                if (this.additionalClasses === '') {
                    this.additionalClasses = {};

                    return this;
                }

                this.additionalClasses = this.additionalClasses
                    .trim()
                    .split(' ')
                    .reduce(function (ksClasses, ksName) {
                        ksClasses[ksName] = true;

                        return ksClasses;
                    }, {}
                );
            }

            return this;
        },

        /**
         * Extends 'buttonClasses' object.
         *
         * @returns {Object} Chainable.
         */
        _setButtonClasses: function () {
            var ksAdditional = this.buttonClasses;

            if (_.isString(ksAdditional)) {
                this.buttonClasses = {};

                if (ksAdditional.trim().length) {
                    ksAdditional = ksAdditional.trim().split(' ');

                    ksAdditional.forEach(function (name) {
                        if (name.length) {
                            this.buttonClasses[name] = true;
                        }
                    }, this);
                }
            }

            _.extend(this.buttonClasses, {
                'action-basic': !this.displayAsLink,
                'action-additional': this.displayAsLink
            });

            return this;
        }
    });
});
