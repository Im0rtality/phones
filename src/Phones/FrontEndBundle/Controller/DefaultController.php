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
        return $this->render('PhonesFrontEndBundle:Default:index.html.twig', []);
    }

    /**
     * @Route("/phones/{currentPage}")
     * @param int $currentPage
     *
     * @return Response
     */
    public function phonesAction($currentPage)
    {
        $params = [];
        $pageSize = 72;
        $data = $this->getQueryHelper()->getPhones($currentPage, $pageSize);
        list($phones, $pageCount) = $data;

        $params['products']       = $phones;
        $params['dynamicPages']   = range(1, $pageCount);
        $params['totalPageCount'] = $pageCount;
        $params['currentPage']    = $currentPage;

        $params['dynamicPages'] = $this->getDynamicPages($currentPage, $pageCount);

        return $this->render('PhonesFrontEndBundle:Default:phones.html.twig', $params);
    }

    /**
     * @param $currentPage
     * @param $pageCount
     *
     * @return array
     */
    private function getDynamicPages($currentPage, $pageCount)
    {
        $previousPageCount = $currentPage > 1 ? $currentPage - 1 : 0;
        $nextPageCount = $pageCount - $currentPage;

        $totalPreviousPageCount = ($previousPageCount >= 5) ? 5 : $previousPageCount;
        $totalNextPageCount = ($nextPageCount >= 5) ? 5 + (5 - $totalPreviousPageCount) : $nextPageCount;
//        $totalPreviousPageCount += 5 - $totalNextPageCount;

        $pages = [];
        if ($previousPageCount != 0) {
            $pages = range($currentPage - $totalPreviousPageCount, $previousPageCount);
        }
        $pages = array_merge($pages, range($currentPage, $currentPage + $totalNextPageCount));

        if (($currentPage - (5+1)) > 0) {
            if ($currentPage - (5+1) > 1) {
                array_unshift($pages, '...');
            }
            array_unshift($pages, 1);
        }

        if (($pageCount - $currentPage) > 5) {
            if ($currentPage != ($pageCount - 1)) {
                $pages[] = '...';
            }
            $pages[] = $pageCount;
        }

        return $pages;
    }

    /**
     * @Route("/phones/phone/{phoneId}")
     */
    public function singlePhoneAction($phoneId)
    {
        $phone = $this->getQueryHelper()->getPhone($phoneId);

        if (!empty($phone)) {
            $params = [];
            $params['phone']               = $phone;
            $params['relatedPhones']       = $this->getQueryHelper()->getRelatedPhonesByBrand($phone);
            $params['specificationsMain']  = $this->getQueryHelper()->getPhoneMainSpecs($phone);
            $params['specificationsOther'] = $this->getQueryHelper()->getPhoneOtherSpecs($phone);
            $params['costs']               = $this->getQueryHelper()->getPhoneCosts($phone);
            $params['generalRating']       = $this->getQueryHelper()->getPhoneGeneralRating($phone, 2);
            $params['ratings']             = $this->getQueryHelper()->getPhoneRatings($phone, 0);

            return $this->render('PhonesFrontEndBundle:Default:single.phone.html.twig', $params);
        } else {
            return $this->render('PhonesFrontEndBundle:Default:404.html.twig', []);
        }
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function phoneSearchAction(Request $request)
    {
        $phoneId = $request->get('phone-search-value', null);

        return $this->singlePhoneAction($phoneId);
    }

    /**
     * @return Response
     */
    public function getPhonesJsonAction()
    {
        $phones = $this->getQueryHelper()->getPhonesJson();

        $response = new Response(json_encode($phones));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * @return Response
     */
    public function bestPhoneSearchAction()
    {
        $params = [];
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
        set_time_limit(0);

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
