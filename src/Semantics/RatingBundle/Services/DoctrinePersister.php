<?php

namespace Semantics\RatingBundle\Services;

use Semantics\RatingBundle\Entity\Expression;
use Semantics\RatingBundle\Entity\Review;
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
        $score        = $this->morph->sentimentAnalyzer($review);
        $this->review = $this->builder->create(Review::class)->build(['review' => $review, 'hash' => md5($review)] + $score)->getConcrete();
        $this->review->setLines(array_map(function($line) {
                    $lineEntity = $this->parseExpression($this->cleanup($line), $this->review);
                    return $lineEntity;
                }, $this->split($this->review->getReview())));
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
    /**
     *
     * @param RevireEntity $review
     * @return $this
     */
    private function saveEntity(SemanticEntityHolder $review)
    {
        $myReviewEntity = $this->doctrine->getRepository('RatingBundle:Review')->findOneBy(['hash' => $review->getHash()]);
        if (!$myReviewEntity instanceof SemanticEntityHolder) {
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
     * @param SemanticEntityHolder $reviewEntity
     * @param SemanticEntityHolder $lineEntity
     * @param array $score
     * @return SemanticEntityHolder
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
    /**
     *
     * @param string $text
     * @return string
     */
    private function split($text)
    {
        return array_filter(explode('.', $text), function ($line) {
            return strlen(trim($line)) > 0 && preg_match('/[a-zA-Z]/', trim($line));
        });
    }
    /**
     *
     * @param string $text
     * @return string
     */
    private function cleanup($text)
    {
        return trim(preg_replace('/[^a-zA-Z,;\s\']+/', ' ', $text));
    }
}
