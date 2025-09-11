<?php
/**
 * Copyright © Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
declare(strict_types=1);

namespace Klarna\Kco\Model\Cart;

use Magento\Quote\Api\Data\AddressInterface;

/**
 * @internal
 */
class FlatAddress
{
    /**
     * Getting back the billing address
     *
     * @param AddressInterface $billingAddress
     * @return array
     */
    public function getBillingAddress(AddressInterface $billingAddress): array
    {
        return [
            'email'      => $billingAddress->getEmail(),
            'company'    => $billingAddress->getCompany(),
            'prefix'     => $billingAddress->getPrefix(),
            'firstname'  => $billingAddress->getFirstname(),
            'lastname'   => $billingAddress->getLastname(),
            'street'     => $billingAddress->getStreet(),
            'city'       => $billingAddress->getCity(),
            'region'     => $billingAddress->getRegionModel($billingAddress->getRegionId()),
            'regionId'   => $billingAddress->getRegionId(),
            'regionCode' => $billingAddress->getRegionCode(),
            'postcode'   => $billingAddress->getPostcode(),
            'countryId'  => $billingAddress->getCountryId(),
            'telephone'  => $billingAddress->getTelephone()
        ];
    }

    /**
     * Getting back the full shipping address
     *
     * @param AddressInterface $shippingAddress
     * @return array
     */
    public function getFullShippingAddress(AddressInterface $shippingAddress): array
    {
        return [
            'email'      => $shippingAddress->getEmail(),
            'company'    => $shippingAddress->getCompany(),
            'prefix'     => $shippingAddress->getPrefix(),
            'firstname'  => $shippingAddress->getFirstname(),
            'lastname'   => $shippingAddress->getLastname(),
            'street'     => $shippingAddress->getStreet(),
            'city'       => $shippingAddress->getCity(),
            'region'     => $shippingAddress->getRegionModel($shippingAddress->getRegionId()),
            'regionId'   => $shippingAddress->getRegionId(),
            'regionCode' => $shippingAddress->getRegionCode(),
            'postcode'   => $shippingAddress->getPostcode(),
            'countryId'  => $shippingAddress->getCountryId(),
            'telephone'  => $shippingAddress->getTelephone()
        ];
    }

    /**
     * Getting back basic shipping information
     *
     * @param AddressInterface $shippingAddress
     * @return array
     */
    public function getBasicShippingInformation(AddressInterface $shippingAddress): array
    {
        return [
            'postcode'  => $shippingAddress->getPostcode(),
            'countryId' => $shippingAddress->getCountryId()
        ];
    }
}
