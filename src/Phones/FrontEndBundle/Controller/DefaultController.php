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
        return $this->render('PhonesFrontEndBundle:Default:index.html.twig');
//        $id = 'Apple iPhone 6';s
//        $product = $this->getDoctrine()
//            ->getRepository('PhonesPhoneBundle:Phone')
//            ->find($id);
//
//        $costs = $product->getCosts();
////        var_dump($costs);
//        if ($costs) {
//            /** @var Cost $cost */
//            foreach ($costs as $cost) {
//                var_dump($cost->getPhone()->getCosts());
//            }
//        }
//
//        if (!$product) {
//            throw $this->createNotFoundException(
//                'No product found for id '.$id
//            );
//        }
    }
}
