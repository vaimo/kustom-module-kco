<?php
/**
 * Copyright © Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
declare(strict_types=1);

namespace Klarna\Kco\Controller\Adminhtml\Om;

use Klarna\Kco\Model\Order\Order as KlarnaOrder;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\UrlInterface;

/**
 * Updating the state of a order
 *
 * @SuppressWarnings(PHPMD.AllPurposeAction)
 * @api
 */
class PaymentStatus implements HttpGetActionInterface
{
    /**
     * @var KlarnaOrder
     */
    private KlarnaOrder $order;
    /**
     * @var RequestInterface
     */
    private RequestInterface $request;
    /**
     * @var RedirectFactory
     */
    private RedirectFactory $redirectFactory;
    /**
     * @var ManagerInterface
     */
    private ManagerInterface $messageManager;
    /**
     * @var UrlInterface
     */
    private UrlInterface $url;

    /**
     * @param KlarnaOrder $order
     * @param RequestInterface $request
     * @param RedirectFactory $redirectFactory
     * @param ManagerInterface $messageManager
     * @param UrlInterface $url
     * @codeCoverageIgnore
     */
    public function __construct(
        KlarnaOrder $order,
        RequestInterface $request,
        RedirectFactory $redirectFactory,
        ManagerInterface $messageManager,
        UrlInterface $url
    ) {
        $this->order = $order;
        $this->request = $request;
        $this->redirectFactory = $redirectFactory;
        $this->messageManager = $messageManager;
        $this->url = $url;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        $orderId = $this->request->getParam('id');
        $url     = $this->url->getUrl('sales/order/view/order_id/' . $orderId);

        try {
            $this->order->checkAndUpdateOrderState($orderId);
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            return $this->redirectFactory->create()->setUrl($url);
        }

        $this->messageManager->addSuccessMessage('Order updated');
        return $this->redirectFactory->create()->setUrl($url);
    }
}
