/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
define(
    [
        'jquery',
        'Magento_Checkout/js/view/payment/default',
        'mage/url'
    ],
    function ($, Component, url) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Oxipay_OxipayPaymentGateway/payment/form',
                transactionResult: ''
            },

            initObservable: function () {

                this._super()
                    .observe([
                        'transactionResult'
                    ]);
                return this;
            },

            getCode: function() {
                return 'oxipay_gateway';
            },
            getData: function() {
                return {
                    'method': this.item.method,
                    'additional_data': {
                        'transaction_result': this.transactionResult()
                    }
                };
            },

            getTransactionResults: function() {
                return _.map(window.checkoutConfig.payment.oxipay_gateway.transactionResults, function(value, key) {
                    return {
                        'value': key,
                        'transaction_result': value
                    };
                });
            },

            continueToOxipay: function () {
                $('#oxipayredirectform').submit();
                //this.placeOrder();

                return false;
            },
			getFromOxipay: function() {
                return window.checkoutConfig.payment.oxipay_gateway.form_oxipay;
            },
            getDescription: function() {
                return window.checkoutConfig.payment.oxipay_gateway.description;
            },
            getOxipayLogo:function(){
                var logo = window.checkoutConfig.payment.oxipay_gateway.logo;
                console.log(logo);
                console.log(window.checkoutConfig.payment.oxipay_gateway.defaultLogo);

                return logo;
            },
            getErrors: function()
            {
                if(window.checkoutConfig.payment.oxipay_gateway.errors)
                    $('#payment-method-content .error-msg').show();
                else
                    $('#payment-method-content .error-msg').hide();
                return window.checkoutConfig.payment.oxipay_gateway.errors;
                            
            },                       
            afterPlaceOrder: function() {

                var geturl = url.build('oxipay/Outbound/Redirect')
                $.ajax({
                        url: geturl,
                        method: "GET",
                }).done(function (data) {
                        var payload = data;
                        document.getElementById('oxipayredirectform').action = payload['url'];
                        $.each(payload['payload'], function(itemkey, itemvalue) {
                            document.getElementById(itemkey).value = itemvalue;
                        });
                        document.getElementById('oxipayredirectform').submit();
                });
            }
        });
    }
);