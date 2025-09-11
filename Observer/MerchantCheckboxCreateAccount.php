<?php
/**
 * Copyright © Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
declare(strict_types=1);

namespace Klarna\Kco\Observer;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Customer\Model\Registration;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderCustomerManagementInterface;
use Magento\Customer\Model\AccountManagement;

/**
 * Register a new user when they check the box
 *
 * @internal
 */
class MerchantCheckboxCreateAccount implements ObserverInterface
{

    /**
     * @var Registration
     */
    private Registration $registration;

    /**
     * @var OrderCustomerManagementInterface
     */
    private OrderCustomerManagementInterface $orderCustomerService;

    /**
     * @var CustomerRepositoryInterface
     */
    private CustomerRepositoryInterface $customerRepository;
    /**
     * @var AccountManagement
     */
    private AccountManagement $accountManagement;

    /**
     * @param Registration $registration
     * @param OrderCustomerManagementInterface $orderCustomerService
     * @param CustomerRepositoryInterface $customerRepository
     * @param AccountManagement $accountManagement
     * @codeCoverageIgnore
     */
    public function __construct(
        Registration $registration,
        OrderCustomerManagementInterface $orderCustomerService,
        CustomerRepositoryInterface $customerRepository,
        AccountManagement $accountManagement
    ) {
        $this->registration = $registration;
        $this->orderCustomerService = $orderCustomerService;
        $this->customerRepository = $customerRepository;
        $this->accountManagement = $accountManagement;
    }

    /**
     * Creating a account
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        if ($observer->getChecked() && $this->registration->isAllowed()) {
            /** @var \Magento\Quote\Model\Quote $quote */
            $quote = $observer->getQuote();
            /** @var OrderInterface $order */
            $order = $observer->getOrder();

            if ($quote->getCustomerId() ||
                $this->accountManagement->isEmailAvailable(
                    $quote->getCustomerEmail(),
                    $quote->getStore()->getWebsiteId()
                )) {
                return;
            }
            /** @var \Magento\Customer\Api\Data\CustomerInterface $customer */
            $customer = $this->orderCustomerService->create($order->getId());
            $customer->setDob($order->getCustomerDob());
            $customer->setGender($order->getCustomerGender());
            $this->customerRepository->save($customer);
        }
    }
}
