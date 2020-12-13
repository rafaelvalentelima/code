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

use Magento\Framework\Stdlib\DateTime\Filter\Date;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\View\Model\Layout\Update\ValidatorFactory;
use ImaginationMedia\Correios\Helper\Data;

class PostDataProcessor
{
    /**
     * @var Date
     */
    protected $dateFilter;

    /**
     * @var ValidatorFactory
     */
    protected $validatorFactory;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * PostDataProcessor constructor.
     * @param Date $dateFilter
     * @param ManagerInterface $messageManager
     * @param ValidatorFactory $validatorFactory
     * @param Data $helper
     */
    public function __construct(
        Date $dateFilter,
        ManagerInterface $messageManager,
        ValidatorFactory $validatorFactory,
        Data $helper
    ) {
        $this->dateFilter = $dateFilter;
        $this->messageManager = $messageManager;
        $this->validatorFactory = $validatorFactory;
        $this->helper = $helper;
    }

    /**
     * @param $data
     * @return mixed
     */
    public function filter($data)
    {
        $filterRules = [];

        foreach (['custom_theme_from', 'custom_theme_to'] as $dateField) {
            if (!empty($data[$dateField])) {
                $filterRules[$dateField] = $this->dateFilter;
            }
        }

        return (new \Zend_Filter_Input($filterRules, [], $data))->getUnescaped();
    }

    /**
     * @param $postcode
     * @return int
     */
    private function isValidCEP($postcode)
    {
        try {
            $cep = trim($postcode);
            return preg_match("^[0-9]{5}-[0-9]{3}$", $cep);
        } catch (\Exception $ex) {
            return false;
        }
    }

    /**
     * @param $str_dt
     * @param string $str_dateformat
     * @return bool
     */
    private function isValidDateTimeString($str_dt, $str_dateformat = "m/d/Y hh:mm:ss")
    {
        $date = \DateTime::createFromFormat($str_dateformat, $str_dt);
        return ($date === false) ? false : true;
    }

    /**
     * @param $model
     * @return bool
     */
    public function validate($model)
    {
        $errorNo = true;
        if (!$model->getServico() && is_numeric($model->getServico())) {
            $this->messageManager->addErrorMessage(__("The field 'Service' can't be empty!"));
            $errorNo = false;
        }
        if (!$model->getCepInicio() && $this->isValidCEP($model->getCepInicio())) {
            $this->messageManager->addErrorMessage(__("The field 'First Postcode' can't be empty!"));
            $errorNo = false;
        }
        if (!$model->getCepFim() && $this->isValidCEP($model->getCepFim())) {
            $this->messageManager->addErrorMessage(__("The field 'Last Postcode' can't be empty!"));
            $errorNo = false;
        }
        if (!$model->getValor() && floatval($model->getValor()) > 0) {
            $this->messageManager->addErrorMessage(__("The field 'Price' can't be empty!"));
            $errorNo = false;
        }
        if (!$model->getPeso() && floatval($model->getPeso()) > 0) {
            $this->messageManager->addErrorMessage(__("The field 'Weight' can't be empty!"));
            $errorNo = false;
        }
        if (!$model->getPrazo() && floatval($model->getPrazo()) > 0) {
            $this->messageManager->addErrorMessage(__("The field 'Delivery Days' can't be empty!"));
            $errorNo = false;
        }
        if (!$model->getUltimoUpdate() && $this->isValidDateTimeString($model->getUltimoUpdate())) {
            $this->messageManager->addErrorMessage(__("The field 'Last Update' can't be empty!"));
            $errorNo = false;
        }
        if ($this->helper->canCreateOfflineTrack(
            $model->getServico(),
            $model->getCepInicio(),
            $model->getCepFim()
        ) == false) {
            $this->messageManager->addErrorMessage(
                __("Your DB have a postcode track to this postcode track. 
                You can't create more than one postcode track for an one service and same postcode tracks.")
            );
            $errorNo = false;
        }
        return $errorNo;
    }
}
