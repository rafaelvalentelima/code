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

use Magento\Backend\App\Action;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Exception\LocalizedException;
use ImaginationMedia\Correios\Controller\Adminhtml\Cotacoes\PostDataProcessor;
use ImaginationMedia\Correios\Helper\Data;
use ImaginationMedia\Correios\Model\CotacoesFactory;

class Save extends Action
{
    const ADMIN_RESOURCE = 'ImaginationMedia_Correios::correios_menuoption1';
    /**
     * @var \ImaginationMedia\Correios\Controller\Adminhtml\Cotacoes\PostDataProcessor
     */
    protected $dataProcessor;
    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;
    /**
     * @var Data
     */
    protected $helper;
    /**
     * @var CotacoesFactory
     */
    protected $cotacoesFactory;

    /**
     * Save constructor.
     * @param Action\Context $context
     * @param PostDataProcessor $dataProcessor
     * @param DataPersistorInterface $dataPersistor
     * @param Data $data
     * @param CotacoesFactory $factory
     */
    public function __construct(
        Action\Context $context,
        PostDataProcessor $dataProcessor,
        DataPersistorInterface $dataPersistor,
        Data $data,
        CotacoesFactory $factory
    ) {
        $this->dataProcessor = $dataProcessor;
        $this->dataPersistor = $dataPersistor;
        $this->helper = $data;
        $this->cotacoesFactory = $factory;
        parent::__construct($context);
    }

    /**
     * @return $this|\Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {
            $data = $this->dataProcessor->filter($data);
            if (empty($data['cotacoes_id'])) {
                $data['cotacoes_id'] = null;
            }

            $model = $this->cotacoesFactory->create();

            $id = $this->getRequest()->getParam('cotacoes_id');
            if ($id) {
                $model->load($id);
            }

            $model->setCepInicio($this->helper->formatZip($data["cep_inicio"]));
            $model->setCepFim($this->helper->formatZip($data["cep_fim"]));
            $model->setValor($this->helper->formatPrice($data["valor"]));
            $model->setPeso($data["peso"]);
            $model->setPrazo($data["prazo"]);
            $model->setUltimoUpdate($data["ultimo_update"]);
            $model->setServico($data["servico"]);

            if (!$this->dataProcessor->validate($model)) {
                $this->messageManager->addErrorMessage(__('Invalid postcode data.'));
                $this->dataPersistor->clear('correios_cotacoes');
                return $resultRedirect->setPath('*/*/edit', ['cotacoes_id' => $model->getId(), '_current' => true]);
            }

            try {
                if ($model->save()) {
                    $this->messageManager->addSuccessMessage(__('You saved the new postcode track.'));
                    $this->dataPersistor->clear('correios_cotacoes');
                    if ($this->getRequest()->getParam('back')) {
                        return $resultRedirect->setPath(
                            '*/*/edit',
                            ['cotacoes_id' => $model->getId(), '_current' => true]
                        );
                    }
                } else {
                    $this->messageManager->addErrorMessage(__('Was not possible to save this new postcode track.'));
                    $this->dataPersistor->clear('correios_cotacoes');
                    return $resultRedirect->setPath('*/*/edit', ['cotacoes_id' => $model->getId(), '_current' => true]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage(
                    $e,
                    __('Something went wrong while saving the new postcode track.')
                );
            }

            $this->dataPersistor->set('correios_cotacoes', $data);
            return $resultRedirect->setPath(
                '*/*/edit',
                ['cotacoes_id' => $this->getRequest()->getParam('cotacoes_id')]
            );
        }
        return $resultRedirect->setPath('*/*/');
    }
}
