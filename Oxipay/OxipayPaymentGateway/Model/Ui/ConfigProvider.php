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
    protected $_scopeConfigInterface;
    protected $customerSession;
    protected $_urlBuilder;
    protected $request;
    protected $_assetRepo;

    public function __construct(
    \Magento\Framework\App\Config\ScopeConfigInterface $configInterface,
    \Magento\Customer\Model\Session $customerSession,
    \Magento\Backend\Model\Session\Quote $sessionQuote,
    \Magento\Framework\App\Action\Action $action, 
    \Magento\Framework\App\Helper\Context $context,
    \Magento\Framework\View\Asset\Repository $assetRepo
    )
    {
        $this->_scopeConfigInterface = $configInterface;
        $this->customerSession = $customerSession;
        $this->sessionQuote = $sessionQuote;
        $this->_urlBuilder = $context->getUrlBuilder();     
        $this->action = $action;
        $this->_assetRepo = $assetRepo;
    }
    public function getConfig()
    {
        $config = [
            'payment' => [
                self::CODE => [
                    'transactionResults' => [
                        OxipayClient::SUCCESS => __('Success'),
                        OxipayClient::FAILURE => __('Failure')
                    ],
                    'errors' => $this->action->getRequest()->getParams('error_oxipay')?'The Payment provider rejected the transaction. Please try again.':'',
                    'title' => $this->_scopeConfigInterface->getValue('payment/oxipay_gateway/title', \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
                    'description' => $this->_scopeConfigInterface->getValue('payment/oxipay_gateway/description', \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
                    'gateway_url' => $this->_scopeConfigInterface->getValue('payment/oxipay_gateway/gateway_url', \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE),
                    'allowed_countries' => $this->_scopeConfigInterface->getValue('payment/oxipay_gateway/specificcountry', \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE),
                ]
            ]
        ];

        $logoFile = $this->_scopeConfigInterface->getValue('payment/oxipay_gateway/gateway_logo', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if(strlen($logoFile) > 0){
            $logo = '../pub/media/sales/store/logo/' . $logoFile;
        }
        else{
            $params = ['_secure' => $this->action->getRequest()->isSecure()];
            $logo = $this->_assetRepo->getUrlWithParams('Oxipay_OxipayPaymentGateway::images/oxipay_logo.png', $params);
        }

        $config['payment'][self::CODE]['logo'] = $logo;

        return $config;
    }
}
