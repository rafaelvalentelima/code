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
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use ImaginationMedia\Correios\Model\CotacoesRepository;

class PopulateTracks extends Action
{
    /**
     * @var CotacoesRepository
     */
    protected $cotacoesRepository;

    const ADMIN_RESOURCE = 'ImaginationMedia_Correios::correios_menuoption1';

    /**
     * PopulateTracks constructor.
     * @param Context $context
     * @param CotacoesRepository $cotacoesRepository
     */
    public function __construct(Context $context, CotacoesRepository $cotacoesRepository)
    {
        $this->cotacoesRepository = $cotacoesRepository;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        if ($this->cotacoesRepository->getCollection()->count() > 0) {
            $this->messageManager->addErrorMessage(__("You have to clear the postcode tracks db first!"));
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            return $resultRedirect->setPath('*/*/');
        } else {
            if ($this->cotacoesRepository->populate()) {
                $this->messageManager->addSuccessMessage(__("Postcode tracks database populated!"));
                $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
                return $resultRedirect->setPath('*/*/');
            } else {
                $this->messageManager->addErrorMessage(
                    __("An error occurred when the populate action was executed. 
                    Check the logs to see the cause of error.")
                );
                $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
                return $resultRedirect->setPath('*/*/');
            }
        }
    }
}
