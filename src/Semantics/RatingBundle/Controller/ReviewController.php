<?php

namespace Semantics\RatingBundle\Controller;

use Semantics\RatingBundle\Entity\Review;
use Semantics\RatingBundle\Interfaces\SemanticEntityHolder;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class ReviewController extends Controller
{
    /**
     *
     * @param Request $request
     * @return type
     */
    public function indexAction(Request $request)
    {
        $r  = ($this->getDoctrine()
                        ->getRepository('RatingBundle:Review')
                        ->findAll());
        /** @var Review $r */
        $r2 = array_map(function (SemanticEntityHolder $entity) {
            return $entity->toArray();
        }, $r);

        return $this->render('RatingBundle:Review:index.html.twig', ['list' => $r2]);
    }
    /**
     *
     * @param Request $request
     * @return type
     */
    public function ajaxfetchallAction(Request $request)
    {
        $r  = $this->getDoctrine()
                ->getRepository('RatingBundle:Review')
                ->findBy([], ['id' => $request->query->get('sord')], $request->query->get('rows'), ($request->query->get('page') - 1) * $request->query->get('rows'));
        /** @var Review $r */
        $r2 = array_map(function (SemanticEntityHolder $entity) {
            return $entity->toArray();
        }, $r);

        $response = new JsonResponse();
        $response->setData($r2);
        $response->setCallback($request->query->get('callback'));
        return $response;
    }
}
