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

        $foundPhones = [];
        $points      = [];
        foreach ($phones as $phone) {
            if (isset($phone['phoneId'])) {
                $foundPhones[] = $this->getQueryHelper()->getPhone($phone['phoneId']);
                $points[] = isset($phone['points']) ? $phone['points'] : null;
            }
        }

        $params['products'] = $foundPhones;
        $params['points'] = $points;

        return $this->render('PhonesFrontEndBundle:Default:results.html.twig', $params);
    }

    /* DATA */

    /**
     * @return Response
     */
    public function gsmArenaComAction(){
        $mainDownloader = $this->get('phones_data_providers_gsm_arena_com.main_downloader');
        $mainDownloader->download();

        return $this->render('PhonesFrontEndBundle:Default:test.html.twig', []);
    }

    /* COSTS */

    /**
    * @return Response
    */
    public function teleArenaLtAction()
    {
        $mainDownloader = $this->get('phones_cost_providers_tele_arena_lt.main_downloader');
        $mainDownloader->download();

        return $this->render('PhonesFrontEndBundle:Default:test.html.twig', []);
    }

    /**
    * @return Response
    */
    public function gsmArenaLtAction()
    {
        $mainDownloader = $this->get('phones_cost_providers_gsm_arena_lt.main_downloader');
        $mainDownloader->download();

        return $this->render('PhonesFrontEndBundle:Default:test.html.twig', []);
    }

    /**
     * @return Response
     */
    public function mobiliLinijaAction()
    {
        $mainDownloader = $this->get('phones_cost_providers_mobili_linija.main_downloader');
        $mainDownloader->download();

        return $this->render('PhonesFrontEndBundle:Default:test.html.twig', []);
    }

    /* STATS */

    /**
     * @return Response
     */
    public function dxOMarkComAction()
    {
        $mainDownloader = $this->get('phones_stat_providers_dx_o_mark_com.main_downloader');
        $mainDownloader->download();

        return $this->render('PhonesFrontEndBundle:Default:test.html.twig', []);
    }

    /**
     * @return Response
     */
    public function gsmArenaComBatteryLifeAction()
    {
        $mainDownloader = $this->get('phones_stat_providers_gsm_arena_com_battery_life.main_downloader');
        $mainDownloader->download();

        return $this->render('PhonesFrontEndBundle:Default:test.html.twig', []);
    }

    /**
     * @return Response
     */
    public function gsmArenaComBasemarkXAction()
    {
        $mainDownloader = $this->get('phones_stat_providers_gsm_arena_com_basemark_x.main_downloader');
        $mainDownloader->download();

        return $this->render('PhonesFrontEndBundle:Default:test.html.twig', []);
    }

    /**
     * @return Response
     */
    public function gsmArenaComBasemarkOSIIAction()
    {
        $mainDownloader = $this->get('phones_stat_providers_gsm_arena_com_basemark_os_ii.main_downloader');
        $mainDownloader->download();

        return $this->render('PhonesFrontEndBundle:Default:test.html.twig', []);
    }

    /**
     * @return Response
     */
    public function phoneArenaComCameraSpeedAction()
    {
        $mainDownloader = $this->get('phones_stat_providers_phone_arena_com_camera_speed.main_downloader');
        $mainDownloader->download();

        return $this->render('PhonesFrontEndBundle:Default:test.html.twig', []);
    }

    /**
     * @return Response
     */
    public function phoneArenaComChargingAction()
    {
        $mainDownloader = $this->get('phones_stat_providers_phone_arena_com_charging.main_downloader');
        $mainDownloader->download();

        return $this->render('PhonesFrontEndBundle:Default:test.html.twig', []);
    }
}
