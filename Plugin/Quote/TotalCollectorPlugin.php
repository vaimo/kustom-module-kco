<?php
/**
 * Copyright © Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
declare(strict_types=1);

namespace Klarna\Kco\Plugin\Quote;

use Klarna\Kco\Model\Checkout\Kco\Session as KcoSession;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\Quote\TotalsCollector;
use Klarna\Kco\Model\Checkout\Configuration\SettingsProvider;

/**
 * @internal
 */
class TotalCollectorPlugin
{
    /**
     * @var KcoSession
     */
    private $kcoSession;
    /**
     * @var SettingsProvider
     */
    private SettingsProvider $config;

    /**
     * @param KcoSession $kcoSession
     * @param SettingsProvider $config
     * @codeCoverageIgnore
     */
    public function __construct(KcoSession $kcoSession, SettingsProvider $config)
    {
        $this->kcoSession = $kcoSession;
        $this->config = $config;
    }

    /**
     * Setting specific values if KSS is used
     *
     * @param TotalsCollector $totalsCollector
     * @param CartInterface $quote
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeCollect(
        TotalsCollector $totalsCollector,
        CartInterface $quote
    ): void {
        if (!$this->config->isKcoEnabled($quote->getStore())) {
            return;
        }

        if ($quote->getId() === null) {
            return;
        }

        $this->kcoSession->setQuote($quote);
        if (!$this->kcoSession->hasActiveKlarnaShippingGatewayInformation()) {
            return;
        }

        $quote->getShippingAddress()->setShippingAmountForDiscount(null);
        $quote->getShippingAddress()->setBaseShippingAmountForDiscount(null);
    }
}
