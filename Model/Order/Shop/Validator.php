<?php
/**
 * Copyright © Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
declare(strict_types=1);

namespace Klarna\Kco\Model\Order\Shop;

use Klarna\Base\Exception as KlarnaException;
use Klarna\Kco\Model\Checkout\Configuration\SettingsProvider;
use Magento\Checkout\Model\Type\Onepage;
use Magento\Quote\Api\Data\CartInterface;

/**
 * @internal
 */
class Validator
{
    /**
     * @var SettingsProvider
     */
    private SettingsProvider $settingsProvider;

    /**
     * @param SettingsProvider $settingsProvider
     * @codeCoverageIgnore
     */
    public function __construct(SettingsProvider $settingsProvider)
    {
        $this->settingsProvider = $settingsProvider;
    }

    /**
     * Checking if the quote is placeable
     *
     * @param CartInterface $quote
     * @throws KlarnaException
     */
    public function checkIfPlaceable(CartInterface $quote): void
    {
        if ($quote->getIsMultiShipping()) {
            throw new KlarnaException(__('Invalid checkout type.'));
        }

        if ($quote->getCheckoutMethod() === Onepage::METHOD_GUEST
            && !$this->settingsProvider->isAllowedGuestCheckout($quote->getStore())) {
            throw new KlarnaException(
                __(
                    'Sorry, guest checkout is not enabled. Please try again or contact the store owner.'
                )
            );
        }
    }
}
