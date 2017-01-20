<?php
namespace Oxipay\OxipayPaymentGateway\Block;
use Magento\Checkout\Model\ConfigProviderInterface;
use Oxipay\OxipayPaymentGateway\Gateway\Http\Client\OxipayClient;
class Errors extends \Magento\Framework\View\Element\Template
{
    protected $_urlBuilder;
    protected $response;
	public function __construct(\Magento\Framework\View\Element\Template\Context $context,
     \Magento\Framework\View\Page\Config $pageConfig,
     \Magento\Framework\App\Response\Http $response
    )
	{
        $this->pageConfig = $pageConfig;
        $this->pageConfig->getTitle()->set('Oxipay errors');
        $this->_urlBuilder = $context->getUrlBuilder();
        $this->response = $response;
		parent::__construct($context);
	}

	public function displayErrors()
	{
	    $om = \Magento\Framework\App\ObjectManager::getInstance();
        $store = $om->get('Magento\Store\Model\StoreManagerInterface');
        $cart = $om->get('\Magento\Checkout\Model\Cart'); 
        $error_oxipay= $this->getRequest()->getParam('error_oxipay');
        return '<h1>vu quang chung</h1>';
        if($quoteId==$cart->getQuote()->getId()||!$quoteId)
        {
            $this->response->setRedirect($this->_urlBuilder->getUrl("checkout"));
        }
        else   
		return array(
            'shop_name' =>$store->getStore()->getName(),
            'url_contact' => $this->_urlBuilder->getUrl("contact"),
        );
	}
}