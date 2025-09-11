<?php
/**
 * Copyright © Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
declare(strict_types=1);

namespace Klarna\Kco\Controller\Klarna;

use Klarna\Kco\Model\Checkout\Url;
use Magento\Checkout\Model\Session\SuccessValidator;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\Translate\InlineInterface;
use \Magento\Framework\Data\Form\FormKey\Validator;
use \Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\View\LayoutFactory;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\View\Result\LayoutFactory as ResultLayoutFactory;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Customer\Model\Session;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\Page;
use Klarna\Kco\Model\Checkout\Kco\Session as KcoSession;
use Magento\Checkout\Controller\Onepage\Success as MagentoSuccess;

/**
 * Order success action
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @api
 */
class Success extends MagentoSuccess
{
    /**
     * @var SuccessValidator
     */
    private $successValidator;
    /**
     * @var Url
     */
    private $url;
    /**
     * @var KcoSession
     */
    private $kcoSession;

    /**
     * @param Context                     $context
     * @param Session                     $customerSession
     * @param CustomerRepositoryInterface $customerRepository
     * @param AccountManagementInterface  $accountManagement
     * @param Registry                    $coreRegistry
     * @param InlineInterface             $translateInline
     * @param Validator                   $formKeyValidator
     * @param ScopeConfigInterface        $scopeConfig
     * @param LayoutFactory               $layoutFactory
     * @param CartRepositoryInterface     $quoteRepository
     * @param PageFactory                 $resultPageFactory
     * @param ResultLayoutFactory         $resultLayoutFactory
     * @param RawFactory                  $resultRawFactory
     * @param JsonFactory                 $resultJsonFactory
     * @param SuccessValidator            $successValidator
     * @param Url                         $url
     * @param KcoSession                  $kcoSession
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * @codeCoverageIgnore
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        CustomerRepositoryInterface $customerRepository,
        AccountManagementInterface $accountManagement,
        Registry $coreRegistry,
        InlineInterface $translateInline,
        Validator $formKeyValidator,
        ScopeConfigInterface $scopeConfig,
        LayoutFactory $layoutFactory,
        CartRepositoryInterface $quoteRepository,
        PageFactory $resultPageFactory,
        ResultLayoutFactory $resultLayoutFactory,
        RawFactory $resultRawFactory,
        JsonFactory $resultJsonFactory,
        SuccessValidator $successValidator,
        Url $url,
        KcoSession $kcoSession
    ) {
        parent::__construct(
            $context,
            $customerSession,
            $customerRepository,
            $accountManagement,
            $coreRegistry,
            $translateInline,
            $formKeyValidator,
            $scopeConfig,
            $layoutFactory,
            $quoteRepository,
            $resultPageFactory,
            $resultLayoutFactory,
            $resultRawFactory,
            $resultJsonFactory
        );

        $this->successValidator = $successValidator;
        $this->url              = $url;
        $this->kcoSession       = $kcoSession;
    }

    /**
     * Performing the success action
     *
     * @return ResultInterface|Page
     */
    public function execute()
    {
        if (!$this->successValidator->isValid()) {
            $this->messageManager->addErrorMessage(
                __('There was a error when showing the success page.')
            );
            return $this->resultRedirectFactory->create()->setUrl($this->url->getFailureUrl());
        }

        $session = $this->kcoSession->getCheckout();
        $session->clearQuote();

        /**
         * This must be called before the events are fired. Else the order can not be found in the respective
         * success block class.
         */
        $resultPage = $this->resultPageFactory->create();
        $this->fireEvents($session);

        return $resultPage;
    }

    /**
     * Firing events
     *
     * @param CheckoutSession $session
     */
    private function fireEvents(CheckoutSession $session): void
    {
        $lastOrderId = $session->getLastOrderId();
        $this->_eventManager->dispatch(
            'checkout_kco_controller_success_action',
            ['order_ids' => [$lastOrderId]]
        );
        // Fire off onepage success event also to handle for GA as well as anyone else that might be listening
        $this->_eventManager->dispatch(
            'checkout_onepage_controller_success_action',
            ['order_ids' => [$lastOrderId]]
        );
    }
}
