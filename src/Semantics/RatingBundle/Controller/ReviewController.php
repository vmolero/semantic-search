<?php

namespace Semantics\RatingBundle\Controller;

use Semantics\RatingBundle\Entity\Review;
use Semantics\RatingBundle\Interfaces\IEntity;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class ReviewController extends Controller
{
    public function indexAction(Request $request)
    {
        $r  = ($this->getDoctrine()
                        ->getRepository('RatingBundle:Review')
                        ->findAll());
        /** @var Review $r */
        $r2 = array_map(function (IEntity $entity) {
            return $entity->toArray();
        }, $r);

        return $this->render('RatingBundle:Review:index.html.twig', ['list' => $r2]);
    }
}
