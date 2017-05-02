<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Oxipay\OxipayPaymentGateway\Model\Ui;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Customer\Model\Session;
use Magento\Backend\Model\Session\Quote;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\View\Asset\Repository;
use Oxipay\OxipayPaymentGateway\Gateway\Config\Config;

/**
 * Class ConfigProvider
 */
final class ConfigProvider implements ConfigProviderInterface
{
    protected $_gatewayConfig;
    protected $_scopeConfigInterface;
    protected $customerSession;
    protected $_urlBuilder;
    protected $request;
    protected $_assetRepo;

    public function __construct(
    Config $gatewayConfig,
    ScopeConfigInterface $configInterface,
    Session $customerSession,
    Quote $sessionQuote,
    Action $action, 
    Context $context,
    Repository $assetRepo
    )
    {
        $this->_gatewayConfig = $gatewayConfig;
        $this->_scopeConfigInterface = $configInterface;
        $this->customerSession = $customerSession;
        $this->sessionQuote = $sessionQuote;
        $this->_urlBuilder = $context->getUrlBuilder();     
        $this->action = $action;
        $this->_assetRepo = $assetRepo;
    }

    public function getConfig()
    {
        $logoFile = $this->_gatewayConfig->getLogo();
        if(strlen($logoFile) > 0){
            $logo = '../pub/media/sales/store/logo/' . $logoFile;
        }
        else{
            $params = ['_secure' => $this->action->getRequest()->isSecure()];
            $logo = $this->_assetRepo->getUrlWithParams('Oxipay_OxipayPaymentGateway::images/oxipay_logo.png', $params);
        }

        //TODO: use Config
        $config = [
            'payment' => [
                Config::CODE => [
                    'title' => $this->_gatewayConfig->getTitle(),
                    'description' => $this->_gatewayConfig->getDescription(),
                    'logo' => $logo,
                    'allowed_countries' => $this->_gatewayConfig->getSpecificCountry(),
                ]
            ]
        ];

        return $config;
    }

}
