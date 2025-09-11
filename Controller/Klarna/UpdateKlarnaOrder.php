<?php
/**
 * Copyright © Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
declare(strict_types=1);

namespace Klarna\Kco\Controller\Klarna;

use Exception;
use Klarna\Kco\Model\Responder\Ajax;
use Klarna\Kco\Model\Checkout\Kco\Initializer;
use Klarna\Logger\Api\LoggerInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultInterface;

/**
 * Sending a Klarna order update request for the current state of the quote
 *
 * @api
 */
class UpdateKlarnaOrder implements HttpPostActionInterface
{
    /**
     * @var ManagerInterface
     */
    private $manager;
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var Initializer
     */
    private $initializer;
    /**
     * @var Ajax
     */
    private $ajax;

    /**
     * @param LoggerInterface     $logger
     * @param Initializer         $initializer
     * @param ManagerInterface    $manager
     * @param Ajax                $ajax
     * @codeCoverageIgnore
     */
    public function __construct(
        LoggerInterface $logger,
        Initializer $initializer,
        ManagerInterface $manager,
        Ajax $ajax
    ) {
        $this->logger         = $logger;
        $this->initializer    = $initializer;
        $this->manager        = $manager;
        $this->ajax           = $ajax;
    }

    /**
     * Updating the Klarna order
     *
     * @return Json|ResultInterface
     */
    public function execute()
    {
        try {
            $this->initializer->updateKlarnaTotals();
            return $this->ajax->getSummaryResponse();
        } catch (Exception $e) {
            $this->logger->critical($e);
            $this->manager->addErrorMessage($e->getMessage());

            return $this->ajax->getSummaryResponse([
                'error' => true
            ]);
        }
    }
}
