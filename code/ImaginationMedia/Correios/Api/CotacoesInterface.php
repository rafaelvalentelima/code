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

namespace ImaginationMedia\Correios\Api;

interface CotacoesInterface
{
    /**
     * Get cotacao from cotacao id.
     * @param $id
     * @return mixed
     */
    public function getById($id);

    /**
     * Get cotacoes from postcode.
     * @param $postcode
     * @return mixed
     */
    public function getFromPostcode($postcode);

    /**
     * Get collection.
     * @return mixed
     */
    public function getCollection();

    /**
     * Save cotacao model.
     * @param CotacoesInterface $model
     * @return mixed
     */
    public function save(CotacoesInterface $model);

    /**
     * Delete cotacao model.
     * @param CotacoesInterface $model
     * @return mixed
     */
    public function delete(CotacoesInterface $model);

    /**
     * Populate database with postcode tracks.
     * @return mixed
     */
    public function populate();
}
