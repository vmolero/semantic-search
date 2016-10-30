<?php

namespace Semantics\RatingBundle\Controller;

use Semantics\RatingBundle\Entity\Review;
use Semantics\RatingBundle\Interfaces\SemanticEntityHolder;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as RTG;

/**
 * @RTG\Route("/review")
 */
class ReviewController extends Controller
{
    /**
     * @RTG\Route("/")
     * @RTG\Method({"get"})
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

        return $this->render('RatingBundle:Review:index.html.twig', ['page'=> 'index','reviews' => $r2]);
    }
    /**
     * @RTG\Route("/{id}/delete")
     * @RTG\Method({"get"})
     * @param Request $request
     * @return type
     */
    public function deleteAction(Request $request, $id)
    {
        $entity = $this->getDoctrine()->getRepository('RatingBundle:Review')->find($id);
        $this->get('DoctrinePersister')->delete($entity);
        return $this->redirect('/review');
    }
}
