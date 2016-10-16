<?php

namespace Semantics\RatingBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as RTG;

/**
 * @RTG\Route("/rate")
 */
class RatingController extends Controller
{
    /**
     * @RTG\Route("/review")
     * @RTG\Method({"post"})
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function reviewAction(Request $request)
    {
        $scoredReview = $this->get('SemanticApp')->handle($request->request->get('review'));
        if ($request->isXmlHttpRequest()) {
            return new JsonResponse($scoredReview->toString());
        }
        return $this->render('RatingBundle:Rating:review.html.twig', ['review' => $scoredReview->toArray()]);
    }
}
