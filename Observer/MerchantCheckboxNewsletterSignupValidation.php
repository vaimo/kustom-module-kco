<?php
/**
 * Copyright © Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
declare(strict_types=1);

namespace Klarna\Kco\Observer;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Newsletter\Model\Subscriber;
use Magento\Framework\Module\Manager;

/**
 * Validate the merchant checkbox should display for newsletter signup
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 * @internal
 */
class MerchantCheckboxNewsletterSignupValidation implements ObserverInterface
{

    /**
     * @var Session
     */
    private Session $session;

    /**
     * @var Subscriber
     */
    private Subscriber $subscriber;

    /**
     * @var Manager
     */
    private Manager $moduleManager;

    /**
     * @var ScopeConfigInterface
     */
    private ScopeConfigInterface $config;

    /**
     * MerchantCheckboxNewsletterSignupValidation constructor.
     *
     * @param Session              $session
     * @param Subscriber           $subscriber
     * @param ScopeConfigInterface $config
     * @param Manager              $moduleManager
     * @codeCoverageIgnore
     */
    public function __construct(
        Session $session,
        Subscriber $subscriber,
        ScopeConfigInterface $config,
        Manager $moduleManager
    ) {
        $this->session = $session;
        $this->subscriber = $subscriber;
        $this->config = $config;
        $this->moduleManager = $moduleManager;
    }

    /**
     * Performing the validation for the newsletter signup
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        if ((!$this->config->isSetFlag(Subscriber::XML_PATH_ALLOW_GUEST_SUBSCRIBE_FLAG)
                && !$this->session->isLoggedIn())
            || !$this->isNewsletterModuleEnabled()
        ) {
            $observer->getState()->setEnabled(false);

            return;
        }

        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $observer->getQuote();
        $customerEmail = $quote->getCustomerEmail() ?: $quote->getCustomer()->getEmail();
        if ($customerEmail) {
            $newsLetter = $this->subscriber->loadBySubscriberEmail($customerEmail, $quote->getStoreId());
            $observer->getState()->setEnabled(!$newsLetter->isSubscribed());
        }
    }

    /**
     * Check if the Magento_Newsletter module is enabled
     *
     * @return bool
     */
    private function isNewsletterModuleEnabled(): bool
    {
        return $this->moduleManager->isEnabled('Magento_Newsletter');
    }
}
