<?php
/**
 * Copyright © Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
declare(strict_types=1);

namespace Klarna\Kco\Plugin\Model;

use Klarna\Kco\Model\Payment\Kco;
use Klarna\Kco\Model\Checkout\Configuration\SettingsProvider;
use Magento\Payment\Model\Method\Free;
use Magento\Payment\Model\MethodList;
use Magento\Quote\Api\Data\CartInterface;

/**
 * @internal
 */
class MethodListPlugin
{
    /**
     * @var SettingsProvider
     */
    private $config;

    /**
     * @param SettingsProvider $config
     * @codeCoverageIgnore
     */
    public function __construct(SettingsProvider $config)
    {
        $this->config = $config;
    }

    /**
     * Removing the payment method "free" or return the unchanged result.
     *
     * The payment method "free" will be returned when the grand total (for example after a coupon) is zero.
     * The original issue leads to problems and a Magento error message was shown which could confuse the customer.
     *
     * @param MethodList $methodList
     * @param array $result
     * @param CartInterface|null $quote
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetAvailableMethods(
        MethodList $methodList,
        array $result,
        ?CartInterface $quote = null
    ) {
        if ($quote->getGrandTotal() > 0 ||
            !$quote->hasItems() ||
            !$this->config->isKcoEnabled($quote->getStore()) ||
            $quote->getPayment()->getMethod() !== Kco::METHOD_CODE
        ) {
            return $result;
        }

        foreach ($result as $key => $value) {
            if ($value instanceof Free) {
                unset($result[$key]);
                break;
            }
        }

        return $result;
    }
}
