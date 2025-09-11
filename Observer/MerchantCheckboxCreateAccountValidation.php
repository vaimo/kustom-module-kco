<?php
/**
 * Copyright © Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
declare(strict_types=1);

namespace Klarna\Kco\Observer;

use Magento\Customer\Model\CustomerRegistry;
use Magento\Customer\Model\Registration;
use Magento\Customer\Model\Session;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Customer\Model\AccountManagement;

/**
 * Validate the merchant checkbox should display for user signup
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 * @internal
 */
class MerchantCheckboxCreateAccountValidation implements ObserverInterface
{
    /**
     * @var Session
     */
    private Session $session;

    /**
     * @var Registration
     */
    private Registration $registration;
    /**
     * @var AccountManagement
     */
    private AccountManagement $accountManagement;

    /**
     * @param Session $session
     * @param Registration $registration
     * @param AccountManagement $accountManagement
     * @codeCoverageIgnore
     */
    public function __construct(
        Session $session,
        Registration $registration,
        AccountManagement $accountManagement
    ) {
        $this->session = $session;
        $this->registration = $registration;
        $this->accountManagement = $accountManagement;
    }

    /**
     * Performing the validation for the account creation
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $quote = $observer->getQuote();

        $customerExist = $this->accountManagement->isEmailAvailable(
            $quote->getCustomerEmail(),
            $quote->getStore()->getWebsiteId()
        );
        $enabled = !$customerExist && !$this->session->isLoggedIn()
            && $this->registration->isAllowed();
        $observer->getState()->setEnabled($enabled);
    }
}
