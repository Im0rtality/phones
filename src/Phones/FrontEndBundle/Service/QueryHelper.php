<?php

namespace Phones\FrontEndBundle\Service;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\Query\Parameter;
use Doctrine\ORM\QueryBuilder;
use Phones\PhoneBundle\Entity\Phone;
use Symfony\Component\HttpFoundation\Request;

class QueryHelper
{
    /** @var EntityManager */
    private $entityManager;

    /** @var  Connection */
    private $dbConnection;

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
     * @param Connection $dbConnection
     */
    public function setDbConnection($dbConnection)
    {
        $this->dbConnection = $dbConnection;
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
        //#1
//        $result = $query->getResult();

        var_export($query->getDQL());

        /** @var Parameter $parameterData */
        $paramArray = [];
        foreach ($query->getParameters() as $parameterData) {
            $paramArray[$parameterData->getName()] = $parameterData->getValue();
        }

        //#2 convert queryBuilder to createQuery
//        $em = $this->entityManager;
//        $query = $em->createQuery($query->getDQL());
//        $query->setParameters($paramArray);
//        $result = $query->getResult();

        //#3 execute with dbConnection
        $queryStr = $query->getDQL();
        var_dump('bandom');
        var_export($queryStr);
        $queryStr = str_replace('phones', 'phone', $queryStr);
        $queryStr = str_replace('costs', 'cost', $queryStr);
        $queryStr = str_replace('audio_output', 'stat_audio_output', $queryStr);
        $queryStr = str_replace('battery_charging_times', 'stat_battery_charging_times', $queryStr);
//        $queryStr = str_replace('cost_original', 'cost', $queryStr);
        $queryStr = str_replace('WITH', 'ON', $queryStr);
        $pregResult = preg_replace('/[a-z]+:[a-z]+/i', '', $queryStr);
        if ($pregResult) {
            $queryStr = $pregResult;
        }
        foreach ($paramArray as $name => $value) {
            $queryStr = str_replace(':'.$name, $value, $queryStr);
        }

        var_dump('po');
        var_export($queryStr);
        $regeneratedQuery = '';
//        $stmt = $this->dbConnection->executeQuery($queryStr);
        $stmt = $this->dbConnection->executeQuery('SELECT phone.phoneId, minPrice, (COALESCE(SUM(stat_audio_output.grade),0)+COALESCE(SUM(stat_battery_charging_times.grade),0)) AS points FROM phone LEFT JOIN (SELECT MIN(cost.cost) AS minPrice, cost.phone_id FROM cost GROUP BY cost.phone_id) AS costs ON costs.phone_id=phone.phoneId LEFT JOIN stat_audio_output ON stat_audio_output.phoneId=phone.phoneId LEFT JOIN stat_battery_charging_times ON stat_battery_charging_times.phoneId=phone.phoneId WHERE (minPrice >= 250 AND minPrice <= 1250) AND (phone.weight >= 0 AND phone.weight <= 200) AND (phone.cpu_freq >= 500 AND phone.cpu_freq <= 3000) AND (phone.cpu_cores >= 1 AND phone.cpu_cores <= 8) AND (phone.ram_mb >= 0 AND phone.ram_mb <= 3072) AND (phone.display_size >= 0 AND phone.display_size <= 6) AND (phone.camera_mpx >= 0 AND phone.camera_mpx <= 41) AND (phone.video_p >= 0 AND phone.video_p <= 2160) AND (phone.battery_talk_time >= 4 AND phone.battery_talk_time <= 20) GROUP BY phone.phoneId ORDER BY points DESC, minPrice ASC');
        $rez = $stmt->fetchAll();
        var_dump($rez);
//        die;
//        return $result;
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

//        $qBuilder->from('PhonesPhoneBundle:Phone','phones');
//        $qBuilder->leftJoin('PhonesPhoneBundle:Cost', 'costs', 'WITH', 'costs.phone_id=phones.phoneId');
        $qBuilder->from('PhonesPhoneBundle:Phone','phones'. ' LEFT JOIN ('.$qBuilder2->getDQL().') AS costs WITH costs.phone_id=phones.phoneId');

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
