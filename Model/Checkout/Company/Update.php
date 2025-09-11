<?php
/**
 * Copyright © Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
declare(strict_types=1);

namespace Klarna\Kco\Model\Checkout\Company;

use Magento\Framework\DataObject;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;

/**
 * @internal
 */
class Update
{
    /**
     * @var DataProvider
     */
    private DataProvider $dataProvider;
    /**
     * @var CustomerRepositoryInterface
     */
    private CustomerRepositoryInterface $customerRepository;

    /**
     * @param DataProvider $dataProvider
     * @param CustomerRepositoryInterface $customerRepository
     * @codeCoverageIgnore
     */
    public function __construct(DataProvider $dataProvider, CustomerRepositoryInterface $customerRepository)
    {
        $this->dataProvider = $dataProvider;
        $this->customerRepository = $customerRepository;
    }

    /**
     * Setting company ID into quote and customer session
     *
     * @param DataObject    $request
     * @param CartInterface $quote
     */
    public function updateCustomerBusinessIdFromRequest(DataObject $request, CartInterface $quote): void
    {
        if ($quote->getCustomerIsGuest()) {
            return;
        }

        $klarnaCompanyId = $this->dataProvider->getKlarnaRequestCompanyId($request);
        if ($klarnaCompanyId === '') {
            return;
        }

        $storeCompanyIdAttributeCode = $this->dataProvider->getStoreCompanyIdAttributeCode($quote->getStore());
        if ($storeCompanyIdAttributeCode === '') {
            return;
        }

        $customerId = $quote->getCustomer()->getId();
        $customer = $this->customerRepository->getById($customerId);
        $customerCompanyId = $customer->getCustomAttribute($storeCompanyIdAttributeCode)->getValue();

        if (!empty($customerCompanyId) && $customerCompanyId === $klarnaCompanyId) {
            return;
        }

        $customer->setCustomAttribute($storeCompanyIdAttributeCode, $klarnaCompanyId);
        $this->customerRepository->save($customer);
    }
}
