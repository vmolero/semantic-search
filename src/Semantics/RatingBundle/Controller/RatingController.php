<?php

namespace Semantics\RatingBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class RatingController extends Controller
{
    public function indexAction()
    {
        return $this->render('RatingBundle:Rating:index.html.twig', ['base_dir' => realpath($this->container->getParameter('kernel.root_dir') . '/..') . DIRECTORY_SEPARATOR]);
    }
}
