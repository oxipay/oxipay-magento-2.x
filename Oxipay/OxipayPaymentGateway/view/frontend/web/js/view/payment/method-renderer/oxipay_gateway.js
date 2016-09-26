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

            redirectAfterPlaceOrder: false,

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
                this.placeOrder();

                return false;
            },
			
            afterPlaceOrder: function() {
				
				$.ajax({
					showLoader: true,
					url: url.build('oxipay/Outbound/Redirect'),
					data: {orderid: '000000098'},
					type: "GET"
				}).done(function (data) {
					window.location.replace(data);
				});
            }
        });
    }
);