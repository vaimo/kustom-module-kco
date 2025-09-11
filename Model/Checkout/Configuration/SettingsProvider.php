<?php
/**
 * Copyright © Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
declare(strict_types=1);

namespace Klarna\Kco\Model\Checkout\Configuration;

use Magento\Customer\Model\Customer;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;

/**
 * Checking and returning configuration values from the database
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 * @internal
 */
class SettingsProvider
{
    public const FAILURE_URL = 'checkout/klarna_kco/failure_url';

    /**
     * @var ScopeConfigInterface
     */
    private ScopeconfigInterface $scopeConfig;
    /**
     * @var Session
     */
    private Session $session;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param Session $session
     * @codeCoverageIgnore
     */
    public function __construct(
        ScopeconfigInterface $scopeConfig,
        Session $session
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->session = $session;
    }

    /**
     * Determine if pack station config setting has been enabled.
     *
     * @param StoreInterface $store
     * @return bool
     */
    public function isPackstationEnabled(StoreInterface $store): bool
    {
        return $this->isCheckoutConfigFlag('packstation_enabled', $store);
    }

    /**
     * Get checkout config value
     *
     * @param string $config
     * @param StoreInterface $store
     *
     * @return bool
     */
    public function isCheckoutConfigFlag(string $config, StoreInterface $store): bool
    {
        return $this->scopeConfig->isSetFlag(
            sprintf(
                'checkout/klarna_kco/%s',
                $config
            ),
            ScopeInterface::SCOPE_STORES,
            $store
        );
    }

    /**
     * Determine if the pre-fill notice is enabled
     *
     * @param StoreInterface $store
     *
     * @return bool
     */
    public function isPrefillNoticeEnabled(StoreInterface $store): bool
    {
        if (!$this->session->isLoggedIn()) {
            return false;
        }
        if (!$this->scopeConfig->isSetFlag(
            'checkout/klarna_kco/merchant_prefill',
            ScopeInterface::SCOPE_STORES,
            $store
        )) {
            return false;
        }
        if (!$this->scopeConfig->isSetFlag(
            'checkout/klarna_kco/prefill_notice',
            ScopeInterface::SCOPE_STORES,
            $store
        )) {
            return false;
        }
        return true;
    }

    /**
     * Check is allowed Guest Checkout. Use config settings and observer
     *
     * @param StoreInterface $store
     * @return bool
     */
    public function isAllowedGuestCheckout(StoreInterface $store): bool
    {
        return $this->isCheckoutConfigFlag('guest_checkout', $store);
    }

    /**
     * Get checkout config value
     *
     * @param string $config
     * @param StoreInterface $store
     * @return string
     */
    public function getCheckoutConfig(string $config, StoreInterface $store): string
    {
        $result = $this->scopeConfig->getValue(
            sprintf(
                'checkout/klarna_kco/%s',
                $config
            ),
            ScopeInterface::SCOPE_STORES,
            $store
        );
        if ($result === null) {
            $result = '';
        }

        return $result;
    }

    /**
     * Determine if KCO checkout is enabled by checking if the Klarna payment method and Checkout is enabled
     *
     * @param StoreInterface $store
     * @param Customer|null $customer
     * @return bool
     */
    public function isKcoEnabled(StoreInterface $store, ?Customer $customer = null): bool
    {
        if (!$this->isKlarnaCheckoutPaymentEnabled($store)) {
            return false;
        }

        if (null === $customer) {
            $customer = $this->session->getCustomer();
        }

        $customerGroupId = $customer->getId() ? $customer->getGroupId() : 0;
        $disabledCustomerGroups = $this->getDisabledGroups($store);
        $disabledCustomerGroups = trim($disabledCustomerGroups);

        if ('' === $disabledCustomerGroups) {
            return true;
        }

        if (!is_array($disabledCustomerGroups)) {
            $disabledCustomerGroups = explode(',', (string)$disabledCustomerGroups);
        }

        return !in_array($customerGroupId, $disabledCustomerGroups);
    }

    /**
     * Check if the Klarna checkout payment method is enabled
     *
     * @param StoreInterface $store
     * @return bool
     */
    public function isKlarnaCheckoutPaymentEnabled(StoreInterface $store): bool
    {
        return $this->scopeConfig->isSetFlag(
            'payment/klarna_kco/active',
            ScopeInterface::SCOPE_STORES,
            $store
        );
    }

    /**
     * Get disabled customer groups
     *
     * @param StoreInterface $store
     * @return string
     */
    public function getDisabledGroups(StoreInterface $store): string
    {
        $result = $this->scopeConfig->getValue(
            'payment/klarna_kco/disable_customer_group',
            ScopeInterface::SCOPE_STORES,
            $store
        );

        if ($result === null) {
            $result = '';
        }

        return $result;
    }
}
