<?php
/**
 * Copyright © Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */

namespace Klarna\Kco\Test\Unit\Model;

use Klarna\Base\Test\Unit\Mock\TestCase;
use Klarna\Kco\Model\Quote as KlarnaQuote;
use Klarna\Kco\Model\ResourceModel\Quote as QuoteResource;
use Klarna\Kco\Model\ResourceModel\Quote\Collection as QuoteCollection;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\Quote;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @coversDefaultClass \Klarna\Kco\Model\QuoteRepository
 */
class QuoteRepositoryTest extends TestCase
{
    /**
     * @var \Klarna\Kco\Api\QuoteRepositoryInterface
     */
    private $model;

    /**
     * @var KlarnaQuote|\PHPUnit_Framework_MockObject_MockObject
     */
    private $klarnaQuoteMock;

    /**
     * @var Quote|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mageQuoteMock;

    /**
     * @var QuoteResource|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteResourceMock;

    /**
     * @var QuoteCollection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteCollectionMock;

    /**
     * @var DataObject|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dataObjectMock;

    /**
     * @covers ::getByCheckoutId()
     * @covers ::getIdByCheckoutId()
     * @covers ::loadQuote()
     */
    public function testGetByCheckoutIdResourceModelReturnsQuoteId()
    {
        $checkoutId = '752a7690-b999-5d5b-bcd6-3f198defa1d4';
        $quoteId = '1';

        $this->dependencyMocks['quoteFactory']->expects(static::once())
            ->method('create')
            ->willReturn($this->klarnaQuoteMock);
        $this->dependencyMocks['resourceModel']->expects(static::once())
            ->method('load')
            ->with($this->klarnaQuoteMock, $checkoutId, 'klarna_checkout_id');
        $this->klarnaQuoteMock->expects(static::once())
            ->method('getId')
            ->willReturn((int)$quoteId);


        static::assertEquals($this->klarnaQuoteMock, $this->model->getByCheckoutId($checkoutId));
    }

    /**
     * @covers ::getByCheckoutId()
     * @covers ::loadQuote()
     */
    public function testGetByCheckoutIdResourceModelDoesNotReturnQuoteId()
    {
        $checkoutId = '752a7690-b999-5d5b-bcd6-3f198defa1d4';

        $this->dependencyMocks['quoteFactory']->expects(static::once())
            ->method('create')
            ->willReturn($this->klarnaQuoteMock);
        $this->dependencyMocks['resourceModel']->expects(static::once())
            ->method('load')
            ->with($this->klarnaQuoteMock, $checkoutId, 'klarna_checkout_id');
        $this->klarnaQuoteMock->expects(static::once())
            ->method('getId')
            ->willReturn(false);

        $this->expectException(NoSuchEntityException::class);
        $this->expectExceptionMessage("No such entity with klarna_checkout_id = $checkoutId");
        $this->model->getByCheckoutId($checkoutId);
    }

    /**
     * @covers ::getActiveByQuote()
     * @covers ::loadQuote()
     */
    public function testGetActiveByQuoteWithException()
    {
        $mageQuoteId = 1;
        $this->mageQuoteMock->expects(static::exactly(2))
            ->method('getId')
            ->willReturn($mageQuoteId);
        $this->quoteCollectionMock->expects(static::once())
            ->method('setOrder')
            ->willReturn($this->quoteCollectionMock);
        $this->quoteCollectionMock->expects(static::exactly(2))
            ->method('addFieldToFilter')
            ->willReturnCallback(fn($field, $condition) =>
                match([$field, $condition]) {
                    ['is_active', 1] => $this->quoteCollectionMock,
                    ['quote_id', $mageQuoteId] => $this->quoteCollectionMock
                }
            );
        $this->quoteCollectionMock->expects(static::once())
            ->method('setPageSize')
            ->with(1)
            ->willReturn($this->quoteCollectionMock);
        $this->quoteCollectionMock->expects(static::once())
            ->method('getFirstItem')
            ->willReturn($this->quoteCollectionMock);
        $this->quoteCollectionMock->expects(static::once())
            ->method('getData')
            ->with('kco_quote_id')
            ->willReturn(null);

        $this->expectException(NoSuchEntityException::class);
        $this->expectExceptionMessage("No such entity with quote_id = ");
        $this->model->getActiveByQuote($this->mageQuoteMock);
    }

    /**
     * @covers ::getActiveByQuote()
     * @covers ::getIdByActiveQuote()
     */
    public function testGetActiveByQuote()
    {
        $klarnaQuoteId = '14';

        $this->quoteCollectionMock->expects(static::once())
            ->method('setOrder')
            ->with('kco_quote_id', 'desc')
            ->willReturn($this->quoteCollectionMock);
        $this->quoteCollectionMock->expects(static::exactly(2))
            ->method('addFieldToFilter')
            ->willReturnCallback(fn($field, $condition) =>
                match([$field, $condition]) {
                    ['is_active', 1] => $this->quoteCollectionMock,
                    ['quote_id', $this->mageQuoteMock->getId()] => $this->quoteCollectionMock
                }
            );
        $this->quoteCollectionMock->expects(static::once())
            ->method('setPageSize')
            ->with(1)
            ->willReturn($this->quoteCollectionMock);
        $this->quoteCollectionMock->expects(static::once())
            ->method('getFirstItem')
            ->willReturn($this->quoteCollectionMock);
        $this->quoteCollectionMock->expects(static::once())
            ->method('getData')
            ->willReturn($klarnaQuoteId);

        $this->dependencyMocks['quoteFactory']->expects(static::once())
            ->method('create')
            ->willReturn($this->klarnaQuoteMock);
        $this->dependencyMocks['resourceModel']->expects(static::once())
            ->method('load')
            ->with($this->klarnaQuoteMock, $klarnaQuoteId, 'kco_quote_id');
        $this->klarnaQuoteMock->expects(static::exactly(2))
            ->method('getId')
            ->willReturn($klarnaQuoteId);

        $klarnaQuote = $this->model->getActiveByQuote($this->mageQuoteMock);

        static::assertEquals($klarnaQuote->getId(), $klarnaQuoteId);
    }

    /**
     * @covers ::save()
     */
    public function testSave()
    {
        $this->dependencyMocks['resourceModel']->expects(static::once())
            ->method('save')
            ->with($this->klarnaQuoteMock)
            ->willReturn($this->quoteResourceMock);

        static::assertEquals($this->quoteResourceMock, $this->model->save($this->klarnaQuoteMock));
    }

    /**
     * @covers ::save()
     */
    public function testSaveWithException()
    {
        $this->expectException(\Magento\Framework\Exception\CouldNotSaveException::class);
        $this->expectExceptionMessage("No such entity with kco_quote_id = ");
        $exceptionMessage = 'No such entity with kco_quote_id = ';
        $this->dependencyMocks['resourceModel']->expects(static::once())
            ->method('save')
            ->with($this->klarnaQuoteMock)
            ->willThrowException(new \Exception($exceptionMessage));

        $this->model->save($this->klarnaQuoteMock);
    }

    /**
     * @covers ::getIdByCheckoutId()
     */
    public function testGetIdByCheckoutId()
    {
        $checkoutId = '752a7690-b999-5d5b-bcd6-3f198defa1d4';
        $expected = 1;

        $this->dependencyMocks['resourceModel']->expects(static::once())
            ->method('load')
            ->with($this->klarnaQuoteMock, $checkoutId, 'klarna_checkout_id');
        $this->klarnaQuoteMock->expects(static::once())
            ->method('getId')
            ->willReturn($expected);

        static::assertEquals($this->klarnaQuoteMock, $this->model->getIdByCheckoutId($checkoutId, $this->klarnaQuoteMock));
    }

    /**
     * @covers ::getIdByCheckoutId()
     */
    public function testGetIdByCheckoutIdReturnsFalse()
    {
        $checkoutId = '752a7690-b999-5d5b-bcd6-3f198defa1d4';

        static::expectException(NoSuchEntityException::class);
        static::expectExceptionMessage("No such entity with klarna_checkout_id = 752a7690-b999-5d5b-bcd6-3f198defa1d4");

        $this->model->getIdByCheckoutId($checkoutId, $this->klarnaQuoteMock);
    }

    /**
     * @covers ::getIdByActiveQuote()
     */
    public function testGetIdByActiveQuote()
    {
        $quoteId = 1;
        $data = [
            'kco_quote_id' => '1',
            'is_active' => 1,
            'quote_id' => $quoteId
        ];
        $this->mageQuoteMock->expects(static::once())
            ->method('getId')
            ->willReturn($quoteId);
        $this->quoteCollectionMock->expects(static::once())
            ->method('setOrder')
            ->with('kco_quote_id', 'desc')
            ->willReturn($this->quoteCollectionMock);
        $this->quoteCollectionMock->expects(static::exactly(2))
            ->method('addFieldToFilter')
            ->willReturnCallback(fn($field, $condition) =>
                match([$field, $condition]) {
                    ['is_active', 1] => $this->quoteCollectionMock,
                    ['quote_id', $quoteId] => $this->quoteCollectionMock
                }
            );
        $this->quoteCollectionMock->expects(static::once())
            ->method('setPageSize')
            ->with(1)
            ->willReturn($this->quoteCollectionMock);
        $this->quoteCollectionMock->expects(static::once())
            ->method('getFirstItem')
            ->willReturn($this->dataObjectMock);
        $this->dataObjectMock->expects(static::once())
            ->method('getData')
            ->with('kco_quote_id')
            ->willReturn($data['kco_quote_id']);

        static::assertEquals($data['kco_quote_id'], $this->model->getIdByActiveQuote($this->mageQuoteMock));
    }

    /**
     * @covers ::getIdByActiveQuote()
     */
    public function testGetIdByActiveQuoteReturnsNull()
    {
        $quoteId = 1;
        $expected = null;

        $this->mageQuoteMock->expects(static::once())
            ->method('getId')
            ->willReturn($quoteId);
        $this->quoteCollectionMock->expects(static::once())
            ->method('setOrder')
            ->with('kco_quote_id', 'desc')
            ->willReturn($this->quoteCollectionMock);
        $this->quoteCollectionMock->expects(static::exactly(2))
            ->method('addFieldToFilter')
            ->willReturnCallback(fn($field, $condition) =>
                match([$field, $condition]) {
                    ['is_active', 1] => $this->quoteCollectionMock,
                    ['quote_id', $quoteId] => $this->quoteCollectionMock
                }
            );
        $this->quoteCollectionMock->expects(static::once())
            ->method('setPageSize')
            ->with(1)
            ->willReturn($this->quoteCollectionMock);
        $this->quoteCollectionMock->expects(static::once())
            ->method('getFirstItem')
            ->willReturn($this->dataObjectMock);
        $this->dataObjectMock->expects(static::once())
            ->method('getData')
            ->willReturn($expected);

        static::assertEquals($expected, $this->model->getIdByActiveQuote($this->mageQuoteMock));
    }

    /**
     * @covers ::getQuotes()
     */
    public function testGetQuotes()
    {
        $pageSize = 1;
        $order = 'DESC';

        $expected = [
            [
                'kco_quote_id' => '1',
                'is_active' => 0,
                'quote_id' => 1
            ],
            [
                'kco_quote_id' => '2',
                'is_active' => 1,
                'quote_id' => 2
            ],
            [
                'kco_quote_id' => '2',
                'is_active' => 0,
                'quote_id' => 2
            ],
        ];

        $this->quoteCollectionMock->expects(static::once())
            ->method('setPageSize')
            ->with($pageSize)
            ->willReturn($this->quoteCollectionMock);
        $this->quoteCollectionMock->expects(static::once())
            ->method('setOrder')
            ->with('quote_id', $order)
            ->willReturn($this->quoteCollectionMock);
        $this->quoteCollectionMock->expects(static::once())
            ->method('getItems')
            ->willReturn([
                $this->dataObjectMock,
                $this->dataObjectMock,
                $this->dataObjectMock
            ]);
        $this->dataObjectMock->expects(static::exactly(3))
            ->method('getData')
            ->willReturnOnConsecutiveCalls(
                $expected[0],
                $expected[1],
                $expected[2],
            );

        static::assertEquals($expected, $this->model->getQuotes($pageSize, $order));
    }

    /**
     * @covers ::getQuotes()
     */
    public function testGetQuotesFindsNoQuotes()
    {
        $pageSize = 5;
        $order = 'ASC';

        $expected = [];

        $this->quoteCollectionMock->expects(static::once())
            ->method('setPageSize')
            ->with($pageSize)
            ->willReturn($this->quoteCollectionMock);
        $this->quoteCollectionMock->expects(static::once())
            ->method('setOrder')
            ->with('quote_id', $order)
            ->willReturn($this->quoteCollectionMock);
        $this->quoteCollectionMock->expects(static::once())
            ->method('getItems')
            ->willReturn($expected);

        static::assertEquals($expected, $this->model->getQuotes($pageSize, $order));
    }

    /**
     * @covers ::getQuotes()
     */
    public function testGetQuotesOrderParameterIsInvalid()
    {
        $pageSize = 5;
        $order = 'lorem ipsum';

        static::expectException(\InvalidArgumentException::class);
        static::expectExceptionMessage('$order must be either DESC or ASC');
        $this->model->getQuotes($pageSize, $order);
    }

    /**
     * Set up
     */
    protected function setUp(): void
    {
        $this->model = $this->setUpMocks(\Klarna\Kco\Model\QuoteRepository::class, [
            QuoteFactory::class => ['create'],
            QuoteCollectionFactory::class => ['create']
        ]);

        $this->mageQuoteMock = $this->createSingleMock(Quote::class);
        $this->klarnaQuoteMock = $this->createSingleMock(KlarnaQuote::class);
        $this->quoteResourceMock = $this->createSingleMock(QuoteResource::class);
        $this->dataObjectMock = $this->createSingleMock(DataObject::class);
        $this->quoteCollectionMock = $this->createSingleMock(QuoteCollection::class,
            [
                'setOrder',
                'setPageSize',
                'addFieldToFilter',
                'getFirstItem',
                'getData',
                'getItems'
            ]
        );

        $this->dependencyMocks['quoteCollectionFactory']
            ->method('create')
            ->willReturn($this->quoteCollectionMock);
    }
}

