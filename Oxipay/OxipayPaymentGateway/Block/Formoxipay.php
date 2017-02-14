<?php
namespace Oxipay\OxipayPaymentGateway\Block;
use Magento\Checkout\Model\ConfigProviderInterface;
use Oxipay\OxipayPaymentGateway\Gateway\Http\Client\OxipayClient;
class Formoxipay extends \Magento\Framework\View\Element\Template
{
    protected $_scopeConfigInterface;
    protected $customerSession;
    protected $_urlBuilder;
	public function __construct(
    \Magento\Framework\App\Config\ScopeConfigInterface $configInterface,
    \Magento\Directory\Api\CountryInformationAcquirerInterface $countryInformation,
    \Magento\Customer\Model\Session $customerSession,
    \Magento\Backend\Model\Session\Quote $sessionQuote,
    \Magento\Framework\View\Element\Template\Context $context)
	{
		parent::__construct($context);
        $this->_scopeConfigInterface = $configInterface;
        $this->countryInformation = $countryInformation;
        $this->customerSession = $customerSession;
        $this->sessionQuote = $sessionQuote;
        $this->_urlBuilder = $context->getUrlBuilder(); 
	}

    public function getPaymentForm()
    {
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $customerSession = $om->get('Magento\Customer\Model\Session');
        $cart = $om->get('\Magento\Checkout\Model\Cart'); 
        $store = $om->get('Magento\Store\Model\StoreManagerInterface');
        $checkout = $om->get('Oxipay\OxipayPaymentGateway\Model\Checkout');        
        $billingAddress = $cart->getQuote()->getBillingAddress();
        $shippingAddress= $cart->getQuote()->getShippingAddress();
        $CustomerID= $customerSession->getCustomer()->getId();
        $firstname=$customerSession->getCustomer()->getFirstname();
        $lastname= $customerSession->getCustomer()->getLastname();
        $email = $customerSession->getCustomer()->getEmail();
        $countryid = $this->_scopeConfigInterface->getValue('general/country/default');
//        $country = $this->countryInformation->getCountryInfo($countryid);
//        $countryname = $country->getFullNameLocale();
        $query =Array
        (
            'x_reference' => $cart->getQuote()->getId(),
            'x_account_id' => $checkout->getConfigData('payment/oxipay_gateway/merchant_number'),
            'x_amount' => (float)$cart->getQuote()->getGrandTotal(),
            'x_currency' => $store->getStore()->getCurrentCurrencyCode(),
            'x_url_callback' =>$this->_urlBuilder->getUrl("oxipay/checkout/saveorder",['quoteId' => $cart->getQuote()->getId()]),
            'x_url_complete' => $this->_urlBuilder->getUrl("oxipay/checkout/success",['quoteId' => $cart->getQuote()->getId()]),
            'x_url_cancel' => $this->_urlBuilder->getUrl("checkout"),
            'x_test' => $this->_scopeConfigInterface->getValue('payment/oxipay_gateway/test_mode')?true:false,
            'x_shop_country' => $countryid,
            'x_shop_name' =>$store->getStore()->getName(), 
            'x_customer_first_name' => $firstname,
            'x_customer_last_name' => $lastname,
            'x_customer_email' => $email,
            'x_customer_phone' => $billingAddress->getTelephone(),
            'x_customer_billing_country' => $billingAddress->getCountryId(),
            'x_customer_billing_city' => $billingAddress->getCity(),
            'x_customer_billing_address1' => $billingAddress->getStreetLine(1),
            'x_customer_billing_address2' => $billingAddress->getStreetLine(2),
            'x_customer_billing_state' => 'ACT',
            'x_customer_billing_zip' => $billingAddress->getPostcode(),
            'x_customer_shipping_country' => $shippingAddress->getCountryId(),
            'x_customer_shipping_city' => $shippingAddress->getCity(),
            'x_customer_shipping_address1' => $shippingAddress->getStreetLine(1),
            'x_customer_shipping_address2'=> $shippingAddress->getStreetLine(2),
            'x_customer_shipping_state' => '',
            'x_customer_shipping_zip' => $shippingAddress->getPostcode(),
            'gateway_url' => $this->getUrlGateway(),
        );
        $signature = $this->oxipay_sign($query, $this->_scopeConfigInterface->getValue('payment/oxipay_gateway/api_key'));
		$query['x_signature'] = $signature; 
        return $this->generate_processing_form($query);
    }
    public function getConfigDataETS($key)
    {
        return $key;
        return Mage::getStoreConfig('payment/oxipay_gateway/' . $key);
    }
    function generate_processing_form($query) {
        $url = $query["gateway_url"];
    
        $html ="<form style='display:none;' id='oxipayredirectform' method='post' action='$url'>";
    
        foreach ($query as $item => $value) {
            if (substr($item, 0, 2) === "x_") {
                $html .= "<input id='$item' name='$item' value='$value' type='hidden'/>";
            }
        }
    
        $html .= "</form>";
        return $html;
    }
    public function getUrlGateway()
    {
        if(!(int)$this->_scopeConfigInterface->getValue('payment/oxipay_gateway/test_mode'))
            return $this->_scopeConfigInterface->getValue('payment/oxipay_gateway/gateway_url');
        else
            return $this->_scopeConfigInterface->getValue('payment/oxipay_gateway/gateway_redirect_url');
    }
    public function oxipay_sign($query, $api_key )
    {
        $clear_text = '';
        ksort($query);
        foreach ($query as $key => $value) {
            if (substr($key, 0, 2) === "x_") {
                $clear_text .= $key . $value;
            }
        }
        $hash = hash_hmac( "sha256", $clear_text, $api_key);
        return str_replace('-', '', $hash);
    }
    public function displayErrors()
	{
	    $om = \Magento\Framework\App\ObjectManager::getInstance();
        $store = $om->get('Magento\Store\Model\StoreManagerInterface');
        $cart = $om->get('\Magento\Checkout\Model\Cart'); 
        $error_oxipay= $this->getRequest()->getParam('error_oixpay');
        if($error_oxipay)
            return true;
        else
            return false;
        
	}
}