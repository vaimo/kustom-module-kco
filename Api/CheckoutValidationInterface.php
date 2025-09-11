<?php
/**
 * Copyright © Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */

namespace Klarna\Kco\Api;

use Klarna\Base\Exception as KlarnaException;
use Magento\Framework\DataObject;
use Magento\Quote\Api\Data\CartInterface;

/**
 * interface for all checkout validation classes
 *
 * @api
 */
interface CheckoutValidationInterface
{
    /**
     * Validate request
     *
     * @param DataObject    $request
     * @param CartInterface $quote
     *
     * @throws KlarnaException
     */
    public function validate(DataObject $request, CartInterface $quote): void;
}
