<?php

namespace Semantics\RatingBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ReviewController extends Controller
{
    public function indexAction()
    {
        return $this->render('RatingBundle:Review:index.html.twig');
    }
}
