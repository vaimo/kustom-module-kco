<?php
/**
 * Copyright © Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
declare(strict_types=1);

namespace Klarna\Kco\Controller\Klarna;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\ResultInterface;
use Klarna\Kco\Model\Checkout\Url;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Controller\Result\RedirectFactory;

/**
 * @api
 */
class ValidateFailed implements HttpGetActionInterface
{
    /**
     * @var Url
     */
    private $url;
    /**
     * @var RequestInterface
     */
    private RequestInterface $request;
    /**
     * @var ManagerInterface
     */
    private ManagerInterface $messageManager;
    /**
     * @var RedirectFactory
     */
    private RedirectFactory $redirectFactory;

    /**
     * @param Url $url
     * @param RequestInterface $request
     * @param ManagerInterface $messageManager
     * @param RedirectFactory $redirectFactory
     * @codeCoverageIgnore
     */
    public function __construct(
        Url $url,
        RequestInterface $request,
        ManagerInterface $messageManager,
        RedirectFactory $redirectFactory
    ) {
        $this->url = $url;
        $this->request = $request;
        $this->messageManager = $messageManager;
        $this->redirectFactory = $redirectFactory;
    }

    /**
     * Redirecting the customer to the respective page after an error happened
     *
     * @return ResultInterface
     */
    public function execute()
    {
        $message = $this->request->getParam('message', '');
        $this->addErrorMessage($message);

        $redirect = $this->redirectFactory->create();
        $redirect->setStatusHeader(303, null, $message);
        return $redirect->setPath($this->url->getFailureUrl());
    }

    /**
     * Adding the error message to the message container.
     *
     * @param string $message
     */
    private function addErrorMessage(string $message): void
    {
        $this->messageManager->addErrorMessage(
            __('Unable to complete order. Please try again')
        );
        if ($message) {
            $this->messageManager->addErrorMessage($message);
        }
    }
}
