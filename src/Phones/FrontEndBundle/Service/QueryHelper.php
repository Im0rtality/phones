<?php

namespace Phones\FrontEndBundle\Service;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\Tools\Pagination\Paginator;
use PDO;
use Phones\PhoneBundle\Entity\Cost;
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

    /** @var array  */
    private $dbTableNamings = [];

    /** @var array  */
    private $statTablesByProviders = [];

    /** @var array  */
    private $leftJoins = [];
    /** @var array  */
    private $whereValues = [];
    /** @var string */
    private $selectVal;
    /** @var string */
    private $orderByPointValue;

    /**
     * @param array $dbTableNamings
     */
    public function setDbTableNamings($dbTableNamings)
    {
        $this->dbTableNamings = $dbTableNamings;
    }

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

    /**
     * @param array $statTablesByProviders
     */
    public function setStatTablesByProviders($statTablesByProviders)
    {
        $this->statTablesByProviders = $statTablesByProviders;
    }

    /**
     * @param Request $request
     * @return array
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getBestPhones(Request $request){

        $this->setFormValues($request);
        $query = $this->getBestPhonesQuery();
//        var_export($query);
        $statement = $this->dbConnection->executeQuery($query);
        $result = $statement->fetchAll();

        return $result;
    }

    /**
     * @param string $provider
     */
    public function updatePoints($provider)
    {
        $tableName = isset($this->statTablesByProviders[$provider]['tableName']) ?
            $this->statTablesByProviders[$provider]['tableName'] : null;
        $columnName = isset($this->statTablesByProviders[$provider]['byColumn']) ?
            $this->statTablesByProviders[$provider]['byColumn'] : null;
        $normaliseBy = isset($this->statTablesByProviders[$provider]['normalisationBy']) ?
            $this->statTablesByProviders[$provider]['normalisationBy'] : null;

        $query = null;
        if ($columnName && $tableName) {
            $query = 'UPDATE '.$tableName.
                ' LEFT JOIN ('.
                'SELECT '.$tableName.'.phoneId, '.
                '(SELECT MAX('.$columnName.') FROM '.$tableName.') as max_value '.
                'FROM '.$tableName.') virtual_table '.
                'ON virtual_table.phoneId='.$tableName.'.phoneId '.
                'SET '.$tableName.'.grade='.$tableName.'.'.$columnName.'*100/virtual_table.max_value';

            if ($normaliseBy == 'MIN') {
                $query = 'UPDATE '.$tableName.
                    ' LEFT JOIN ('.
                    'SELECT '.$tableName.'.phoneId, '.
                    '(SELECT MAX('.$columnName.') FROM '.$tableName.') as max_value, '.
                    '(SELECT MIN('.$columnName.') FROM '.$tableName.') as min_value '.
                    'FROM '.$tableName.') virtual_table '.
                    'ON virtual_table.phoneId='.$tableName.'.phoneId '.
                    'SET '.$tableName.'.grade=(virtual_table.max_value-'.$tableName.'.'.$columnName.')/((virtual_table.max_value-virtual_table.min_value)/100)';
            }
        }
        if ($query != null) {
            $this->dbConnection->executeUpdate($query);
        }
    }

    /**
     * @return string
     */
    public function getBestPhonesQuery()
    {
        //main query values
        $selectValues = [
            'PHONES_TABLE.phoneId',
            'minPrice',
        ];

        $fromValues = [
            'PHONES_TABLE'
        ];

        $costsSubSelect = '(SELECT MIN(COSTS_TABLE.cost) AS minPrice, COSTS_TABLE.phone_id '.
            'FROM COSTS_TABLE '.
            'GROUP BY COSTS_TABLE.phone_id) ';
        $leftJoins = [
            sprintf('LEFT JOIN %s AS costs ON costs.phone_id=PHONES_TABLE.phoneId', $costsSubSelect)
        ];

        $whereValues = [];

        $groupByValues = [
            'PHONES_TABLE.phoneId'
        ];

        $orderByValues = [
            'minPrice ASC',
        ];

        //generate query parts
        if ($this->selectVal) {
            $selectValues[] = $this->selectVal;
        }
        $select        = $this->generateSelectorQuery($selectValues, ', ');
        $from          = $this->generateSelectorQuery($fromValues, ', ');
        $leftJoinQuery = $this->generateSelectorQuery(array_merge($leftJoins, $this->leftJoins), ' ');
        $whereQuery    = $this->generateSelectorQuery(array_merge($whereValues, $this->whereValues), ' AND ');
        $groupByQuery  = $this->generateSelectorQuery($groupByValues, ', ');
        if ($this->orderByPointValue) {
            array_unshift($orderByValues, $this->orderByPointValue);
        }
        $orderByQuery = $this->generateSelectorQuery($orderByValues, ', ');

        //construct query
        $query  = 'SELECT ' . $select . ' ';
        $query .= 'FROM ' . $from . ' ';
        $query .= !empty($leftJoinQuery) ? $leftJoinQuery. ' ' : '';
        $query .= !empty($whereQuery)   ? 'WHERE ' . $whereQuery. ' ' : '';
        $query .= !empty($groupByQuery) ? 'GROUP BY ' . $groupByQuery. ' ' : '';
        $query .= !empty($orderByQuery) ? 'ORDER BY ' . $orderByQuery : '';

        //rename db tables to real table names
        foreach ($this->dbTableNamings as $tableName => $realTableName) {
            $query = str_replace($tableName, $realTableName, $query);
        }

        return $query;
    }

    /**
     * @param array  $selectors
     * @param string $separation
     *
     * @return string
     */
    private function generateSelectorQuery($selectors, $separation)
    {
        $query = '';
        foreach ($selectors as $value) {
            $query .= !empty($query) ? $separation : '';
            $query .= $value;
        }

        return $query;
    }

    /**
     * @param Request $request
     */
    private function setFormValues($request)
    {
        $queryParams = $request->query->getIterator();

        $sumElements = [];
        foreach ($queryParams as $paramName => $paramValue) {
            if (isset($this->possibleRangeNames[$paramName])) {
                $params = explode(',', $paramValue);
                $column = sprintf('%s.%s', $this->possibleRangeNames[$paramName], $paramName);
                $this->generateRangeConditions($column . ' >= %s AND '. $column . ' <= %s', $params);
            } elseif (isset($this->possibleSelectNames[$paramName])) {
                if (is_array($paramValue)) {
                    if (in_array('any', $paramValue)) {
                        unset($paramValue[array_search('any', $paramValue)]);
                    }
                    $column = sprintf('%s.%s', $this->possibleSelectNames[$paramName], $paramName);
                    $this->generateMultipleConditions($column . ' = %s', $paramValue);
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
                    $this->generateMultipleConditions($column . ' LIKE %s', $paramValue);
                }
            } elseif (isset($this->possibleCheckBoxNames[$paramName])) {
                $column = $this->possibleCheckBoxNames[$paramName] . '.' . $paramName;
                if (!empty($paramValue)) {
                    $this->generateMultipleConditions($column . ' = %s', [1]);
                }
            } elseif (isset($this->joinableFormTables[$paramName])) {
                $sumElements[] = $this->joinableFormTables[$paramName];
                $this->leftJoins[] = sprintf(
                    'LEFT JOIN %s ON %s.phoneId=PHONES_TABLE.phoneId',
                    $this->joinableFormTables[$paramName],
                    $this->joinableFormTables[$paramName]
                );
            }
        }

        $sumQuery = '';
        foreach ($sumElements as $element) {
            $sumQuery .= !empty($sumQuery) ? '+' : '';
            $sumQuery .= sprintf('COALESCE(SUM(%s.grade),0)', $element);
        }

        $countQuery = '';
        foreach ($sumElements as $element) {
            $countQuery .= !empty($countQuery) ? '+' : '';
            $countQuery .= sprintf('COUNT(%s.grade)', $element);
        }

        if (!empty($sumQuery)) {
            $this->selectVal = sprintf('(%s)/(%s) AS points', $sumQuery, $countQuery);
            $this->orderByPointValue = 'points DESC';
        }
    }

    /**
     * @param string $pattern
     * @param array $values or
     */
    private function generateMultipleConditions($pattern, $values)
    {
        $condition = '';
        foreach ($values as $key => $value) {
            //need protection from SLQ injection
            $condition .= !empty($condition) ? ' OR ' : '';
            $condition .= sprintf($pattern, is_numeric($value) ? $value : '"'.$value.'"');
        }
        if ($condition) {
            $this->whereValues[] = sprintf("(%s)", $condition);
        }
    }

    /**
     * @param string $pattern
     * @param array  $values
     */
    private function generateRangeConditions($pattern, $values)
    {
        $fromVal = isset($values[0]) && is_numeric($values[0]) ? $values[0] : null;
        $toVal   = isset($values[1]) && is_numeric($values[1]) ? $values[1] : null;
        if ($fromVal != null && $toVal != null) {
            $condition = sprintf($pattern, $fromVal, $toVal);
            $condition = str_replace('COSTS_TABLE.cost', 'minPrice', $condition);
            $this->whereValues[] = sprintf("(%s)", $condition);
        }
    }

    /**
     * @param int $currentPage
     * @param int $pageSize
     *
     * @return array(\Phones\PhoneBundle\Entity\Phone[], int)
     */
    public function getPhones($currentPage, $pageSize)
    {
        $products = [];

        $qBuilder = $this->entityManager->createQueryBuilder()
            ->addSelect('phone')
            ->from('PhonesPhoneBundle:Phone', 'phone');

        $query = $this->entityManager->createQuery($qBuilder->getDQL())
            ->setFirstResult(0)
            ->setMaxResults(100);

        $paginator = new Paginator($query, $fetchJoinCollection = true);
//        $paginator->setUseOutputWalkers(false); //if using another(simple) query

        $totalItems = count($paginator);
        $pagesCount = ceil($totalItems / $pageSize);

        // page's items:
        $paginator
            ->getQuery()
            ->setFirstResult($pageSize * ($currentPage - 1)) // set the offset
            ->setMaxResults($pageSize); // set the limit

        foreach ($paginator as $phone) {
            $products[] = $phone;
        }

        return [$products, $pagesCount];
    }

    /**
     * @param $phoneId
     * @return Phone
     */
    public function getPhone($phoneId)
    {
        $phone = $this->entityManager->getRepository('PhonesPhoneBundle:Phone')->find($phoneId);

        return $phone;
    }

    /**
     * @param Phone $phone
     *
     * @return float
     */
    public function getPhoneGeneralRating($phone, $roundVal = 0)
    {
        $rating = null;

        if ($phone) {
            $stats = [];
            $stats['baseMarkOSII']       = $phone->getBasemarkOSII();
            $stats['baseMarkX']          = $phone->getBasemarkX();
            $stats['cameraRate']         = $phone->getCameraRate();
            $stats['cameraSpeed']        = $phone->getCameraSpeed();
            $stats['batteryCahrginTime'] = $phone->getBatteryChargingTime();
            $stats['cameraSpeed']        = $phone->getBatteryLife();

            $ratings = [];
            foreach ($stats as $stat) {
                if ($stat) {
                    $ratings[] = $stat->getGrade();
                }
            }
            $ratings = array_filter($ratings);

            if (!empty($ratings)) {
                $rating = array_sum($ratings) / count($ratings);
                if ($rating) {
                    $rating = round($rating, $roundVal);
                }
            }

        }

        return $rating;
    }

    /**
     * @param Phone $phone
     * @param int $roundVal
     *
     * @return array
     */
    public function getPhoneRatings($phone, $roundVal = 0)
    {
        $anotherStats = [];

        if ($phone) {
            $stats = [];
            $stats['baseMarkOSII']       = $phone->getBasemarkOSII();
            $stats['baseMarkX']          = $phone->getBasemarkX();
            $stats['cameraRate']         = $phone->getCameraRate();
            $stats['cameraSpeed']        = $phone->getCameraSpeed();
            $stats['batteryCahrginTime'] = $phone->getBatteryChargingTime();
            $stats['cameraSpeed']        = $phone->getBatteryLife();

            $colors = [
                '',
                'red',
                'orange',
                'green',
                'yellow',
            ];

            $i = 0;
            $anotherStats = [];
            foreach ($stats as $key => $stat) {
                if ($stat) {
                    $statVal = $stat->getGrade();
                    $anotherStats[$key]['value'] = $statVal ? round($statVal, $roundVal) : null;
                    $anotherStats[$key]['color'] = $colors[$i];
                } else {
                    $anotherStats[$key]['value'] = null;
                    $anotherStats[$key]['color'] = $colors[$i];
                }
                $i++;
            }

//            if (!empty($anotherStats)) {
//                usort($anotherStats, function($a, $b)
//                {
//                    if ($a['value'] == $b['value']) {
//                        return 0;
//                    }
//                    return ($a['value'] > $b['value']) ? -1 : 1;
//                });
//            }
        }

        return $anotherStats;
    }

    /**
     * @param Phone $phone
     *
     * @return array
     */
    public function getPhoneMainSpecs($phone)
    {
        $specs = [];

        if (!empty($phone)) {
            $specs = [
                'Gamintojas' => $phone->getBrand(),
                'Ryšio technologijos' => $phone->getTechnology(),
                'Operacinė'  => $phone->getOs(),
                'Batterijos budėjimo trukmė (val.)' => $phone->getBatteryStandByH() ?
                    $phone->getBatteryStandByH() : 'nėra duomenų',
                'Batterijos kalbėjimo trukmė (val.)' => $phone->getBatteryTalkTime() ?
                    $phone->getBatteryTalkTime() : 'nėra duomenų',
                'Ekrano dydis(coliais)' => $phone->getDisplaySize(),
                'Svoris'     => $phone->getWeight(),
            ];
        }

        return $specs;
    }

    /**
     * @param Phone $phone
     *
     * @return array
     */
    public function getPhoneOtherSpecs($phone)
    {
        $specs = [];

        if (!empty($phone)) {
            $specs = [
                'GPS' => $phone->getGps() ? $phone->getGps() : 'nėra duomenų',
                'Blootooth versija' => $phone->getBluetoothVersion(),
                'CPU sparta (MHz)' => $phone->getCpuFreq(),
                'CPU branduoliai'  => $phone->getCpuCores(),
                'Operatyvioji atmintis(Mb)' => $phone->getRamMb(),
                'Išorinė atmintinė' => $phone->getExternalSd() ? 'Yra' : 'nėra',
                'Kamera (Mpx)' => $phone->getCameraMpx(),
                'Video kamera (p)' => $phone->getVideoP(),
                'Apšvietimas' => $phone->getFlash()? 'Yra' : 'nėra',
            ];
        }

        return $specs;
    }

    /**
     * @param Phone $phone
     *
     * @return array
     */
    public function getPhoneCosts($phone)
    {
        $costs = [];

        /** @var Cost[] $phoneCosts */
        $phoneCosts = $phone->getCosts();
        if (!empty($phoneCosts)) {
            /** @var Cost $cost */
            foreach ($phoneCosts as $phoneCost) {
                if (!empty($phoneCost)) {
                    $cost['deepLink'] = $phoneCost->getDeepLink();
                    $cost['cost']     = $phoneCost->getCost();
                    $cost['provider'] = $phoneCost->getProviderId();
                    $costs[] = $cost;
                }
            }
        }

        return $costs;
    }

    /**
     * @param Phone $phone
     *
     * @return Phone[]|array
     */
    public function getRelatedPhonesByBrand($phone)
    {
        $relatedPhonesRez = [];

        $relatedPhones = $this->entityManager->getRepository('PhonesPhoneBundle:Phone')->findBy(
            ['brand' => $phone->getBrand()],
            null,
            20,
            null
        );

        if (!empty($relatedPhones)) {
            foreach ($relatedPhones as $relatedPhone) {
                if ($relatedPhone->getPhoneId() != $phone->getPhoneId()) {
                    $relatedPhonesRez[] = $relatedPhone;
                }
            }
        }

        return $relatedPhonesRez;
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
        $qb = $this->entityManager
            ->getRepository('PhonesPhoneBundle:Phone')
            ->createQueryBuilder('a')
            ->groupBy('a.brand');

        $result = $qb->getQuery()->getResult();

        $distinct = [];
        /** @var Phone $os */
        foreach($result as $os) {
            $distinct[] = $os->getBrand();
        }

        return $distinct;
    }

    /**
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getPhonesJson()
    {
        $query = 'SELECT PHONES_TABLE.phoneId FROM PHONES_TABLE';
        //rename db tables to real table names
        foreach ($this->dbTableNamings as $tableName => $realTableName) {
            $query = str_replace($tableName, $realTableName, $query);
        }

        $statement = $this->dbConnection->executeQuery($query);
        $result = $statement->fetchAll(PDO::FETCH_COLUMN, 0);
        $result = $result ? $result : [];

        return $result;
    }
}
