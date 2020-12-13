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

use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;
use Magento\Backend\App\Action;
use Magento\Ui\Component\MassAction\Filter;
use ImaginationMedia\Correios\Model\CotacoesRepository;

class MassDelete extends Action
{
    /**
     * @var Filter
     */
    protected $filter;
    /**
     * @var CotacoesRepository
     */
    protected $cotacao;

    const ADMIN_RESOURCE = 'ImaginationMedia_Correios::correios_menuoption1';

    /**
     * MassDelete constructor.
     * @param Context $context
     * @param Filter $filter
     * @param CotacoesRepository $cotacao
     */
    public function __construct(Context $context, Filter $filter, CotacoesRepository $cotacao)
    {
        $this->filter = $filter;
        $this->cotacao = $cotacao;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        $collection = $this->filter->getCollection($this->cotacao->getCollection());

        $sucess = 0;
        $error = 0;

        foreach ($collection as $cotacao) {
            $data = (array)$cotacao->getData();
            $id = $data["id"];
            $cotacaoObj = $this->cotacao->getById($id);
            if ($cotacaoObj->delete()) {
                $sucess++;
            } else {
                $error++;
            }
        }
        if ($error==0) {
            if ($sucess>1) {
                $this->messageManager->addSuccessMessage(
                    __('A total of %1 postcode tracks have been deleted.', $sucess)
                );
            } else {
                $this->messageManager->addSuccessMessage(__('The postcode track has been deleted.'));
            }
        } else {
            $this->messageManager->addErrorMessage(__('Impossible to delete %1 postcode tracks.', $error));
        }

        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/');
    }
}

