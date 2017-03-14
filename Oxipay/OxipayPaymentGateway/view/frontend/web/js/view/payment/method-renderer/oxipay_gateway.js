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
        'ko',
        'mage/url',
        'Magento_Checkout/js/model/quote',
        'Magento_Ui/js/model/messageList',
    ],
    function ($, Component, ko, url, Quote, globalMessageList) {
        'use strict';

        var self, C;

        C = Component.extend({
            defaults: {
                template: 'Oxipay_OxipayPaymentGateway/payment/form',
                transactionResult: ''
            },

            //"private" vars
            _requestTimeout: null,

            //methods
            initialize: function() {
                this._super();
                self = this;

                self.bindToEvents();

                self.updateData();
            },
            canSubmit: ko.observable(true),

            data: {
                reference: ko.observable(''),
                account_id: ko.observable(''),
                amount: ko.observable(''),
                currency: ko.observable(''),
                url_callback: ko.observable(''),
                url_complete: ko.observable(''),
                url_cancel: ko.observable(''),
                test: ko.observable(''),
                shop_country: ko.observable(''),
                shop_name: ko.observable(''),
                customer_first_name: ko.observable(''),
                customer_last_name: ko.observable(''),
                customer_email: ko.observable(''),
                customer_phone: ko.observable(''),
                customer_billing_country: ko.observable(''),
                customer_billing_city: ko.observable(''),
                customer_billing_address1: ko.observable(''),
                customer_billing_address2: ko.observable(''),
                customer_billing_state: ko.observable(''),
                customer_billing_zip: ko.observable(''),
                customer_shipping_country: ko.observable(''),
                customer_shipping_city: ko.observable(''),
                customer_shipping_address1: ko.observable(''),
                customer_shipping_address2: ko.observable(''),
                customer_shipping_state: ko.observable(''),
                customer_shipping_zip: ko.observable(''),
                signature: ko.observable('')
            },

            bindToEvents: function() {
                Quote.paymentMethod.subscribe(self.updateData, null, 'change');
                Quote.billingAddress.subscribe(self.updateData, null, 'change');
                Quote.shippingAddress.subscribe(self.updateData, null, 'change');
                Quote.paymentMethod.subscribe(self.updateData, null, 'change');
                Quote.totals.subscribe(self.updateData, null, 'change');
            },
            updateData: function(response) {
                var quoteData = {},
                    billingAddress,
                    shippingAddress;

                self.canSubmit(false);

                //using the timeout prevents multiple events spamming the server for oxipay detail updats
                if (self._requestTimeout != null) {
                    window.clearTimeout(self._requestTimeout);
                }

                window.setTimeout(function() {
                    billingAddress = Quote.billingAddress();
                    shippingAddress = Quote.shippingAddress();

                    quoteData.amount = Quote.totals().base_grand_total;
                    quoteData.currency = Quote.totals().base_currency_code;
                    quoteData.customer_email = (Quote.guestEmail) ? Quote.guestEmail : window.checkoutConfig.customerData.email;
                    if(billingAddress){
                        quoteData.customer_first_name = billingAddress.firstname;
                        quoteData.customer_last_name = billingAddress.lastname;
                        quoteData.customer_phone = billingAddress.telephone;
                        quoteData.customer_billing_country = billingAddress.countryId;
                        quoteData.customer_billing_city = billingAddress.city;
                        if(billingAddress.street){
                            quoteData.customer_billing_address1 = billingAddress.street[0];
                            quoteData.customer_billing_address2 = billingAddress.street[1];
                        }
                        quoteData.customer_billing_state = billingAddress.regionCode;
                        quoteData.customer_billing_zip = billingAddress.postcode;
                    }
                    if(shippingAddress){
                        quoteData.customer_shipping_country = shippingAddress.countryId;
                        quoteData.customer_shipping_city = shippingAddress.city;
                        if(shippingAddress.street){
                            quoteData.customer_shipping_address1 = shippingAddress.street[0];
                            quoteData.customer_shipping_address2 = shippingAddress.street[1];
                        }
                        quoteData.customer_shipping_state = shippingAddress.regionCode;
                        quoteData.customer_shipping_zip = shippingAddress.postcode;
                    }

                    //make ajax request
                    $.post('/oxipay/Checkout/RequestDetails', quoteData)
                        .complete(function(response) {
                            var responseData = response.responseJSON;

                            self.data.reference(responseData.x_reference);
                            self.data.account_id(responseData.x_account_id);
                            self.data.amount(responseData.x_amount);
                            self.data.currency(responseData.x_currency);
                            self.data.url_callback(responseData.x_url_callback);
                            self.data.url_complete(responseData.x_url_complete);
                            self.data.url_cancel(responseData.x_url_cancel);
                            self.data.test(responseData.x_test);
                            self.data.shop_country(responseData.x_shop_country);
                            self.data.shop_name(responseData.x_shop_name);
                            self.data.customer_first_name(responseData.x_customer_first_name);
                            self.data.customer_last_name(responseData.x_customer_last_name);
                            self.data.customer_email(responseData.x_customer_email);
                            self.data.customer_phone(responseData.x_customer_phone);
                            self.data.customer_billing_country(responseData.x_customer_billing_country);
                            self.data.customer_billing_city(responseData.x_customer_billing_city);
                            self.data.customer_billing_address1(responseData.x_customer_billing_address1);
                            self.data.customer_billing_address2(responseData.x_customer_billing_address2);
                            self.data.customer_billing_state(responseData.x_customer_billing_state);
                            self.data.customer_billing_zip(responseData.x_customer_billing_zip);
                            self.data.customer_shipping_country(responseData.x_customer_shipping_country);
                            self.data.customer_shipping_city(responseData.x_customer_shipping_city);
                            self.data.customer_shipping_address1(responseData.x_customer_shipping_address1);
                            self.data.customer_shipping_address2(responseData.x_customer_shipping_address2);
                            self.data.customer_shipping_state(responseData.x_customer_shipping_state);
                            self.data.customer_shipping_zip(responseData.x_customer_shipping_zip);
                            self.data.signature(responseData.x_signature);

                            if(!responseData.x_customer_email
                                || responseData.x_customer_email.length == 0
                            ){
                                globalMessageList.addErrorMessage({'message': 'Please enter your email address'});
                                self.canSubmit(false);
                            }
                            else if(!responseData.x_customer_first_name
                                || responseData.x_customer_first_name.length == 0
                                || !responseData.x_customer_last_name
                                || responseData.x_customer_last_name.length == 0
                                || !responseData.x_customer_billing_address1
                                || responseData.x_customer_billing_address1.length == 0
                                || !responseData.x_customer_billing_city
                                || responseData.x_customer_billing_city.length == 0
                                || !responseData.x_customer_billing_state
                                || responseData.x_customer_billing_state.length == 0
                                || !responseData.x_customer_billing_zip
                                || responseData.x_customer_billing_zip.length == 0
                            ){
                                globalMessageList.addErrorMessage({'message': 'Please enter your billing details'});
                                self.canSubmit(false);
                            }
                            else{
                                self.canSubmit(true);
                            }
                        });
                }, 300);
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
			getFromOxipay: function() {
                return window.checkoutConfig.payment.oxipay_gateway.form_oxipay;
            },
            getTitle: function() {
                return window.checkoutConfig.payment.oxipay_gateway.title;
            },
            getDescription: function() {
                return window.checkoutConfig.payment.oxipay_gateway.description;
            },
            getOxipayLogo:function(){
                var logo = window.checkoutConfig.payment.oxipay_gateway.logo;

                return logo;
            },
            getGatewayUrl: function() {
                return window.checkoutConfig.payment.oxipay_gateway.gateway_url;
            },
            getErrors: function()
            {
                if(window.checkoutConfig.payment.oxipay_gateway.errors)
                    $('#payment-method-content .error-msg').show();
                else
                    $('#payment-method-content .error-msg').hide();
                return window.checkoutConfig.payment.oxipay_gateway.errors;
            }
        });

        return C;
    }
);