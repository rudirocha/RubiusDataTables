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
     * @return array
     */
    public function getTables()
    {
        return $this->tables;
    }

    /**
     * @param array $alias
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