<?php
/**
 * Copyright © Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
declare(strict_types=1);

namespace Klarna\Kco\Model\Cart\ShippingMethod;

use Klarna\Base\Model\Quote\ShippingMethod\QuoteMethodHandler;
use Klarna\Kco\Model\WorkflowProvider;
use Magento\Quote\Model\QuoteRepository as MageQuoteRepository;
use Magento\Framework\DataObject;
use Klarna\Base\Exception as KlarnaException;

/**
 * @internal
 */
class KlarnaRequestQuoteTransformer
{
    /**
     * @var WorkflowProvider
     */
    private WorkflowProvider $workflowProvider;
    /**
     * @var MageQuoteRepository
     */
    private MageQuoteRepository $mageQuoteRepository;
    /**
     * @var QuoteMethodHandler
     */
    private QuoteMethodHandler $quoteMethodHandler;

    /**
     * @param WorkflowProvider $workflowProvider
     * @param MageQuoteRepository $mageQuoteRepository
     * @param QuoteMethodHandler $quoteMethodHandler
     * @codeCoverageIgnore
     */
    public function __construct(
        WorkflowProvider $workflowProvider,
        MageQuoteRepository $mageQuoteRepository,
        QuoteMethodHandler $quoteMethodHandler
    ) {
        $this->workflowProvider = $workflowProvider;
        $this->mageQuoteRepository = $mageQuoteRepository;
        $this->quoteMethodHandler = $quoteMethodHandler;
    }

    /**
     * Updating the quote shipping method
     *
     * @param DataObject $request
     * @param string $klarnaOrderId
     * @throws KlarnaException
     */
    public function updateQuoteShippingMethod(DataObject $request, string $klarnaOrderId): void
    {
        $this->setQuoteShippingMethod($request, $klarnaOrderId);
        $this->mageQuoteRepository->save($this->workflowProvider->getMagentoQuote());
    }

    /**
     * Setting the shipping method on the quote
     *
     * @param DataObject $request
     * @param string $klarnaOrderId
     * @throws KlarnaException
     */
    public function setQuoteShippingMethod(DataObject $request, string $klarnaOrderId): void
    {
        $this->workflowProvider->setKlarnaOrderId($klarnaOrderId);
        $quote = $this->workflowProvider->getMagentoQuote();

        $shippingMethod = $this->getShippingMethodFromRequest($request, $klarnaOrderId);
        $this->quoteMethodHandler->setShippingMethod($quote, $shippingMethod);
    }

    /**
     * Getting back the selected shipping method.
     *
     * Its defined public so that it can be hooked into from other modules.
     *
     * @param DataObject $request
     * @param string $klarnaOrderId
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getShippingMethodFromRequest(DataObject $request, string $klarnaOrderId): string
    {
        $selectedOption = $request->getSelectedShippingOption();
        return $selectedOption['id'];
    }
}
