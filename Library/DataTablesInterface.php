<?php

/**
 * Created by PhpStorm.
 * User: Rudi
 * Date: 20/09/2015
 * Time: 22:12
 */

namespace Rubius\DataTablesBundle\Library;

use Doctrine\ORM\QueryBuilder;

interface DataTablesInterface
{
    /**
     * @return QueryBuilder
     */
    public function setQueryBuilderObject();

    /**
     * Return Json Structure for the view
     * @param string $ajaxSource
     * @return string
     * @throws \Exception
     */
    public function getDataTableObject($ajaxSource = '');

    /**
     * Build your column structure defining $this->setColumns($columnsArray)
     */
    public function defineColumns();

    /**
     * @param $ajaxSource
     * @return array
     */
    public function buildDtTableObject($ajaxSource);

    /**
     * @return array
     */
    public function getData();

    /**
     * Set Where statement to QueryBuilder
     */
    public function setWhereStatement();

}