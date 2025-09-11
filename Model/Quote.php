<?php
/**
 * Copyright © Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
declare(strict_types=1);

namespace Klarna\Kco\Model;

use Klarna\Kco\Api\QuoteInterface;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;

/**
 * @internal
 */
class Quote extends AbstractModel implements QuoteInterface, IdentityInterface
{
    public const CACHE_TAG = 'klarna_kco_quote';

    /**
     * Get Identities
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * @inheritdoc
     */
    public function setIsActive($active)
    {
        $this->setData('is_active', $active);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getIsActive()
    {
        return $this->_getData('is_active');
    }

    /**
     * @inheritdoc
     */
    public function setKlarnaCheckoutId($checkoutId)
    {
        $this->setData('klarna_checkout_id', $checkoutId);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getKlarnaCheckoutId()
    {
        return $this->_getData('klarna_checkout_id');
    }

    /**
     * @inheritdoc
     */
    public function getQuoteId()
    {
        return $this->_getData('quote_id');
    }

    /**
     * @inheritdoc
     */
    public function setQuoteId($quoteId)
    {
        $this->setData('quote_id', $quoteId);
        return $this;
    }

    /**
     * Constructor
     *
     * @codeCoverageIgnore
     */
    protected function _construct()
    {
        $this->_init(ResourceModel\Quote::class);
    }
}
