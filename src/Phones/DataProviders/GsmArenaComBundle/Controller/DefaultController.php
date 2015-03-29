<?php

namespace Phones\DataProviders\GsmArenaComBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
    private function getMainDownloader()
    {
        return $this->get('phones_data_providers_gsm_arena_com.main_downloader');
    }

    /**
     * @return Response
     */
    public function testResultsAction(){
        $this->getMainDownloader()->download();

        return $this->render('PhonesDataProvidersGsmArenaComBundle:Default:test.html.twig', []);
    }
}
