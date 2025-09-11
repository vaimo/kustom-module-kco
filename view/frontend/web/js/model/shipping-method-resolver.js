/**
 * Copyright © Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
define([
    'Magento_Checkout/js/model/quote',
    'Klarna_Kco/js/action/select-shipping-method'
], function (
    quote,
    kcoShippingMethod
) {
    'use strict';

    var currentQuoteShippingMethod = null,
        observedQuoteShippingMethod = null,

        /**
         * Getting back the shipping method code
         * @param {Object} method
         * @returns {String}
         */
        getMethodCode = function (method) {
            // jscs:disable requireCamelCaseOrUpperCaseIdentifiers
            return method.carrier_code + '_' + method.method_code;
        },

        /**
         * Checking if the selected shipping method changed
         * @returns {String[]}
         */
        isSameQuoteMethod = function () {
            return Object.keys(currentQuoteShippingMethod).every(function (key) {
                // Some method values can have different falsy values so we need to
                // let up on the comparison to not trigger any false negatives
                return currentQuoteShippingMethod[key] === observedQuoteShippingMethod[key];
            });
        };

    quote.shippingMethod.subscribe(function (method) {
        observedQuoteShippingMethod = method;
    });

    return {
        /**
         * Resolving the shipping method
         * @param {Object} rates
         * @param {String} selectedRateCode
         */
        resolveShippingMethod: function (rates, selectedRateCode) {
            var rateCodes = rates.map(function (rate) {
                return getMethodCode(rate);
            });

            // Magento doesn't automatically select a new shipping method if the previously
            // selected method disappears mid-session(e.g. table rate) or if there previously were no
            // available shipping methods. We need to select a method in this case to prevent the user
            // from trying to place an order without a shipping method.
            if (rateCodes.indexOf(selectedRateCode) === -1) {
                kcoShippingMethod(rates[0]);

                return;
            }

            // Extra comparison needed in case the selected shipping method is dynamic and changes on us.
            //
            // There's a race condition where the shipping method in the quote hasn't been updated
            // by the time the shippingService getRates event has been invoked. The quickest of timeouts
            // makes sure we're comparing the correct method objects.
            currentQuoteShippingMethod = quote.shippingMethod();
            setTimeout(function () {

                if (currentQuoteShippingMethod && observedQuoteShippingMethod && !isSameQuoteMethod()) {
                    kcoShippingMethod(currentQuoteShippingMethod);
                }
            });
        }
    };
});
