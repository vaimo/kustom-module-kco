/**
 * Copyright © Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
define([], function () {
    'use strict';

    var suspended = false;

    return {
        suspended: suspended,

        /**
         * Suspending the iframe
         */
        suspend: function () {
            if (!this.suspended && window._klarnaCheckout) {
                window._klarnaCheckout(function (api) {
                    api.suspend();
                });

                this.suspended = true;
            }
        },

        /**
         * Resuming the iframe
         */
        resume: function () {
            if (this.suspended && window._klarnaCheckout) {
                window._klarnaCheckout(function (api) {
                    api.resume();
                });

                this.suspended = false;
            }
        }
    };
});
