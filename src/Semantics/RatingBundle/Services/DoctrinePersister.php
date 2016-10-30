<?php

namespace Semantics\RatingBundle\Services;

use Doctrine\Common\Util\Debug;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Semantics\RatingBundle\Entity\Corpus;
use Semantics\RatingBundle\Entity\Expression;
use Semantics\RatingBundle\Entity\ExpressionWord;
use Semantics\RatingBundle\Entity\Review;
use Semantics\RatingBundle\Entity\Word;
use Semantics\RatingBundle\Interfaces\ReviewPersister;
use Semantics\RatingBundle\Interfaces\SemanticEntityHolder;
use Semantics\RatingBundle\Services\MorphAdornerService;
use Semantics\RatingBundle\Services\RepositoryBuilderService;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Bridge\Monolog\Logger;

/**
 * Description of DoctrinePersister
 *
 * @author Víctor Molero
 */
final class DoctrinePersister implements ReviewPersister
{
    /**
     *
     * @var RegistryInterface
     */
    private $doctrine;
    /**
     *
     * @var MorphAdorner
     */
    private $morph;
    /**
     *
     * @var RepositoryBuilder
     */
    private $builder;
    /**
     *
     * @var ReviewEntity
     */
    private $review;

    

    /**
     *
     * @param RegistryInterface $orm
     * @param MorphAdornerService $morph
     * @param RepositoryBuilderService $builder
     */
    public function __construct(RegistryInterface $orm, MorphAdorner $morph, RepositoryBuilder $builder, Logger $logger)
    {
        $this->doctrine = $orm;
        $this->morph    = $morph;
        $this->builder  = $builder;
        $this->logger   = $logger;
    }
    public function initReview($review)
    {
        $uui = md5(trim($review));
        $this->review = $this->doctrine->getRepository('RatingBundle:Review')->findOneBy(['hash' => $uui]);
        if (!$this->review instanceof Review) {
            $score        = $this->morph->sentimentAnalyzer($review);
            $this->review = $this->builder->create(Review::class)->build(['review' => trim($review), 'hash' => $uui] + $score)->getConcrete();
        }
        $this->review->setTopics($this->doctrine->getRepository('RatingBundle:Topic')->findAll());
        return $this;
    }
    public function saveReview($review)
    {
        $entity = $review;
        if (is_string($review)) {
            $entity = $this->initReview($review)->getEntity();
        }
        return $this->saveEntity($entity);
    }
    public function getReview()
    {
        return $this->review;
    }
    public function delete ($entity) {
        $this->doctrine->getManager()->remove($entity);
        $this->doctrine->getManager()->flush();
        return $this;
    }
    /**
     *
     * @param RevireEntity $review
     * @return $this
     */
    private function saveEntity(SemanticEntityHolder $review)
    {
        try {
            $myReviewEntity = $this->doctrine->getRepository('RatingBundle:Review')->findOneBy(['hash' => $review->getHash()]);
            if (!$myReviewEntity instanceof SemanticEntityHolder) {
                $this->doctrine->getManager()->persist($review);
                $this->doctrine->getManager()->flush();
                return $this;
            }
        } catch (UniqueConstraintViolationException $exc) {
            // var_dump($exc->getMessage());
            // die;
            // return $this;
            throw $exc;
        } finally {
            return $this;
        }
    }
    /**
     *
     * @param string $expression
     * @param SemanticEntityHolder $reviewEntity
     * @param SemanticEntityHolder $lineEntity
     * @param array $score
     * @return SemanticEntityHolder
     */
    private function parseExpression($expression, SemanticEntityHolder $reviewEntity, SemanticEntityHolder $lineEntity = null, array $score = [])
    {
        $expressionEntity = $this->doctrine->getRepository('RatingBundle:Expression')->findOneBy(['hash' => md5($expression)]);
        if (!$expressionEntity instanceof Expression) {
            $entityScore      = empty($score) ? $this->morph->sentimentAnalyzer($expression) : $score;
            $expressionEntity = $this->builder->create(Expression::class)
                            ->build(['review' => $reviewEntity,
                                'hash' => md5($expression),
                                'expression' => $expression] + $entityScore)->getConcrete();
            return $expressionEntity;
        }
        return $expressionEntity;
    }
    
    
    
    private function initWord($word)
    {
        $debug = false;
        if ($word == 'bad') {
            $debug = true;
        }
        $wordEntity = $this->doctrine->getRepository('RatingBundle:Word')->findOneBy(['word' => $word]);
        if ($debug) {
            //print_r($wordEntity->toArray());
            //die;
        }
        if (!$wordEntity instanceof Word) {
            $wordEntity = $this->builder->create(Word::class)->build(['word' => $word])->getConcrete();
        }
        if (!$wordEntity->getCorpus()) {
            $lexicon      = $this->morph->lexiconLookup($word);
            $corpusEntity = $this->doctrine->getRepository('RatingBundle:Corpus')->findOneBy(['lemma' => $lexicon['lemma']]);
            if (!$corpusEntity instanceof Corpus) {
                $corpusEntity = $this->builder->create(Corpus::class)->build($lexicon)->getConcrete();
            }
            $wordEntity->setCorpus($corpusEntity);
        }
        return $wordEntity;
    }
}
