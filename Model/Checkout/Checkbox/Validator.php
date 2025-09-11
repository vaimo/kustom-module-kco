<?php
/**
 * Copyright © Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
declare(strict_types=1);

namespace Klarna\Kco\Model\Checkout\Checkbox;

use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\DataObjectFactory;
use Klarna\Kco\Helper\KlarnaConfig as KcoKlarnaConfig;

/**
 * @internal
 */
class Validator
{
    /**
     * @var ManagerInterface
     */
    private ManagerInterface $eventManager;
    /**
     * @var DataObjectFactory
     */
    private DataObjectFactory $dataObjectFactory;
    /**
     * @var KcoKlarnaConfig
     */
    private KcoKlarnaConfig $klarnaConfig;

    /**
     * @param ManagerInterface $eventManager
     * @param DataObjectFactory $dataObjectFactory
     * @param KcoKlarnaConfig $klarnaConfig
     * @codeCoverageIgnore
     */
    public function __construct(
        ManagerInterface $eventManager,
        DataObjectFactory $dataObjectFactory,
        KcoKlarnaConfig $klarnaConfig
    ) {
        $this->eventManager = $eventManager;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->klarnaConfig = $klarnaConfig;
    }

    /**
     * Returns true if merchant checkbox is enabled
     *
     * @param string $code
     * @param array  $args
     *
     * @return bool
     */
    public function isMerchantCheckboxEnabled(string $code, array $args): bool
    {
        $observer = $this->dataObjectFactory->create();
        $observer->setEnabled(true);
        $args['state'] = $observer;
        $methodConfig = $this->klarnaConfig->getMerchantCheckboxMethodConfig($code);
        $this->eventManager->dispatch(
            sprintf('kco_%s', $methodConfig->getValidationEvent()),
            $args
        );
        return $observer->getEnabled();
    }
}
