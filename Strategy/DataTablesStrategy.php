<?php
/**
 * Created by PhpStorm.
 * User: Rudi
 * Date: 20/09/2015
 * Time: 20:04
 */

namespace Rubius\DataTablesBundle\Strategy;


use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Rubius\DataTablesBundle\Library\ColumnObject;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\Translation\DataCollectorTranslator;

abstract class DataTablesStrategy
{

    /**
     * empty year from Doctrine DateTime
     */
    const EMPTY_YEAR = '-0001';
    const GRID_LIMIT_PARAM = 'iDisplayLength';
    const GRID_OFFSET_PARAM = 'iDisplayStart';
    const GRID_SEARCH_TEXT = 'sSearch';
    const GRID_SORT_COL_PARAM = 'iSortCol_0';
    const GRID_SORT_TYPE = 'sSortDir_0';

    const DATA = 'data';
    const I_TOTAL_RECORDS = 'iTotalRecords';
    const I_TOTAL_DISPLAY_RECORDS = 'iTotalDisplayRecords';

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var DataCollectorTranslator
     */
    private $translator;

    /**
     * @var EngineInterface
     */
    private $renderer;

    /**
     * @var array
     */
    private $columns = [];

    /**
     * @var Request
     */
    private $request;

    /**
     * @var QueryBuilder
     * given by extending this class
     */
    private $queryBuilder;

    /**
     * @param EntityManagerInterface $entityManager
     * @param EngineInterface $templating
     * @param DataCollectorTranslator $translator
     * @param RequestStack $request
     */
    function __construct(
        EntityManagerInterface $entityManager,
        EngineInterface $templating,
        DataCollectorTranslator $translator,
        RequestStack $request
    )
    {
        $this->setEntityManager($entityManager);
        $this->setRenderer($templating);
        $this->setTranslator($translator);
        $this->setRequest($request->getCurrentRequest());
    }

    /**
     * Return Json Structure for the view
     * @param string $ajaxSource
     * @return string
     * @throws \Exception
     */
    public function getDataTableObject($ajaxSource = '')
    {
        if (empty($ajaxSource)) {
            throw new \Exception("There is no ajaxSource defined");
        }
        return json_encode($this->buildDtTableObject($ajaxSource));
    }

    /**
     * @return array
     */
    public function getData()
    {
        //todo: Implement Custom QueryBuilders to allow direct SQL or anothers :)

        $counterQb = clone($this->getQuerybuilder());
        //set grid limits and offset
        $this->getQueryBuilder()
            ->setMaxResults($this->getRequest()->get(self::GRID_LIMIT_PARAM))
            ->setFirstResult($this->getRequest()->get(self::GRID_OFFSET_PARAM));

        $rows = $this->getResults(Query::HYDRATE_ARRAY);
        $data = $this->getFormattedData($rows);

        //getTotal Records
        $alias = current($counterQb->getDQLPart('from'))->getAlias();
        $counterQb->resetDQLPart('groupBy'); //avoid error of multiple rows to getScalar
        $totalRecords = $counterQb->select(sprintf('count(%s.id)', $alias))
            ->getQuery()
            ->getSingleScalarResult();

        return array(
            'data' => $data,
            'iTotalRecords' => $this->getRequest()->get(self::GRID_LIMIT_PARAM),
            'iTotalDisplayRecords' => $totalRecords
        );
    }

    /**
     * @param int $outputType
     * @return array
     */
    protected function getResults($outputType = Query::HYDRATE_OBJECT)
    {
        $this->setSortStatement($this->getQuerybuilder());
        return $this->getQueryBuilder()->getQuery()->getResult($outputType);
    }

    /**
     * Set auto sort statement
     */
    protected function setSortStatement()
    {
        $cols = $this->getColumns();

        if ($this->getRequest()->has(self::GRID_SORT_COL_PARAM)
        ) {
            /**
             * @var ColumnObject $col
             */
            $col = $cols[$this->getRequest()->get(self::GRID_SORT_COL_PARAM)];

            if (!is_null($col->getDbField())) {
                $sortableColumn = sprintf("%s.%s", $col->getTableAlias(), $col->getDbField());
            } else {
                $sortableColumn = sprintf("%s.%s", $col->getTableAlias(), $col->getColumnAlias());
            }

            $this->getQuerybuilder()->addOrderBy(
                $sortableColumn,
                $this->getRequest()->get(self::GRID_SORT_TYPE)
            );
        }
    }

    /**
     * @param $rows
     * @return array
     */
    protected function getFormattedData($rows)
    {
        $data = [];
        foreach ($rows as $row) {
            $data = $this->mapAutomaticFields($row);
        }

        return $data;
    }

    /**
     * @param $dbRow
     * @return array
     */
    protected function mapAutomaticFields($dbRow)
    {
        $dataRow = [];
        /** @var ColumnObject $column */
        foreach ($this->getColumns() as $column) {
            if (isset($dbRow[$column->getColumnAlias()])) {

                //check DataFields custom Types
                switch($column->getDataType()) {
                    case Type::DATETIME:
                        $dataRow[$column->getColumnAlias()] = $this->parseDatetime($dbRow[$column->getColumnAlias()]);
                        break;
                    case Type::DATE:
                        $dataRow[$column->getColumnAlias()] = $this->parseDatetime($dbRow[$column->getColumnAlias()], 'Y-m-d');
                        break;
                    case Type::TIME:
                        $dataRow[$column->getColumnAlias()] = $this->parseDatetime($dbRow[$column->getColumnAlias()], 'H:i:s');
                        break;
                    default:
                        $dataRow[$column->getColumnAlias()] = $dbRow[$column->getColumnAlias()];
                        break;
                }
            }
        }

        return $dataRow;
    }

    /**
     * @param $ajaxSource
     * @return array
     */
    public function buildDtTableObject($ajaxSource)
    {
        $tableStructure = [
            "sAjaxSource" => sprintf("%s", $ajaxSource),
            "processing" => true,
            "paginate" => true,
            "language" => [
                'emptyTable' => $this->getTranslator()->trans('datatables.object.emptyTable', [], 'datatables'),
                'info' => $this->getTranslator()->trans('datatables.object.gridInfo', [], 'datatables'),
                'zeroRecords' => $this->getTranslator()->trans('datatables.object.gridNoRecords', [], 'datatables'),
                'processing' => $this->getTranslator()->trans('datatables.object.gridProcessing', [], 'datatables'),
                'lengthMenu' => $this->getTranslator()->trans('datatables.object.gridLengthMenu', [], 'datatables'),
                'search' => $this->getTranslator()->trans('datatables.object.gridSearchBox', [], 'datatables'),
                'infoFiltered' => $this->getTranslator()->trans('datatables.object.gridFilteredText', [], 'datatables'),
                'paginate' => [
                    'next' => $this->getTranslator()->trans('datatables.object.next', [], 'datatables'),
                    'previous' => $this->getTranslator()->trans('datatables.object.previous', [], 'datatables'),
                ],
            ],
            'fixedHeader' => true,
            "serverSide" => true,
            'columns' => array()
        ];

        /**
         * @var ColumnObject $col
         */
        foreach ($this->getColumns() as $col) {
            $column = array(
                'title' => $this->getTranslator()->trans($col->getHeader(),[],$col->getTransDomain()),
                'data' => $col->getColumnAlias(),
                'orderable' => $col->getSortable()

            );
            $extraParams = $col->getExtraParameters();
            if (is_array($col->getExtraParameters())) {
                foreach ($extraParams as $param => $value) {
                    $column[$param] = $value;
                }
            }

            $tableStructure['columns'][] = $column;
        }

        return $tableStructure;
    }

    /**
     * @param $value
     * @param string $format
     * @return string
     */
    protected function parseDatetime($value, $format = 'Y-m-d H:i:s')
    {
        if (isset($value) &&
            !empty($value) &&
            $value->format('Y') != self::EMPTY_YEAR
        ) {
            return $value->format($format);
        } else {
            return '-';
        }
    }


    /**
     * GETTERS AND SETTERS
     */
    /**
     * @return EntityManagerInterface
     */
    protected function getEntityManager()
    {
        return $this->entityManager;
    }

    /**
     * @param EntityManagerInterface $entityManager
     */
    protected function setEntityManager($entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @return DataCollectorTranslator
     */
    protected function getTranslator()
    {
        return $this->translator;
    }

    /**
     * @param DataCollectorTranslator $translator
     */
    protected function setTranslator($translator)
    {
        $this->translator = $translator;
    }

    /**
     * @return EngineInterface
     */
    protected function getRenderer()
    {
        return $this->renderer;
    }

    /**
     * @param EngineInterface $renderer
     */
    protected function setRenderer($renderer)
    {
        $this->renderer = $renderer;
    }

    /**
     * @return array
     */
    protected function getColumns()
    {
        return $this->columns;
    }

    /**
     * @param array $columns
     */
    protected function setColumns($columns)
    {
        $this->columns = $columns;
    }

    /**
     * @return \Symfony\Component\HttpFoundation\ParameterBag
     */
    protected function getRequest()
    {
        return $this->request->query;
    }

    /**
     * @param Request $request
     */
    protected function setRequest($request)
    {
        $this->request = $request;
    }

    protected function getQuerybuilder()
    {
        return $this->queryBuilder;
    }

    protected function setQueryBuilder(QueryBuilder $qb)
    {
        $this->queryBuilder = $qb;
        return $this;
    }

}