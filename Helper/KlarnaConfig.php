<?php
/**
 * Copyright © Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
declare(strict_types=1);

namespace Klarna\Kco\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Config\DataInterface;
use Magento\Framework\DataObject;
use Magento\Framework\DataObjectFactory;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;
use Magento\Store\Api\Data\StoreInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @internal
 */
class KlarnaConfig extends AbstractHelper
{
    /**
     * @var DataInterface
     */
    private $config;
    /**
     * @var DataObjectFactory
     */
    private $dataObjectFactory;

    /**
     * @param Context                 $context
     * @param DataInterface           $config
     * @param DataObjectFactory       $dataObjectFactory
     * @codeCoverageIgnore
     */
    public function __construct(
        Context $context,
        DataInterface $config,
        DataObjectFactory $dataObjectFactory
    ) {
        parent::__construct($context);
        $this->config = $config;
        $this->dataObjectFactory = $dataObjectFactory;
    }

    /**
     * Getting back external payment methods
     *
     * @param string $code
     * @return mixed
     */
    public function getExternalPaymentOptions(string $code)
    {
        return $this->getConfig(sprintf('external_payment_methods/%s', $code));
    }

    /**
     * Get merchant checkbox method configuration details
     *
     * @param string $code
     *
     * @return DataObject
     */
    public function getMerchantCheckboxMethodConfig($code)
    {
        $options = $this->getConfig(sprintf('merchant_checkbox/%s', $code));
        if ($options === null) {
            $options = [];
        }
        if (!is_array($options)) {
            $options = [$options];
        }
        $options['code'] = $code;

        return $this->dataObjectFactory->create(['data' => $options]);
    }

    /**
     * Get Klarna config value for $key
     *
     * @param string $key
     * @return mixed
     * @throws \RuntimeException
     */
    private function getConfig(string $key)
    {
        return $this->config->get($key);
    }
}
