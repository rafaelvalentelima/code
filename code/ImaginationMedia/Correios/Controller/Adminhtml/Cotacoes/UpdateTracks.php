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

use Magento\Backend\App\Action\Context;
use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultFactory;
use ImaginationMedia\Correios\Model\CotacoesRepository;

class UpdateTracks extends Action
{
    /**
     * @var CotacoesRepository
     */
    protected $cotacao;

    const ADMIN_RESOURCE = 'ImaginationMedia_Correios::correios_menuoption1';

    /**
     * UpdateTracks constructor.
     * @param Context $context
     * @param CotacoesRepository $cotacao
     */
    public function __construct(Context $context, CotacoesRepository $cotacao)
    {
        $this->cotacao = $cotacao;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $arrayResult = $this->cotacao->updateTracks();
        if ($arrayResult!=false) {
            $this->messageManager->addSuccessMessage(
                __(
                    "%1 Successful postcode tracks updated and %2 with error",
                    $arrayResult[0],
                    $arrayResult[1]
                )
            );
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        } else {
            $this->messageManager->addSuccessMessage(
                __(
                    "You don't need to update the database now, the database is updated.",
                    $arrayResult[0],
                    $arrayResult[1]
                )
            );
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        }
        return $resultRedirect->setPath('*/*/');
    }
}
