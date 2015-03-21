<?php

namespace Phones\FrontEndBundle\Service;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query\Expr;
use Phones\PhoneBundle\Entity\Phone;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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

    public function getBestPhones(Request $request){
        $qBuilder = $this->getQueryBuilder();

        $qBuilder->addSelect('phones.phoneId');
        $qBuilder->addSelect('costs.cost');
        $qBuilder->addSelect('SUM(stats.grade) AS points');

        $qBuilder->from('PhonesPhoneBundle:Phone','phones');

        $qBuilder->leftJoin('PhonesPhoneBundle:Cost', 'costs', 'WITH', 'costs.phone_id=phones.phoneId');
        $qBuilder->leftJoin('PhonesPhoneBundle:Stat', 'stats', 'WITH', 'stats.phone_id=phones.phoneId');

        $qBuilder->addGroupBy('phones.phoneId');
//        $qBuilder->addGroupBy('points');
//        $qBuilder->set('stats.grade', $qBuilder->expr()->sum('stats.grade', '2'));
//        $qBuilder->set('stats.grade', $qBuilder->expr()->countDistinct('stats.grade'));

        $qBuilder->addOrderBy('points', 'DESC');
        $qBuilder->addOrderBy('costs.cost', 'ASC');

        $queryParams = $request->query->getIterator();
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
            }
        }

        $query = $qBuilder->getQuery();
        $result = $query->getResult();
        //var_export($query->getDQL());
        //var_dump($query->getResult());

        return $result;
    }

    /**
     * @param \Doctrine\ORM\QueryBuilder $qBuilder
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
