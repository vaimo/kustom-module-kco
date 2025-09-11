<?php
/**
 * Copyright © Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
declare(strict_types=1);

namespace Klarna\Kco\Plugin\ShippingMethodGateway\Calculation;

use Klarna\Kco\Model\Tax;
use Magento\Tax\Api\Data\TaxDetailsItemInterface;
use Magento\Tax\Model\Calculation\RowBaseCalculator;
use Magento\Tax\Model\Sales\Quote\ItemDetails;

/**
 * Recalculating/setting the shipping values when the calculation is based
 * on the unit base when we use a shipping gateway api
 *
 * @internal
 */
class RowBaseCalculatorPlugin
{
    /**
     * @var Tax
     */
    private $tax;

    /**
     * @param Tax $tax
     * @codeCoverageIgnore
     */
    public function __construct(Tax $tax)
    {
        $this->tax = $tax;
    }

    /**
     * Recalculating/setting the shipping and tax values
     *
     * @param RowBaseCalculator $subject
     * @param TaxDetailsItemInterface $result
     * @param ItemDetails $quoteDetails
     * @param int $quantity
     * @param bool $round
     * @return TaxDetailsItemInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @codeCoverageIgnore
     */
    public function afterCalculate(
        RowBaseCalculator $subject,
        TaxDetailsItemInterface $result,
        ItemDetails $quoteDetails,
        int $quantity,
        $round = true
    ): TaxDetailsItemInterface {
        return $this->tax->updateMagentoTax($result);
    }
}
