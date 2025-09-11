<?php
/**
 * Copyright © Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
declare(strict_types=1);

namespace Klarna\Kco\Controller\Klarna;

use Klarna\Kco\Model\Checkout\Url;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;

/**
 * Our failure controller which is used when the merchant did not indicated a own failure url.
 *
 * @api
 */
class Failure implements HttpGetActionInterface
{
    /**
     * @var Url
     */
    private $url;
    /**
     * @var RedirectFactory
     */
    private RedirectFactory $redirectFactory;

    /**
     * @param Url $url
     * @param RedirectFactory $redirectFactory
     * @codeCoverageIgnore
     */
    public function __construct(
        Url $url,
        RedirectFactory $redirectFactory
    ) {
        $this->url = $url;
        $this->redirectFactory = $redirectFactory;
    }

    /**
     * Performing the failure action
     *
     * @return Redirect
     */
    public function execute()
    {
        return $this->redirectFactory->create()->setUrl($this->url->getFailureUrl());
    }
}
