/**
 * Copyright © Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
define(['ko'], function (ko) {
    'use strict';

    return {
        methodUrl: window.checkoutConfig.klarna.methodUrl,
        paymentMethod: window.checkoutConfig.klarna.paymentMethod,
        frontEndShipping: window.checkoutConfig.klarna.frontEndShipping,
        updateKlarnaOrderUrl: window.checkoutConfig.klarna.updateKlarnaOrderUrl,
        failureUrl: window.checkoutConfig.klarna.failureUrl,
        getAddressesUrl: window.checkoutConfig.klarna.getAddressesUrl,
        acceptTermsUrl: window.checkoutConfig.klarna.acceptTermsUrl,
        userTermsUrl: window.checkoutConfig.klarna.userTermsUrl,
        prefillNoticeEnabled: ko.observable(window.checkoutConfig.klarna.prefillNoticeEnabled),
        isKssEnabled: window.checkoutConfig.klarna.isKssEnabled,
        updateKssStatusUrl: window.checkoutConfig.klarna.updateKssStatusUrl,
        updateKssDiscountOrderUrl: window.checkoutConfig.klarna.updateKssDiscountOrderUrl
    };
});
