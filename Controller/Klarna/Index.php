<?php
/**
 * Copyright © Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
declare(strict_types=1);

namespace Klarna\Kco\Controller\Klarna;

use Klarna\Kco\Model\Checkout\Configuration\SettingsProvider;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
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
use Magento\Framework\Controller\Result\ForwardFactory;

/**
 * This controller is called when a customer loads the Klarna KCO page.
 * Here we decide if the customer see this page or if he/she will be redirected to a different page.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @api
 */
class Index extends \Magento\Checkout\Controller\Index\Index
{
    /**
     * @var SettingsProvider
     */
    private $config;
    /**
     * @var ForwardFactory
     */
    private $resultForwardFactory;

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
     * @param SettingsProvider            $config
     * @param ForwardFactory              $resultForwardFactory
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
        SettingsProvider $config,
        ForwardFactory $resultForwardFactory
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

        $this->config = $config;
        $this->resultForwardFactory = $resultForwardFactory;
    }

    /**
     * Dispatch request
     *
     * @return ResultInterface|ResponseInterface
     */
    public function execute()
    {
        /**
         * We need this condition for the case KCO will be disabled and customers are reloading the
         * Klarna KCO page. Without this condition customers will always face a not working checkout.
         */
        if (!$this->config->isKcoEnabled($this->getOnepage()->getQuote()->getStore())) {
            $resultForward = $this->resultForwardFactory->create();
            return $resultForward->forward('noroute');
        }
        $resultPage = parent::execute();
        if ($resultPage instanceof Redirect) {
            return $resultPage;
        }
        $resultPage->getConfig()->getTitle()->set(__('Checkout'));
        return $resultPage;
    }
}
