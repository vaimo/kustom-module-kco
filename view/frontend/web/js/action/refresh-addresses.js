/**
 * Copyright © Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
define([
    'mage/storage',
    'Magento_Checkout/js/checkout-data',
    'Magento_Checkout/js/action/select-shipping-address',
    'Magento_Checkout/js/action/select-billing-address',
    'Magento_Checkout/js/model/new-customer-address',
    'Klarna_Kco/js/model/config'
], function (
    storage,
    checkoutData,
    selectShippingAddress,
    selectBillingAddress,
    newAddress,
    config
) {
    'use strict';

    var ajaxAddressAction = false;

    return function () {
        if (ajaxAddressAction) {
            return;
        }

        ajaxAddressAction = true;
        storage.post(config.getAddressesUrl)
            .done(function (response) {
                // Updating the js quote.shippingAddress with the new shipping address values
                // jscs:disable requireCamelCaseOrUpperCaseIdentifiers
                selectShippingAddress(newAddress(response.full_shipping));

                // Updating the persistence storage shipping address values
                // jscs:disable requireCamelCaseOrUpperCaseIdentifiers
                checkoutData.setShippingAddressFromData(response.min_shipping);

                // Updating the js quote.billingAddress with the new billing address values
                selectBillingAddress(newAddress(response.billing));

                ajaxAddressAction = false;
            });
    };
});
