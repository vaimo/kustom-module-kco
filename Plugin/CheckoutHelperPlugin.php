<?php
/**
 * Copyright © Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
declare(strict_types=1);

namespace Klarna\Kco\Plugin;

use Klarna\Kco\Model\Checkout\Configuration\SettingsProvider;
use Magento\Checkout\Helper\Data;
use Magento\Customer\Model\Session;

/**
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 * @internal
 */
class CheckoutHelperPlugin
{
    /**
     * @var Session
     */
    private $customerSession;
    /**
     * @var SettingsProvider
     */
    private $config;

    /**
     * @param Session          $customerSession
     * @param SettingsProvider $config
     * @codeCoverageIgnore
     */
    public function __construct(Session $customerSession, SettingsProvider $config)
    {
        $this->customerSession = $customerSession;
        $this->config = $config;
    }

    /**
     * Checking the customer is allowed for the checkout
     *
     * @param Data $subject
     * @param bool $result
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterIsAllowedGuestCheckout(Data $subject, bool $result)
    {
        $quote = $subject->getCheckout()->getQuote();

        if (!$this->config->isKcoEnabled($quote->getStore())) {
            return $result;
        }

        if ($this->customerSession->isLoggedIn()) {
            return true;
        }

        return $this->config->isAllowedGuestCheckout($quote->getStore());
    }
}
