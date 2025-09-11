<?php
/**
 * Copyright © Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
declare(strict_types=1);

namespace Klarna\Kco\Model;

use Klarna\Backend\Model\Api\Factory;
use Klarna\Backend\Model\Api\OrderManagement;
use Klarna\Base\Api\OrderInterface;
use Klarna\Kco\Model\Payment\Kco;
use Magento\Framework\DataObject;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\DataObjectFactory;

/**
 * Get the status update object from the completed Klarna order
 *
 * @internal
 */
class PaymentStatus
{
    /**
     * @var OrderManagement
     */
    private $om;
    /**
     * @var Factory
     */
    private $omFactory;
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;
    /**
     * @var DataObjectFactory
     */
    private DataObjectFactory $dataObjectFactory;

    /**
     * AddOrderDetailsOnPush constructor.
     *
     * @param Factory                  $omFactory
     * @param OrderRepositoryInterface $orderRepository
     * @param DataObjectFactory        $dataObjectFactory
     * @codeCoverageIgnore
     */
    public function __construct(
        Factory $omFactory,
        OrderRepositoryInterface $orderRepository,
        DataObjectFactory $dataObjectFactory
    ) {
        $this->omFactory         = $omFactory;
        $this->orderRepository   = $orderRepository;
        $this->dataObjectFactory = $dataObjectFactory;
    }

    /**
     * Get the status update object
     *
     * @param OrderInterface $klarnaOrder
     * @return DataObject
     */
    public function getStatusUpdate(OrderInterface $klarnaOrder): DataObject
    {
        try {
            $order = $this->orderRepository->get($klarnaOrder->getOrderId());
            $this->om = $this->omFactory->createOmApi(
                Kco::METHOD_CODE,
                $order->getOrderCurrencyCode(),
                $order->getStore()
            );
            return $this->om->getPlacedKlarnaOrder($klarnaOrder->getKlarnaOrderId());
        } catch (\Exception $e) {
            return $this->dataObjectFactory->create(
                [
                    'data' => [
                        'status'  => 'ERROR'
                    ]
                ]
            );
        }
    }
}
