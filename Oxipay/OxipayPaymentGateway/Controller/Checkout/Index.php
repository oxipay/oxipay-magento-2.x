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
class Index extends AbstractAction {

    private function getPayload($order) {
        if($order == null) {
            $this->getLogger()->debug('Unable to get order from last lodged order id. Possibly related to a failed database call');
            $this->_redirect('checkout/onepage/error', array('_secure'=> false));
        }

        $shippingAddress = $order->getShippingAddress();
        $billingAddress = $order->getBillingAddress();

        $billingAddressParts = explode(PHP_EOL, $billingAddress->getData('street'));
        $shippingAddressParts = explode(PHP_EOL, $shippingAddress->getData('street'));

        $orderId = $order->getRealOrderId();
        $data = array(
            'x_currency' => str_replace(PHP_EOL, ' ', $order->getOrderCurrencyCode()),
            'x_url_callback' => str_replace(PHP_EOL, ' ', $this->getDataHelper()->getCompleteUrl()),
            'x_url_complete' => str_replace(PHP_EOL, ' ', $this->getDataHelper()->getCompleteUrl()),
            'x_url_cancel' => str_replace(PHP_EOL, ' ', $this->getDataHelper()->getCancelledUrl($orderId)),
            'x_shop_name' => str_replace(PHP_EOL, ' ', $this->getDataHelper()->getStoreCode()),
            'x_account_id' => str_replace(PHP_EOL, ' ', $this->getGatewayConfig()->getMerchantNumber()),
            'x_reference' => str_replace(PHP_EOL, ' ', $orderId),
            'x_invoice' => str_replace(PHP_EOL, ' ', $orderId),
            'x_amount' => str_replace(PHP_EOL, ' ', $order->getTotalDue()),
            'x_customer_first_name' => str_replace(PHP_EOL, ' ', $order->getCustomerFirstname()),
            'x_customer_last_name' => str_replace(PHP_EOL, ' ', $order->getCustomerLastname()),
            'x_customer_email' => str_replace(PHP_EOL, ' ', $order->getData('customer_email')),
            'x_customer_phone' => str_replace(PHP_EOL, ' ', $billingAddress->getData('telephone')),
            'x_customer_billing_address1' => $billingAddressParts[0],
            'x_customer_billing_address2' => count($billingAddressParts) > 1 ? $billingAddressParts[1] : '',
            'x_customer_billing_city' => str_replace(PHP_EOL, ' ', $billingAddress->getData('city')),
            'x_customer_billing_state' => str_replace(PHP_EOL, ' ', $billingAddress->getData('region')),
            'x_customer_billing_zip' => str_replace(PHP_EOL, ' ', $billingAddress->getData('postcode')),
            'x_customer_shipping_address1' => $shippingAddressParts[0],
            'x_customer_shipping_address2' => count($shippingAddressParts) > 1 ? $shippingAddressParts[1] : '',
            'x_customer_shipping_city' => str_replace(PHP_EOL, ' ', $shippingAddress->getData('city')),
            'x_customer_shipping_state' => str_replace(PHP_EOL, ' ', $shippingAddress->getData('region')),
            'x_customer_shipping_zip' => str_replace(PHP_EOL, ' ', $shippingAddress->getData('postcode')),
            'x_test' => 'false'
        );
        $apiKey = $this->getGatewayConfig()->getApiKey();
        $signature = $this->getCryptoHelper()->generateSignature($data, $apiKey);
        $data['x_signature'] = $signature;

        return $data;
    }

    private function postToCheckout($checkoutUrl, $payload)
    {
        echo
        "<html>
            <body>
            <form id='form' action='$checkoutUrl' method='post'>";
        foreach ($payload as $key => $value) {
            echo "<input type='hidden' id='$key' name='$key' value='".htmlspecialchars($value, ENT_QUOTES)."'/>";
        }
        echo
            '</form>
            </body>';
        echo
            '<script>
                var form = document.getElementById("form");
                form.submit();
            </script>
        </html>';
    }

    /**
     * 
     *
     * @return void
     */
    public function execute() {
        try {
            $order = $this->getOrder();
            if ($order->getState() === Order::STATE_PENDING_PAYMENT) {
                $payload = $this->getPayload($order);
                $this->postToCheckout($this->getGatewayConfig()->getGatewayUrl(), $payload);
            } else if ($order->getState() === Order::STATE_CANCELED) {
                $errorMessage = $this->getCheckoutSession()->getOxipayErrorMessage(); //set in InitializationRequest
                if ($errorMessage) {
                    $this->getMessageManager()->addWarningMessage($errorMessage);
                    $errorMessage = $this->getCheckoutSession()->unsOxipayErrorMessage();
                }
                $this->getCheckoutHelper()->restoreQuote(); //restore cart
                $this->_redirect('checkout/cart');
            } else {
                $this->getLogger()->debug('Order in unrecognized state: ' . $order->getState());
                $this->_redirect('checkout/cart');
            }
        } catch (Exception $ex) {
            $this->getLogger()->debug('An exception was encountered in oxipay/checkout/index: ' . $ex->getMessage());
            $this->getLogger()->debug($ex->getTraceAsString());
            $this->getMessageManager()->addErrorMessage(__('Unable to start Oxipay Checkout.'));
        }
    }

}
