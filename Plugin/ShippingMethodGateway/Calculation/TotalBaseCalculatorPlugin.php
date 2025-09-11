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
use Magento\Tax\Model\Calculation\TotalBaseCalculator;

/**
 * Recalculating/setting the shipping values when the calculation is based
 * on the total base when we use a shipping gateway api
 *
 * @internal
 */
class TotalBaseCalculatorPlugin
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
     * @param TotalBaseCalculator $subject
     * @param TaxDetailsItemInterface $result
     * @param \Magento\Tax\Model\Sales\Quote\ItemDetails $quoteDetails
     * @param int $storeId
     * @param bool $round
     * @return TaxDetailsItemInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @codeCoverageIgnore
     */
    public function afterCalculate(
        TotalBaseCalculator $subject,
        TaxDetailsItemInterface $result,
        \Magento\Tax\Model\Sales\Quote\ItemDetails $quoteDetails,
        $storeId,
        $round
    ): TaxDetailsItemInterface {
        return $this->tax->updateMagentoTax($result);
    }
}
