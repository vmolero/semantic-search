<?php

namespace Semantics\RatingBundle\Controller;

use Semantics\RatingBundle\Interfaces\IEntity;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class CriteriaController extends Controller
{
    public function indexAction()
    {
        $r  = ($this->getDoctrine()
                        ->getRepository('RatingBundle:Topic')
                        ->findAll());
        /** @var Topic $r */
        $r2 = array_map(function (IEntity $entity) {
            return $entity->toArray();
        }, $r);

        return $this->render('RatingBundle:Criteria:index.html.twig', ['list' => $r2]);
    }
    /**
     *
     * @param Request $request
     * @return type
     */
    public function ajaxfetchallAction(Request $request)
    {
        $r  = $this->getDoctrine()
                ->getRepository('RatingBundle:TopicWord')
                ->findBy([], ['id' => $request->query->get('sord')], $request->query->get('rows'), ($request->query->get('page') - 1) * $request->query->get('rows'));
        /** @var Review $r */
        $r2 = array_map(function (IEntity $entity) {
            return $entity->toArray();
        }, $r);

        $response = new JsonResponse();
        $response->setData($r2);
        $response->setCallback($request->query->get('callback'));
        return $response;
    }
}
