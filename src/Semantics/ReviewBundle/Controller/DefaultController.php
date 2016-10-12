<?php

namespace Semantics\ReviewBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('ReviewBundle:Default:index.html.twig');
    }
    public function newAction()
    {
        return $this->render('ReviewBundle:Default:index.html.twig');
    }
}
