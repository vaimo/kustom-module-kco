<?php
/**
 * Copyright © Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */

namespace Klarna\Kco\Api;

use Klarna\Base\Model\Api\Exception as KlarnaApiException;
use Magento\Framework\DataObject;

/**
 * Klarna api integration interface
 *
 * @api
 */
interface ApiInterface
{
    /**
     * Order statuses
     */
    public const ORDER_STATUS_AUTHORIZED    = 'AUTHORIZED';
    public const ORDER_STATUS_PART_CAPTURED = 'PART_CAPTURED';
    public const ORDER_STATUS_CAPTURED      = 'CAPTURED';
    public const ORDER_STATUS_CANCELLED     = 'CANCELLED';
    public const ORDER_STATUS_EXPIRED       = 'EXPIRED';
    public const ORDER_STATUS_CLOSED        = 'CLOSED';

    public const ACTIONS = [
        'get_order'              => 'Get Order',
        'update_order'           => 'Update Order',
        'create_order'           => 'Create Order',
        'address_update'         => 'Address Update (Callback)',
        'shipping_method_update' => 'Shipping Method Update (Callback)',
        'validate'               => 'Validate (Callback)',
        'push'                   => 'Push (Callback)',
        'disabled'               => 'Disabled (Callback)'
    ];

    /**
     * Create order if not exists and update order in the checkout API
     *
     * @return DataObject
     */
    public function createOrder();

    /**
     * Retrieve order in the checkout API
     *
     * @param string $currency
     * @param string|null $checkoutId
     * @return DataObject
     */
    public function retrieveOrder(string $currency, $checkoutId = null);

    /**
     * Update order in the checkout API
     *
     * @param string|null $checkoutId
     * @return DataObject
     */
    public function updateOrder($checkoutId = null);

    /**
     * Get Klarna Checkout Reservation Id
     *
     * @return string
     */
    public function getReservationId();

    /**
     * Get Klarna checkout order details
     *
     * @return DataObject
     */
    public function getKlarnaOrder();

    /**
     * Set Klarna checkout order details
     *
     * @param DataObject $klarnaOrder
     *
     * @return $this
     */
    public function setKlarnaOrder(DataObject $klarnaOrder);

    /**
     * Get Klarna checkout html snippets
     *
     * @return string
     */
    public function getKlarnaCheckoutGui();

    /**
     * Get generated update request
     *
     * @return array
     * @throws KlarnaApiException
     */
    public function getGeneratedUpdateRequest();
}
