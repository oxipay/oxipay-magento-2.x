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
        if (isset($buildSubject['payment']) && $buildSubject['payment'] instanceof PaymentDataObjectInterface) {
            $payment = $buildSubject['payment'];
            $order = $payment->getOrder();
        } else {
            throw new \InvalidArgumentException('Payment data object should be provided');
        }

        /** @var PaymentDataObjectInterface $payment */
        $shippingaddress = $order->getShippingAddress();
		$billingaddress = $order->getBillingAddress();
		
		$om = \Magento\Framework\App\ObjectManager::getInstance();
		$manager = $om->get('Magento\Store\Model\StoreManagerInterface');
		$store = $manager->getStore($order->getStoreId());

                $array = [

			'x_currency' => $order->getCurrencyCode(),
			'x_url_complete' => "",
			'x_url_callback' => "",
			'x_url_cancel' => "",
			'x_shop_name' => $store->getName(),
			'x_account_id' => $this->config->getValue(
			'merchant_number', $order->getStoreId()),
			'x_reference' => $order->getOrderIncrementId(),
			'x_invoice' => $order->getOrderIncrementId(),
			'x_amount' => $order->getGrandTotalAmount(),
			'x_customer_first_name' => $billingaddress->getFirstname(),
			'x_customer_last_name' => $billingaddress->getLastname(),
			'x_customer_email' => $billingaddress->getEmail(),
			'x_customer_phone' => $billingaddress->getTelephone(),
			'x_customer_billing_address1' => $billingaddress->getStreetLine1(),
			'x_customer_billing_address2' => $billingaddress->getStreetLine2(),
			'x_customer_billing_city' => $billingaddress->getCity(),
			'x_customer_billing_state' => $billingaddress->getRegionCode(),
			'x_customer_billing_zip' => $billingaddress->getPostcode(),
			'x_customer_shipping_address1' => $shippingaddress->getStreetLine1(),
			'x_customer_shipping_address2' => $shippingaddress->getStreetLine2(),
			'x_customer_shipping_city' => $shippingaddress->getCity(),
			'x_customer_shipping_state' => $shippingaddress->getRegionCode(),
			'x_customer_shipping_zip' => $shippingaddress->getPostcode(),
			'x_test' => $this->config->getValue('test_mode', $order->getStoreId())
        ];
        
        $merchantkey = $this->config->getValue(
                'api_key',
                $order->getStoreId());
        $signedarray = $this->crypto->sign($array, $merchantkey);
		
        if (isset($buildSubject['payment'])){
            $signedarray['gateway_url'] = $this->config->getValue(
            'gateway_url',
            $order->getStoreId());
        } else {
            $signedarray['gateway_url'] = $this->config->getValue(
            'gateway_redirect_url',
            $order->getStoreId());
        }
                
        return $signedarray;
    }
}
