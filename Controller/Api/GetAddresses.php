<?php
/**
 * Copyright © Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
declare(strict_types=1);

namespace Klarna\Kco\Controller\Api;

use Klarna\Kco\Model\Cart\FlatAddress;
use Klarna\Kco\Model\Checkout\Kco\Session;
use Klarna\Logger\Api\LoggerInterface;
use Klarna\Base\Model\Responder\Result;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Exception\LocalizedException;
use Klarna\Base\Controller\CsrfAbstract;

/**
 * Returning the current address values of the magento quote to set them later in the respective js quote variable
 *
 * @api
 */
class GetAddresses implements HttpPostActionInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var Result
     */
    private $result;
    /**
     * @var Session
     */
    private Session $kcoSession;
    /**
     * @var FlatAddress
     */
    private FlatAddress $flatAddress;
    /**
     * @var RequestInterface
     */
    private RequestInterface $request;

    /**
     * @param LoggerInterface $logger
     * @param Result $result
     * @param Session $kcoSession
     * @param FlatAddress $flatAddress
     * @param RequestInterface $request
     * @codeCoverageIgnore
     */
    public function __construct(
        LoggerInterface $logger,
        Result $result,
        Session $kcoSession,
        FlatAddress $flatAddress,
        RequestInterface $request
    ) {
        $this->logger = $logger;
        $this->result = $result;
        $this->kcoSession = $kcoSession;
        $this->flatAddress = $flatAddress;
        $this->request = $request;
    }

    /**
     * Getting back the addresses
     *
     * @return Json
     * @throws LocalizedException
     */
    public function execute()
    {
        $this->logger->info('GetAddresses: start');

        try {
            $billingAddress  = $this->kcoSession->getQuote()->getBillingAddress();
            $shippingAddress = $this->kcoSession->getQuote()->getShippingAddress();

            $data = [
                'billing' => $this->flatAddress->getBillingAddress($billingAddress),
                'full_shipping' => $this->flatAddress->getFullShippingAddress($shippingAddress),
                'min_shipping' => $this->flatAddress->getBasicShippingInformation($shippingAddress)
            ];
            $this->logger->info('GetAddresses: final address data: ');

            $dataResult = $data;
            unset($data['billing']['region'], $data['full_shipping']['region']);

            $this->logger->info((string)json_encode($data));
        } catch (LocalizedException $e) {
            $this->logger->critical($e);
            throw $e;
        }

        return $this->getSuccessfulResult($dataResult);
    }

    /**
     * Getting back a successful result containing the input
     *
     * @param array $data
     * @return Json
     */
    private function getSuccessfulResult(array $data): Json
    {
        $this->logger->info('GetAddresses: success');
        return $this->result->getJsonResult(200, $data);
    }
}
