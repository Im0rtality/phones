<?php

namespace Phones\FrontEndBundle\Service;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use Phones\PhoneBundle\Entity\Phone;
use Symfony\Component\HttpFoundation\Request;

class QueryHelper
{
    /** @var EntityManager */
    private $entityManager;

    /** @var array  */
    private $possibleRangeNames = [];

    /** @var array  */
    private $possibleSelectNames = [];

    /** @var array  */
    private $possibleLikeSelectNames = [];

    /** @var array  */
    private $possibleCheckBoxNames = [];

    /** @var array  */
    private $joinableFormTables = [];

    /**
     * @param EntityManager $entityManager
     */
    public function setEntityManager($entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param array $possibleCheckBoxNames
     */
    public function setPossibleCheckBoxNames($possibleCheckBoxNames)
    {
        $this->possibleCheckBoxNames = $possibleCheckBoxNames;
    }

    /**
     * @param array $possibleLikeSelectNames
     */
    public function setPossibleLikeSelectNames($possibleLikeSelectNames)
    {
        $this->possibleLikeSelectNames = $possibleLikeSelectNames;
    }

    /**
     * @param array $possibleRangeNames
     */
    public function setPossibleRangeNames($possibleRangeNames)
    {
        $this->possibleRangeNames = $possibleRangeNames;
    }

    /**
     * @param array $possibleSelectNames
     */
    public function setPossibleSelectNames($possibleSelectNames)
    {
        $this->possibleSelectNames = $possibleSelectNames;
    }

    /**
     * @param array $joinableFormTables
     */
    public function setJoinableFormTables($joinableFormTables)
    {
        $this->joinableFormTables = $joinableFormTables;
    }

    public function getBestPhones(Request $request){
        $qBuilder = $this->getMainQuery();

        $this->setFormValues($request, $qBuilder);

        $query = $qBuilder->getQuery();
        $result = $query->getResult();

        var_export($query->getDQL());
//        var_dump($query->getResult());

        return $result;
    }

    /**
     * @return QueryBuilder
     */
    private function getMainQuery()
    {
        $qBuilder = $this->getQueryBuilder();

        $qBuilder2 = $this->getQueryBuilder();
        $qBuilder2
            ->addSelect('MIN(costs_original.cost) AS minPrice')
            ->addSelect('costs_original.phone_id')
            ->from('PhonesPhoneBundle:Cost', 'costs_original')
            ->addGroupBy('costs_original.phone_id');
        var_dump($qBuilder2->getDQL());
//        SELECT
//            MIN(PhonePrices.price) AS minPrice,
//            PhonePrices.phoneId
//        FROM PhonePrices
//        GROUP BY phoneId
//        ) AS MinPIces ON MinPIces.phoneId = Phones.phoneId

        $qBuilder->addSelect('phones.phoneId');
        $qBuilder->addSelect('costs.cost');

        $qBuilder->from('PhonesPhoneBundle:Phone','phones');

        $qBuilder->leftJoin('PhonesPhoneBundle:Cost', 'costs', 'WITH', 'costs.phone_id=phones.phoneId');
//        $qBuilder->expr()->any('LEFT JOIN ('.$qBuilder2->getDQL(). ') AS costs WITH costs.phone_id=phones.phoneId');
//        $qBuilder->leftJoin( $qBuilder2->getDQL(). ' WITH costs.phone_id=phones.phoneId', 'costs');
//        $qBuilder->leftJoin($qBuilder2->getDQL(), 'costs', 'WITH', 'costs.phone_id=phones.phoneId');

        $qBuilder->addGroupBy('phones.phoneId');

        return $qBuilder;
    }

    /**
     * @param QueryBuilder $qBuilder
     * @param string $table
     * @param string $newTableName
     */
    private function joinStats($qBuilder, $table, $newTableName)
    {
        $qBuilder->leftJoin(
            $table,
            $newTableName,
            'WITH',
            $newTableName . '.phoneId=phones.phoneId'
        );
    }

    /**
     * @param Request $request
     * @param QueryBuilder $qBuilder
     */
    private function setFormValues($request, $qBuilder)
    {
        $queryParams = $request->query->getIterator();

//        $this->joinStats($qBuilder, $this->joinableFormTables['audio_output'], 'audio_output');
//        $this->joinStats($qBuilder, $this->joinableFormTables['battery_charging_times'], 'battery_charging_times');
//        $qBuilder->addSelect('(COALESCE(SUM(battery_charging_times.grade),0)+COALESCE(SUM(audio_output.grade), 0)) AS points');
//        $qBuilder->addSelect('SUM(audio_output.grade) AS points');

        $sumElements = [];
        foreach ($queryParams as $paramName => $paramValue) {
            if (isset($this->possibleRangeNames[$paramName])) {
                $params = explode(',', $paramValue);
                $column = sprintf('%s.%s', $this->possibleRangeNames[$paramName], $paramName);
                $this->generateRangeConditions(
                    $qBuilder,
                    $column . ' >= :%s AND '. $column . ' <= :%s',
                    $paramName,
                    $params
                );
            } elseif (isset($this->possibleSelectNames[$paramName])) {
                if (is_array($paramValue)) {
                    if (in_array('any', $paramValue)) {
                        unset($paramValue[array_search('any', $paramValue)]);
                    }
                    $column = sprintf('%s.%s', $this->possibleSelectNames[$paramName], $paramName);
                    $this->generateMultipleConditions(
                        $qBuilder,
                        $qBuilder->expr()->orX(),
                        $column . ' = :%s',
                        $paramName,
                        $paramValue
                    );
                }
            } elseif (isset($this->possibleLikeSelectNames[$paramName]))  {
                if (is_array($paramValue)) {
                    if (in_array('any', $paramValue)) {
                        unset($paramValue[array_search('any', $paramValue)]);
                    }

                    $column = $this->possibleLikeSelectNames[$paramName] . '.' . $paramName;
                    foreach ($paramValue as $key => $valueStr) {
                        //need protection
                        $paramValue[$key] = '%' . $valueStr . '%';
                    }
                    $this->generateMultipleConditions(
                        $qBuilder,
                        $qBuilder->expr()->orX(),
                        $column . ' LIKE :%s',
                        $paramName,
                        $paramValue
                    );
                }
            } elseif (isset($this->possibleCheckBoxNames[$paramName])) {
                $column = $this->possibleCheckBoxNames[$paramName] . '.' . $paramName;
                if (!empty($paramValue)) {
                    $this->generateMultipleConditions(
                        $qBuilder,
                        $qBuilder->expr()->orX(),
                        $column . ' = :%s',
                        $paramName,
                        [1]
                    );
                }
            } elseif (isset($this->joinableFormTables[$paramName])) {
                $this->joinStats($qBuilder, $this->joinableFormTables[$paramName], $paramName);
                $sumElements[] = $paramName;
            }
        }

        $sumQuery = '';
        foreach ($sumElements as $element) {
            $sumQuery .= !empty($sumQuery) ? '+' : '';
            $sumQuery .= 'COALESCE(SUM(' . $element . '.grade),0)';
        }

        if (!empty($sumQuery)) {
            $qBuilder->addSelect('('. $sumQuery . ') AS points');
            $qBuilder->addOrderBy('points', 'DESC');
        }
        $qBuilder->addOrderBy('costs.cost', 'ASC');
    }

    /**
     * @param QueryBuilder $qBuilder
     * @param Expr $expr
     * @param string $pattern
     * @param string $fieldName
     * @param array $values
     */
    private function generateMultipleConditions($qBuilder, $expr, $pattern, $fieldName, $values)
    {
        foreach ($values as $key => $value) {
            //need protection from SLQ injection
            $paramName = $fieldName . $key;
            $condition = sprintf($pattern, $paramName);
            $expr->add($condition);
            $qBuilder->setParameter($paramName, $value);
        }

        $qBuilder->andWhere($expr);
    }

    /**
     * @param \Doctrine\ORM\QueryBuilder $qBuilder
     * @param string $pattern
     * @param string $fieldName
     * @param array  $values
     */
    private function generateRangeConditions($qBuilder, $pattern, $fieldName, $values)
    {
        $fromVal = isset($values[0]) && is_numeric($values[0]) ? $values[0] : null;
        $toVal   = isset($values[1]) && is_numeric($values[1]) ? $values[1] : null;
        if ($fromVal != null && $toVal != null) {
            $paramFromName = $fieldName. 0;
            $paramToName   = $fieldName. 1;
            $qBuilder->setParameter($paramFromName, $fromVal);
            $qBuilder->setParameter($paramToName, $toVal);

            $condition = sprintf($pattern, $paramFromName, $paramToName);
            $qBuilder->andWhere($condition);
        }
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    private function getQueryBuilder()
    {
        $em = $this->entityManager;
        $qb = $em->createQueryBuilder();

        return $qb;
    }

    /**
     * @return array|\Phones\PhoneBundle\Entity\Phone[]
     */
    public function getPhones()
    {
        $products = $this->entityManager
            ->getRepository('PhonesPhoneBundle:Phone')
            ->findAll();

        return $products;
    }

    /**
     * @param $phoneId
     * @return Phone
     */
    public function getPhone($phoneId)
    {
        $products = $this->entityManager
            ->getRepository('PhonesPhoneBundle:Phone')
            ->find($phoneId);

        return $products;
    }

    /**
     * @return array
     */
    public function getExistingOs()
    {
        /** @var EntityManager $em */
        $em = $this->entityManager->getRepository('PhonesPhoneBundle:Phone');
        $qb = $em->createQueryBuilder('a')->groupBy('a.os');
        $result = $qb->getQuery()->getResult();

        $distinctOs = [];
        /** @var Phone $os */
        foreach($result as $os) {
            $distinctOs[] = $os->getOs();
        }

        return $distinctOs;
    }

    /**
     * @return array
     */
    public function getExistingBrands()
    {
        $qb = $this->entityManager->getRepository('PhonesPhoneBundle:Phone')->createQueryBuilder('a')->groupBy('a.brand');
        $result = $qb->getQuery()->getResult();

        $distinct = [];
        /** @var Phone $os */
        foreach($result as $os) {
            $distinct[] = $os->getBrand();
        }

        return $distinct;
    }
}
