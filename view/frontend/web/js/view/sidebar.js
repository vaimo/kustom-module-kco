/**
 * Copyright © Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
define(['uiComponent'], function (Component) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Klarna_Kco/sidebar'
        },
        visible: true,

        /**
         * Initialization
         */
        initialize: function () {
            // eslint-disable-next-line no-unused-vars
            var self = this;

            this._super();
        }
    });
});
