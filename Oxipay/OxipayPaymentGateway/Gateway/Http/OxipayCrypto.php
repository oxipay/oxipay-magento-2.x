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
                
        	//order by key_name ascending
        	$clear_text = '';
        	ksort($authRequest);
        	foreach ($authRequest as $key => $value) {
	        	//step 2: concat all keys in form "{key}{value}"
        		$clear_text .= $key . $value;
        	}

                //crypt
                $secret = $apikey . '&';
                $hash = base64_encode( hash_hmac( "sha256", $clear_text, $secret, true ));
		$hash = str_replace('+', '', $hash);
		/* Append signature onto the end of our array */
		$authRequest["x_signature"] = $hash;
		
		return $authRequest;
	}
}
