<?php
/**
 * Copyright © Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
declare(strict_types=1);

namespace Klarna\Kco\Model\Order\Shop;

use Klarna\Base\Exception as KlarnaException;
use Klarna\Kco\Model\Checkout\Checkbox\Dispatcher;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Sales\Model\Order;
use Klarna\Logger\Api\LoggerInterface;
use Klarna\Kco\Api\QuoteInterface;
use Klarna\Base\Api\OrderInterface;
use Magento\Framework\DataObject;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @internal
 */
class Action
{
    /**
     * @var Placement
     */
    private Placement $placement;
    /**
     * @var Dispatcher
     */
    private Dispatcher $dispatcher;
    /**
     * @var Validator
     */
    private Validator $validator;
    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @param Placement $placement
     * @param Dispatcher $dispatcher
     * @param Validator $validator
     * @param LoggerInterface $logger
     * @codeCoverageIgnore
     */
    public function __construct(
        Placement $placement,
        Dispatcher $dispatcher,
        Validator $validator,
        LoggerInterface $logger
    ) {
        $this->placement = $placement;
        $this->dispatcher = $dispatcher;
        $this->validator = $validator;
        $this->logger = $logger;
    }

    /**
     * Create order based on checkout type. Create customer if necessary.
     *
     * @param CartInterface $magentoQuote
     * @param QuoteInterface $klarnaQuote
     * @param OrderInterface $klarnaOrder
     * @param DataObject $klarnaResponse
     *
     * @throws KlarnaException&\Throwable
     * @throws RuntimeException&\Throwable
     * @throws InvalidArgumentException&\Throwable
     * @throws LocalizedException&\Throwable
     */
    public function createOrder(
        CartInterface $magentoQuote,
        QuoteInterface $klarnaQuote,
        OrderInterface $klarnaOrder,
        DataObject $klarnaResponse
    ): Order {
        $this->validator->checkIfPlaceable($magentoQuote);

        $order = $this->placement->placeOrder($magentoQuote, $klarnaQuote, $klarnaOrder);

        $this->dispatcher->dispatchMerchantCheckboxMethod(
            [
                'quote' => $magentoQuote,
                'order' => $order,
                'klarna_quote' => $klarnaQuote,
                'checked' => (bool)$klarnaResponse->getData('merchant_requested/additional_checkbox')
            ],
            $magentoQuote->getStore()
        );
        $this->dispatcher->dispatchMultipleCheckboxesEvent($klarnaResponse, $order, $magentoQuote, $klarnaQuote);

        return $order;
    }
}
