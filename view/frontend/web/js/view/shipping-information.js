/**
 * Copyright © Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
define([
    'Magento_Checkout/js/view/shipping-information'
], function (
    Component
) {
    'use strict';

    return Component.extend({
        /**
         * @return {Boolean}
         */
        isVisible: function () {
            return false;
        }
    });
});
