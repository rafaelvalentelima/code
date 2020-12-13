<?php
namespace RicardoMartins\PagSeguro\Controller\Test;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Class GetConfig
 *
 * @see       http://bit.ly/pagseguromagento Official Website
 * @author    Ricardo Martins (and others) <pagseguro-transparente@ricardomartins.net.br>
 * @copyright 2018-2019 Ricardo Martins
 * @license   https://www.gnu.org/licenses/gpl-3.0.pt-br.html GNU GPL, version 3
 * @package   RicardoMartins\PagSeguro\Controller\Test
 */
class GetConfig extends \Magento\Framework\App\Action\Action
{
    /**
     * GetConfig resultPageFactory
     * @var PageFactory
     */
    protected $resultPageFactory;

    protected $resultJsonFactory;
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * GetConfig constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \RicardoMartins\PagSeguro\Helper\Data $helper,
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    )
    {
        $this->_helper = $helper;
        $this->resultJsonFactory = $jsonFactory;
        $this->scopeConfig = $scopeConfig;
        return parent::__construct($context);
    }

    /**
     * Function execute
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();

        $tokenLen = strlen($this->_helper->getToken());
        $redirectMethod = $this->scopeConfig->getValue(
            \RicardoMartins\PagSeguro\Helper\Data::XML_PATH_PAYMENT_PAGSEGURO_TEF_ACTIVE, ScopeInterface::SCOPE_STORE
        );

        $info = array(
            'Magento Version' => substr($this->_helper->getMagentoVersion(), 0, 1),
            'RicardoMartins_PagSeguro' => array(
                'version'   => $this->_helper->getModuleInformation()['setup_version'],
                'debug'     => (boolean)$this->_helper->isDebugActive()
            ),
            'configJs'      => json_decode($this->_helper->getConfigJs()),
            'redirect'         => $redirectMethod,
            'sandbox_active' => $this->_helper->isSandbox(),
            'key_validate'  => $this->_helper->validateKey(),
            'token_consistency' => ($tokenLen == 32 || $tokenLen == 100) ? "Good" : "Token does not consist 32 or 100 characters"
        );

        if ($this->_helper->isSandbox()) {
            unset($info['token_consistency']);
        }

        $resultJson->setData($info);
        return $resultJson;
    }
}
