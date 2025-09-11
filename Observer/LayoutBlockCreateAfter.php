<?php
/**
 * Copyright © Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
declare(strict_types=1);

namespace Klarna\Kco\Observer;

use Klarna\Kco\Block\Success;
use Klarna\Kco\Model\Api\Factory;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Message\ManagerInterface;
use Magento\Checkout\Block\Cart\ValidationMessages;

/**
 * @internal
 */
class LayoutBlockCreateAfter implements ObserverInterface
{
    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * @param ManagerInterface $messageManager
     * @codeCoverageIgnore
     */
    public function __construct(ManagerInterface $messageManager)
    {
        $this->messageManager = $messageManager;
    }

    /**
     * Showing error messages if there are any
     *
     * @param Observer $observer
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(Observer $observer)
    {
        $block = $observer->getData('block');
        if ($block instanceof ValidationMessages) {
            $messages = $this->messageManager->getMessages(true, Factory::ERROR_MESSAGES_KEY);
            if ($messages->getCount() > 0) {
                $block->addMessages($messages);
            }
        }
        if ($block instanceof Success) {
            /** Success $block */
            $block->prepareBlockData();
        }
    }
}
