<?php
/**
 * Copyright © Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
declare(strict_types=1);

namespace Klarna\Kco\Model\Cart\Validations;

use Klarna\Base\Exception as KlarnaException;
use Klarna\Kco\Api\CheckoutValidationInterface;
use Klarna\Kco\Model\Cart\FullUpdate;
use Magento\Framework\DataObject;
use Magento\Quote\Api\Data\CartInterface;

/**
 * Performing validations
 *
 * @internal
 */
class Handler
{
    /**
     * @var CheckoutValidationInterface[]
     */
    private array $validations;
    /**
     * @var FullUpdate
     */
    private FullUpdate $fullUpdate;

    /**
     * @param FullUpdate $fullUpdate
     * @param CheckoutValidationInterface[] $validations
     * @codeCoverageIgnore
     */
    public function __construct(FullUpdate $fullUpdate, array $validations = [])
    {
        $this->validations = $validations;
        $this->fullUpdate = $fullUpdate;
    }

    /**
     * Performing different validation metrics between the Magento quote and the Klarna order
     *
     * @param DataObject    $request
     * @param CartInterface $quote
     *
     * @throws KlarnaException
     */
    public function validateRequestObject(DataObject $request, CartInterface $quote): void
    {
        /**
         * This lines is just added to fix the escalated issue in: MAGE-2706.
         * As long as you can not replicate the issue on the respective support tickets please do not
         * change this line.
         */
        $this->fullUpdate->updateByKlarnaRequestObject($request, $request->getOrderId(), $quote);

        foreach ($this->validations as $validation) {
            $validation->validate($request, $quote);
        }
    }
}
