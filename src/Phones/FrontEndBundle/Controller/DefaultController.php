<?php

namespace Phones\FrontEndBundle\Controller;

use Phones\FrontEndBundle\Service\QueryHelper;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
    /**
     * @return QueryHelper
     */
    public function getQueryHelper()
    {
        return $this->get('phones_front_end.query_helper');
    }

    /**
     * @Route("/", name="homepage")
     *
     * @return Response
     */
    public function indexAction()
    {
        $params =[];
        $params['products'] = $this->getQueryHelper()->getPhones();

        return $this->render('PhonesFrontEndBundle:Default:index.html.twig', $params);
    }

    /**
     * @return Response
     */
    public function bestPhoneSearchAction()
    {
        $params =[];
        $params['products'] = $this->getQueryHelper()->getPhones();
        $params['distinctOs'] = $this->getQueryHelper()->getExistingOs();
        $params['distinctBrands'] = $this->getQueryHelper()->getExistingBrands();

        return $this->render('PhonesFrontEndBundle:Default:best.phone.search.html.twig', $params);
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function searchResultsAction(Request $request){
        $phones = $this->getQueryHelper()->getBestPhones($request);
        var_dump($phones);
        $params = [];

        return $this->render('PhonesFrontEndBundle:Default:results.html.twig', $params);
    }
}
