<?php
/**
 * Copyright © Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
declare(strict_types=1);

namespace Klarna\Kco\Model\Carrier;

use Klarna\Kco\Model\Checkout\Kco\Session;
use Klarna\Kss\Model\Carrier;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Directory\Helper\Data;
use Magento\Directory\Model\CountryFactory;
use Magento\Directory\Model\CurrencyFactory;
use Magento\Directory\Model\RegionFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Xml\Security;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory as RateErrorFactory;
use Magento\Quote\Model\Quote\Address\RateResult\MethodFactory;
use Magento\Shipping\Model\Carrier\AbstractCarrierOnline;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Shipping\Model\Rate\Result;
use Magento\Shipping\Model\Rate\ResultFactory as RateResultFactory;
use Magento\Shipping\Model\Simplexml\ElementFactory;
use Magento\Shipping\Model\Tracking\Result\ErrorFactory as TrackingErrorFactory;
use Magento\Shipping\Model\Tracking\Result\StatusFactory;
use Magento\Shipping\Model\Tracking\ResultFactory as TrackingResultFactory;
use Psr\Log\LoggerInterface;

/**
 * The Klarna Shipping Carrier for our shipping api gateway
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @internal
 */
class Klarna extends AbstractCarrierOnline implements CarrierInterface
{
    public const GATEWAY_KEY = Carrier::GATEWAY_KEY;
    /**
     * @var string $_code
     * @codingStandardsIgnoreLine
     */
    protected $_code = Carrier::CODE;
    /**
     * @var Session $klarnaSession
     */
    private $klarnaSession;
    /**
     * @var Carrier
     */
    private $carrier;

    /**
     * @param ScopeConfigInterface   $scopeConfig
     * @param RateErrorFactory       $rateErrorFactory
     * @param LoggerInterface        $logger
     * @param Security               $xmlSecurity
     * @param ElementFactory         $xmlElFactory
     * @param RateResultFactory      $rateFactory
     * @param MethodFactory          $rateMethodFactory
     * @param TrackingResultFactory  $trackFactory
     * @param TrackingErrorFactory   $trackErrorFactory
     * @param StatusFactory          $trackStatusFactory
     * @param RegionFactory          $regionFactory
     * @param CountryFactory         $countryFactory
     * @param CurrencyFactory        $currencyFactory
     * @param Data                   $directoryData
     * @param StockRegistryInterface $stockRegistry
     * @param Session                $klarnaSession
     * @param Carrier                $carrier
     * @param array                  $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * @codeCoverageIgnore
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        RateErrorFactory $rateErrorFactory,
        LoggerInterface $logger,
        Security $xmlSecurity,
        ElementFactory $xmlElFactory,
        RateResultFactory $rateFactory,
        MethodFactory $rateMethodFactory,
        TrackingResultFactory $trackFactory,
        TrackingErrorFactory $trackErrorFactory,
        StatusFactory $trackStatusFactory,
        RegionFactory $regionFactory,
        CountryFactory $countryFactory,
        CurrencyFactory $currencyFactory,
        Data $directoryData,
        StockRegistryInterface $stockRegistry,
        Session $klarnaSession,
        Carrier $carrier,
        array $data = []
    ) {
        parent::__construct(
            $scopeConfig,
            $rateErrorFactory,
            $logger,
            $xmlSecurity,
            $xmlElFactory,
            $rateFactory,
            $rateMethodFactory,
            $trackFactory,
            $trackErrorFactory,
            $trackStatusFactory,
            $regionFactory,
            $countryFactory,
            $currencyFactory,
            $directoryData,
            $stockRegistry,
            $data
        );

        $this->klarnaSession = $klarnaSession;
        $this->carrier       = $carrier;
    }

    /**
     * Collect and get rates
     *
     * @param RateRequest $request
     * @return Result
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function collectRates(RateRequest $request): Result
    {
        return $this->carrier->collectRates($request, $this->klarnaSession->getKlarnaShippingGateway());
    }

    /**
     * Return unchanged input object because we're not having a pure carrier web service
     *
     * @param DataObject $request
     * @return DataObject
     * @codeCoverageIgnore
     * @codingStandardsIgnoreLine
     */
    protected function _doShipmentRequest(DataObject $request): DataObject
    {
        return $request;
    }

    /**
     * Return empty set of shipping methods
     *
     * @return array
     */
    public function getAllowedMethods(): array
    {
        return [];
    }

    /**
     * Checking if the carrier can be used
     *
     * @param DataObject $request
     * @return self|bool
     * @throws LocalizedException
     */
    public function processAdditionalValidation(DataObject $request)
    {
        if (!$this->carrier->isValidRequest($request)) {
            return false;
        }

        $quoteItem = current($request->getAllItems());
        $quote = $quoteItem->getQuote();
        if (isset($quote)) {
            $this->klarnaSession->setQuote($quote);
        }

        if (!$this->klarnaSession->hasActiveKlarnaShippingGatewayInformation()) {
            return false;
        }

        return $this;
    }
}
