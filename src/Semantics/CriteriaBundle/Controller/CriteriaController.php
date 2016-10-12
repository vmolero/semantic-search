<?php

namespace Semantics\CriteriaBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class CriteriaController extends Controller
{
    public function indexAction()
    {
        return $this->render('CriteriaBundle:Criteria:index.html.twig');
    }
}
