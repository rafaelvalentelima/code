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

namespace ImaginationMedia\Correios\Controller\Adminhtml\Cotacoes;

use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use ImaginationMedia\Correios\Model\ResourceModel\CotacoesFactory;
use ImaginationMedia\Correios\Helper\Data as CorreiosHelper;

class InlineEdit extends Action
{
    /**
     * @var bool|PageFactory
     */
    protected $resultPageFactory = false;
    /**
     * @var JsonFactory
     */
    protected $jsonFactory;
    /**
     * @var CorreiosHelper
     */
    protected $helper;
    /**
     * @var CotacoesFactory
     */
    protected $cotacoesFactory;
    /**
     * @var TimezoneInterface
     */
    protected $timeZoneInterface;

    const ADMIN_RESOURCE = 'ImaginationMedia_Correios::correios_menuoption1';

    /**
     * InlineEdit constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param JsonFactory $jsonFactory
     * @param CorreiosHelper $data
     * @param CotacoesFactory $cotacoesFactory
     * @param TimezoneInterface $timezone
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        JsonFactory $jsonFactory,
        CorreiosHelper $data,
        CotacoesFactory $cotacoesFactory,
        TimezoneInterface $timezone
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->jsonFactory = $jsonFactory;
        $this->helper = $data;
        $this->cotacoesFactory = $cotacoesFactory;
        $this->timeZoneInterface = $timezone;
    }

    /**
     * @return $this|\Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultJson = $this->jsonFactory->create();
        $error = false;
        $messages = [];

        $postItems = $this->getRequest()->getParam('items', []);
        if (!($this->getRequest()->getParam('isAjax') && count($postItems))) {
            return $resultJson->setData([
                'messages' => [__('Please correct the data sent.')],
                'error' => true,
            ]);
        }
        $cotacaoModel = $this->cotacoesFactory->create();

        foreach ($postItems as $item) {
            $objCotacao = $cotacaoModel->load($item["id"]);
            if ($objCotacao->getData()) {
                $objCotacao->setServico($item["servico"]);
                $objCotacao->setPrazo($item["prazo"]);
                $objCotacao->setPeso($item["peso"]);
                $objCotacao->setValor($item["valor"]);
                $objCotacao->setCepInicio($item["cep_inicio"]);
                $objCotacao->setCepFim($item["cep_fim"]);
                $currentTime = strtotime($this->timeZoneInterface
                    ->date(new \DateTime())
                    ->format('m/d/y H:i:s'));
                $objCotacao->setUltimoUpdate($currentTime);
                if ((bool)$objCotacao->save() === false) {
                    $messages[] = __("The Postcode Track %s wasn't updated. Check the logs.", $objCotacao->getId());
                    $error = true;
                }
            }
        }
        return $resultJson->setData([
            'messages' => $messages,
            'error' => $error
        ]);
    }
}
