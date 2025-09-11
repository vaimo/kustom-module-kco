<?php
/**
 * Copyright © Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
declare(strict_types=1);

namespace Klarna\Kco\Model\Api\Rest\Service;

use Klarna\Base\Api\ServiceInterface;
use Klarna\Base\Helper\VersionInfo;
use Klarna\Kco\Api\ApiInterface;
use Klarna\Kco\Api\KasperInterface;
use Magento\Store\Model\StoreManagerInterface;
use Klarna\Base\Exception as KlarnaException;
use Klarna\AdminSettings\Model\Configurations\Api;

/**
 * @internal
 */
class Checkout implements KasperInterface
{
    public const API_VERSION = 'v3';

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var VersionInfo
     */
    private $versionInfo;
    /**
     * @var ServiceInterface
     */
    private $service;
    /**
     * @var string
     */
    private $uri;
    /**
     * @var Api
     */
    private Api $apiConfiguration;

    /**
     * Initialize class
     *
     * @param ServiceInterface      $service
     * @param VersionInfo           $versionInfo
     * @param StoreManagerInterface $storeManager
     * @param Api                   $apiConfiguration
     * @codeCoverageIgnore
     */
    public function __construct(
        ServiceInterface $service,
        VersionInfo $versionInfo,
        StoreManagerInterface $storeManager,
        Api $apiConfiguration
    ) {
        $this->service = $service;
        $this->storeManager = $storeManager;
        $this->versionInfo = $versionInfo;
        $this->apiConfiguration = $apiConfiguration;
    }

    /**
     * Performing the connection
     *
     * @param string $currency
     * @throws KlarnaException
     */
    private function connect(string $currency)
    {
        $this->setUserAgent($this->versionInfo);
        $this->service->setHeader('Accept', '*/*');

        $store = $this->storeManager->getStore();
        $this->service->connect(
            $this->apiConfiguration->getUserName($store, $currency),
            $this->apiConfiguration->getPassword($store, $currency),
            $this->apiConfiguration->getApiUrl($store, $currency)
        );
    }

    /**
     * Get Klarna order details
     *
     * @param string $id
     * @param string $currency
     * @return array
     * @throws KlarnaException
     */
    public function getOrder(string $id, string $currency): array
    {
        $this->connect($currency);

        $url = "{$this->uri}/checkout/" . self::API_VERSION . "/orders/{$id}";
        return $this->sendRequest($url, [], 'get_order', ServiceInterface::GET, $id);
    }

    /**
     * Create new order
     *
     * @param array $data
     * @param string $currency
     * @return array
     * @throws \Klarna\Base\Exception
     */
    public function createOrder(array $data, string $currency): array
    {
        $this->connect($currency);

        $url = "{$this->uri}/checkout/" . self::API_VERSION . "/orders";
        return $this->sendRequest($url, $data, 'create_order', ServiceInterface::POST);
    }

    /**
     * Update Klarna order
     *
     * @param string $id
     * @param array  $data
     * @param string $currency
     * @return array
     * @throws KlarnaException
     */
    public function updateOrder(string $id, array $data, string $currency): array
    {
        $this->connect($currency);

        $url = "{$this->uri}/checkout/" . self::API_VERSION . "/orders/{$id}";
        return $this->sendRequest($url, $data, 'update_order', ServiceInterface::POST, $id);
    }

    /**
     * Sending the request and returning the response
     *
     * @param string $url
     * @param array $data
     * @param string $actionType
     * @param string $httpMethod
     * @param string|null $id
     * @return array
     */
    private function sendRequest(
        string $url,
        array $data,
        string $actionType,
        string $httpMethod,
        ?string $id = null
    ): array {
        return $this->service->makeRequest(
            $url,
            ServiceInterface::SERVICE_KCO,
            $data,
            $httpMethod,
            $id,
            ApiInterface::ACTIONS[$actionType]
        );
    }

    /**
     * Set the service User-Agent
     *
     * @param VersionInfo $versionInfo
     * @return void
     */
    private function setUserAgent(VersionInfo $versionInfo): void
    {
        $version = sprintf(
            '%s;%s;Core/%s;OM/%s',
            $versionInfo->getVersion('Klarna_Kco'),
            $versionInfo->getVersion('Klarna_Base'),
            $versionInfo->getVersion('Klarna_Backend'),
            $versionInfo->getFullM2KlarnaVersion()
        );
        $mageInfo = $versionInfo->getMageInfo();
        $this->service->setUserAgent('Magento2_KCO', $version, $mageInfo);
    }
}
