<?php
/**
 * Copyright © Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
declare(strict_types=1);

namespace Klarna\Kco\Observer;

use Klarna\Kco\Api\QuoteRepositoryInterface;
use Klarna\Kco\Model\Checkout\Url;
use Magento\Framework\App\RequestInterface;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\ActionFlag;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 * @internal
 */
class PreFillNoticeCheckAccept implements ObserverInterface
{
    /**
     * @var Session
     */
    private Session $checkoutSession;

    /**
     * @var QuoteRepositoryInterface
     */
    private QuoteRepositoryInterface $kcoQuoteRepository;

    /**
     * @var ActionFlag
     */
    private $actionFlag;

    /**
     * @var RedirectInterface
     */
    private RedirectInterface $redirect;
    /**
     * @var RequestInterface
     */
    private RequestInterface $request;
    /**
     * @var ResponseInterface
     */
    private ResponseInterface $response;

    /**
     * PreFillNoticeCheckAccept constructor.
     *
     * @param Session                  $checkoutSession
     * @param ActionFlag               $actionFlag
     * @param RedirectInterface        $redirect
     * @param QuoteRepositoryInterface $quoteRepository
     * @param RequestInterface         $request
     * @param ResponseInterface        $response
     * @codeCoverageIgnore
     */
    public function __construct(
        Session $checkoutSession,
        ActionFlag $actionFlag,
        RedirectInterface $redirect,
        QuoteRepositoryInterface $quoteRepository,
        RequestInterface $request,
        ResponseInterface $response
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->actionFlag = $actionFlag;
        $this->redirect = $redirect;
        $this->kcoQuoteRepository = $quoteRepository;
        $this->request = $request;
        $this->response = $response;
    }

    /**
     * Check if the pre-fill notice has been accepted
     *
     * @param Observer $observer
     *
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(Observer $observer)
    {
        $termsParam = $this->request->getParam('terms');

        if ($termsParam) {
            $this->checkoutSession->setKlarnaFillNoticeTerms($termsParam);
        }

        if ('accept' === $termsParam) {
            $quote = $this->checkoutSession->getQuote();
            $klarnaQuote = $this->kcoQuoteRepository->getActiveByQuote($quote);

            if ($klarnaQuote->getId()) {
                $klarnaQuote->setIsActive(0);
                $this->kcoQuoteRepository->save($klarnaQuote);
            }

            $this->actionFlag->set('', Action::FLAG_NO_DISPATCH, true);
            $this->redirect->redirect($this->response, Url::CHECKOUT_ACTION_PREFIX);
        }
    }
}
