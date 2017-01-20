<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Oxipay\OxipayPaymentGateway\Controller\Checkout;

use Magento\Framework\DataObject;
use Magento\Framework\Exception\PaymentException;
use Magento\Checkout\Model\ConfigProviderInterface;

class SaveOrder extends \Magento\Framework\App\Action\Action
{   
    public function execute()
    { 
        $checkout = $this->_objectManager->get('Oxipay\OxipayPaymentGateway\Model\Checkout');
        $query = array(
            'x_account_id'=>$this->getRequest()->getParam('x_account_id'),
            'x_reference'=>$this->getRequest()->getParam('x_reference'),
            'x_currency' =>$this->getRequest()->getParam('x_currency'),
            'x_test'=>$this->getRequest()->getParam('x_test'),
            'x_amount' => $this->getRequest()->getParam('x_amount'),
            'x_gateway_reference'=>$this->getRequest()->getParam('x_gateway_reference'),
            'x_timestamp' => $this->getRequest()->getParam('x_timestamp'),
            'x_result' =>$this->getRequest()->getParam('x_result'),
       );
       $signature = $this->oxipay_sign($query,  $checkout->getConfigData('payment/oxipay_gateway/api_key'));
       if(($quoteId = $this->getRequest()->getParam('x_reference')) && ($quote = $this->_objectManager->create('Magento\Quote\Model\Quote')->loadByIdWithoutStore($quoteId)))
       {   
            if($signature==$this->getRequest()->getParam('x_signature') && $this->getRequest()->getParam('x_result')=='completed')
            {   
                $order = $checkout->createOrder($quote);
            }           
       }       
       else
          die('Quote does not exist');
       
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
}