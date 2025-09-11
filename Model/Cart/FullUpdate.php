<?php
/**
 * Copyright © Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
declare(strict_types=1);

namespace Klarna\Kco\Model\Cart;

use Klarna\Base\Model\Quote\Address\Handler;
use Klarna\Base\Model\Quote\Customer;
use Klarna\Kco\Model\Checkout\Company\Update;
use Klarna\Base\Model\Quote\ShippingMethod\SelectionAssurance;
use Magento\Quote\Model\QuoteRepository;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Framework\DataObject;

/**
 * @internal
 */
class FullUpdate
{
    /**
     * @var Customer
     */
    private Customer $customer;
    /**
     * @var Handler
     */
    private Handler $handler;
    /**
     * @var SelectionAssurance
     */
    private SelectionAssurance $selectionAssurance;
    /**
     * @var CartRepositoryInterface
     */
    private CartRepositoryInterface $cartRepository;
    /**
     * @var Update
     */
    private Update $companyIdUpdate;
    /**
     * @var Payment
     */
    private Payment $payment;

    /**
     * @param Customer $customer
     * @param Handler $handler
     * @param SelectionAssurance $selectionAssurance
     * @param CartRepositoryInterface $cartRepository
     * @param Update $companyIdUpdate
     * @param Payment $payment
     * @codeCoverageIgnore
     */
    public function __construct(
        Customer $customer,
        Handler $handler,
        SelectionAssurance $selectionAssurance,
        CartRepositoryInterface $cartRepository,
        Update $companyIdUpdate,
        Payment $payment
    ) {
        $this->customer = $customer;
        $this->handler = $handler;
        $this->selectionAssurance = $selectionAssurance;
        $this->cartRepository = $cartRepository;
        $this->companyIdUpdate = $companyIdUpdate;
        $this->payment = $payment;
    }

    /**
     * Updating the whole cart based on the klarna request object
     *
     * @param DataObject $klarnaRequest
     * @param string $klarnaId Used for hooks
     * @param CartInterface $quote
     * @return CartInterface
     * @throws \Exception
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function updateByKlarnaRequestObject(
        DataObject $klarnaRequest,
        string $klarnaId,
        CartInterface $quote
    ): CartInterface {
        if (count($klarnaRequest->getBillingAddress()) === 1 || count($klarnaRequest->getShippingAddress()) === 1) {
            return $quote;
        }

        $this->payment->addToQuote($quote);
        $this->companyIdUpdate->updateCustomerBusinessIdFromRequest($klarnaRequest, $quote);
        $this->handler->setBillingAddressDataFromRequest($klarnaRequest, $quote);
        $this->handler->setShippingAddressDataFromRequest($klarnaRequest, $quote);
        $this->customer->setCustomerDataFromRequest($klarnaRequest, $quote);

        $this->selectionAssurance->ensureShippingMethodSelectedWithPreCollect($quote);

        if (!$quote->getReservedOrderId()) {
            $quote->reserveOrderId();
        }

        $quote->setTotalsCollectedFlag(false);
        $quote->collectTotals();
        $this->cartRepository->save($quote);

        return $quote;
    }
}
