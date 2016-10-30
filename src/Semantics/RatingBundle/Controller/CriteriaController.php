<?php

namespace Semantics\RatingBundle\Controller;

use Semantics\RatingBundle\Interfaces\SemanticEntityHolder;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Semantics\RatingBundle\Entity\Topic;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as RTG;
/**
 * @RTG\Route("/criteria")
 */
class CriteriaController extends Controller
{
    /**
     * @RTG\Route("/")
     * @RTG\Method({"get"})
     * @param Request $request
     * @return type
     */
    public function indexAction(Request $request)
    {
        $r  = ($this->getDoctrine()
                        ->getRepository('RatingBundle:Topic')
                        ->findAll());
        /** @var Review $r */
        $r2 = array_map(function (SemanticEntityHolder $entity) {
            return $entity->toArray();
        }, $r);

        return $this->render('RatingBundle:Criteria:index.html.twig', ['page'=> 'criteria','topics' => $r2]);
    }
    /**
     * @RTG\Route("/add")
     * @RTG\Method({"post"})
     *
     * @param Request $request
     * @return Response
     */
    public function addAction(Request $request)
    {
        $entity = $this->getDoctrine()->getRepository('RatingBundle:Topic')->findOneBy(['topic' => $request->get('topic'), 'tag' => $request->get('tag')]);
        if (!$entity instanceof Topic) {
            $entity = $this->get('RepositoryBuilder')->create(Topic::class)->build(['topic' => $request->get('topic'), 'tag' => $request->get('tag')])->getConcrete();
           $this->getDoctrine()->getManager()->persist($entity);
           $this->getDoctrine()->getManager()->flush();
        }
        return $this->indexAction($request);
    }
    /**
     * @RTG\Route("/{id}/delete")
     * @RTG\Method({"get"})
     * @param Request $request
     * @return type
     */
    public function deleteAction(Request $request, $id)
    {
        $entity = $this->getDoctrine()->getRepository('RatingBundle:Topic')->find($id);
        $this->get('DoctrinePersister')->delete($entity);
        return $this->redirect('/criteria');
    }
}
