<?php

namespace Oxipay\OxipayPaymentGateway\Controller\Checkout;

use Magento\Framework\DataObject;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Controller\ResultFactory;

class RequestDetails extends \Magento\Framework\App\Action\Action
{
    protected $_scopeConfigInterface;
    protected $_checkoutSession;
    protected $_urlBuilder;
    protected $_oxipayCrypto;
    
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $configInterface,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Oxipay\OxipayPaymentGateway\Gateway\Http\OxipayCrypto $oxipayCrypto)
	{
	    parent::__construct($context);
	    
	    $this->_scopeConfigInterface = $configInterface;
        $this->_checkoutSession = $checkoutSession;
        $this->_urlBuilder = $urlBuilder;
        $this->_oxipayCrypto = $oxipayCrypto;
	}
    
    public function execute()
    {
        $request = $this->getRequest();
        $response = $this->resultFactory->create(ResultFactory::TYPE_JSON);

        $quote = $this->_checkoutSession->getQuote();
        $store = $quote->getStore();
        
        $storeCountry = $this->_scopeConfigInterface->getValue('general/store_information/country_id', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        //default to Australia if not set
        if(strlen($storeCountry) == 0){
            $storeCountry = 'AU';
        }
        
        //data to be sent to Oxipay
        $data = [
            'x_reference' => $quote->getId(),
            'x_account_id' => $this->_scopeConfigInterface->getValue('payment/oxipay_gateway/merchant_number', \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE),
            'x_amount' => $request->getParam('amount'),
            'x_currency' => $request->getParam('currency'),
            'x_url_callback' => $this->_urlBuilder->getUrl("oxipay/checkout/saveorder", ['quoteId' => $quote->getId()]),
            'x_url_complete' => $this->_urlBuilder->getUrl("oxipay/checkout/success", ['quoteId' => $quote->getId()]),
            'x_url_cancel' => $this->_urlBuilder->getUrl("checkout"),
            'x_test' => 'false',
            'x_shop_country' => $storeCountry,
            'x_shop_name' => $this->_scopeConfigInterface->getValue('general/store_information/name', \Magento\Store\Model\ScopeInterface::SCOPE_STORE), 
            'x_customer_first_name' => $request->getParam('customer_first_name'),
            'x_customer_last_name' => $request->getParam('customer_last_name'),
            'x_customer_email' => $request->getParam('customer_email'),
            'x_customer_phone' => $request->getParam('customer_phone'),
            'x_customer_billing_country' => $request->getParam('customer_billing_country'),
            'x_customer_billing_city' => $request->getParam('customer_billing_city'),
            'x_customer_billing_address1' => $request->getParam('customer_billing_address1'),
            'x_customer_billing_address2' => $request->getParam('customer_billing_address2'),
            'x_customer_billing_state' => $request->getParam('customer_billing_state'),
            'x_customer_billing_zip' => $request->getParam('customer_billing_zip'),
            'x_customer_shipping_country' => $request->getParam('customer_shipping_country'),
            'x_customer_shipping_city' => $request->getParam('customer_shipping_city'),
            'x_customer_shipping_address1' => $request->getParam('customer_shipping_address1'),
            'x_customer_shipping_address2'=> $request->getParam('customer_shipping_address2'),
            'x_customer_shipping_state' => $request->getParam('customer_shipping_state'),
            'x_customer_shipping_zip' => $request->getParam('customer_shipping_zip'),
        ];
        
        //sign the data
        $data = $this->_oxipayCrypto->sign($data, $this->_scopeConfigInterface->getValue('payment/oxipay_gateway/api_key', \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE));
        
        //return it to the front end
        $response->setData($data);

        return $response;
    }
}