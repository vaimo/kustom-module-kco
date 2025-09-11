<?php
/**
 * Copyright © Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
declare(strict_types=1);

namespace Klarna\Kco\Controller\Klarna;

use Klarna\Base\Model\Quote\ShippingMethod\QuoteMethodHandler as QuoteShippingMethod;
use Klarna\Logger\Api\LoggerInterface;
use Klarna\Kco\Model\Checkout\Kco\Initializer as kcoInitializer;
use Klarna\Kco\Model\Responder\Ajax;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Message\ManagerInterface;

/**
 * This method is used when backend shipping method callbacks are not supported in the Klarna market.
 * It is for example the case when the selection of the shipping methods is happening outside of the iframe.
 *
 * @api
 */
class SaveShippingMethod implements HttpPostActionInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var kcoInitializer
     */
    private $kcoInitializer;
    /**
     * @var Ajax
     */
    private $ajax;
    /**
     * @var QuoteShippingMethod
     */
    private QuoteShippingMethod $quoteShippingMethod;
    /**
     * @var RequestInterface
     */
    private RequestInterface $request;
    /**
     * @var ManagerInterface
     */
    private ManagerInterface $messageManager;

    /**
     * @param LoggerInterface $logger
     * @param kcoInitializer $kcoInitializer
     * @param Ajax $ajax
     * @param QuoteShippingMethod $quoteShippingMethod
     * @param RequestInterface $request
     * @param ManagerInterface $messageManager
     * @codeCoverageIgnore
     */
    public function __construct(
        LoggerInterface $logger,
        kcoInitializer $kcoInitializer,
        Ajax $ajax,
        QuoteShippingMethod $quoteShippingMethod,
        RequestInterface $request,
        ManagerInterface $messageManager
    ) {
        $this->logger              = $logger;
        $this->kcoInitializer      = $kcoInitializer;
        $this->ajax                = $ajax;
        $this->quoteShippingMethod = $quoteShippingMethod;
        $this->request = $request;
        $this->messageManager = $messageManager;
    }

    /**
     * Saving the shipping method
     *
     * @return ResultInterface
     */
    public function execute()
    {
        try {
            $body = json_decode($this->request->getContent(), true);

            $this->quoteShippingMethod->updateShippingMethod(
                $this->kcoInitializer->getKcoSession()->getQuote(),
                $body['carrier_code'] . '_' . $body['method_code']
            );

        } catch (LocalizedException $e) {
            $this->logger->critical($e);
            $this->messageManager->addErrorMessage(__(
                'An issue occurred when saving the shipping method.'
            ));
        }

        return $this->ajax->getSummaryResponse();
    }
}
