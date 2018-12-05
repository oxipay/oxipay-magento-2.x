<?php

namespace Oxipay\OxipayPaymentGateway\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Oxipay\OxipayPaymentGateway\Gateway\Config\Config;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Payment\Helper\Data as PaymentData;

/**
 * Class Oxipay_Oxipayments_Helper_Data
 *
 * Provides helper methods for retrieving data for the oxipay plugin
 */
class Data extends AbstractHelper {

    /**
     * @var \Oxipay\OxipayPaymentGateway\Gateway\Config\Config
     */
    protected $_gatewayConfig;
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;
    /**
     * @var \Magento\Payment\Helper\Data
     */
    protected $_paymentData;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;
    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    protected $_localeResolver;

    /**
     * @param \Oxipay\OxipayPaymentGateway\Gateway\Config\Config;
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Payment\Helper\Data $paymentData
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager,
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     */
    public function __construct(
        Config $gatewayConfig,
        ObjectManagerInterface $objectManager,
        Context $context,
        PaymentData $paymentData,
        StoreManagerInterface $storeManager,
        ResolverInterface $localeResolver
    ) {
        $this->_gatewayConfig = $gatewayConfig;
        $this->_objectManager = $objectManager;
        $this->_paymentData   = $paymentData;
        $this->_storeManager  = $storeManager;
        $this->_localeResolver = $localeResolver;

        $this->_scopeConfig   = $context->getScopeConfig();

        parent::__construct($context);
    }

    /**
     * Creates an Instance of the Helper
     * @param  \Magento\Framework\ObjectManagerInterface $objectManager
     * @return \Oxipay\OxipayPaymentGateway\Helper\Data
     */
    public static function getInstance($objectManager)
    {
        return $objectManager->create(
            get_class()
        );
    }

    protected function getGatewayConfig() {
        return $this->_gatewayConfig;
    }

    /**
     * Get an Instance of the Magento Object Manager
     * @return \Magento\Framework\ObjectManagerInterface
     */
    protected function getObjectManager()
    {
        return $this->_objectManager;
    }

    /**
     * Get an Instance of the Magento Store Manager
     * @return \Magento\Store\Model\StoreManagerInterface
     */
    protected function getStoreManager()
    {
        return $this->_storeManager;
    }

    /**
     * Get an Instance of the Magento UrlBuilder
     * @return \Magento\Framework\UrlInterface
     */
    public function getUrlBuilder()
    {
        return $this->_urlBuilder;
    }

    /**
     * Get an Instance of the Magento Scope Config
     * @return \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected function getScopeConfig()
    {
        return $this->_scopeConfig;
    }

    /**
     * Get an Instance of the Magento Core Locale Object
     * @return \Magento\Framework\Locale\ResolverInterface
     */
    protected function getLocaleResolver()
    {
        return $this->_localeResolver;
    }

    /**
     * get the URL of the configured oxipay gateway checkout
     * @return string
     */
    public function getCheckoutUrl() {
        return $this->getGatewayConfig()->getGatewayUrl();
    }

    /**
     * @return string
     */
    public function getCompleteUrl() {
        return $this->getStoreManager()->getStore()->getBaseUrl() . 'oxipay/checkout/success';
    }

    /**
     * @param string
     * @return string
     */
    public function getCancelledUrl($orderId) {
        return $this->getStoreManager()->getStore()->getBaseUrl() . "oxipay/checkout/cancel?orderId=$orderId";
    }

    /**
     * Get Store code
     *
     * @return string
     */
    public function getStoreCode()
    {
        return $this->getStoreManager()->getStore()->getCode();
    }
}
