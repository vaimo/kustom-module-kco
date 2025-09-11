<?php
/**
 * Copyright © Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
declare(strict_types=1);

namespace Klarna\Kco\Model\Order\Shop;

use Klarna\Base\Api\OrderInterface;
use Klarna\Base\Api\OrderRepositoryInterface;
use Klarna\AdminSettings\Model\Configurations\Api;
use Klarna\Kco\Api\QuoteInterface;
use Klarna\AdminSettings\Model\Configurations\Kco\Checkout;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Sales\Api\OrderRepositoryInterface as MagentoOrderRepositoryInterface;
use Magento\Sales\Api\Data\OrderInterface as MagentoOrderInterface;

/**
 * @internal
 */
class Placement
{
    /**
     * @var CartManagementInterface
     */
    private CartManagementInterface $cartManagement;
    /**
     * @var OrderRepositoryInterface
     */
    private OrderRepositoryInterface $klarnaOrderRepository;
    /**
     * @var MagentoOrderRepositoryInterface
     */
    private MagentoOrderRepositoryInterface $magentoOrderRepository;
    /**
     * @var Checkout
     */
    private Checkout $checkoutConfiguration;
    /**
     * @var Api
     */
    private Api $apiConfiguration;

    /**
     * @param CartManagementInterface $cartManagement
     * @param OrderRepositoryInterface $klarnaOrderRepository
     * @param MagentoOrderRepositoryInterface $magentoOrderRepository
     * @param Checkout $checkoutConfiguration
     * @param Api $apiConfiguration
     * @codeCoverageIgnore
     */
    public function __construct(
        CartManagementInterface $cartManagement,
        OrderRepositoryInterface $klarnaOrderRepository,
        MagentoOrderRepositoryInterface $magentoOrderRepository,
        Checkout $checkoutConfiguration,
        Api $apiConfiguration
    ) {
        $this->cartManagement = $cartManagement;
        $this->klarnaOrderRepository = $klarnaOrderRepository;
        $this->magentoOrderRepository = $magentoOrderRepository;
        $this->checkoutConfiguration = $checkoutConfiguration;
        $this->apiConfiguration = $apiConfiguration;
    }

    /**
     * Placing the order
     *
     * @param CartInterface $quote
     * @param QuoteInterface $klarnaQuote
     * @param OrderInterface $klarnaOrder
     * @return MagentoOrderInterface
     */
    public function placeOrder(
        CartInterface $quote,
        QuoteInterface $klarnaQuote,
        OrderInterface $klarnaOrder
    ): MagentoOrderInterface {
        $magentoOrderId = $this->cartManagement->placeOrder($quote->getId());

        $klarnaOrder->setOrderId($magentoOrderId);
        $klarnaOrder->setKlarnaOrderId($klarnaQuote->getKlarnaCheckoutId());
        $klarnaOrder->setUsedMid(
            $this->apiConfiguration->getUserName(
                $quote->getStore(),
                $quote->getStore()->getCurrentCurrency()->getCode()
            )
        );
        $klarnaOrder->setIsB2b($this->checkoutConfiguration->isB2bEnabled($quote->getStore()));

        $this->klarnaOrderRepository->save($klarnaOrder);
        return $this->magentoOrderRepository->get($magentoOrderId);
    }
}
