<?php
/**
 * Copyright © Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */

namespace Klarna\Kco\Api;

use Klarna\Base\Model\Api\Exception as KlarnaApiException;

/**
 * @api
 */
interface KasperInterface
{
    /**
     * Get Klarna order details
     *
     * @param string $id
     * @param string $currency
     * @return array
     */
    public function getOrder(string $id, string $currency): array;

    /**
     * Create new order
     *
     * @param array $data
     * @param string $currency
     * @return array
     */
    public function createOrder(array $data, string $currency): array;

    /**
     * Update Klarna order
     *
     * @param string $id
     * @param array $data
     * @param string $currency
     * @return array
     * @throws KlarnaApiException
     */
    public function updateOrder(string $id, array $data, string $currency): array;
}
