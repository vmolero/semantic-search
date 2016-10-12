<?php

namespace Semantics\RatingBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class CriteriaController extends Controller
{
    public function indexAction()
    {
        return $this->render('RatingBundle:Criteria:index.html.twig');
    }
}
