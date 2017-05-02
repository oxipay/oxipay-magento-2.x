<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Oxipay\OxipayPaymentGateway\Gateway\Http\Client;

use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Http\TransferInterface;
use Magento\Payment\Model\Method\Logger;

class DummyClient implements ClientInterface {
    /**
     * This is  the place where requests to the Payment Gateway API are placed.
     * As we are a Redirect-based gateway and only used the "initialize" method, 
     * we don't place API invocations or requests to the Payment Gateway here.
     * TODO: check how to get rid of this, as the following error is raised
     * when not setting a transferFactory:
     * [Payment/Model/Method/Adapter][executeCommand]ERROR: Cannot instantiate 
     * interface Magento\Payment\Gateway\Http\ClientInterface.
     * 
     * Returns result as ENV array
     *
     * @param TransferInterface $transferObject
     * @return array
     */
    public function placeRequest(TransferInterface $transferObject)
    {
        $response = [ 'IGNORED' => [ 'IGNORED' ] ];
        return $response;
    }

}
