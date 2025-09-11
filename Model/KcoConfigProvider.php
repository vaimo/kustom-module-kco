<?php
/**
 * Copyright © Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
declare(strict_types=1);

namespace Klarna\Kco\Model;

use Klarna\Base\Exception;
use Klarna\Kco\Model\Checkout\Url;
use Klarna\AdminSettings\Model\Configurations\Kco\ShippingOptions;
use Klarna\Kco\Model\Payment\Kco;
use Klarna\Kco\Model\Checkout\Configuration\SettingsProvider;
use Klarna\Kss\Model\KssConfigProvider;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Checkout\Model\Session;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\UrlInterface;
use Magento\Store\Api\Data\StoreInterface;

/**
 * Providing configuration values which are used on our js workflow like the kco events
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 * @internal
 */
class KcoConfigProvider implements ConfigProviderInterface
{
    /**
     * @var UrlInterface
     */
    private UrlInterface $urlBuilder;
    /**
     * @var Url
     */
    private Url $url;
    /**
     * @var SettingsProvider
     */
    private SettingsProvider $config;
    /**
     * @var Session
     */
    private Session $session;
    /**
     * @var KssConfigProvider
     */
    private KssConfigProvider $kssConfigProvider;
    /**
     * @var ShippingOptions
     */
    private ShippingOptions $shippingOptions;

    /**
     * @param Url                  $url
     * @param SettingsProvider     $config
     * @param UrlInterface         $urlBuilder
     * @param Session              $session
     * @param KssConfigProvider    $kssConfigProvider
     * @param ShippingOptions      $shippingOptions
     * @codeCoverageIgnore
     */
    public function __construct(
        Url $url,
        SettingsProvider $config,
        UrlInterface $urlBuilder,
        Session $session,
        KssConfigProvider $kssConfigProvider,
        ShippingOptions $shippingOptions
    ) {
        $this->url               = $url;
        $this->config            = $config;
        $this->urlBuilder        = $urlBuilder;
        $this->session           = $session;
        $this->kssConfigProvider = $kssConfigProvider;
        $this->shippingOptions   = $shippingOptions;
    }

    /**
     * Get JS config
     *
     * @return array
     * @throws Exception
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function getConfig(): array
    {
        $store = $this->session->getQuote()->getStore();
        return [
            'klarna' => [
                'failureUrl'                => $this->url->getFailureUrl($store),
                'updateKlarnaOrderUrl'      => $this->getUrl(Url::CHECKOUT_ACTION_PREFIX . '/updateKlarnaOrder'),
                'getAddressesUrl'           => $this->getUrl(Url::API_ACTION_PREFIX . '/getAddresses'),
                'frontEndShipping'          => $this->shippingOptions->isShippingInIframe($store),
                'paymentMethod'             => Kco::METHOD_CODE,
                'acceptTermsUrl'            => $this->getAcceptTermsUrl(),
                'userTermsUrl'              => $this->url->getUserTermsUrl($this->session->getQuote()->getStore()),
                'prefillNoticeEnabled'      => $this->isNoticeEnabled($store),
                'methodUrl'                 => $this->getUrl(Url::CHECKOUT_ACTION_PREFIX . '/saveShippingMethod'),
                'isKssEnabled'              => $this->kssConfigProvider->isKssEnabled($store),
                'updateKssStatusUrl'        => $this->getUrl(Url::API_ACTION_PREFIX . '/updateKssStatus'),
                'updateKssDiscountOrderUrl' => $this->getUrl(Url::API_ACTION_PREFIX . '/updateKssDiscountOrder'),
            ]
        ];
    }

    /**
     * Generate url by route and parameters
     *
     * @param string $route
     * @return string
     */
    private function getUrl(string $route): string
    {
        return $this->urlBuilder->getUrl($route, []);
    }

    /**
     * Get url to continue to checkout
     *
     * @return string
     */
    private function getAcceptTermsUrl(): string
    {
        $urlParams = [
            '_nosid'         => true,
            '_forced_secure' => true
        ];

        return $this->urlBuilder->getUrl('*/*/*/terms/accept', $urlParams);
    }

    /**
     * Determine if notice should display.
     *
     * This method stays public so that a merchant can add a plugin for controlling the flag for customized solutions.
     *
     * @param StoreInterface $store
     * @return bool
     */
    public function isNoticeEnabled(StoreInterface $store): bool
    {
        return $this->config->isPrefillNoticeEnabled($store);
    }
}
