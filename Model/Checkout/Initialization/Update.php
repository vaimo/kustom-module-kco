<?php
/**
 * Copyright © Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
declare(strict_types=1);

namespace Klarna\Kco\Model\Checkout\Initialization;

use Klarna\Kco\Model\Checkout\Kco\Initializer;
use Klarna\Kco\Model\Checkout\Kco\Session;

/**
 * @internal
 */
class Update
{
    /**
     * @var Session
     */
    private Session $kcoSession;
    /**
     * @var Initializer
     */
    private Initializer $kcoInitializer;

    /**
     * @param Session $kcoSession
     * @param Initializer $kcoInitializer
     * @codeCoverageIgnore
     */
    public function __construct(Session $kcoSession, Initializer $kcoInitializer)
    {
        $this->kcoSession = $kcoSession;
        $this->kcoInitializer = $kcoInitializer;
    }

    /**
     * Updating the Klarna session
     */
    public function updateKlarnaSession(): void
    {
        $klarnaCheckoutId = $this->kcoSession->getKlarnaQuote()->getKlarnaCheckoutId();
        $this->kcoInitializer->updateKlarnaCheckout($klarnaCheckoutId);
    }
}
