<?php

namespace Semantics\RatingBundle\Services;

use Semantics\RatingBundle\Entity\Corpus;
use Semantics\RatingBundle\Entity\Expression;
use Semantics\RatingBundle\Entity\Word;
use Semantics\RatingBundle\Interfaces\SemanticEntityHolder;
use Semantics\RatingBundle\Interfaces\StrategyDigestor;

/**
 * Description of Flow
 *
 * @author Víctor Molero
 */
final class ReviewAnalyzer implements StrategyDigestor
{
    /**
     *
     * @var RepositoryBuilder
     */
    private $builder;
    /**
     *
     * @var MorphAdorner
     */
    private $morph;

    const WINDOW_SIZE = 4;

    public function __construct(MorphAdorner $morph, RepositoryBuilder $builder)
    {
        $this->builder = $builder;
        $this->morph   = $morph;
    }
    public function digest(SemanticEntityHolder $review)
    {
        return $this->analyze($review);
    }
    private function createTopicRegExpr($topics)
    {
        return '/(' . implode('|', $topics) . ')/';
    }
    private function analyze(SemanticEntityHolder $review)
    {
        print_r($review->toArray());
        die;
        array_walk($review->getExpressions()->getValues(), function (SemanticEntityHolder &$exprEntity) {
            /** @var Expression $exprEntity */
            $subordinates = preg_split('/[,;]+/', $exprEntity->getExpression());
            $partials     = [];
            foreach ($subordinates as $subExpression) {
                $tokens      = $this->tokenizeSentence($subExpression);
                $ngramResult = $this->ngramSentenceParser($tokens, self::WINDOW_SIZE);
                if (count($ngramResult)) {
                    $partials[] = $this->selectHighestWithNounAndAdjective($ngramResult);
                }
            }
            var_dump($subordinates);

            if (!empty($partials)) {
                $exprEntity->setFragments(array_map(function ($partial) {
                            return $this->builder->create(Expression::class)->build(['word' => $partial])->getConcrete();
                        }, $partials));
            }
        });

        return $review;
    }
    private function tokenizeSentence($sentence)
    {
        return array_map(function($word) {

            $wordEntity   = $this->builder->create(Word::class)->build(['word' => $word])->getConcrete();
            $corpusEntity = $this->builder->create(Corpus::class)->build($this->morph->lexiconLookup($word))->getConcrete();
            $wordEntity->setCorpus($corpusEntity);
            return $wordEntity;
        }, preg_split('/[\s]+/', trim($sentence)));
    }
    private function selectHighestWithNounAndAdjective(array $ngramResult)
    {
        $maxPositive      = null;
        $maxPositiveScore = 0;
        $maxNegative      = null;
        $maxNegativeScore = 0;
        while ($current          = array_shift($ngramResult)) {
            if ($current['feedback'] == 1 && $current['score'] > $maxPositiveScore) {
                $maxPositiveScore = $current['score'];
                $maxPositive      = $current;
            }
            if ($current['feedback'] == -1 && $current['score'] > $maxNegativeScore) {
                $maxNegativeScore = $current['score'];
                $maxNegative      = $current;
            }
        }
        $final       = ($maxPositiveScore - $maxNegativeScore);
        $finalResult = $final > 0 ? $maxPositive : ($final < 0 ? $maxNegative : []);
        var_dump($finalResult);
        die;
        if (count($finalResult)) {
            return $this->builder->create(Expression::class)->build($finalResult)->getConcrete();
        }
    }
    private function ngramSentenceParser(array $tokens, $window)
    {
        $break     = false;
        $resultSet = [];
        for ($i = 0; $i < count($tokens) && !$break; $i++) {
            $partialTokens = array_slice($tokens, $i, $window);
            $substring     = trim(implode(' ', array_map(function (SemanticEntityHolder $word) {
                                return $word->getWord();
                            }, $partialTokens)));
            $classes = array_unique(array_map(function (SemanticEntityHolder $word) {
                        return $word->getClass();
                    }, $partialTokens));
            //preg_match($this->createTopicRegExpr(), $substring)
            if ((in_array('noun', $classes) && in_array('adjective', $classes)) || in_array('adjective', $classes)) {
                $resultSet[] = ['expression' => $substring] + $this->morph->sentimentAnalyzer($substring);
            }
            $break = ($i + self::WINDOW_SIZE) == count($tokens);
        }
        return $resultSet;
    }
}
