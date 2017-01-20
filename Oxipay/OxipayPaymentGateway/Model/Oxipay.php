<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Oxipay\OxipayPaymentGateway\Model;

/**
 * Class Checkmo
 *
 * @method \Magento\Quote\Api\Data\PaymentMethodExtensionInterface getExtensionAttributes()
 */
class Oxipay extends \Magento\Payment\Model\Method\AbstractMethod
{
    const PAYMENT_METHOD_OXIPAY_CODE = 'oxipay_gateway';

    /**
     * Payment method code
     *
     * @var string
     */
    protected $_code = self::PAYMENT_METHOD_OXIPAY_CODE;    
}
