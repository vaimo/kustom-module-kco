<?php
/**
 * Copyright © Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
declare(strict_types=1);

namespace Klarna\Kco\Model\Responder;

use Klarna\Base\Model\Responder\Result;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\DataObject;
use Magento\Framework\DataObjectFactory;
use Klarna\Logger\Api\LoggerInterface;

/**
 * Handling controller specific Klarna requests and responses
 *
 * @internal
 */
class Klarna
{
    /**
     * @var Result
     */
    private $result;
    /**
     * @var DataObjectFactory
     */
    private $dataObjectFactory;
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param Result            $result
     * @param DataObjectFactory $dataObjectFactory
     * @param LoggerInterface   $logger
     * @codeCoverageIgnore
     */
    public function __construct(
        Result $result,
        DataObjectFactory $dataObjectFactory,
        LoggerInterface $logger
    ) {
        $this->result            = $result;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->logger            = $logger;
    }

    /**
     * Getting back the address error response for showing the given message in the kco iframe popup
     *
     * @param string $message
     * @param int    $responseCode
     * @return Json
     */
    public function getAddressErrorResponse(string $message, $responseCode = 400): Json
    {
        $data = [
            'error_type' => 'address_error',
            'error_text' => $message
        ];

        return $this->result->getJsonResult($responseCode, $data);
    }

    /**
     * Getting back the Klarna request body
     *
     * @param RequestInterface $request
     * @return DataObject
     */
    public function getKlarnaRequestBody(RequestInterface $request): DataObject
    {
        $this->logger->debug($request->getContent());

        return $this->dataObjectFactory->create(['data' => json_decode($request->getContent(), true)]);
    }
}
