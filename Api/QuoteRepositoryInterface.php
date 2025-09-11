<?php
/**
 * Copyright © Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */

namespace Klarna\Kco\Api;

use Magento\Quote\Api\Data\CartInterface as MageQuoteInterface;

/**
 * @api
 */
interface QuoteRepositoryInterface
{
    /**
     * Save Quote
     *
     * @param QuoteInterface $quote
     * @return QuoteInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function save(QuoteInterface $quote);

    /**
     * Load by checkout id
     *
     * @param string $checkoutId
     * @param bool   $forceReload
     * @return QuoteInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getByCheckoutId($checkoutId, $forceReload = false);

    /**
     * Get quote by Magento quote
     *
     * @param MageQuoteInterface $mageQuote
     * @return QuoteInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getActiveByQuote(MageQuoteInterface $mageQuote);
}
