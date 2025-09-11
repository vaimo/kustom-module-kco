<?php
/**
 * Copyright © Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
declare(strict_types=1);

namespace Klarna\Kco\Observer;

use Klarna\Base\Exception as KlarnaException;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Customer\Model\Url;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Newsletter\Model\Subscriber;
use Magento\Newsletter\Model\SubscriptionManager;

/**
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 * @internal
 */
class MerchantCheckboxNewsletterSignup implements ObserverInterface
{

    /**
     * @var Url
     */
    private Url $url;

    /**
     * @var CustomerSession
     */
    private CustomerSession $customerSession;

    /**
     * @var ScopeConfigInterface
     */
    private ScopeConfigInterface $config;

    /**
     * @var ManagerInterface
     */
    private ManagerInterface $messageManager;

    /**
     * @var SubscriptionManager
     */
    private SubscriptionManager $subscriptionManager;

    /**
     * @param CustomerSession $customerSession
     * @param Url $url
     * @param ScopeConfigInterface $config
     * @param ManagerInterface $messageManager
     * @param SubscriptionManager $subscriber
     * @codeCoverageIgnore
     */
    public function __construct(
        CustomerSession      $customerSession,
        Url                  $url,
        ScopeConfigInterface $config,
        ManagerInterface     $messageManager,
        SubscriptionManager  $subscriber
    ) {
        $this->customerSession = $customerSession;
        $this->url = $url;
        $this->config = $config;
        $this->messageManager = $messageManager;
        $this->subscriptionManager = $subscriber;
    }

    /**
     * Signing up for the newsletter
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $observer->getQuote();
        $storeId = $quote->getStoreId();
        if ($observer->getChecked() && ($email = ($quote->getCustomerEmail() ?: $quote->getCustomer()->getEmail()))) {
            try {
                if (!$this->config->isSetFlag(Subscriber::XML_PATH_ALLOW_GUEST_SUBSCRIBE_FLAG)
                    && !$this->customerSession->isLoggedIn()
                ) {
                    throw new KlarnaException(__(
                        'Sorry, but administrator denied subscription for guests. Please <a href="%1">register</a>.',
                        $this->url->getRegisterUrl()
                    ));
                }

                $subscriber = $this->subscriptionManager->subscribe($email, $storeId);
                $statusMessage = $subscriber->isSubscribed() ?
                    __('Confirmation request has been sent.') :
                    __('Thank you for your subscription.');

                $this->messageManager->addSuccessMessage($statusMessage);
            } catch (KlarnaException $exception) {
                $this->messageManager->addExceptionMessage(
                    $exception,
                    __(
                        'There was a problem with the subscription: %1',
                        $exception->getMessage()
                    )
                );
            } catch (\Exception $exception) {
                $this->messageManager->addExceptionMessage(
                    $exception,
                    __('There was a problem with the subscription.')
                );
            }
        }
    }
}
