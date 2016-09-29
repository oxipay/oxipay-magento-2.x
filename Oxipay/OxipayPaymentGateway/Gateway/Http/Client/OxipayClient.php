<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Oxipay\OxipayPaymentGateway\Gateway\Http\Client;

use Magento\Framework\HTTP\ZendClientFactory;
use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Http\TransferInterface;
use Magento\Payment\Model\Method\Logger;

class OxipayClient implements ClientInterface
{
    const SUCCESS = 1;
    const FAILURE = 0;

    /**
     * @var array
     */
    private $results = [
        self::SUCCESS,
        self::FAILURE
    ];

	private $clientFactory;
	
    /**
     * @var Logger
     */
    private $logger;

    /**
     * @param Logger $logger
     */
    public function __construct(
        Logger $logger,
        ZendClientFactory $clientFactory
    ) {
        $this->logger = $logger;
        $this->clientFactory = $clientFactory;
    }

    /**
     * Places request to gateway. Returns result as ENV array
     *
     * @param TransferInterface $transferObject
     * @return array
     */
    public function placeRequest(TransferInterface $transferObject)
    {
        $bodyarray = $transferObject->getBody();
        $headers = $transferObject->getHeaders();
		$url = $bodyarray['gateway_url']; 
		unset($bodyarray['gateway_url']);
        $method = $transferObject->getMethod();
        
        $client = $this->clientFactory->create();
        $client->setUri($url);
        $client->setMethod($method);
        $client->setHeaders($headers);
        $client->setParameterPost($bodyarray);
		
        /* Body contains the JSON request string */

        $response = $client->request($method);

        $responsebody = $response->getBody();
        
        $this->logger->debug(
            [
                'request' => $transferObject->getBody(),
                'response' => $responsebody
            ]
        );

        return ['TransactionId' => $responsebody];
    }
}
