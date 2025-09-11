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
use Klarna\Kco\Api\QuoteRepositoryInterface;
use Klarna\Kco\Model\ResourceModel\Quote as QuoteResource;
use Klarna\Kco\Model\ResourceModel\Quote\CollectionFactory as QuoteCollectionFactory;
use Klarna\Kco\Model\ResourceModel\Quote\Collection as QuoteCollection;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\Data\CartInterface as MageQuoteInterface;

/**
 * @internal
 */
class QuoteRepository implements QuoteRepositoryInterface
{
    /**
     * @var QuoteFactory
     */
    private QuoteFactory $quoteFactory;
    /**
     * @var QuoteResource
     */
    private QuoteResource $resourceModel;
    /**
     * @var QuoteCollectionFactory
     */
    private QuoteCollectionFactory $quoteCollectionFactory;

    /**
     * @param QuoteFactory $quoteFactory
     * @param QuoteResource $resourceModel
     * @param QuoteCollectionFactory $quoteCollectionFactory
     * @codeCoverageIgnore
     */
    public function __construct(
        QuoteFactory $quoteFactory,
        QuoteResource $resourceModel,
        QuoteCollectionFactory $quoteCollectionFactory
    ) {
        $this->quoteFactory = $quoteFactory;
        $this->resourceModel = $resourceModel;
        $this->quoteCollectionFactory = $quoteCollectionFactory;
    }

    /**
     * @inheritdoc
     *
     * @throws LocalizedException|NoSuchEntityException
     */
    public function getByCheckoutId($checkoutId, $forceReload = false): QuoteInterface
    {
        $quote = $this->quoteFactory->create();
        return $this->getIdByCheckoutId($checkoutId, $quote);
    }

    /**
     * Load quote with different methods
     *
     * @param string $identifier
     * @param Quote|null $quote
     * @return QuoteInterface
     * @throws NoSuchEntityException
     */
    private function loadQuote(string $identifier, ?Quote $quote = null): QuoteInterface
    {
        if ($quote === null) {
             /** @var Quote $quote */
            $quote = $this->quoteFactory->create();
        }
        $this->resourceModel->load($quote, $identifier, 'kco_quote_id');
        if (empty($quote->getId())) {
            throw NoSuchEntityException::singleField('kco_quote_id', $identifier);
        }
        return $quote;
    }

    /**
     * @inheritdoc
     */
    public function save(QuoteInterface $quote): QuoteResource
    {
        try {
            return $this->resourceModel->save($quote);
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__($e->getMessage()));
        }
    }

    /**
     * @inheritdoc
     */
    public function getActiveByQuote(MageQuoteInterface $mageQuote): QuoteInterface
    {
        $quoteId = $this->getIdByActiveQuote($mageQuote);
        if (empty($quoteId)) {
            throw NoSuchEntityException::singleField('quote_id', $mageQuote->getId());
        }
        return $this->loadQuote($quoteId);
    }

    /**
     * Get quote identifier by checkout_id
     *
     * @param string $checkoutId
     * @param QuoteInterface $quote
     * @return QuoteInterface
     * @throws NoSuchEntityException
     */
    public function getIdByCheckoutId(string $checkoutId, QuoteInterface $quote): QuoteInterface
    {
        $this->resourceModel->load($quote, $checkoutId, 'klarna_checkout_id');
        $quoteId = $quote->getId();

        if (empty($quoteId)) {
            throw NoSuchEntityException::singleField('klarna_checkout_id', $checkoutId);
        }
        return $quote;
    }

    /**
     * Get quote identifier by active Magento quote
     *
     * @param CartInterface $mageQuote
     * @return string|null
     */
    public function getIdByActiveQuote(CartInterface $mageQuote): null|string
    {
        $collection = $this->createEmptyCollection();
        $mageQuoteId = $mageQuote->getId();
        return $collection->setOrder('kco_quote_id', 'desc')
            ->addFieldToFilter('is_active', 1)
            ->addFieldToFilter('quote_id', $mageQuoteId)
            ->setPageSize(1)
            ->getFirstItem()
            ->getData('kco_quote_id');
    }

    /**
     * Get quotes with limit and order of quote_id (DESC | ASC)
     *
     * @param int $pageSize
     * @param string $order
     * @return array[]
     */
    public function getQuotes(int $pageSize, string $order): array
    {
        $order = strtoupper($order);
        if ($order !== 'DESC' && $order !== 'ASC') {
            throw new \InvalidArgumentException('$order must be either DESC or ASC');
        }

        $data = $this->createEmptyCollection()
            ->setPageSize($pageSize)
            ->setOrder('quote_id', $order)
            ->getItems();

        $result = [];
        foreach ($data as $item) {
            $result[] = $item->getData();
        }
        return $result;
    }

    /**
     * Create empty collection
     *
     * @return QuoteCollection
     */
    private function createEmptyCollection(): QuoteCollection
    {
        return $this->quoteCollectionFactory->create();
    }
}
