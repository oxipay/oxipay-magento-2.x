<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Oxipay\OxipayPaymentGateway\Gateway\Request;

use Magento\Sales\Model\Order;
use Magento\Payment\Gateway\Data\Order\OrderAdapter;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Checkout\Model\Session;
use Psr\Log\LoggerInterface;

class InitializationRequest implements BuilderInterface {

    const LOG_FILE = 'oxipay.log';
    const OXIPAY_DEFAULT_CURRENCY_CODE = 'AUD';
    const OXIPAY_DEFAULT_COUNTRY_CODE = 'AU';

    /**
     * @var ConfigInterface
     */
    private $_config;

    private $_messageManager;

    private $_logger;

    private $_session;

    /**
     * @param ConfigInterface $config
     */
    public function __construct(
        ConfigInterface $config,
        ManagerInterface $messageManager,
        LoggerInterface $logger,
        Session $session
    ) {
        $this->_config = $config;
        $this->_logger = $logger;
        $this->_messageManager = $messageManager;
        $this->_session = $session;
    }

    /**
     * Checks the quote for validity
     * @throws Mage_Api_Exception
     */
    private function validateQuote(OrderAdapter $order) {
        $this->_logger->debug("[InitializationRequest][validateQuote]: ".($order->getGrandTotalAmount()).", ".($order->getBillingAddress()->getCountryId()).", ".($order->getCurrencyCode())."");
        if($order->getGrandTotalAmount() < 20) {
            $this->_session->setOxipayErrorMessage(__("Oxipay doesn't support purchases less than $20."));
            return false;
        }

        if($order->getBillingAddress()->getCountryId() != self::OXIPAY_DEFAULT_COUNTRY_CODE || $order->getCurrencyCode() != self::OXIPAY_DEFAULT_CURRENCY_CODE) {
            $this->_session->setOxipayErrorMessage(__("Oxipay doesn't support purchases from outside Australia."));
            return false;
        }

        if($order->getShippingAddress()->getCountryId() != self::OXIPAY_DEFAULT_COUNTRY_CODE) {
            $this->_session->setOxipayErrorMessage(__("Oxipay doesn't support purchases shipped outside Australia."));
            return false;
        }

        return true;
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

        $payment = $buildSubject['payment'];
        $stateObject = $buildSubject['stateObject'];

        $order = $payment->getOrder();

        if($this->validateQuote($order)) {
            $stateObject->setState(Order::STATE_PENDING_PAYMENT);
            $stateObject->setStatus(Order::STATE_PENDING_PAYMENT);
            $stateObject->setIsNotified(false);
        } else {
            $this->_logger->debug("[InitializationRequest][validateQuote]Canceling order.");
            $stateObject->setState(Order::STATE_CANCELED);
            $stateObject->setStatus(Order::STATE_CANCELED);
            $stateObject->setIsNotified(false);
        }
        
        return [ 'IGNORED' => [ 'IGNORED' ] ];

    }
}
