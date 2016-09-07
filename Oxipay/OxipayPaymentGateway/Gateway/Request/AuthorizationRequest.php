<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Oxipay\OxipayPaymentGateway\Gateway\Request;

use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Request\BuilderInterface;

class AuthorizationRequest implements BuilderInterface
{
    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @param ConfigInterface $config
     */
    public function __construct(
        ConfigInterface $config
    ) {
        $this->config = $config;
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
		
        return [
			'MERCHANT_NUMBER' => $this->config->getValue(
                'merchant_number',
                $order->getStoreId()),
			'FIRSTNAME' => $order->getCustomerFirstName(),
			'LASTNAME' => $order->getCustomerLastName(),
			'EMAIL' => $billingaddress->getEmail(),
			'MOBILE' => $billingaddress->getTelephone(),
			'SHIPPINGADDRESS' => $shippingaddress->getStreet(),
			'SHIPPINGSUBURB' => $shippingaddress->getCity(),
			'SHIPPINGSTATE' => $shippingaddress->getRegion(),
			'SHIPPINGPOSTCODE' => $shippingaddress->getPostcode(),
			'BILLINGADDRESS' => $billingaddress->getStreet(),
			'BILLINGSUBURB' => $billingaddress->getCity(),
			'BILLINGSTATE' => $billingaddress->getRegion(),
			'BILLINGPOSTCODE' => $billingaddress->getPostcode(),
			'AMOUNT' => $order->getGrandTotalAmount()
			
            /*'TXN_TYPE' => 'A',
            'INVOICE' => $order->getOrderIncrementId(),
            'AMOUNT' => $order->getGrandTotalAmount(),
            'CURRENCY' => $order->getCurrencyCode(),
            'EMAIL' => $address->getEmail(),
            'MERCHANT_NUMBER' => $this->config->getValue(
                'merchant_number',
                $order->getStoreId()
            )*/
        ];
    }
}
