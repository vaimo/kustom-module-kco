<?php
/**
 * Copyright © Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
declare(strict_types=1);

namespace Klarna\Kco\Model\Payment;

use Klarna\Base\Payment\Core;
use Klarna\Kco\Model\Checkout\Configuration\SettingsProvider;
use Magento\Payment\Model\Method\Adapter;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * @internal
 */
class Kco extends Core
{
    public const METHOD_CODE = 'klarna_kco';

    /**
     * @var SettingsProvider
     */
    private SettingsProvider $config;
    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;

    /**
     * @param Adapter $adapter
     * @param SettingsProvider $config
     * @param StoreManagerInterface $storeManager
     * @codeCoverageIgnore
     */
    public function __construct(Adapter $adapter, SettingsProvider $config, StoreManagerInterface $storeManager)
    {
        parent::__construct($adapter);

        $this->config = $config;
        $this->storeManager = $storeManager;
    }

    /**
     * @inheritdoc
     */
    public function isActive($storeId = null): bool
    {
        $store = $this->storeManager->getStore($storeId);
        return $this->config->isKcoEnabled($store);
    }

    /**
     * @inheritdoc
     */
    public function getCode(): string
    {
        return $this->adapter->getCode();
    }

    /**
     * @inheritdoc
     */
    public function isAvailable(?CartInterface $quote = null): bool
    {
        $store = $quote->getStore();
        if (!$this->config->isKcoEnabled($store)) {
            return $this->adapter->isAvailable($quote);
        }
        return true;
    }
}
