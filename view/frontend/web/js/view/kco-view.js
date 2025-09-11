/**
 * Copyright © Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
define([
    'ko',
    'uiComponent',
    'underscore',
    'domReady',
    'Klarna_Kco/js/action/update-klarna-order',
    'Klarna_Kco/js/model/config',
    'Klarna_Kco/js/model/events',
    'Magento_Checkout/js/model/step-navigator',
    'Magento_Checkout/js/model/shipping-service',
    'Magento_Customer/js/customer-data',
    'Magento_Checkout/js/model/quote',
    'mage/translate',
    'Magento_Ui/js/model/messageList',
    'Klarna_Kco/js/action/refresh-addresses',
    'Klarna_Kco/js/action/select-shipping-method'
], function (
    ko,
    Component,
    _,
    domReady,
    updateKlarnaOrder,
    config,
    kcoEvents,
    stepNavigator,
    shippingService,
    customerData,
    quote,
    $t,
    messageList,
    kcoRefreshAddresses,
    kcoShippingMethod
) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Klarna_Kco/klarna'
        },
        isVisible: ko.observable(true),
        currentTotals: {},

        /**
         * Initialization
         * @returns {*}
         */
        initialize: function () {
            this._super();
            stepNavigator.registerStep(
                'klarna_kco',
                null,
                $t('Checkout'),
                this.isVisible,
                _.bind(this.navigate, this),
                100
            );

            /**
             * We need to set this value to avoid a "Email is required" error message when applying a coupon
             */
            if (quote.guestEmail == null) {
                quote.guestEmail = '';
            }

            this.selectShippingMethod();
            shippingService.getShippingRates().subscribe(function (rates) {

                if (!config.frontEndShipping || config.isKssEnabled) {
                    /**
                     * We do nothing since either there is already visual feedback when shipping is outside the iframe
                     * or KSA is used.
                     */
                    return;
                }

                if ((!rates || rates.length === 0) &&
                    !quote.isVirtual() &&
                    quote.shippingAddress().street !== undefined
                ) {

                    messageList.addErrorMessage({
                        message: $t('No shipping methods available for entered address')
                    });
                    customerData.set('messages', {
                        messages: [{
                            type: 'error',
                            text: $t('No shipping methods available for entered address')
                        }]
                    });
                }
            });

            /**
             * This will be called whenever a getTotals(...) call will happen.
             *
             * We have two different workflows:
             * 1) When something happens inside the iframe then we don't update the Klarna order. This is not needed
             * since we're doing the logic in the callback controllers.
             * 2) When something happens outside the iframe (for example coupon; shipping methods will be handled
             * differently) then we will update the Klarna order. Without it just the shop quote will have the correct
             * values but the total values inside the iframe won't be updated. This leads to a wrong synchronization
             * and the purchase can not be completed.
             */
            quote.totals.subscribe(function (newTotals) {
                if (window.checkoutConfig.klarna.klarnaUpdateNeeded === false) {
                    window.checkoutConfig.klarna.klarnaUpdateNeeded = true;
                    this.currentTotals = newTotals;

                    return;
                }

                if (JSON.stringify(newTotals) !== JSON.stringify(this.currentTotals)) {
                    updateKlarnaOrder();
                }

                window.checkoutConfig.klarna.klarnaUpdateNeeded = true;
                this.currentTotals = newTotals;
            });

            domReady(function () {
                var checkExist = window.setInterval(function () {
                    if (window._klarnaCheckout) {
                        kcoEvents.attachEvents();
                        // We need to let Magento know about the addresses Klarna have in case
                        // this init comes from a page reload on an existing KCO session.
                        if (!config.frontEndShipping) {
                            kcoRefreshAddresses();
                        }
                        window.clearInterval(checkExist);
                    }
                }, 1000);
            });

            return this;
        },

        /**
         * Selecting the shipping method
         */
        selectShippingMethod: function () {
            // Getting the method from Magento's checkout config
            var method = window.checkoutConfig.selectedShippingMethod;

            /**
             * There is no shipping method (default) selected when initial loading the checkout page as guest or
             * no shipping methods are available for the customer.
             */
            if (method !== null) {
                kcoShippingMethod(method);
            }
        },

        /**
         * The navigate() method is responsible for navigation between checkout step
         * during checkout. You can add custom logic, for example some conditions
         * for switching to your custom step. (This method is required even though it
         * is blank, don't delete)
         */
        navigate: function () {

        },

        /**
         * Navigating to the next step
         */
        navigateToNextStep: function () {
            stepNavigator.next();
        }
    });
});
