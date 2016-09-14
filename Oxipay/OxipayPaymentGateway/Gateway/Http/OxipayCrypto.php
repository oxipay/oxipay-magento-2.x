<?php
/**
 * Copyright © 2016 Oxipay
 * See COPYING.txt for license details.
 */
namespace Oxipay\OxipayPaymentGateway\Gateway\Http;

class OxipayCrypto
{
    /**
     * Signs authRequest.
     *
     * @param array $authRequest
     * @return array
     */
    public function sign(array $authRequest, $apikey)
	{
		/* Check if we have the required crypt algorithm (SHA256) */
		if (!in_array('sha256', hash_algos()))
		{
	        throw new Exception("Hash algorithm sha256 not available. Please check your installation.");
		}
				
		/* Does the source array already have a signature component? If so, strip it. */
		if (array_key_exists('x_signature', $authRequest))
		{
			unset($authRequest['x_signature']);
		}
			
		/* Prepare array (sort by key, json export) */
		ksort($authRequest);
		$json = json_encode($authRequest);
		
		/* sign */
		$signature = hash_hmac("sha256", $json, $apikey);
		
		/* Append signature onto the end of our array */
		$authRequest["x_signature"] = $signature;
		
		return $authRequest;
	}
}
