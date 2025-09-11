<?php
/**
 * Copyright © Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
declare(strict_types=1);

namespace Klarna\Kco\Model\Checkout\Initialization;

use Klarna\Kco\Model\Checkout\Configuration\SettingsProvider;
use Klarna\Kco\Model\Checkout\Kco\Session;

/**
 * @internal
 */
class Validator
{
    /**
     * @var Session
     */
    private Session $kcoSession;
    /**
     * @var SettingsProvider
     */
    private SettingsProvider $settingsProvider;

    /**
     * @param Session $kcoSession
     * @param SettingsProvider $settingsProvider
     * @codeCoverageIgnore
     */
    public function __construct(Session $kcoSession, SettingsProvider $settingsProvider)
    {
        $this->kcoSession = $kcoSession;
        $this->settingsProvider = $settingsProvider;
    }

    /**
     * Returns true if the customer is allowed to use KCO
     *
     * @return bool
     */
    public function isCheckoutAllowedForCustomer(): bool
    {
        return $this->kcoSession->getCustomerSession()->isLoggedIn()
            || $this->settingsProvider->isAllowedGuestCheckout($this->kcoSession->getQuote()->getStore());
    }

    /**
     * Returns true if a Klarna session is already running
     *
     * @return bool
     */
    public function isKlarnaSessionRunning(): bool
    {
        return !empty($this->kcoSession->getKlarnaQuote()->getKlarnaCheckoutId());
    }
}
