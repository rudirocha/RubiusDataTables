<?php
/**
 * Created by PhpStorm.
 * User: Rudi
 * Date: 20/09/2015
 * Time: 20:41
 */

namespace Rubius\DataTablesBundle\Library;


use Doctrine\DBAL\Types\Type;

class ColumnObject
{

    /**
     * @var string The translation key for Column header
     */
    private $header;

    /**
     * @var string The table alias from QueryBuilder
     */
    private $tableAlias;

    /**
     * @var string The column alias from QueryBuilder
     */
    private $columnAlias;

    /**
     * @var string The attribute to use in case to need to rename that at query
     */
    private $dbField;

    /**
     * @var array extra parameters of DataTables Plugin
     */
    private $extraParameters = array();

    /**
     * @var bool column is sortable?
     */
    private $sortable;

    /**
     * @var string DBAL\Type string
     */
    private $dataType;

    /**
     * @param string $header
     * @param string $columnAlias
     * @param string $tableAlias
     */
    public function __construct($header, $columnAlias, $tableAlias)
    {
        $this->setHeader($header);
        $this->setColumnAlias($columnAlias);
        $this->setTableAlias($tableAlias);
        $this->setSortable();
        $this->setDataType();

    }

    /**
     * @return mixed
     */
    public function getColumnAlias()
    {
        return $this->columnAlias;
    }

    /**
     * @param mixed $columnAlias
     * @return $this
     */
    public function setColumnAlias($columnAlias)
    {
        $this->columnAlias = $columnAlias;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getHeader()
    {
        return $this->header;
    }

    /**
     * @param mixed $header
     * @return $this
     */
    public function setHeader($header)
    {
        $this->header = $header;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getTableAlias()
    {
        return $this->tableAlias;
    }

    /**
     * @param mixed $tableAlias
     * @return $this
     */
    public function setTableAlias($tableAlias)
    {
        $this->tableAlias = $tableAlias;
        return $this;
    }

    /**
     * @return array
     */
    public function getExtraParameters()
    {
        return $this->extraParameters;
    }

    /**
     * @param array $extraParameters
     * @return $this
     */
    public function setExtraParameters($extraParameters)
    {
        $this->extraParameters = $extraParameters;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSortable()
    {
        return $this->sortable;
    }

    /**
     * @param mixed $sortable
     * @return $this
     */
    public function setSortable($sortable = true)
    {
        $this->sortable = $sortable;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDataType()
    {
        return $this->dataType;
    }

    /**
     * @param mixed $dataType
     * @return $this
     */
    public function setDataType($dataType = Type::TEXT)
    {
        $this->dataType = $dataType;
        return $this;
    }

    /**
     * @return string
     */
    public function getDbField()
    {
        return $this->dbField;
    }

    /**
     * @param string $dbField
     */
    public function setDbField($dbField)
    {
        $this->dbField = $dbField;
    }
}