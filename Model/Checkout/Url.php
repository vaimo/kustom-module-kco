<?php
/**
 * Copyright © Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
declare(strict_types=1);

namespace Klarna\Kco\Model\Checkout;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Config\Placeholder;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\Store;
use Klarna\Base\Model\Api\MagentoToKlarnaLocaleMapper;
use Klarna\Kco\Model\Checkout\Configuration\SettingsProvider;

/**
 * Providing methods and logic for the url topic
 *
 * @internal
 */
class Url
{
    public const CHECKOUT_ACTION_PREFIX = 'checkout/klarna';
    public const API_ACTION_PREFIX = 'kco/api';

    /** @var ScopeConfigInterface $scopeConfig */
    private $scopeConfig;

    /** @var Placeholder $placeholder */
    private $placeholder;

    /** @var UrlInterface $urlBuilder */
    private $urlBuilder;

    /** @var MagentoToKlarnaLocaleMapper $localeResolver */
    private $localeResolver;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param Placeholder $placeholder
     * @param UrlInterface $urlBuilder
     * @param MagentoToKlarnaLocaleMapper $localeResolver
     * @codeCoverageIgnore
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Placeholder $placeholder,
        UrlInterface $urlBuilder,
        MagentoToKlarnaLocaleMapper $localeResolver
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->placeholder = $placeholder;
        $this->urlBuilder = $urlBuilder;
        $this->localeResolver = $localeResolver;
    }

    /**
     * Get url using url template variables
     *
     * @param string         $url
     * @param StoreInterface $store
     * @return string
     */
    public function getProcessedUrl(string $url, StoreInterface $store): string
    {
        $data = [
            'web' => [
                'unsecure' => [
                    'base_url' => $this->scopeConfig->getValue(
                        'web/unsecure/base_url',
                        ScopeInterface::SCOPE_STORE,
                        $store
                    )
                ],
                'secure'   => [
                    'base_url' => $this->scopeConfig->getValue(
                        'web/secure/base_url',
                        ScopeInterface::SCOPE_STORE,
                        $store
                    )
                ]
            ]
        ];

        $data['url'] = $this->addUrlSuffix($store, $url);
        $value = $this->placeholder->process($data)['url'];
        return $this->urlBuilder->escape($value);
    }

    /**
     * Adding the url suffix to the url
     *
     * @param StoreInterface $store
     * @param string $url
     * @return string
     */
    private function addUrlSuffix(StoreInterface $store, string $url): string
    {
        if ($this->isStoreCodeInUrlUsed($store)) {
            $code = $store->getCode();
            $url = str_replace('{{secure_base_url}}', '{{secure_base_url}}' . $code . '/', $url);
            $url = str_replace('{{unsecure_base_url}}', '{{unsecure_base_url}}' . $code . '/', $url);
        }

        return $url;
    }

    /**
     * Returns true if the store code is added to the url
     *
     * @param StoreInterface $store
     * @return bool
     */
    private function isStoreCodeInUrlUsed(StoreInterface $store): bool
    {
        return $this->scopeConfig->isSetFlag(
            Store::XML_PATH_STORE_IN_URL,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Getting back the failure url where we redirect the customer after something bad happened.
     *
     * @param Store $store
     * @return string
     */
    public function getFailureUrl($store = null): string
    {
        $scope = ($store === null ? ScopeConfigInterface::SCOPE_TYPE_DEFAULT : ScopeInterface::SCOPE_STORES);
        $failureUrl = $this->scopeConfig->getValue(SettingsProvider::FAILURE_URL, $scope, $store);

        if (!$failureUrl) {
            $failureUrl = $this->urlBuilder->getUrl('checkout/cart');
        }
        return $failureUrl;
    }

    /**
     * Get Klarna terms url
     *
     * @param StoreInterface $store
     * @return string
     */
    public function getUserTermsUrl($store = null)
    {
        $merchantId = $this->scopeConfig->getValue('klarna/api/merchant_id', ScopeInterface::SCOPE_STORES, $store);
        $locale = strtolower($this->localeResolver->getLocale($store));

        return sprintf('https://cdn.klarna.com/1.0/shared/content/legal/terms/%s/%s/checkout', $merchantId, $locale);
    }
}
