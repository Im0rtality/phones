<?php

namespace Phones\FrontEndBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction()
    {
        $params =[];
        $params['products'] = $this->getPhones();

        return $this->render('PhonesFrontEndBundle:Default:index.html.twig', $params);
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
}
