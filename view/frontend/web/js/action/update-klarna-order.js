/**
 * Copyright © Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
/*jshint browser:true jquery:true*/
define([
    'mage/storage',
    'Klarna_Kco/js/model/iframe',
    'Klarna_Kco/js/model/config'
], function (
    storage,
    iframe,
    config
) {
    'use strict';

    return function () {
        iframe.suspend();
        storage.post(config.updateKlarnaOrderUrl).done(function (response) {
            if (response.error) {
                /**
                 * We scroll the customer to the top of the page even the customer is on a lower part of the page to
                 * ensure that he/she definitely see it.
                 */
                window.scrollTo(0, 'smooth');
            }
            iframe.resume();
        });
    };
});
