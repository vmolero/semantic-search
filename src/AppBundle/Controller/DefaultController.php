<?php

namespace AppBundle\Controller;

use AppBundle\VMolero\Semantic\PorterStemmer;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        // replace this example code with whatever you need
        return $this->render('default/index.html.twig', array(
                    'base_dir' => realpath($this->container->getParameter('kernel.root_dir') . '/..') . DIRECTORY_SEPARATOR,
        ));
    }
    /**
     * @Route("/new", name="review")
     * @param Request $request
     */
    public function reviewAction(Request $request)
    {
        return $this->render('default/review.html.twig', []);
    }
    /**
     * @Route("/parse", name="parse")
     * @param Request $request
     */
    public function parseAction(Request $request)
    {
        $input = $request->get('review');
        $lines = explode('.', $input);
        $words = [];
        foreach ($lines as $line) {
            $words += array_filter(array_map(function($single) {
                        return PorterStemmer::Stem(trim(preg_replace('/[^A-Za-z]/', '', $single)));
                    }, explode(' ', $line)));
        }

        return new Response(implode('+', $words));
    }
    /**
     * @Route("/import", name="import")
     * @param Request $request
     * @return Response
     */
    public function importCSVAction(Request $request)
    {
        $file = fopen(realpath($this->container->getParameter('kernel.root_dir')) . '/Resources/csv/reviews.csv', 'r');
        ob_start();
        fgetcsv($file, 1000);
        while ($line = fgetcsv($file, 1000)) {
            echo implode('::', $line) . '<br>';
        }
        return new Response(ob_get_clean());
    }
    /**
     * @Route("/review", name="show_all")
     * @Route("/review/{reviewId}", name="show_one")
     */
    public function showAction(Request $request, $reviewId = null)
    {
        /** @var EntityManager $em */
        $em    = $this->getDoctrine()->getManager();
        /** @var Connection $con */
        $query = $em->createQuery('SELECT * FROM ss_review');
        $r     = $query->getResult();
        var_dump($r);
        die;
        return new Response($reviewId ?: "ALL");
    }
}
