<?php
/**
 * Copyright © Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
declare(strict_types=1);

namespace Klarna\Kco\Controller\Api;

use Klarna\Base\Api\ServiceInterface;
use Klarna\Kco\Api\ApiInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Controller\ResultFactory;
use Klarna\Logger\Api\LoggerInterface;
use Klarna\Logger\Model\Api\Logger;
use Klarna\Logger\Model\Api\Container;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\RequestInterface;

/**
 * This controller is used for callbacks which are indicated in the Klarna order request but not used
 *
 * @SuppressWarnings(PHPMD.AllPurposeAction)
 * @api
 */
class Disabled implements HttpPostActionInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var Logger
     */
    private $apiLogger;
    /**
     * @var Container
     */
    private $container;
    /**
     * @var RequestInterface
     */
    private RequestInterface $request;
    /**
     * @var ResultFactory
     */
    private ResultFactory $resultFactory;

    /**
     * @param LoggerInterface $logger
     * @param Logger $apiLogger
     * @param Container $container
     * @param RequestInterface $request
     * @param ResultFactory $resultFactory
     * @codeCoverageIgnore
     */
    public function __construct(
        LoggerInterface $logger,
        Logger $apiLogger,
        Container $container,
        RequestInterface $request,
        ResultFactory $resultFactory
    ) {
        $this->logger = $logger;
        $this->apiLogger = $apiLogger;
        $this->container = $container;
        $this->request = $request;
        $this->resultFactory = $resultFactory;
    }

    /**
     * Returning 200 http code
     *
     * @return ResultInterface
     */
    public function execute()
    {
        $this->logger->info('Disabled: returning a 200 http code');

        $this->container->setService(ServiceInterface::SERVICE_KCO);
        $this->apiLogger->logCallback($this->container, ApiInterface::ACTIONS['disabled'], $this->request, []);

        return $this->resultFactory->create(ResultFactory::TYPE_RAW)
            ->setContents("")
            ->setHttpResponseCode(200);
    }
}
