<?php

namespace Phones\FrontEndBundle\Controller;

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
        $params['distinctBlueTooth'] = $this->getExistingBlueToothVersions();

        return $this->render('PhonesFrontEndBundle:Default:best.phone.search.html.twig', $params);
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function searchResultsAction(Request $request){
        $brand             = $request->query->get('brand');
        $cost              = $request->query->get('cost');
        $weight            = $request->query->get('weight');
        $os                = $request->query->get('os');
        $cpu_freq          = $request->query->get('cpu_freq');
        $cpu_cores         = $request->query->get('cpu_cores');
        $ram               = $request->query->get('ram');
        $external_sd       = $request->query->get('external_sd');
        $display_size      = $request->query->get('display_size');
        $camera_mpx        = $request->query->get('camera_mpx');
        $video_p           = $request->query->get('video_p');
        $flash             = $request->query->get('flash');
        $technology        = $request->query->get('technology');
        $gps               = $request->query->get('gps');
        $wlan              = $request->query->get('wlan');
        $bluetooth_version = $request->query->get('bluetooth_version');
        $battery_stand_by  = $request->query->get('battery_stand_by');
        $battery_talk_time = $request->query->get('battery_talk_time');

        $params = [];
        $params['results'] = $cost;

        return $this->render('PhonesFrontEndBundle:Default:results.html.twig', $params);
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

    public function getExistingOs()
    {
        $qb = $this->getDoctrine()->getRepository('PhonesPhoneBundle:Phone')->createQueryBuilder('a')->groupBy('a.os');
        $result = $qb->getQuery()->getResult();

        $distinctOs = [];
        /** @var Phone $os */
        foreach($result as $os) {
            $distinctOs[] = $os->getOs();
        }

        return $distinctOs;
    }

    public function getExistingBrands()
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

    public function getExistingBlueToothVersions()
    {
        $qb = $this->getDoctrine()->getRepository('PhonesPhoneBundle:Phone')->createQueryBuilder('a')->groupBy('a.bluetooth_version');
        $result = $qb->getQuery()->getResult();

        $distinct = [];
        /** @var Phone $os */
        foreach($result as $os) {
            $distinct[] = $os->getBluetoothVersion();
        }

        return $distinct;
    }
}
