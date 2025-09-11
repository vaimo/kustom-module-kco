<?php
/**
 * Copyright © Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
declare(strict_types=1);

namespace Klarna\Kco\Model\Checkout\Initialization;

use Klarna\Base\Model\Quote\ShippingMethod\SelectionAssurance;
use Klarna\Kco\Model\Checkout\Kco\Initializer;
use Klarna\Kco\Model\Checkout\Kco\Session;
use Klarna\Base\Model\Quote\Address\Handler;
use Magento\Quote\Model\QuoteRepository;
use Klarna\Base\Exception as KlarnaException;

/**
 * @internal
 */
class Startup
{
    /**
     * @var Session
     */
    private Session $kcoSession;
    /**
     * @var Handler
     */
    private Handler $handler;
    /**
     * @var SelectionAssurance
     */
    private SelectionAssurance $selectionAssurance;
    /**
     * @var Initializer
     */
    private Initializer $kcoInitializer;
    /**
     * @var QuoteRepository
     */
    private QuoteRepository $magentoQuoteRepository;

    /**
     * @param Session $kcoSession
     * @param Handler $handler
     * @param SelectionAssurance $selectionAssurance
     * @param Initializer $kcoInitializer
     * @param QuoteRepository $magentoQuoteRepository
     * @codeCoverageIgnore
     */
    public function __construct(
        Session $kcoSession,
        Handler $handler,
        SelectionAssurance $selectionAssurance,
        Initializer $kcoInitializer,
        QuoteRepository $magentoQuoteRepository
    ) {
        $this->kcoSession = $kcoSession;
        $this->handler = $handler;
        $this->selectionAssurance = $selectionAssurance;
        $this->kcoInitializer = $kcoInitializer;
        $this->magentoQuoteRepository = $magentoQuoteRepository;
    }

    /**
     * Creating the Klarna session
     *
     * @throws KlarnaException
     */
    public function createKlarnaSession(): void
    {
        $quote = $this->kcoSession->getQuote();

        if ($quote->getIsMultiShipping()) {
            $quote->setIsMultiShipping(false);
        }

        if (!$quote->isVirtual()) {
            if (!$quote->getShippingAddress()->getCountryId()) {
                $this->handler->setDefaultShippingAddress($quote);
            }

            $quote->getShippingAddress()->setCollectShippingRates(true);
        }

        $this->selectionAssurance->ensureShippingMethodSelected($quote);
        $this->magentoQuoteRepository->save($quote);

        $this->kcoInitializer->createKlarnaCheckout();
    }
}
