<?php
/**
 * @Author: Rudi Rocha <rudi.rocha@gmail.com>
 * @Project: RubiusDataTablesBundle
 */

namespace Rubius\DataTablesBundle\Factory;

/**
 * Class DataTablesFactory
 * @package Rubius\DataTablesBundle\Factory
 */
class DataTablesFactory
{
    private $tables = [];

    /**
     * @param string $alias
     * @return array
     */
    public function getTable($alias)
    {
        return $this->tables[$alias];
    }

    /**
     * @param string $alias
     */
    public function addTable($alias)
    {
        $table = $this->tables[$alias];
        if (!isset($table)) {
            throw new \RuntimeException(sprintf("DataTable with name [%s] does not exists.", $alias));
        }

        $dtTable = $table;
        $dtTable->defineColumns();

        return $dtTable;
    }





}