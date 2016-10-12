<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Corpus;
use AppBundle\Entity\Word;
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
     *
     * @Route("/parse", name="parse")
     * @param Request $request
     */
    public function parseAction(Request $request)
    {
        //$input = $request->get('review');
        //$words = $this->parseReview($input);
        //return new Response(implode('+', $words));
        $word = 'happiest';

        ob_start();
        $lexicon = $this->getMorphAdornerService()->lexiconLookup($word);
        print_r($lexicon);

        return new Response(ob_get_clean());
    }
    /**
     *
     * @param type $input
     * @return type
     */
    private function parseReview($input)
    {
        $lines = explode('.', $input);
        $words = [];
        foreach ($lines as $line) {
            $words += $this->toPorterStem($line);
        }
        return $words;
    }
    /**
     *
     * @param type $line
     * @return type
     */
    private function toPorterStem($line)
    {
        return array_filter(array_map(function($single) {
                    return $this->toWord(trim(preg_replace('/[^A-Za-z]/', '', $single)));
                }, explode(' ', $line)));
    }
    /**
     *
     * @param array $criteria
     * @return type
     */
    private function corpusLookup(array $criteria)
    {
        $r = ($this->getDoctrine()
                        ->getRepository('AppBundle:Corpus')
                        ->findBy($criteria));
        return count($r) ? array_shift($r) : false;
    }
    /**
     *
     * @param type $stem
     * @return type
     */
    private function corpusLookupByStem($stem)
    {
        return current($this->getDoctrine()
                        ->getRepository('AppBundle:Corpus')
                        ->findBy(['stem' => $stem]));
    }
    /**
     *
     * @param type $word
     */
    private function toWord($word)
    {
        if ($word = corpusLookup(['stem' => $stem]))
            $em   = $this->getDoctrine()->getManager();
        $stem = PorterStemmer::Stem($word);
        $cc   = $this->getDoctrine()
                ->getRepository('AppBundle:Corpus')
                ->findBy(['stem' => $stem]);
        if (empty($cc)) {
            $c = new Corpus();
            $c->setStem($stem);
            $c->setPrescore(0);
            $em->persist($c);
            $em->flush();
            $w = new Word();
            $w->setWord($word);
            $w->setCorpusId($c->getId());
            $em->persist($w);
            $em->flush();
        }
    }
    /**
     * @Route("/import", name="import")
     * @param Request $request
     * @return Response
     */
    public function importCSVAction(Request $request)
    {
        $em   = $this->getDoctrine()->getManager();
        $file = fopen(realpath($this->container->getParameter('kernel.root_dir')) . '/Resources/csv/corpus.csv', 'r');
        ob_start();
        fgetcsv($file, 1000);
        while ($line = fgetcsv($file, 1000)) {
            if (strlen($line[2]) > 0) {
                $entry = new Corpus();
                $entry->setStem(trim($line[2]));
                $entry->setLemma($this->remoteLexiconLookup($line[2]));
                $entry->setPrescore($this->remoteSentimentAnalysis($line[2]));
                $entry->setFeedback(1);
                $em->persist($entry);
                $em->flush();
                print_r($entry->toArray());
            }
            if (strlen($line[3]) > 0) {
                $entry = new Corpus();
                $entry->setStem(trim($line[3]));
                $entry->setLemma($this->remoteLexiconLookup($line[3]));
                $entry->setPrescore($this->remoteSentimentAnalysis($line[3]));
                $entry->setFeedback(0);
                $em->persist($entry);
                $em->flush();
                print_r($entry->toArray());
            }
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
        $query = $em->createQuery('SELECT * FROM AppBundle:Review');
        $r     = $query->getResult();
        var_dump($r);
        die;
        return new Response($reviewId ?: "ALL");
    }
    /**
     * @Route("/info", name="phpinfo")
     */
    public function infoAction()
    {
        ob_start();
        echo phpinfo();
        return new Response(ob_get_clean());
    }
}
