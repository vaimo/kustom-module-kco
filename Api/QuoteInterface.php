<?php
/**
 * Copyright © Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */

namespace Klarna\Kco\Api;

/**
 * @api
 */
interface QuoteInterface
{
    /**
     * Set quote active/inactive
     *
     * @param int $active
     * @return $this
     */
    public function setIsActive($active);

    /**
     * Get whether the quote is active or not
     *
     * @return int
     */
    public function getIsActive();

    /**
     * Set checkout_id
     *
     * @param string $checkoutId
     * @return $this
     */
    public function setKlarnaCheckoutId($checkoutId);

    /**
     * Get checkout_id
     *
     * @return string
     */
    public function getKlarnaCheckoutId();

    /**
     * Get Magento Quote ID
     *
     * @return int
     */
    public function getQuoteId();

    /**
     * Set Magento Quote ID
     *
     * @param int $quoteId
     * @return $this
     */
    public function setQuoteId($quoteId);

    /**
     * Entity ID
     *
     * @return int
     */
    public function getId();
}
