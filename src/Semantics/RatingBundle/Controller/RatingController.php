<?php

namespace Semantics\RatingBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as RTG;

/**
 * @RTG\Route("/rate")
 */
class RatingController extends Controller
{
    
    /**
     * @RTG\Route("/new")
     * @RTG\Method({"get"})
     * @param Request $request
     * @return Response
     */
    public function newAction(Request $request)
    {
        return $this->render('RatingBundle:Rating:new.html.twig', ['page'=> 'rate']);
    }

    /**
     * @RTG\Route("/review")
     * @RTG\Method({"post"})
     *
     * @param Request $request
     * @return Response
     */
    public function doAction(Request $request)
    {
        $input = $request->request->get('review');
        if (strlen($input) > 0) {
        $scoredReview = $this->get('SemanticApp')->handle($input);
        if ($request->isXmlHttpRequest()) {
            return new JsonResponse($scoredReview->toString());
        }
        return $this->render('RatingBundle:Rating:review.html.twig', ['page'=> 'rate', 'review' => $scoredReview->toArray()]);
        }
        return $this->redirect('/rate/new');
    }
}
