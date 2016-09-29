<?php
namespace Oxipay\OxipayPaymentGateway\Controller\Outbound;

use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\App\Action\Context;
use Magento\Sales\Model\OrderFactory;
use Oxipay\OxipayPaymentGateway\Gateway\Request\AuthorizationRequest;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Checkout\Model\Session;

class Redirect extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * Namespace for session.
     * Should be defined for proper working session.
     *
     * @var string
     */
    protected $_sessionNamespace;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_eventManager;

    /**
     * @var \Magento\Framework\App\ActionFlag
     */
    protected $_actionFlag;

    /**
     * @var \Magento\Framework\App\Response\RedirectInterface
     */
    protected $_redirect;

    /**
     * @var \Magento\Framework\App\ViewInterface
     */
    protected $_view;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $_url;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;
    
    protected $rawresult;
    
    protected $orderFactory;
    
    protected $configint;
    
    protected $session;

    /**
     * @param Context $context
     */
    public function __construct(
            Context $context, 
            JsonFactory $jsonresultfactory,
            AuthorizationRequest $authRequest,
            OrderFactory $orderFactory,
            ConfigInterface $config,
            Session $session)
    {
        parent::__construct($context);
        $this->_objectManager = $context->getObjectManager();
        $this->_eventManager = $context->getEventManager();
        $this->_url = $context->getUrl();
        $this->_actionFlag = $context->getActionFlag();
        $this->_redirect = $context->getRedirect();
        $this->_view = $context->getView();
        $this->messageManager = $context->getMessageManager();
        $this->jsonresultfactory = $jsonresultfactory;
        $this->authRequest = $authRequest;
        $this->orderFactory = $orderFactory;
        $this->configint = $config;
        $this->session = $session;
    }

    
    public function execute()
    {
        //get order details
        $order = $this->orderFactory->create()->getCollection()->getLastItem();
        $authobject = $this->authRequest->build(['order' => $order]);
        $url = $authobject['gateway_url'];
        unset($authobject['gateway_url']);
        $json = $this->jsonresultfactory->create();
        $json->setData(['payload' => $authobject, 'url' => $url]);
        
        return $json;
    }
}
?>