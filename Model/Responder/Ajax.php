<?php
/**
 * Copyright © Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
declare(strict_types=1);

namespace Klarna\Kco\Model\Responder;

use Klarna\Base\Model\Responder\Result;
use Magento\Framework\App\RequestInterface;
use Magento\Checkout\Model\Session;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\View\LayoutFactory;
use Magento\Framework\Translate\InlineInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Event\ManagerInterface;

/**
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 * @internal
 */
class Ajax
{
    /**
     * @var Result
     */
    private Result $result;
    /**
     * @var LayoutFactory
     */
    private LayoutFactory $layoutFactory;
    /**
     * @var InlineInterface
     */
    private InlineInterface $inline;

    /**
     * @param Result $result
     * @param LayoutFactory $layoutFactory
     * @param InlineInterface $inline
     * @codeCoverageIgnore
     */
    public function __construct(
        Result $result,
        LayoutFactory $layoutFactory,
        InlineInterface $inline
    ) {
        $this->result = $result;
        $this->layoutFactory = $layoutFactory;
        $this->inline = $inline;
    }

    /**
     * Get the response for the order summary
     *
     * @param array $result
     * @return ResultInterface
     * @throws LocalizedException
     */
    public function getSummaryResponse(array $result = []): ResultInterface
    {
        $result['update_section'] = [
            'name' => 'checkout_summary',
            'html' => $this->getSummaryHtml()
        ];

        return $this->result->getJsonResult(200, $result);
    }

    /**
     * Get the html of the checkout details summary
     *
     * @return string
     * @throws LocalizedException
     */
    private function getSummaryHtml(): string
    {
        $layout = $this->layoutFactory->create();
        $update = $layout->getUpdate();
        $update->load('checkout_klarna_summary');
        $layout->generateXml();
        $layout->generateElements();
        $output = $layout->getOutput();

        $this->inline->processResponseBody($output);
        return $output;
    }
}
