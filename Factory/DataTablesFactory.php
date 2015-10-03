<?php
/**
 * @Author: Rudi Rocha <rudi.rocha@gmail.com>
 * @Project: RubiusDataTablesBundle
 */

namespace Rubius\DataTablesBundle\Factory;
use Rubius\DataTablesBundle\Library\DataTablesInterface;

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

        if (!isset($this->tables[$alias])) {
            throw new \RuntimeException(sprintf("DataTable with name [%s] does not exists.", $alias));
        }

        $dtTable = $this->tables[$alias];
        $dtTable->defineColumns();
        $dtTable->setQueryBuilderObject();

        return $dtTable;
    }

    /**
     * @param string $alias
     */
    public function addTable(DataTablesInterface $table, $alias)
    {
        $this->tables[$alias] = $table;
    }





}