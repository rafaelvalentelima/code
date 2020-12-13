<?php

/**
 * Correios
 *
 * Correios Shipping Method for Magento 2.
 *
 * @package ImaginationMedia\Correios
 * @author Igor Ludgero Miura <igor@imaginationmedia.com>
 * @copyright Copyright (c) 2017 Imagination Media (https://www.imaginationmedia.com/)
 * @license https://opensource.org/licenses/OSL-3.0.php Open Software License 3.0
 */

namespace ImaginationMedia\Correios\Model\Carrier;

use Psr\Log\LoggerInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Checkout\Model\Session;
use Magento\Backend\Model\UrlInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory;
use Magento\Quote\Model\Quote\Address\RateResult\MethodFactory;
use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Shipping\Model\Tracking\Result\StatusFactory;
use Magento\Shipping\Model\Tracking\Result\Error;
use Magento\Shipping\Model\Tracking\Result\Status;
use Magento\Shipping\Model\Tracking\Result;
use Magento\Shipping\Model\Rate\ResultFactory;
use ImaginationMedia\Correios\Helper\Data;
use ImaginationMedia\Correios\Model\CotacoesRepository;

class Correios extends AbstractCarrier implements CarrierInterface
{
    protected $_code = 'correios';
    protected $_scopeConfig;
    protected $_storeScope;
    protected $_session;
    protected $_helper;
    protected $_enabled;
    protected $_destinationPostCode;
    protected $_weight;
    protected $_url;
    protected $_login;
    protected $_password;
    protected $_defHeight;
    protected $_defWidth;
    protected $_defDepth;
    protected $_weightType;
    protected $_postingMethods;
    protected $_deleteCodes;
    protected $_ownerHands;
    protected $_receivedWarning;
    protected $_declaredValue;
    protected $_maxWeight;
    protected $_packageValue;
    protected $_cubic;
    protected $_origPostcode;
    protected $_freeShipping;
    protected $_freeMethod;
    protected $_freeShippingMessage;
    protected $_statusFactory;
    protected $_handlingFee;
    protected $_functionMode;

    //Shipping Result
    protected $_result;
    /**
     * @var Error
     */

    protected $_resultError;
    /**
     * @var Status
     */
    protected $_tracking;

    /**
     * @var CotacoesRepository
     */
    protected $_cotacoes;

    /**
     * @var UrlInterface
     */
    protected $_urlBuilder;

    /**
     * @var ResultFactory
     */
    protected $_rateResultFactory;

    /**
     * @var MethodFactory
     */
    protected $_rateMethodFactory;

    /**
     * Correios constructor.
     * @param StatusFactory $statusFactory
     * @param Error $resultError
     * @param Status $resultStatus
     * @param Result $result
     * @param Session $session
     * @param Data $helperData
     * @param ScopeConfigInterface $scopeConfig
     * @param ErrorFactory $rateErrorFactory
     * @param LoggerInterface $logger
     * @param ResultFactory $rateResultFactory
     * @param MethodFactory $rateMethodFactory
     * @param CotacoesRepository $_cotacoes
     * @param UrlInterface $urlBuilder
     * @param array $data
     */
    public function __construct(
        StatusFactory $statusFactory,
        Error $resultError,
        Status $resultStatus,
        Result $result,
        Session $session,
        Data $helperData,
        ScopeConfigInterface $scopeConfig,
        ErrorFactory $rateErrorFactory,
        LoggerInterface $logger,
        ResultFactory $rateResultFactory,
        MethodFactory $rateMethodFactory,
        CotacoesRepository $_cotacoes,
        UrlInterface $urlBuilder,
        array $data = []
    )
    {
        $this->_statusFactory = $statusFactory;
        $this->_helper = $helperData;
        $this->_rateResultFactory = $rateResultFactory;
        $this->_rateMethodFactory = $rateMethodFactory;
        $this->_session = $session;
        $this->_scopeConfig = $scopeConfig;
        $this->_storeScope = ScopeInterface::SCOPE_STORE;
        $this->_result = $result;
        $this->_resultError = $resultError;
        $this->_tracking = $resultStatus;
        $this->_cotacoes = $_cotacoes;
        $this->_urlBuilder = $urlBuilder;
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
    }

    /**
     * @return array
     */
    public function getAllowedMethods()
    {
        return ['correios' => "correios"];
    }

    /**
     * @param RateRequest $request
     * @return bool|\Magento\Framework\DataObject|null
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function collectRates(RateRequest $request)
    {
        $result = $this->_rateResultFactory->create();

        //Init Correios Shipping Values
        $this->_enabled = $this->_scopeConfig->getValue(
            "carriers/imaginationmedia_correios/active",
            $this->_storeScope
        );
        if ($this->_scopeConfig->getValue(
                'carriers/imaginationmedia_correios/webservice_url',
                $this->_storeScope
            ) != "") {
            $this->_url = $this->_scopeConfig->getValue(
                'carriers/imaginationmedia_correios/webservice_url',
                $this->_storeScope
            );
        } else {
            $this->_url = "http://ws.correios.com.br/calculador/CalcPrecoPrazo.aspx?StrRetorno=xml";
        }
        $this->_login = $this->_scopeConfig->getValue('carriers/imaginationmedia_correios/login', $this->_storeScope);
        $this->_password = $this->_scopeConfig->getValue(
            'carriers/imaginationmedia_correios/password',
            $this->_storeScope
        );

        if ((int)$this->_scopeConfig->getValue(
                'carriers/imaginationmedia_correios/default_height',
                $this->_storeScope
            ) > 0) {
            $this->_defHeight = (int)$this->_scopeConfig->getValue(
                'carriers/imaginationmedia_correios/default_height',
                $this->_storeScope
            );
        } else {
            $this->_defHeight = 2;
        }

        if ((int)$this->_scopeConfig->getValue(
                'carriers/imaginationmedia_correios/default_width',
                $this->_storeScope
            ) > 0) {
            $this->_defWidth = (int)$this->_scopeConfig->getValue(
                'carriers/imaginationmedia_correios/default_width',
                $this->_storeScope
            );
        } else {
            $this->_defWidth = 16;
        }

        if ((int)$this->_scopeConfig->getValue(
                'carriers/imaginationmedia_correios/default_depth',
                $this->_storeScope
            ) > 0) {
            $this->_defDepth = (int)$this->_scopeConfig->getValue(
                'carriers/imaginationmedia_correios/default_depth',
                $this->_storeScope
            );
        } else {
            $this->_defDepth = 11;
        }
        $this->_weightType = $this->_scopeConfig->getValue(
            'carriers/imaginationmedia_correios/weight_type',
            $this->_storeScope
        );
        $this->_postingMethods = explode(",", $this->_scopeConfig->getValue(
            'carriers/imaginationmedia_correios/posting_methods',
            $this->_storeScope
        ));
        $this->_deleteCodes = explode(",", "008,-10,16");
        if ($this->_scopeConfig->getValue('carriers/imaginationmedia_correios/owner_hands', $this->_storeScope) == 0) {
            $this->_ownerHands = 'N';
        } else {
            $this->_ownerHands = 'S';
        }

        if ($this->_scopeConfig->getValue(
                'carriers/imaginationmedia_correios/received_warning',
                $this->_storeScope
            ) == 0) {
            $this->_receivedWarning = 'N';
        } else {
            $this->_receivedWarning = 'S';
        }

        $this->_freeShippingMessage = $this->_scopeConfig->getValue(
            "carriers/imaginationmedia_correios/freeshipping_message",
            $this->_storeScope
        );
        $this->_origPostcode = $this->_scopeConfig->getValue(
            "shipping/origin/postcode",
            $this->_storeScope
        );
        $this->_declaredValue = $this->_scopeConfig->getValue(
            'carriers/imaginationmedia_correios/declared_value',
            $this->_storeScope
        );
        $this->_maxWeight = ((double)$this->_scopeConfig->getValue(
            "carriers/imaginationmedia_correios/max_weight",
            $this->_storeScope
        ));
        $this->_freeMethod = $this->_scopeConfig->getValue(
            "carriers/imaginationmedia_correios/posting_freemethod",
            $this->_storeScope
        );
        $this->_functionMode = $this->_scopeConfig->getValue(
            "carriers/imaginationmedia_correios/function_mode",
            $this->_storeScope
        );
        $this->_destinationPostCode = $this->_helper->formatZip($request->getDestPostcode());
        if (is_int($request->getPackageWeight())) {
            $this->_weight = $request->getPackageWeight();
        } else {
            $this->_weight = ceil($this->_helper->fixWeight($request->getPackageWeight()));
        }
        $this->_packageValue = $request->getBaseCurrency()->convert(
            $request->getPackageValue(),
            $request->getPackageCurrency()
        );

        $this->_handlingFee = 0;
        if ($this->_scopeConfig->getValue(
                "carriers/imaginationmedia_correios/handling_fee",
                $this->_storeScope
            ) != "") {
            if (is_numeric($this->_scopeConfig->getValue(
                "carriers/imaginationmedia_correios/handling_fee",
                $this->_storeScope
            ))) {
                $this->_handlingFee = $this->_scopeConfig->getValue(
                    "carriers/imaginationmedia_correios/handling_fee",
                    $this->_storeScope
                );
            }
        }

        if ($this->_enabled == 0) {
            $this->_helper->logMessage("Module disabled");
            return false;
        }
        if (!$this->_helper->checkCountry(
            $request,
            $this->_scopeConfig->getValue("shipping/origin/country_id", $this->_storeScope)
        )
        ) {
            $this->_helper->logMessage("Invalid Countries");
            return false;
        }
        if (!$this->_helper->checkWeightRange($request)) {
            $this->_helper->logMessage("Invalid Weight in checkWeightRange");
            return false;
        }

        if ($this->_helper->getCubicWeight($this->_session->getQuote()) == 0) {
            $this->_helper->logMessage("Invalid Weight in getCubicWeight");
            return false;
        } else {
            $this->_cubic = $this->_helper->getCubicWeight($this->_session->getQuote());
        }
        $arrayConsult = $this->generateConsultUrl($request);
        $correiosMethods = [];
        if ($this->_functionMode == 2 || $this->_functionMode == 3) {
            $correiosMethods = $this->_helper->getOnlineShippingQuotes($arrayConsult);
        }

        if ($request->getFreeShipping() == true) {
            $this->_freeShipping = true;
        } else {
            $this->_freeShipping = false;
        }
        $invalidPostcodeChars = ["-", "."];
        $postcodeNumber = str_replace($invalidPostcodeChars, "", $this->_destinationPostCode);
        //If not available online get offline
        if (($this->_functionMode == 2 && count($correiosMethods) != count($this->_postingMethods))
            || $this->_functionMode == 1) {
            $deliveryMessage = $this->_scopeConfig->getValue(
                "carriers/imaginationmedia_correios/deliverydays_message",
                $this->_storeScope
            );
            if ($deliveryMessage == "") {
                $deliveryMessage = "%s - Em média %d dia(s)";
            }
            $showDeliveryDays = $this->_scopeConfig->getValue(
                "carriers/imaginationmedia_correios/show_deliverydays",
                $this->_storeScope
            );
            $addDeliveryDays = (int)$this->_scopeConfig->getValue(
                "carriers/imaginationmedia_correios/add_deliverydays",
                $this->_storeScope
            );
            foreach ($this->_postingMethods as $method) {
                $haveToGetOffline = true;
                foreach ($correiosMethods as $onlineMethods) {
                    if ($onlineMethods["servico"] == $method && ($onlineMethods["valor"] > 0 &&
                            $onlineMethods["prazo"] > 0)) {
                        $haveToGetOffline = false;
                    }
                }
                if ($haveToGetOffline) {
                    if ($this->_cubic > 5) {
                        $correiosWeight = max($this->_weight, $this->_cubic);
                    } else {
                        $correiosWeight = $this->_weight;
                    }

                    if (is_int($correiosWeight) == false) {
                        if ($correiosWeight > 0.5) {
                            $correiosWeight = round($correiosWeight);
                        } else {
                            $correiosWeight = 0.3;
                        }
                    }
                    $cotacaoOffline = $this->_cotacoes->getCollection()
                        ->addFieldToFilter('cep_inicio', ["lteq" => $postcodeNumber])
                        ->addFieldToFilter('cep_fim', ["gteq" => $postcodeNumber])
                        ->addFilter("servico", $method)
                        ->addFilter("peso", $correiosWeight)
                        ->getFirstItem();
                    if ($cotacaoOffline) {
                        if ($cotacaoOffline->getData()) {
                            if ($cotacaoOffline->getValor() > 0) {
                                $data = [];
                                if ($showDeliveryDays == 0) {
                                    $data['servico'] = $this->_helper->getMethodName($cotacaoOffline->getServico());
                                } else {
                                    $data['servico'] = sprintf(
                                        $deliveryMessage,
                                        $this->_helper->getMethodName($cotacaoOffline->getServico()),
                                        (int)$cotacaoOffline->getPrazo() + $addDeliveryDays
                                    );
                                }
                                $data['valor'] = str_replace(
                                        ",",
                                        ".",
                                        $cotacaoOffline->getValor()
                                    ) + $this->_handlingFee;
                                $data['prazo'] = $cotacaoOffline->getPrazo() + $addDeliveryDays;
                                $data['servico_codigo'] = $cotacaoOffline->getServico();
                                $correiosMethods[] = $data;
                            }
                        }
                    }
                }
            }
        }
        foreach ($correiosMethods as $correiosMethod) {
            if ($correiosMethod["valor"] > 0 && $this->validateSameRegion($postcodeNumber, $correiosMethod['servico_codigo'])) {
                $method = $this->_rateMethodFactory->create();
                $method->setCarrier('correios');
                $method->setCarrierTitle($this->_scopeConfig->getValue(
                    'carriers/imaginationmedia_correios/name',
                    $this->_storeScope
                ));
                $method->setMethod('correios' . $correiosMethod['servico_codigo']);
                if ($this->_freeShipping == true && $correiosMethod["servico_codigo"] == $this->_freeMethod) {
                    if ($this->_freeShippingMessage != "") {
                        $method->setMethodTitle("[" . $this->_freeShippingMessage . "] " . $correiosMethod['servico']);
                    } else {
                        $method->setMethodTitle($correiosMethod['servico']);
                    }
                    $amount = 0;
                } else {
                    $amount = $correiosMethod['valor'];
                    $method->setMethodTitle($correiosMethod['servico']);
                }
                $method->setPrice($amount);
                $method->setCost($amount);
                $result->append($method);
            }
        }
        return $result;
    }

    /**
     * @return bool
     */
    public function isTrackingAvailable()
    {
        return true;
    }

    /**
     * @param $code
     * @return array
     */
    protected function _getTracking($code)
    {
        return [
            'url' => 'http://www.linkcorreios.com.br/?id=' . $code
        ];
    }

    /**
     * @param $number
     * @return mixed
     */
    public function getTrackingInfo($number)
    {
        $aux = $this->_getTracking($number);
        $tracking = $this->_statusFactory->create();
        $tracking->setCarrier($this->_code);
        $tracking->setCarrierTitle("Correios");
        $tracking->setTracking($number);
        if ($aux != false) {
            $tracking->addData($aux);
        }
        return $tracking;
    }

    /**
     * @param $request
     * @return array
     */
    protected function generateConsultUrl($request)
    {
        if (count($this->_postingMethods) > 0) {
            $arrayConsult = [];
            if ($this->_cubic > 5) {
                $correiosWeight = max($this->_weight, $this->_cubic);
            } else {
                $correiosWeight = $this->_weight;
            }

            if ($this->_login != "") {
                $url_d = $this->_url . "&nCdEmpresa=" . $this->_login . "&sDsSenha=" .
                    $this->_password . "&nCdFormato=1&nCdServico=" . implode(',', $this->_postingMethods) .
                    "&nVlComprimento=" .
                    $this->_defWidth . "&nVlAltura=" . $this->_defHeight . "&nVlLargura=" .
                    $this->_defDepth . "&sCepOrigem=" . $this->_origPostcode . "&sCdMaoPropria=" .
                    $this->_ownerHands . "&sCdAvisoRecebimento=" . $this->_receivedWarning . "&nVlPeso=" .
                    $correiosWeight . "&sCepDestino=" . $this->_destinationPostCode;
                if ($this->_declaredValue) {
                    $url_d = $url_d . "&nVlValorDeclarado=" . $request->getPackageValue();
                }
                $arrayConsult[] = $url_d;
            } else {
                foreach ($this->_postingMethods as $_method) {
                    $url_d = $this->_url . "&nCdFormato=1&nCdServico=" . $_method . "&nVlComprimento=" .
                        $this->_defWidth . "&nVlAltura=" . $this->_defHeight . "&nVlLargura=" .
                        $this->_defDepth . "&sCepOrigem=" . $this->_origPostcode . "&sCdMaoPropria=" .
                        $this->_ownerHands . "&sCdAvisoRecebimento=" . $this->_receivedWarning . "&nVlPeso=" .
                        $correiosWeight . "&sCepDestino=" . $this->_destinationPostCode;
                    if ($this->_declaredValue) {
                        $url_d = $url_d . "&nVlValorDeclarado=" . $request->getPackageValue();
                    }
                    $arrayConsult[] = $url_d;
                }
            }

            $this->_helper->logMessage(implode("\n", $arrayConsult));
            return $arrayConsult;
        }
    }

    /**
     * On PAC shipping method check if both addresses (origin and destination) are from same city
     * @param string $postCode
     * @param string $methodCode
     * @return bool
     */
    private function validateSameRegion($postCode, $methodCode)
    {
        if (in_array($methodCode, $this->_helper->getPacCodes())) {
            $originAddress = json_decode($this->_helper->makeCurlCall("https://viacep.com.br/ws/" . $this->_origPostcode . "/json/"), true);
            $destinationAddress = json_decode($this->_helper->makeCurlCall("https://viacep.com.br/ws/" . $postCode . "/json/"), true);
            if ((key_exists("erro", $destinationAddress) && (bool)$destinationAddress["erro"] === true)
                || $originAddress["localidade"] === $destinationAddress["localidade"]) {
                $this->_helper->logMessage("PAC unavailable, origin and destination in same region.");
                return false;
            } else {
                return true;
            }
        }
        return true;
    }
}
