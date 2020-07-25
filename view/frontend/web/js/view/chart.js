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
        initialize: function () {
            this._super();
        },

        /**
         * Preselect the Magento swatches
         *
         * @param uiClass
         * @param event
         */
        preselect: function (uiClass, event) {
            var size = $(event.currentTarget).data('size');
            $('[attribute-code="size"] [option-id=' + size + ']').click();
        }
    });
});
