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

    public function bestPhoneSearchAction()
    {
        $params =[];
        $params['products'] = $this->getPhones();

        // create a task and give it some dummy data for this example
//        $task = new Task();
//        $task->setTask('Write a blog post');
//        $task->setDueDate(new \DateTime('tomorrow'));

        $rowOptions = [
            'attr' => [
                'class' => 'form-control'
            ],
            'label_attr' => [
//                'class' => 'col-sm-2 control-label'
                'class' => 'control-label'
            ],
//            'placeholder' => 'type sth'
        ];
        $selectOptions = [
            'label'=> 'Paieska',
            'attr' => [
                'class' => 'btn btn-success'
            ]
        ];
        $formOptions = [
            'attr' => [
                'class' => 'form-horizontal'
            ]
        ];

        $form = $this->createFormBuilder(null, $formOptions)
            ->add('brand', 'text', $rowOptions) //select and checkbox(multi select)
            ->add('cost', 'text', $rowOptions) //range
            ->add('weight', 'text', $rowOptions) //range
            ->add('os', 'text', $rowOptions) //select and checkbox(multi select)
            ->add('cpu_freq', 'text', $rowOptions) //range
            ->add('cpu_cores', 'text', $rowOptions) //range
            ->add('ram', 'text', $rowOptions) //range
            ->add('external_sd', 'checkbox', $rowOptions) //bool
            ->add('display_size', 'text', $rowOptions) //range
            ->add('display_resolution', 'text', $rowOptions) //range
            ->add('camera_mpx', 'text', $rowOptions) //range
            ->add('video_p', 'text', $rowOptions) //range? gal isvis nereik
            ->add('flash', 'checkbox', $rowOptions) //bool
            ->add('technology', 'text', $rowOptions) //select and checkbox(multi select)
            ->add('gps', 'text', $rowOptions) //select and checkbox(multi select)
            ->add('wlan', 'text', $rowOptions) //select and checkbox(multi select)
            ->add('bluetoot_version', 'text', $rowOptions) //select and checkbox(multi select)
            ->add('battery_stand_by', 'text', $rowOptions) //range
            ->add('battery_talk_time', 'text', $rowOptions) //range

            ->add('submit', 'submit', $selectOptions)
            ->getForm();

        $params['form'] = $form->createView();

        return $this->render('PhonesFrontEndBundle:Default:best.phone.search.html.twig', $params);
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
