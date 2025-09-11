<?php
/**
 * Copyright © Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
declare(strict_types=1);

namespace Klarna\Kco\Plugin\ShippingMethodGateway;

use Klarna\Kco\Model\Carrier\Klarna;
use Magento\Shipping\Model\CarrierFactory;
use Magento\Shipping\Model\Carrier\CarrierInterface;

/**
 * @internal
 */
class CarrierFactoryPlugin
{
    /**
     * @var Klarna
     */
    private $klarnaCarrier;

    /**
     * @param Klarna $klarnaCarrier
     * @codeCoverageIgnore
     */
    public function __construct(Klarna $klarnaCarrier)
    {
        $this->klarnaCarrier = $klarnaCarrier;
    }

    /**
     * Setting the carrier code so that KSA is known.
     *
     * Plugin for the carrier factory so that the Klarna Shipping Gateway is "registered" or known
     * in the list of grouped carriers (See: Magento\Quote\Model\Quote\Address::getGroupedAllShippingRates()).
     * Else on initial page load a not KSS method will be used what can have unseen side effects.
     *
     * @param CarrierFactory $subject
     * @param CarrierInterface|bool $result
     * @param string|null $carrierCode Can also be null for the case no code is set for the respective carrier
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGet(CarrierFactory $subject, $result, ?string $carrierCode = null)
    {
        if ($carrierCode === 'klarna') {
            $this->klarnaCarrier->setId($carrierCode);
            return $this->klarnaCarrier;
        }

        return $result;
    }
}
