<?php
/**
 * @author    Frank Clark
 */
namespace Oxipay\OxipayPaymentGateway\Model;

class Checkout
{
    protected $_scopeConfigInterface;
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Product $product,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        \Magento\Quote\Model\QuoteManagement $quoteManagement,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Sales\Model\Service\OrderService $orderService,
        \Magento\Quote\Api\CartRepositoryInterface $cartRepositoryInterface,
        \Magento\Quote\Api\CartManagementInterface $cartManagementInterface,
        \Magento\Checkout\Helper\Data $checkoutData,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Framework\App\Config\ScopeConfigInterface $configInterface
    ) {
        $this->_storeManager = $storeManager;
        $this->_product = $product;
        $this->quoteFactory = $quoteFactory;
        $this->quoteManagement = $quoteManagement;
        $this->customerFactory = $customerFactory;
        $this->customerRepository = $customerRepository;
        $this->orderService = $orderService;
        $this->cartRepositoryInterface = $cartRepositoryInterface;
        $this->cartManagementInterface = $cartManagementInterface;
        $this->quoteRepository = $quoteRepository;
        $this->checkoutData = $checkoutData;
        $this->checkoutSession = $checkoutSession;
        $this->customerSession = $customerSession;
        $this->_scopeConfigInterface = $configInterface;
        $this->_urlBuilder = $context->getUrlBuilder();
    }    
    public function createOrder($quote) {
        $quote->reserveOrderId();
        $this->quoteRepository->save($quote);
        if ($this->getCheckoutMethod($quote) == \Magento\Checkout\Model\Type\Onepage::METHOD_GUEST) {
            $quote->setCustomerId(null)
            ->setCustomerEmail($quote->getBillingAddress()->getEmail())
            ->setCustomerIsGuest(true)
            ->setCustomerGroupId(\Magento\Customer\Model\Group::NOT_LOGGED_IN_ID);
        }
        $quote->getBillingAddress()->setShouldIgnoreValidation(true);
        if (!$quote->getIsVirtual()) {
            $quote->getShippingAddress()->setShouldIgnoreValidation(true);
            if (!$quote->getBillingAddress()->getEmail()
            ) {
                $quote->getBillingAddress()->setSameAsBilling(1);
            }
        }
        $quote->setPaymentMethod('oxipay_gateway'); 
        $quote->save();
        $quote->getPayment()->importData(['method' => 'oxipay_gateway']);
        $quote->collectTotals();
        try {
            $order = $this->quoteManagement->submit($quote);  
            return $order; 
        } catch (Exception $e) {
           return false;
        } 
        
    }
    private function getCheckoutMethod($quote)
    {
        if ($this->customerSession->isLoggedIn()) {
            return \Magento\Checkout\Model\Type\Onepage::METHOD_CUSTOMER;
        }
        if (!$quote->getCheckoutMethod()) {
            if ($this->checkoutData->isAllowedGuestCheckout($quote)) {
                $quote->setCheckoutMethod(\Magento\Checkout\Model\Type\Onepage::METHOD_GUEST);
            } else {
                $quote->setCheckoutMethod(\Magento\Checkout\Model\Type\Onepage::METHOD_REGISTER);
            }
        }
        return $quote->getCheckoutMethod();
    }
    public function getConfigData($key)
    {
        return $this->_scopeConfigInterface->getValue($key, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
}
?>