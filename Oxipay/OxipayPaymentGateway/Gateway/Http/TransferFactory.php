<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Oxipay\OxipayPaymentGateway\Gateway\Http;

use Magento\Payment\Gateway\Http\TransferBuilder;
use Magento\Payment\Gateway\Http\TransferFactoryInterface;
use Magento\Payment\Gateway\Http\TransferInterface;
use Oxipay\OxipayPaymentGateway\Gateway\Http\OxipayCrypto;

class TransferFactory implements TransferFactoryInterface
{
    /**
     * @var TransferBuilder
     */
    private $transferBuilder;
	
	/**
	 * @var $crypto
	 */
	private $crypto;

    /**
     * @param TransferBuilder $transferBuilder
	 * @param OxipayCrypto crypto
     */
    public function __construct(
        TransferBuilder $transferBuilder, 
		OxipayCrypto $crypto
    ) {
        $this->transferBuilder = $transferBuilder;
		$this->crypto = $crypto;
    }

    /**
     * Builds gateway transfer object
     *
     * @param array $request
     * @return TransferInterface
     */
    public function create(array $request)
    {
		$signed = $this->crypto->sign($request);
		$jsontext = json_encode($signed);
		
        return $this->transferBuilder
            ->setBody($jsontext)
            ->setMethod('POST')
			->setHeaders(['Content-Type: application/json'])
            ->build();
    }
}
