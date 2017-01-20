<?php
namespace Oxipay\OxipayPaymentGateway\Block;
use Magento\Checkout\Model\ConfigProviderInterface;
use Oxipay\OxipayPaymentGateway\Gateway\Http\Client\OxipayClient;
class Success extends \Magento\Framework\View\Element\Template
{
    protected $_urlBuilder;
    protected $response;
	public function __construct(\Magento\Framework\View\Element\Template\Context $context,
     \Magento\Framework\View\Page\Config $pageConfig,
     \Magento\Quote\Model\QuoteFactory $quoteFactory,
     \Magento\Framework\App\Response\Http $response
    )
	{
        $this->pageConfig = $pageConfig;
        $this->pageConfig->getTitle()->set('Oxipay success');
        $this->_urlBuilder = $context->getUrlBuilder();
        $this->response = $response;
        $this->quoteFactory = $quoteFactory;
		parent::__construct($context);
	}

	public function paymentReturn()
	{
	    $om = \Magento\Framework\App\ObjectManager::getInstance();
        $store = $om->get('Magento\Store\Model\StoreManagerInterface');
        $cart = $om->get('\Magento\Checkout\Model\Cart'); 
        $quoteId= $this->getRequest()->getParam('x_reference');
        if($quoteId==$cart->getQuote()->getId()||!$quoteId)
        {
            $this->response->setRedirect($this->_urlBuilder->getUrl("checkout",['error_oixpay' => 1]));
        }
        else   
		return array(
            'shop_name' =>$store->getStore()->getName(),
            'url_contact' => $this->_urlBuilder->getUrl("contact"),
        ); 
	}
    public function isOrderCreated($quoteId)
    {        
        if(($quote = $this->quoteFactory->create()->loadByIdWithoutStore($quoteId))&& $quote->getId() && $quote->getReservedOrderId())
            return true;
        else
            return false;
    }
}