<?php
/**
 * Copyright © Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
declare(strict_types=1);

namespace Klarna\Kco\Model\Api;

use Klarna\Kco\Api\ApiInterface;
use Klarna\Base\Exception as KlarnaException;
use Magento\Store\Api\Data\StoreInterface;
use Klarna\Kco\Model\Api\Kasper;

/**
 * @api
 */
class Factory
{
    public const ERROR_MESSAGES_KEY = 'klarna_kco_messages';

    /**
     * @var Kasper
     */
    private Kasper $kasper;

    /**
     * Construct
     *
     * @param Kasper                                    $kasper
     * @codeCoverageIgnore
     */
    public function __construct(
        Kasper $kasper
    ) {
        $this->kasper = $kasper;
    }

    /**
     * Get Api instance
     *
     * @param StoreInterface|null $store
     *
     * @return ApiInterface
     * @throws KlarnaException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function createApiInstance(?StoreInterface $store = null)
    {
        $this->kasper->setStore($store);
        return $this->kasper;
    }
}
