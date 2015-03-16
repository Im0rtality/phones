<?php

namespace Phones\FrontEndBundle\Controller;

use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query\Expr;
use Phones\PhoneBundle\Entity\Phone;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     *
     * @return Response
     */
    public function indexAction()
    {
        $params =[];
        $params['products'] = $this->getPhones();

        return $this->render('PhonesFrontEndBundle:Default:index.html.twig', $params);
    }

    /**
     * @return Response
     */
    public function bestPhoneSearchAction()
    {
        $params =[];
        $params['products'] = $this->getPhones();
        $params['distinctOs'] = $this->getExistingOs();
        $params['distinctBrands'] = $this->getExistingBrands();

        return $this->render('PhonesFrontEndBundle:Default:best.phone.search.html.twig', $params);
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function searchResultsAction(Request $request){
        $possibleRangeNames = [
            'cost' => 'costs',
            'weight' => 'phones',
            'cpu_freq' => 'phones',
            'cpu_cores' => 'phones',
            'ram_mb' => 'phones',
            'display_size' => 'phones',
            'camera_mpx' => 'phones',
            'video_p' => 'phones',
            'battery_stand_by_h' => 'phones',
            'battery_talk_time' => 'phones',
        ];

        $possibleSelectNames = [
            'brand' => 'phones',
            'os' => 'phones',
            'bluetooth_version' => 'phones',
        ];

        $possibleLikeSelectNames = [
            'technology' => 'phones',
            'gps' => 'phones',
            'wlan' => 'phones',
        ];

        $possibleCheckBoxNames = [
            'external_sd' => 'phones',
            'flash' => 'phones',
        ];

        $qBuilder = $this->getQueryBuilder();
        $qBuilder->addSelect('phones.phoneId');
        $qBuilder->addSelect('costs.cost');

        $qBuilder->from('PhonesPhoneBundle:Phone','phones');

        $qBuilder->leftJoin('PhonesPhoneBundle:Cost', 'costs', "WITH", 'costs.phone_id=phones.phoneId');

        $queryParams = $request->query->getIterator();
        foreach ($queryParams as $paramName => $paramValue) {
            if (isset($possibleRangeNames[$paramName])) {
                $params = explode(',', $paramValue);
                $value = $possibleRangeNames[$paramName] . '.' . $paramName;
                $this->generateRangeConditions(
                    $qBuilder,
                    $value . ' >= :%s AND '.$value.' <= :%s',
                    $paramName,
                    $params
                );
            } elseif (isset($possibleSelectNames[$paramName])) {
                if (is_array($paramValue) && in_array('any', $paramValue)) {
                    unset($paramValue[array_search('any', $paramValue)]);
                }
                $value = $possibleSelectNames[$paramName] . '.' . $paramName;
                $this->generateMultipleConditions(
                    $qBuilder,
                    $qBuilder->expr()->orX(),
                    $value . ' = :%s',
                    $paramName,
                    $paramValue
                );
            } elseif (isset($possibleLikeSelectNames[$paramName]))  {
                if (is_array($paramValue) && in_array('any', $paramValue)) {
                    unset($paramValue[array_search('any', $paramValue)]);
                }
                $value = $possibleLikeSelectNames[$paramName] . '.' . $paramName;
//                $this->generatelikeConditions(
//                    $qBuilder,
//                    $qBuilder->expr()->orX(),
//                    $value,
//                    $paramName,
//                    $paramValue
//                );
                foreach ($paramValue as $key => $valueStr) {
                    $paramValue[$key] = '%'.$valueStr.'%';
                }
                $this->generateMultipleConditions(
                    $qBuilder,
                    $qBuilder->expr()->orX(),
                    $value . ' LIKE :%s',
                    $paramName,
                    $paramValue
                );
            } elseif (isset($possibleCheckBoxNames[$paramName])) {
                $value = $possibleCheckBoxNames[$paramName] . '.' . $paramName;
                $this->generateMultipleConditions(
                    $qBuilder,
                    $qBuilder->expr()->orX(),
                    $value . ' = :%s',
                    $paramName,
                    [1]
                );
            }
//            var_dump($key, $param);
        }

        $query = $qBuilder->getQuery();
        var_dump($query->getResult());

        $params = [];

//        var_dump($this->getFilteredPhones(['bluetooth_version' > '2']));
//        var_dump($this->test());

        return $this->render('PhonesFrontEndBundle:Default:results.html.twig', $params);
    }

    private function test()
    {
        $qb = $this->getQueryBuilder();

        $qb->addSelect('phones.phoneId');
        $qb->addSelect('costs.cost');

        $qb->from('PhonesPhoneBundle:Phone','phones');

        $qb->leftJoin('PhonesPhoneBundle:Cost', 'costs', "WITH", 'costs.phone_id=phones.phoneId');

        $this->generateMultipleConditions($qb, $qb->expr()->orX(), 'phones.brand = :%s', 'brand', [
            'Nokia',
            'Apple',
            'HTC'
        ]);
        $this->generateRangeConditions($qb, 'costs.cost >= :%s AND costs.cost <= :%s', 'cost', [500, 1000]);
        $this->generateMultipleConditions($qb, $qb->expr()->orX(), 'phones.os = :%s', 'os', [
            'iOS',
            'Microsoft Windows Phone',
            'Android'
        ]);

        $query = $qb->getQuery();
        return $query->getResult();
        return $query;
    }

    /**
     * @param \Doctrine\ORM\QueryBuilder $qBuilder
     * @param $expr
     * @param string $pattern
     * @param string $fieldName
     * @param array $values
     */
    private function generateMultipleConditions($qBuilder, $expr, $pattern, $fieldName, $values)
    {
        foreach ($values as $key => $value) {
            $paramName = $fieldName . $key;
            $condition = sprintf($pattern, $paramName);
            $expr->add($condition);
            $qBuilder->setParameter($paramName, $value);
        }

        if (!empty($values)) {
            $qBuilder->andWhere($expr);
        }
    }

    /**
     * @param \Doctrine\ORM\QueryBuilder $qBuilder
     * @param $expr
     * @param string $pattern
     * @param string $fieldName
     * @param array $values
     */
    private function generateLikeConditions($qBuilder, $expr, $pattern, $fieldName, $values)
    {
        foreach ($values as $value) {
            $paramName = 'aaa'.rand(1,5);
            $expr->add($qBuilder->expr()->like($pattern, ':'.$paramName));
            $qBuilder->setParameter($paramName, $value);
        }

        if (!empty($values)) {
            $qBuilder->andWhere($expr);
        }
    }

    /**
     * @param \Doctrine\ORM\QueryBuilder $qBuilder
     * @param string $pattern
     * @param string $fieldName
     * @param array  $values
     */
    private function generateRangeConditions($qBuilder, $pattern, $fieldName, $values)
    {
        if (!empty($values)) {
            $paramFromName = $fieldName. 0;
            $paramToName   = $fieldName. 1;
            $qBuilder->setParameter($paramFromName, $values[0]);
            $qBuilder->setParameter($paramToName, $values[1]);

            $condition = sprintf($pattern, $paramFromName, $paramToName);
            $qBuilder->andWhere($condition);
        }
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    private function getQueryBuilder()
    {
        $em = $this->getDoctrine()->getEntityManager();
        $qb = $em->createQueryBuilder();

        return $qb;
    }

    /**
     * @return array|\Phones\PhoneBundle\Entity\Phone[]
     */
    private function getPhones()
    {
        $products = $this->getDoctrine()
            ->getRepository('PhonesPhoneBundle:Phone')
            ->findAll();

        return $products;
    }

    private function getExistingOs()
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getRepository('PhonesPhoneBundle:Phone');
        $qb = $em->createQueryBuilder('a')->groupBy('a.os');
        $result = $qb->getQuery()->getResult();

        $distinctOs = [];
        /** @var Phone $os */
        foreach($result as $os) {
            $distinctOs[] = $os->getOs();
        }

        return $distinctOs;
    }

    private function getExistingBrands()
    {
        $qb = $this->getDoctrine()->getRepository('PhonesPhoneBundle:Phone')->createQueryBuilder('a')->groupBy('a.brand');
        $result = $qb->getQuery()->getResult();

        $distinct = [];
        /** @var Phone $os */
        foreach($result as $os) {
            $distinct[] = $os->getBrand();
        }

        return $distinct;
    }
}
