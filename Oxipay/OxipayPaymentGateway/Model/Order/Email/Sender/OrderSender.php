<?php

namespace Oxipay\OxipayPaymentGateway\Model\Order\Email\Sender;

use Magento\Sales\Model\Order;

class OrderSender extends \Magento\Sales\Model\Order\Email\Sender\OrderSender {

    public function send(Order $order, $forceSyncMode = false)
    {
        $payment = $order->getPayment()->getMethodInstance()->getCode();

        if($payment === 'oxipay_gateway' && $order->getState() === 'pending_payment'){
            return false;
        }

        $order->setSendEmail(true);

        if (!$this->globalConfig->getValue('sales_email/general/async_sending') || $forceSyncMode) {
            if ($this->checkAndSend($order)) {
                $order->setEmailSent(true);
                $this->orderResource->saveAttribute($order, ['send_email', 'email_sent']);
                return true;
            }
        }

        $this->orderResource->saveAttribute($order, 'send_email');

        return false;
    }
}