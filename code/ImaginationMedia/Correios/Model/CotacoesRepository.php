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

namespace ImaginationMedia\Correios\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use ImaginationMedia\Correios\Api\CotacoesInterface;
use ImaginationMedia\Correios\Model\CotacoesFactory;
use ImaginationMedia\Correios\Helper\Data as CorreiosHelper;

class CotacoesRepository implements CotacoesInterface
{
    /**
     * @var CorreiosHelper
     */
    protected $helper;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var CotacoesFactory
     */
    protected $cotacoesFactory;

    protected $maxWeights = array(
        array('service' => 40010, 'max' => 30),
        array('service' => 4162, 'max' => 30),
        array('service' => 40436, 'max' => 30),
        array('service' => 40444, 'max' => 30),
        array('service' => 81019, 'max' => 15),
        array('service' => 41106, 'max' => 30),
        array('service' => 4669, 'max' => 30),
        array('service' => 40215, 'max' => 10),
        array('service' => 40290, 'max' => 10),
        array('service' => 40045, 'max' => 10)
    );

    protected $ratesPacAndSedex = array(
        array(1,9999999,4811210),
        array(10000000,19999999,19999999),
        array(20000000,24799999,24799999),
        array(24800001,28999999,28999999),
        array(29000000,29184999,29184999),
        array(29185000,29999999,29999999),
        array(30000000,34999999,34999999),
        array(35000000,39999999,39999999),
        array(40000000,43849999,43849999),
        array(43850000,48999999,48999999),
        array(49000000,49099999,49099999),
        array(49100000,49999999,49999999),
        array(50000000,54999999,54999999),
        array(55000000,56999999,56999999),
        array(57000000,57099999,57099999),
        array(57100000,57999999,57999999),
        array(58000000,58099999,58099999),
        array(58100000,58999999,58999999),
        array(59000000,59099000,59099000),
        array(59100000,59999999,59999999),
        array(60000000,61699999,61699999),
        array(61700000,63999999,63999999),
        array(64000000,64099999,64099999),
        array(64100000,64999999,64999999),
        array(65000000,65099000,65099000),
        array(65100000,65999999,65999999),
        array(66000000,67999999,67999999),
        array(68000000,68899999,68899999),
        array(68900000,68929999,68929999),
        array(68930000,68999999,68999999),
        array(69000000,69099000,69099000),
        array(69100000,69299000,69299000),
        array(69300000,69339999,69339999),
        array(69340000,69399999,69399999),
        array(69400000,69899999,69899999),
        array(69900000,69920999,69920999),
        array(69921000,69999999,69999999),
        array(70000000,73699999,73699999),
        array(73700000,76799999,76799999),
        array(76800000,76834999,76834999),
        array(76835000,76999999,76999999),
        array(77000000,77299999,77299999),
        array(77300000,77999999,77999999),
        array(78000000,78169999,78169999),
        array(78170000,78899999,78899999),
        array(79000000,79124999,79124999),
        array(79125000,79999999,79999999),
        array(80000000,83729999,83729999),
        array(83730000,87999999,87999999),
        array(88000000,88139999,88139999),
        array(88140000,89999999,89999999),
        array(90000000,94999999,94999999),
        array(95000000,99999999,99999999)
    );

    protected $offlineAvailable = array(40010,4162,40436,40444,81019,41106,4669);

    /**
     * CotacoesRepository constructor.
     * @param CotacoesFactory $cotacoesFactory
     * @param CorreiosHelper $helper
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        CotacoesFactory $cotacoesFactory,
        CorreiosHelper $helper,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->cotacoesFactory = $cotacoesFactory;
        $this->helper = $helper;
        $this->scopeConfig = $scopeConfig;
        $this->addOfflinePostMethods();
    }

    /**
     * Get cotacao from cotacao id.
     * @param $id
     * @return mixed
     */
    public function getById($id)
    {
        return $this->cotacoesFactory->create()->load($id);
    }

    /**
     * Get cotacoes from postcode.
     * @param $postcode
     * @return mixed
     */
    public function getFromPostcode($postcode)
    {
        return $this->cotacoesFactory->create()
            ->getCollection()
            ->addAttributeToFilter('cep_inicio', array('lt' => $postcode))
            ->addAttributeToFilter('cep_fim', array('gt' => $postcode));
    }

    /**
     * Get collection.
     * @return mixed
     */
    public function getCollection()
    {
        return $this->cotacoesFactory
            ->create()
            ->getCollection();
    }

    /**
     * Save cotacao model.
     * @param CotacoesInterface $model
     * @return mixed
     */
    public function save(CotacoesInterface $model)
    {
        return $this->cotacoesFactory
            ->create()
            ->setData($model->getData())
            ->save();
    }

    /**
     * Delete cotacao model.
     * @param CotacoesInterface $model
     * @return mixed
     */
    public function delete(CotacoesInterface $model)
    {
        return $this->cotacoesFactory
            ->create()
            ->load($model->getId())
            ->delete();
    }


    public function addOfflinePostMethods()
    {
        if ($this->scopeConfig->getValue('correios_postingmethods_config/settings/sedex') != "") {
            $this->offlineAvailable[] = (int)$this->scopeConfig->getValue(
                'correios_postingmethods_config/settings/sedex'
            );
        }
        if ($this->scopeConfig->getValue('correios_postingmethods_config/settings/pac') != "") {
            $this->offlineAvailable[] = (int)$this->scopeConfig->getValue(
                'correios_postingmethods_config/settings/pac'
            );
        }
    }

    /**
     * Populate database with postcode tracks.
     * @return mixed
     */
    public function populate()
    {
        $postingMethods = explode(",", $this->scopeConfig->getValue(
            'carriers/imaginationmedia_correios/posting_methods'
        ));
        if ($this->cotacoesFactory->create()->getCollection()->count() > 0) {
            $this->helper->logMessage("Can't populate because the db isn't empty. First you to clear the db.");
            return false;
        }
        foreach ($postingMethods as $method) {
            if (in_array($method, $this->offlineAvailable) == true) {
                $maxWeight = 0;
                foreach ($this->maxWeights as $weights) {
                    if ($weights["service"]==$method) {
                        $maxWeight = $weights["max"];
                    }
                }
                $weights = array(0.3);
                for ($i=1; $i <= $maxWeight; $i++) {
                    $weights[] = $i;
                }
                foreach ($weights as $weight) {
                    foreach ($this->ratesPacAndSedex as $rate) {
                        $newCotacao = $this->cotacoesFactory->create();
                        $now = new \DateTime();
                        $newCotacao->setServico($method)
                            ->setPrazo(0)
                            ->setPeso($weight)
                            ->setValor(0)
                            ->setCepInicio($rate[0])
                            ->setCepFim($rate[1])
                            ->setUltimoUpdate($now->format('Y-m-d H:i:s'));
                        if ($newCotacao->save()==false) {
                            $this->helper->logMessage("Erro saving the service ".$method.
                                " with the weight: ".$weight);
                        }
                    }
                }
            } else {
                $this->helper->logMessage("Service ".$method." ignored because you can't store this service offline.");
            }
        }
        return true;
    }

    /** Update offline postcode tracks */
    public function updateTracks()
    {
        $updated = 0;
        $errors = 0;
        $collection = $this->cotacoesFactory->create()
            ->getCollection()
            ->addFilter("valor", 0);
        if ($collection->count()>0) {
            //Updating in the first time
            foreach ($collection as $cotacao) {
                $cotacaoObj = $this->cotacoesFactory->create()->load($cotacao->getId());
                $cotacaoValues = $this->helper->getServiceToPopulate(
                    $cotacaoObj->getServico(),
                    $cotacaoObj->getPeso(),
                    $cotacaoObj->getCepFim()
                );
                if ($cotacaoValues != false) {
                    $now = new \DateTime();
                    $cotacaoObj->setPrazo($cotacaoValues["prazo"])
                        ->setValor($cotacaoValues["valor"])
                        ->setUltimoUpdate($now->format('Y-m-d H:i:s'));
                    if ($cotacaoObj->save()) {
                        $updated++;
                    } else {
                        $errors++;
                    }
                } else {
                    $halfPostcode = ($cotacaoObj->getCepFim() -
                        ($cotacaoObj->getCepFim() - $cotacaoObj->getCepInicio()) / 2);
                    $cotacaoValues = $this->helper->getServiceToPopulate(
                        $cotacaoObj->getServico(),
                        $cotacaoObj->getPeso(),
                        $halfPostcode
                    );
                    if ($cotacaoValues != false) {
                        $now = new \DateTime();
                        $cotacaoObj->setPrazo($cotacaoValues["prazo"])
                            ->setValor($cotacaoValues["valor"])
                            ->setUltimoUpdate($now->format('Y-m-d H:i:s'));
                        if ($cotacaoObj->save()) {
                            $updated++;
                        } else {
                            $errors++;
                        }
                    } else {
                        $errors++;
                    }
                }
            }
        } else {
            //Trying to update manually
            $result = $this->helper->updateOfflineTracks();
            if ($result==false) {
                return false;
            } else {
                $updated = $result[0];
                $errors = $result[1];
            }
        }
        return array($updated,$errors);
    }
}
