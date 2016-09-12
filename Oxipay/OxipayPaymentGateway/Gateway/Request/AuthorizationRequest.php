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
			'MERCHANT_NUMBER' => $this->config->getValue(
                'merchant_number',
                $order->getStoreId()),
			'FIRSTNAME' => $billingaddress->getFirstname(),
			'LASTNAME' => $billingaddress->getLastname(),
			'EMAIL' => $billingaddress->getEmail(),
			'MOBILE' => $billingaddress->getTelephone(),
			'SHIPPINGADDRESS1' => $shippingaddress->getStreetLine1(),
			'SHIPPINGADDRESS2' => $shippingaddress->getStreetLine2(),
			'SHIPPINGSUBURB' => $shippingaddress->getCity(),
			'SHIPPINGSTATE' => $shippingaddress->getRegionCode(),
			'SHIPPINGPOSTCODE' => $shippingaddress->getPostcode(),
			'BILLINGADDRESS1' => $billingaddress->getStreetLine1(),
			'BILLINGADDRESS2' => $billingaddress->getStreetLine2(),
			'BILLINGSUBURB' => $billingaddress->getCity(),
			'BILLINGSTATE' => $billingaddress->getRegionCode(),
			'BILLINGPOSTCODE' => $billingaddress->getPostcode(),
			'AMOUNT' => $order->getGrandTotalAmount()
        ];
        
        $merchantkey = $this->config->getValue(
                'api_key',
                $order->getStoreId());
        $signedarray = $this->crypto->sign($array, $merchantkey);
                
        return $signedarray;
    }
}
