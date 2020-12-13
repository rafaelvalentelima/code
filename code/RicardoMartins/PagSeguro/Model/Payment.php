<?php
namespace RicardoMartins\PagSeguro\Model;

/**
 * Class Payment
 *
 * @see       http://bit.ly/pagseguromagento Official Website
 * @author    Ricardo Martins (and others) <pagseguro-transparente@ricardomartins.net.br>
 * @copyright 2018-2019 Ricardo Martins
 * @license   https://www.gnu.org/licenses/gpl-3.0.pt-br.html GNU GPL, version 3
 * @package   RicardoMartins\PagSeguro\Model
 */
class Payment extends \Magento\Payment\Model\Method\Cc
{
    /**
     * @var string
     */
    protected $_formBlockType = \RicardoMartins\PagSeguro\Block\Form\Cc::class;

    const CODE = 'rm_pagseguro_cc';

    protected $_code = self::CODE;
    protected $_isGateway                   = true;
    protected $_canCapture                  = true;
    protected $_canCapturePartial           = true;
    protected $_canRefund                   = true;
    protected $_canRefundInvoicePartial     = true;
    protected $_countryFactory;
    protected $_minAmount = null;
    protected $_maxAmount = null;
    protected $_supportedCurrencyCodes = array('BRL');

    protected $_debugReplacePrivateDataKeys = ['number', 'exp_month', 'exp_year', 'cvc'];
    /**
     * PagSeguro Helper
     *
     * @var RicardoMartins\PagSeguro\Helper\Data;
     */ 
    protected $pagSeguroHelper;

    /**
     * PagSeguro Abstract Model
     *
     * @var RicardoMartins\PagSeguro\Model\Notifications
     */ 
    protected $pagSeguroAbModel;

    /**
     * Backend Auth Session
     *
     * @var Magento\Backend\Model\Auth\Session $adminSession
     */ 
    protected $adminSession;


    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Payment\Model\Method\Logger $logger,
        \Magento\Framework\Module\ModuleListInterface $moduleList,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        \RicardoMartins\PagSeguro\Helper\Data $pagSeguroHelper,
        \RicardoMartins\PagSeguro\Model\Notifications $pagSeguroAbModel,
        \Magento\Backend\Model\Auth\Session $adminSession,
        array $data = array()
    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger,
            $moduleList,
            $localeDate,
            null,
            null,
            $data
        );

        $this->_countryFactory = $countryFactory;

        // $this->_minAmount = 1;
        // $this->_maxAmount = 999999999; 
        $this->pagSeguroHelper = $pagSeguroHelper;  
        $this->pagSeguroAbModel = $pagSeguroAbModel; 
        $this->adminSession = $adminSession;    
    }

    public function order(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        //@TODO Review. Really necessary?
        /*@var \Magento\Sales\Model\Order $order */
        $this->pagSeguroHelper->writeLog('Inside Order');
    }

    public function authorize(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        //@TODO Review. Really necessary?
        $this->pagSeguroHelper->writeLog('Inside Auth');
    }

    /**
     * Payment capturing
     *
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @param float $amount
     * @return $this
     * @throws \Magento\Framework\Validator\Exception
     */
    public function capture(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        //$this->pagSeguroHelper->writeLog('Inside capture');
        /*@var \Magento\Sales\Model\Order $order */
        $order = $payment->getOrder();
        
        try {

            //will grab data to be send via POST to API inside $params
            $params = $this->pagSeguroHelper->getCreditCardApiCallParams($order, $payment);

            //call API
            $returnXml = $this->pagSeguroHelper->callApi($params, $payment);
            #print_r($returnXml);
            if (isset($returnXml->error)) {throw new \Magento\Framework\Exception\LocalizedException(__('The capture action is not available.')); }

            //@TODO Review
         /*   if (isset($returnXml->error)) {
                $errMsg = array();
                foreach ($returnXml->error as $error) {
                    $errMsg[] = __((string)$error->message) . '(' . $error->code . ')';
                }
                throw new \Magento\Framework\Validator\Exception('Um ou mais erros ocorreram no seu pagamento.' . PHP_EOL . implode(PHP_EOL, $errMsg));
            }*/
      /*      if (isset($returnXml->error)) {
                foreach ($returnXml->error as $error) {
                $errMsg[] = __((string)$error->message) . ' (' . $error->code . ')';
            }
                throw new \Magento\Framework\Validator\Exception('Um erro ocorreu em seu pagamento.' . PHP_EOL . implode(PHP_EOL, $errMsg));
            }*/
            /* process return result code status*/
            if ((int)$returnXml->status == 6 || (int)$returnXml->status == 7) {
                throw new \Magento\Framework\Validator\Exception('An error occurred in your payment.');
            }

            $payment->setSkipOrderProcessing(true);

            if (isset($returnXml->code)) {

                $additional = array('transaction_id'=>(string)$returnXml->code);
                if ($existing = $payment->getAdditionalInformation()) {
                    if (is_array($existing)) {
                        $additional = array_merge($additional, $existing);
                    }
                }
                $payment->setAdditionalInformation($additional);

            }
            return $this;
          //$this->pagSeguroAbModel->proccessNotificatonResult($returnXml);
        } catch (\Exception $e) {

            $this->_logger->error(__('Payment capturing error.'));
            throw new \Magento\Framework\Validator\Exception(__('Payment capturing error.'));
            return;
//             echo $this->pagSeguroHelper->getSessionVl();
  
            //return;
        }
    }

    /**
     * Payment refund
     *
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @param float $amount
     * @return $this
     * @throws \Magento\Framework\Validator\Exception
     */
    public function refund(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        // recupera a informação adicional do PagSeguro
        $info = $this->getInfoInstance();
        $transactionId = $info->getAdditionalInformation('transaction_id');

        $params = array(
            'transactionCode'   => $transactionId,
            'refundValue'       => number_format($amount, 2, '.', ''),
        );
    
        $params['token'] = $this->pagSeguroHelper->getToken();
        $params['email'] = $this->pagSeguroHelper->getMerchantEmail();

        try {
           // call API - refund
            $returnXml  = $this->pagSeguroHelper->callApi($params, $payment, 'transactions/refunds');

            if ($returnXml === null) {
                $errorMsg = $this->_getHelper()->__('Erro ao solicitar o reembolso.\n');
                throw new \Magento\Framework\Validator\Exception($errorMsg);
            }
        } catch (\Exception $e) {
            $this->logger->error(__('Payment refunding error.'));
            throw new \Magento\Framework\Validator\Exception(__('Payment refunding error.'));
        }

        $payment
            ->setTransactionId($transactionId . '-' . \Magento\Sales\Model\Order\Payment\Transaction::TYPE_REFUND)
            ->setParentTransactionId($transactionId)
            ->setIsTransactionClosed(1)
            ->setShouldCloseParentTransaction(1);

        return $this;
    }


    /**
     * Assign data to info model instance
     *
     * @param   mixed $data
     * @return  object
     */
    public function assignData(\Magento\Framework\DataObject $data)
    {
        if (!$data instanceof \Magento\Framework\DataObject) {
            $data = new \Magento\Framework\DataObject($data);
        }

        $info = $this->getInfoInstance();
        $info->setAdditionalInformation('sender_hash', $this->pagSeguroHelper->getPaymentHash('sender_hash'))
            ->setAdditionalInformation('credit_card_token', $this->pagSeguroHelper->getPaymentHash('credit_card_token'))
            ->setAdditionalInformation('credit_card_owner', $this->pagSeguroHelper->getCCOwnerData('credit_card_owner'))
            ->setCcType($this->pagSeguroHelper->getPaymentHash('cc_type'))
            ->setCcLast4(substr($data['additional_data']['cc_number'], -4))
            ->setCcExpYear($data['additional_data']['cc_exp_year'])
            ->setCcExpMonth($data['additional_data']['cc_exp_month']);

        // set cpf
        //if ($this->pagSeguroHelper->isCpfVisible()) {
            $info->setAdditionalInformation($this->getCode() . '_cpf', $this->pagSeguroHelper->getCCOwnerData('credit_card_cpf'));
        //}

        //DOB value
        if ($this->pagSeguroHelper->isDobVisible()) {
            $info->setAdditionalInformation(
                'credit_card_owner_birthdate',
                date(
                    'd/m/Y',
                    strtotime(
                        $this->pagSeguroHelper->getCCOwnerData('credit_card_birthyear').
                        '/'.
                        $this->pagSeguroHelper->getCCOwnerData('credit_card_birthmonth').
                        '/'.$this->pagSeguroHelper->getCCOwnerData('credit_card_birthday')
                    )
                )
            );
        }

        //Installments value
        if ($this->pagSeguroHelper->getInstallments('cc_installment')) {
            $installments = explode('|', $this->pagSeguroHelper->getInstallments('cc_installment'));
            if (false !== $installments && count($installments)==2) {
                $info->setAdditionalInformation('installment_quantity', (int)$installments[0]);
                $info->setAdditionalInformation('installment_value', $installments[1]);
            }
        }
        return $this;
    }

    /**
     * Determine method availability based on quote amount and config data
     *
     * @param \Magento\Quote\Api\Data\CartInterface|null $quote
     * @return bool
     */
    public function isAvailable(\Magento\Quote\Api\Data\CartInterface $quote = null)
    {   
        if($this->adminSession->getUser()){
            return false;
        }
        $isAvailable =  $this->getConfigData('active', $quote ? $quote->getStoreId() : null);
        if (empty($quote)) {
            return $isAvailable;
        }
        if ($this->getConfigData("group_restriction") == false) {
            return $isAvailable;
        }

        $currentGroupId = $quote->getCustomerGroupId();
        $customerGroups = explode(',', $this->getConfigData("customer_groups"));

        if ($isAvailable && in_array($currentGroupId, $customerGroups)) {
            return true;
        }

        return false;
    }

    /**
     * Availability for currency
     *
     * @param string $currencyCode
     * @return bool
     */
    public function canUseForCurrency($currencyCode)
    {
        if (!in_array($currencyCode, $this->_supportedCurrencyCodes)) {
            return false;
        }
        return true;
    }

    /**
     * Validate payment method information object
     *
     * @return Payment Model
     */
    public function validate()
    {
        //parent::validate();
        $missingInfo = $this->getInfoInstance();

        $senderHash = $this->pagSeguroHelper->getPaymentHash('sender_hash');
        $creditCardToken = $this->pagSeguroHelper->getPaymentHash('credit_card_token');
        
        if (!$creditCardToken || !$senderHash) {
            $missingInfo = sprintf('Token do cartão: %s', var_export($creditCardToken, true));
            $missingInfo .= sprintf('/ Sender_hash: %s', var_export($senderHash, true));
            $this->pagSeguroHelper->writeLog(
                    "Falha ao obter o token do cartao ou sender_hash.
                    Ative o modo debug e observe o console de erros do seu navegador.
                    Se esta for uma atualização via Ajax, ignore esta mensagem até a finalização do pedido.
                    $missingInfo"
                );
            throw new \Magento\Framework\Validator\Exception(
                'Falha ao processar seu pagamento. Por favor, entre em contato com nossa equipe.'
            );
        }
        return $this;
    }
}