<?php
namespace Oxipay\OxipayPaymentGateway\Controller\Outbound;

use Magento\Framework\Controller\Result\Raw;
use Magento\Framework\App\Action\Context;
use Magento\Sales\Model\OrderFactory;
use Oxipay\OxipayPaymentGateway\Gateway\Request\AuthorizationRequest;
use Magento\Payment\Gateway\ConfigInterface;

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

    /**
     * @param Context $context
     */
    public function __construct(
            Context $context, 
            Raw $rawResultFactory,
            AuthorizationRequest $authRequest,
            OrderFactory $orderFactory,
            ConfigInterface $config)
    {
        parent::__construct($context);
        $this->_objectManager = $context->getObjectManager();
        $this->_eventManager = $context->getEventManager();
        $this->_url = $context->getUrl();
        $this->_actionFlag = $context->getActionFlag();
        $this->_redirect = $context->getRedirect();
        $this->_view = $context->getView();
        $this->messageManager = $context->getMessageManager();
        $this->rawresult = $rawResultFactory;
        $this->authRequest = $authRequest;
        $this->orderFactory = $orderFactory;
        $this->configint = $config;
    }

    
    public function execute()
    {
        //get order details
        $orderid = $this->getRequest()->getParam('orderid');
        $order = $this->orderFactory->create()->loadByIncrementId($orderid);
        
        $payment = $order->getPayment();
        $transactionId = $payment->getTransactionId();
        
        $gatewayurl = $this->configint->getValue(
                'gateway_redirect_url',
                $order->getStoreId());
        
        $query = ['transactionId' => $transactionId];
        
        $payload = $gatewayurl . '?' . http_build_query($query);
        
        $result = $this->rawresult->create();
        $result->setContents($payload);
        
        return $result;
    }
}
?>