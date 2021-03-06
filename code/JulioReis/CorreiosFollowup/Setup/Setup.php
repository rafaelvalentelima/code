<?php
/**
 * JulioReis_CorreiosFollowup
 *
 * Do not edit this file if you want to update this module for future new versions.
 *
 * @category  JulioReis
 * @package   JulioReis_CorreiosFollowup
 *
 * @copyright Copyright (c) 2018 Julio Reis (www.rapidets.com.br)
 *
 * @author    Julio Reis <julioreis.si@gmail.com>
 */

namespace JulioReis\CorreiosFollowup\Setup;

trait Setup
{
    
    /** @var \Magento\Framework\Setup\ModuleDataSetupInterface|\Magento\Framework\Setup\SchemaSetupInterface */
    private $setup;
    
    
    /**
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     */
    private function getConnection()
    {
        return $this->setup()->getConnection();
    }
    
    
    /**
     * @return \Magento\Framework\Setup\ModuleDataSetupInterface|\Magento\Framework\Setup\SchemaSetupInterface
     */
    private function setup()
    {
        return $this->setup;
    }
    
    
    /**
     * @param string $tableName
     *
     * @return string
     */
    private function getTable($tableName)
    {
        return $this->setup()->getTable($tableName);
    }
}
