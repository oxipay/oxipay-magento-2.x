<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Oxipay\OxipayPaymentGateway\Gateway\Request;

use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Oxipay\OxipayPaymentGateway\Gateway\Http\OxipayCrypto;

class AuthorizationRequest implements BuilderInterface
{
    /**
     * @var ConfigInterface
     */
    private $config;
    
    /**
     * @var OxipayCrypto
     */
    private $crypto;

    /**
     * @param ConfigInterface $config
     */
    public function __construct(
        ConfigInterface $config,
        OxipayCrypto $crypto
    ) {
        $this->config = $config;
        $this->crypto = $crypto;
    }

    /**
     * Builds ENV request
     *
     * @param array $buildSubject
     * @return array
     */
    public function build(array $buildSubject)
    {
        if (!isset($buildSubject['payment'])
            || !$buildSubject['payment'] instanceof PaymentDataObjectInterface
        ) {
            throw new \InvalidArgumentException('Payment data object should be provided');
        }

        /** @var PaymentDataObjectInterface $payment */
        $payment = $buildSubject['payment'];
        $order = $payment->getOrder();
        $shippingaddress = $order->getShippingAddress();
		$billingaddress = $order->getBillingAddress();

		/*
		 * Required fields
		 * First Name
		 * Last Name
		 * Email
		 * Mobile
		 * Shipping Address
		 * Shipping Suburb
		 * Shipping State
		 * Shipping Postcode
		 * Billing Address
		 * Billing Suburb
		 * Billing State
		 * Billing Postcode
		 * Total value
		 */
		
        $array = [
			'merchant_number' => $this->config->getValue(
                'merchant_number',
                $order->getStoreId()),
			'first_name' => $billingaddress->getFirstname(),
			'last_name' => $billingaddress->getLastname(),
			'email' => $billingaddress->getEmail(),
			'phone' => $billingaddress->getTelephone(),
			'shipping_address1' => $shippingaddress->getStreetLine1(),
			'shipping_address2' => $shippingaddress->getStreetLine2(),
			'shipping_city' => $shippingaddress->getCity(),
			'shipping_state' => $shippingaddress->getRegionCode(),
			'shipping_postcode' => $shippingaddress->getPostcode(),
			'billing_address1' => $billingaddress->getStreetLine1(),
			'billing_address1' => $billingaddress->getStreetLine2(),
			'billing_city' => $billingaddress->getCity(),
			'billing_state' => $billingaddress->getRegionCode(),
			'billing_postcode' => $billingaddress->getPostcode(),
			'amount' => $order->getGrandTotalAmount(),
			'currency' => $order->getCurrencyCode(),
			'invoice_number' => $order->getOrderIncrementId()
        ];
        
        $merchantkey = $this->config->getValue(
                'api_key',
                $order->getStoreId());
        $signedarray = $this->crypto->sign($array, $merchantkey);
		
		$signedarray['gateway_url'] = $this->config->getValue(
                'gateway_url',
                $order->getStoreId());
                
        return $signedarray;
    }
}
