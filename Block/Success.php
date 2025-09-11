<?php
/**
 * Copyright © Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
declare(strict_types=1);

namespace Klarna\Kco\Block;

use Klarna\Base\Exception as KlarnaException;
use Klarna\Base\Model\OrderRepository;
use Klarna\Kco\Model\Api\Factory;
use Magento\Checkout\Model\Session;
use Magento\Framework\View\Element\Template\Context;

/**
 *
 * @api
 */
class Success extends \Magento\Framework\View\Element\Template
{
    /** @var Factory */
    private $factory;

    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @var OrderRepository
     */
    private $kcoOrderRepository;

    /**
     * @var string
     */
    private $klarna_success_html;

    /**
     * Success constructor.
     *
     * @param Context         $context
     * @param Session         $checkoutSession
     * @param Factory         $factory
     * @param OrderRepository $kcoOrderRepository
     * @param array           $data
     * @codeCoverageIgnore
     */
    public function __construct(
        Context $context,
        Session $checkoutSession,
        Factory $factory,
        OrderRepository $kcoOrderRepository,
        array $data
    ) {
        parent::__construct($context, $data);
        $this->checkoutSession = $checkoutSession;
        $this->kcoOrderRepository = $kcoOrderRepository;
        $this->factory = $factory;
    }

    /**
     * Getting back the Klarna success html
     *
     * @return string
     */
    public function getKlarnaSuccessHtml()
    {
        return $this->klarna_success_html;
    }

    /**
     * Setting the Klarna success html
     *
     * @param string $html
     */
    public function setKlarnaSuccessHtml(string $html)
    {
        $this->klarna_success_html = $html;
    }

    /**
     * Get last order ID from session, fetch it and check whether it can be viewed, printed etc
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function prepareBlockData()
    {
        $order = $this->checkoutSession->getLastRealOrder();

        $klarnaOrder = $this->kcoOrderRepository->getByOrder($order);
        if ($klarnaOrder->getId()) {
            try {
                $api = $this->factory->createApiInstance($order->getStore());
                $api->retrieveOrder($order->getOrderCurrencyCode(), $klarnaOrder->getKlarnaOrderId());
                $html = $api->getKlarnaCheckoutGui();
            } catch (KlarnaException $e) {
                $html = $e->getMessage();
            }

            $this->setKlarnaSuccessHtml($html);
        }
    }
}
