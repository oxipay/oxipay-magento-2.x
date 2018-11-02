<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Oxipay\OxipayPaymentGateway\Gateway\Request;

use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Checkout\Model\Session;
use Oxipay\OxipayPaymentGateway\Gateway\Config\Config;
use Psr\Log\LoggerInterface;

class RefundRequest implements BuilderInterface
{
    private $_logger;
    private $_session;
    private $_gatewayConfig;

    /**
     * @param ConfigInterface $config
     */
    public function __construct(
        Config $gatewayConfig,
        LoggerInterface $logger,
        Session $session
    ) {
        $this->_gatewayConfig = $gatewayConfig;
        $this->_logger = $logger;
        $this->_session = $session;
    }

    /**
     * Builds ENV request
     * From: https://github.com/magento/magento2/blob/2.1.3/app/code/Magento/Payment/Model/Method/Adapter.php
     * The $buildSubject contains:
     * 'payment' => $this->getInfoInstance()
     * 'paymentAction' => $paymentAction
     * 'stateObject' => $stateObject
     *
     * @param array $buildSubject
     * @return array
     */
    public function build(array $buildSubject) {
    	$gateway_api_key = $this->_gatewayConfig->getApiKey();
    	$gateway_merchant_id = $this->_gatewayConfig->getMerchantNumber();
    	$gateway_refund_gateway_url = "https://portalssandbox.oxipay.com.au/api/ExternalRefund/processrefund";
    	return [ 'GATEWAY_MERCHANT_ID'=>$gateway_merchant_id, 'GATEWAY_API_KEY' => $gateway_api_key, 'GATEWAY_REFUND_GATEWAY_URL'=>$gateway_refund_gateway_url ];
    }
}
