<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Oxipay\OxipayPaymentGateway\Gateway\Config;

/**
 * Class Config.
 * Values returned from Magento\Payment\Gateway\Config\Config.getValue()
 * are taken by default from ScopeInterface::SCOPE_STORE
 */
class Config extends \Magento\Payment\Gateway\Config\Config
{
    const CODE = 'oxipay_gateway';

    const KEY_ACTIVE = 'active';
    const KEY_TITLE = 'title';
    const KEY_DESCRIPTION = 'description';
    const KEY_GATEWAY_LOGO = 'gateway_logo';
    const KEY_MERCHANT_NUMBER = 'merchant_number';
    const KEY_API_KEY = 'api_key';
    const KEY_GATEWAY_URL = 'gateway_url';
    const KEY_DEBUG = 'debug';
    const KEY_SPECIFIC_COUNTRY = 'specificcountry';
    const KEY_OXIPAY_APPROVED_ORDER_STATUS = 'oxipay_approved_order_status';
    const KEY_EMAIL_CUSTOMER = 'email_customer';
    const KEY_AUTOMATIC_INVOICE = 'automatic_invoice';

    /**
     * Get Merchant number
     *
     * @return string
     */
    public function getMerchantNumber() {
        return $this->getValue(self::KEY_MERCHANT_NUMBER);
    }

    /**
     * Get Merchant number
     *
     * @return string
     */
    public function getTitle() {
        return $this->getValue(self::KEY_TITLE);
    }

    /**
     * Get Logo
     *
     * @return string
     */
    public function getLogo() {
        return $this->getValue(self::KEY_GATEWAY_LOGO);
    }

    /**
     * Get Description
     *
     * @return string
     */
    public function getDescription() {
        return $this->getValue(self::KEY_DESCRIPTION);
    }

    /**
     * Is store in Australia
     * @return bool
     */
    public function isAus()
    {
        $checkoutUrl = $this->getGatewayUrl();
        return strpos($checkoutUrl, ".co.nz") ? false : true;
    }

    /**
     * Get Gateway URL
     *
     * @return string
     */
    public function getGatewayUrl() {
        return $this->getValue(self::KEY_GATEWAY_URL);
    }

	/**
	 * get the Oxipay refund gateway Url
	 * @return string
	 */
	public function getRefundUrl() {
    	$country_domain = $this->isAus() ? '.com.au' : '.co.nz';

		if (strpos($checkoutUrl, 'sandbox') === false) {
			$isSandbox = false;
		} else {
			$isSandbox = true; //default value
		}

		if (!$isSandbox){
			return 'https://portals.oxipay'.$country_domain.'/api/ExternalRefund/processrefund';
		} else {
			return 'https://portalssandbox.oxipay'.$country_domain.'/api/ExternalRefund/processrefund';
		}
	}

    /**
     * Get API Key
     *
     * @return string
     */
    public function getApiKey()
    {
        return $this->getValue(self::KEY_API_KEY);
    }

    /**
     * Get Oxipay Approved Order Status
     *
     * @return string
     */
    public function getOxipayApprovedOrderStatus()
    {
        return $this->getValue(self::KEY_OXIPAY_APPROVED_ORDER_STATUS);
    }

    /**
     * Check if customer is to be notified
     * @return boolean
     */
    public function isEmailCustomer()
    {
        return (bool) $this->getValue(self::KEY_EMAIL_CUSTOMER);
    }

    /**
     * Check if customer is to be notified
     * @return boolean
     */
    public function isAutomaticInvoice()
    {
        return (bool) $this->getValue(self::KEY_AUTOMATIC_INVOICE);
    }

    /**
     * Get Payment configuration status
     * @return bool
     */
    public function isActive()
    {
        return (bool) $this->getValue(self::KEY_ACTIVE);
    }

    /**
     * Get specific country
     *
     * @return string
     */
    public function getSpecificCountry()
    {
        return $this->getValue(self::KEY_SPECIFIC_COUNTRY);
    }

}
