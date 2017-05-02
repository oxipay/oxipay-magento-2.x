<?php

namespace Oxipay\OxipayPaymentGateway\Controller\Checkout;

use Magento\Sales\Model\Order;
use Oxipay\OxipayPaymentGateway\Helper\Crypto;
use Oxipay\OxipayPaymentGateway\Helper\Data;
use Oxipay\OxipayPaymentGateway\Gateway\Config\Config;
use Oxipay\OxipayPaymentGateway\Controller\Checkout\AbstractAction;

/**
 * @package Oxipay\OxipayPaymentGateway\Controller\Checkout
 */
class Cancel extends AbstractAction {
    
    public function execute() {
        $orderId = $this->getRequest()->get('orderId');
        $order =  $this->getOrderById($orderId);

        if ($order && $order->getId()) {
            $this->getLogger()->debug('Requested order cancellation by customer. OrderId: ' . $order->getIncrementId());
            $this->getCheckoutHelper()->cancelCurrentOrder("Oxipay: ".($order->getId())." was cancelled by the customer.");
            $this->getCheckoutHelper()->restoreQuote(); //restore cart
            $this->getMessageManager()->addWarningMessage(__("You have successfully canceled your Oxipay payment. Please click on 'Update Shopping Cart'."));
        }
        $this->_redirect('checkout/cart');
    }

}
