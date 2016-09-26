<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Oxipay\OxipayPaymentGateway\Model\Ui;

use Magento\Checkout\Model\ConfigProviderInterface;
use Oxipay\OxipayPaymentGateway\Gateway\Http\Client\OxipayClient;

/**
 * Class ConfigProvider
 */
final class ConfigProvider implements ConfigProviderInterface
{
    const CODE = 'oxipay_gateway';

    /**
     * Retrieve assoc array of checkout configuration
     *
     * @return array
     */
    public function getConfig()
    {
        $config = [
            'payment' => [
                self::CODE => [
                    'transactionResults' => [
                        OxipayClient::SUCCESS => __('Success'),
                        OxipayClient::FAILURE => __('Failure')
                    ],
					'oxipayReturnUrl' => 'oxipay/payment/response', /* (Controller Payment/Response) */
					'oxipayAction' => 'http://google.com' /* (TEST ONLY) */
                ]
            ]
        ];
		
        return $config;
    }
}
