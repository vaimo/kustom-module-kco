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
    'jquery',
    'Klarna_Kco/js/model/config',
    'mage/translate'
], function (
    ko,
    Component,
    _,
    $,
    config
) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Klarna_Kco/prefill_notice'
        },
        isVisible: ko.computed(function () {
            return config.prefillNoticeEnabled();
        }),
        showTerms: ko.observable(false),

        /**
         * Toggling the terms
         */
        toggleTerms: function () {
            this.showTerms(!this.showTerms());
        },

        /**
         * Adding the url for the accept terms url to the location href object
         */
        getAcceptTerms: function () {
            location.href = config.acceptTermsUrl;
        },

        /**
         * Getting back the user terms text
         * @returns {*}
         */
        getUserTermsText: function () {
            var notice = $('#notice_terms_hidden').text();

            return notice.replace('%1', config.userTermsUrl);
        },

        /**
         * Initialization
         * @returns {*}
         */
        initialize: function () {
            this._super();

            return this;
        }
    });
});
