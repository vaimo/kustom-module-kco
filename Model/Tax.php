<?php
/**
 * Copyright © Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
declare(strict_types=1);

namespace Klarna\Kco\Model;

use Klarna\Kco\Model\Checkout\Kco\Session;
use Klarna\Kss\Model\Assignment\Tax as KssTaxAssignment;
use Magento\Tax\Api\Data\TaxDetailsItemInterface;

/**
 * Performing checks and updates on the Magento Tax values
 *
 * @api
 */
class Tax
{
    /**
     * @var KssTaxAssignment
     */
    private $assignment;
    /**
     * @var Session
     */
    private $session;

    /**
     * @param KssTaxAssignment $assignment
     * @param Session          $session
     * @codeCoverageIgnore
     */
    public function __construct(KssTaxAssignment $assignment, Session $session)
    {
        $this->assignment = $assignment;
        $this->session    = $session;
    }

    /**
     * Checking if KSS is used and if the Magento tax values can be adjusted.
     *
     * If that is the case these values will be updated.
     *
     * @param TaxDetailsItemInterface $taxDetailsItem
     * @return TaxDetailsItemInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function updateMagentoTax(TaxDetailsItemInterface $taxDetailsItem): TaxDetailsItemInterface
    {
        if (!$this->session->hasActiveKlarnaShippingGatewayInformation()) {
            return $taxDetailsItem;
        }

        if ($this->assignment->canUpdateValues($taxDetailsItem, $this->session->getQuote())) {
            return $this->assignment->assignToTaxInstance(
                $taxDetailsItem,
                $this->session->getKlarnaShippingGateway(),
                $this->session->getQuote()
            );
        }

        return $taxDetailsItem;
    }
}
