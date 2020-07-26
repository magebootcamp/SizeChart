/*
 * Copyright (c) MageBootcamp 2020.
 *
 * Created by MageBootcamp: The Ultimate Online Magento Course.
 * We are here to help you become a Magento PRO.
 * Watch and learn at https://magebootcamp.com.
 *
 * @author Daniel Donselaar
 */
define([
    'uiComponent',
    'jquery',
    'ko',
    'mage/mage',
    'mage/decorate'
], function (Component, $, ko) {
    'use strict';

    return Component.extend({
        /**
         * Injected through component data
         */
        fieldConfig: [],
        fields: [],

        /**
         * On component load add the fields.
         */
        initialize: function () {
            this._super();

            this.fields = this.getFields();
        },

        /**
         * Get an array of form fields
         *
         * @returns {*}
         */
        getFields: function () {
            var self = this;
            return $.map(this.fieldConfig, function (label, key) {
                if (!self.hasUrlParam(key)) {
                    return {key: key, label: label};
                }
            });
        },

        /**
         * Check if a url param is set
         *
         * @param {string} name
         * @returns {boolean}
         */
        hasUrlParam: function (name) {
            return this.getUrlParam(name) > 0;
        },

        /**
         * Get an url param
         *
         * @param {string} name
         * @returns {number}
         */
        getUrlParam: function (name) {
            return parseFloat(new URL(location.href).searchParams.get(name));
        },

        /**
         * Filter the url with the form data
         *
         * @param formElement
         */
        filter: function (formElement) {
            var self = this,
                url = new URL(location.href),
                formChange = false;

            $(formElement).serializeArray().forEach(function (formField) {
                if (!self.hasUrlParam(formField.name) && formField.value > 0) {
                    url.searchParams.append(formField.name, formField.value);
                    formChange = true;
                }
            })

            if (formChange) {
                location.href = url.href;
            }
        },

        filtersAvailable: function () {
            return this.fields.length > 0;
        }
    });
});
