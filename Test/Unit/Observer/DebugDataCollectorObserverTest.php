<?php
/**
 * Copyright © Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
declare(strict_types=1);

namespace Klarna\Kco\Test\Unit\Observer;

use Klarna\Base\Helper\Debug\DebugDataObject;
use Klarna\Base\Test\Unit\Mock\TestCase;
use Klarna\Kco\Model\QuoteRepository;
use Klarna\Kco\Observer\DebugDataCollectorObserver;
use Magento\Framework\Event;
use Magento\Framework\Event\Observer;

/**
 * @internal
 */
class DebugDataCollectorObserverTest extends TestCase
{
    /**
     * @var DebugDataCollectorObserver
     */
    private $debugDataCollectorObserver;

    /**
     * @var Observer
     */
    private $observer;

    /**
     * @var Event|MockObject
     */
    private $event;

    /**
     * @var DebugDataObject|MockObject
     */
    private $debugDataObject;

    /**
     * @var QuoteRepository
     */
    private $quoteRepository;

    protected function setUp(): void
    {
        $this->debugDataCollectorObserver = $this->setUpMocks(DebugDataCollectorObserver::class);
        $this->quoteRepository = $this->createSingleMock(QuoteRepository::class);
        $this->debugDataObject = $this->createSingleMock(DebugDataObject::class);
        $this->observer = $this->createSingleMock(Observer::class);
        $this->event = $this->createSingleMock(Event::class, [], ['getDebugDataObject']);
    }

    /**
     * @dataProvider executionDataProvider
     */
    public function testExecutionAddsStringifiedTableDataToDebugDataObject($data, $expected): void
    {
        $this->debugDataObject->expects(static::once())
            ->method('addData');
        $this->event->expects(static::once())
            ->method('getDebugDataObject')
            ->willReturn($this->debugDataObject);
        $this->observer->expects(static::once())
            ->method('getEvent')
            ->willReturn($this->event);

        $this->dependencyMocks['quoteRepository']->expects(static::once())
            ->method('getQuotes')
            ->with(1000, 'DESC')
            ->willReturn($data);

        $this->dependencyMocks['stringifyDbTableData']->expects(static::once())
            ->method('getStringData')
            ->with($data)
            ->willReturn($expected);

        $this->debugDataCollectorObserver->execute($this->observer);
    }

    public function executionDataProvider(): array
    {
        return [
            'empty data' => [
                [],
                '[]'
            ],
            'non-empty data' => [
                [
                    ['quote_id' => 1, 'klarna_checkout_id' => 'checkout_id_1'],
                    ['quote_id' => 2, 'klarna_checkout_id' => 'checkout_id_2'],
                ],
                '[{"quote_id":1,"klarna_checkout_id":"checkout_id_1"},{"quote_id":2,"klarna_checkout_id":"checkout_id_2"}]'
            ]
        ];
    }
}
