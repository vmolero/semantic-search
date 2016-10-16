<?php

namespace Semantics\RatingBundle\Services;

use Semantics\RatingBundle\Entity\Corpus;
use Semantics\RatingBundle\Entity\Expression;
use Semantics\RatingBundle\Entity\Review;
use Semantics\RatingBundle\Entity\Topic;
use Semantics\RatingBundle\Entity\Word;
use Semantics\RatingBundle\Interfaces\SemanticEntityHolder;
use Semantics\RatingBundle\Interfaces\PersistableReview;
use Semantics\RatingBundle\Services\MorphAdornerService;
use Semantics\RatingBundle\Services\RepositoryBuilderService;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\HttpFoundation\Response;

/**
 * Description of AverageDifference
 *
 * @author Víctor Molero
 */
final class DoctrinePersister implements PersistableReview
{
    /**
     *
     * @var RegistryInterface
     */
    protected $doctrine;
    /**
     *
     * @var MorphAdornerService
     */
    protected $morph;
    /**
     *
     * @var RepositoryBuilderService
     */
    protected $builder;
    /**
     *
     * @var ReviewEntity
     */
    protected $review;

    /**
     *
     * @param RegistryInterface $orm
     * @param MorphAdornerService $morph
     * @param RepositoryBuilderService $builder
     */
    public function __construct(RegistryInterface $orm, MorphAdornerService $morph, RepositoryBuilderService $builder, Logger $logger)
    {
        $this->doctrine = $orm;
        $this->morph    = $morph;
        $this->builder  = $builder;
        $this->logger   = $logger;
    }
    public function initReview($review)
    {
        $score        = $this->morph->sentimentAnalyzer($review);
        $this->entity = $this->builder->create(Review::class)->build(['review' => $review, 'hash' => md5($review)] + $score)->getConcrete();
        $this->entity->setLines(array_map(function($line) {
                    $lineEntity = $this->parseExpression($this->cleanup($line), $this->review);
                    return $lineEntity;
                }, $this->split($this->entity->getReview())));
        $this->entity->setTopics(array_map(function (SemanticEntityHolder $topic) {
                    return $topic->getTopic();
                }, $this->doctrine->getRepository('RatingBundle:Topic')->findAll()));
        return $this;
    }
    public function saveReview($review)
    {
        $entity = $review;
        if (is_string($review)) {
            $entity = $this->initReview($review)->getEntity();
        }
        return $this->saveEntity($review);
    }
    public function getReview()
    {
        return $this->review;
    }
    /**
     *
     * @param RevireEntity $review
     * @return $this
     */
    private function saveEntity(SemanticEntityHolder $review)
    {
        $myReviewEntity = $this->doctrine->getRepository('RatingBundle:Review')->findOneBy(['hash' => $review->getHash()]);
        if (!$myReviewEntity instanceof IEntity) {
            $this->doctrine->getManager()->persist($review);
        } else {
            $myReviewEntity->copy($review);
        }
        $this->doctrine->getManager()->flush();
        return $this;
    }
    /**
     *
     * @param string $expression
     * @param IEntity $reviewEntity
     * @param IEntity $lineEntity
     * @param array $score
     * @return IEntity
     */
    private function parseExpression($expression, SemanticEntityHolder $reviewEntity, SemanticEntityHolder $lineEntity = null, array $score = [])
    {
        $entityScore = empty($score) ? $this->morph->sentimentAnalyzer($expression) : $score;
        return $this->builder->create(Expression::class)
                        ->build(['review' => $reviewEntity,
                            'sentence' => $lineEntity,
                            'hash' => md5($expression),
                            'expression' => $expression] + $entityScore)->getConcrete();
    }
    protected function split($text)
    {
        return array_filter(explode('.', $text), function ($line) {
            return strlen(trim($line)) > 0 && preg_match('/[a-zA-Z]/', trim($line));
        });
    }
    protected function cleanup($text)
    {
        return trim(preg_replace('/[^a-zA-Z,;\s\']+/', ' ', $text));
    }
    public function lexiexprAction()
    {
        $r = ($this->getDoctrine()
                        ->getRepository('RatingBundle:Expression')
                        ->findAll());

        $r2 = array_map(function (Expression $exp) {
            $this->saveWords(array_filter(explode(' ', trim($exp->getExpression()))));
            return $exp->toArray();
        }, $r);

        return $this->render('RatingBundle:Rating:index.html.twig', ['list' => $r2]);
    }
    public function lexitopicAction()
    {
        $this->saveWords(array_map(function (Topic $exp) {
                    return $exp->getTopic();
                }, $this->getDoctrine()
                                ->getRepository('RatingBundle:Topic')
                                ->findAll()));

        return new Response('OK!');
    }
    public function lexiwordAction()
    {
        $this->saveWords(array_map(function (Word $exp) {
                    return $exp->getWord();
                }, $this->getDoctrine()
                                ->getRepository('RatingBundle:Word')
                                ->findBy(['corpusId' => null])));

        return new Response('OK!');
    }
    private function saveWords(array $words)
    {
        $orm     = $this->getDoctrine();
        $morph   = $this->get('MorphAdornerService');
        $builder = $this->get('RepositoryBuilderService');
        foreach ($words as $word) {
            $wordEntity = $orm->getRepository('RatingBundle:Word')
                    ->save($orm, $builder->create(Word::class)
                    ->build(['word' => $word])
                    ->getConcrete()
            );
            if (null === $wordEntity->getCorpusId()) {
                $corpusEntity = $builder->create(Corpus::class)
                        ->build($morph->lexiconLookup($word))
                        ->getConcrete();
                $corpusId     = $orm->getRepository('RatingBundle:Corpus')
                                ->save($orm, $corpusEntity)->getId();
                $wordEntity->setCorpusId($corpusId);
            }
        }
        return true;
    }
}
