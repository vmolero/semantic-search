<?php

namespace Semantics\RatingBundle\Services;

use Semantics\RatingBundle\Entity\Corpus;
use Semantics\RatingBundle\Entity\Expression;
use Semantics\RatingBundle\Entity\ExpressionWord;
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

    const CLASS_DISCRIMINATOR    = ['noun', 'adjective', 'negative', 'adverb']; // 'pronoun', 'verb'
    const PHRASE_SPLITTER_REGEXP = '/[,;:]+/';
    const WORD_SEPARATOR         = '/[\s]+/';
    const WINDOW_SIZE            = 2;

    public function __construct(MorphAdorner $morph, RepositoryBuilder $builder)
    {
        $this->builder = $builder;
        $this->morph   = $morph;
    }
    public function digest(SemanticEntityHolder $review)
    {
        return $this->analyze($review);
    }
    private function analyze(SemanticEntityHolder $review)
    {
        $lines  = $review->getLines();
        $topics = array_map(function (SemanticEntityHolder $word) {
            return $this->tokenizeWord($word->getTopic())->getStem();
        }, $review->getTopics());

        array_walk($lines, function (SemanticEntityHolder &$lineEntity) use ($topics) {
            /* @var $exprEntity Expression  */
            $subordinates                  = preg_split(self::PHRASE_SPLITTER_REGEXP, $lineEntity->getExpression());
            $definitiveRelevantExpressions = [];
            foreach ($subordinates as $subExpression) {
                $relevantExpressions           = array_filter($this->ngramSentenceParser($subExpression, self::WINDOW_SIZE, $topics));
                $definitiveRelevantExpressions += [$this->maxScore($relevantExpressions, $lineEntity->getFeedback())];
            }
            //var_dump("----------------------");
            //var_dump(array_map(function ($candidateFragment) {
            //            var_dump("{$candidateFragment->getFeedback()} Candidate ({$candidateFragment->getScore()}): " . $candidateFragment->getExpression());
            //        }, $definitiveRelevantExpressions));
            if (count($definitiveRelevantExpressions)) {
                $lineEntity->setFragments($definitiveRelevantExpressions);
            }
            //var_dump(array_map(function ($e) {
            //            return $e->toArray();
            //        }, $definitiveRelevantExpressions));
            //die;
            // $relevantExpressions = $this->getRelevantExpressions($matchingFeedbackExpressions);
        });

        return $review;
    }
    /**
     *
     * @param string $sentence
     * @return array
     */
    private function tokenizeExpression($sentence)
    {
        $words                 = preg_split(self::WORD_SEPARATOR, trim($sentence));
        $expresionWordEntities = [];
        foreach ($words as $position => $word) {
            if (strlen(trim($word)) > 0) {
                $expresionWordEntities[] = $this->tokenizeExpressionWord(trim($word), $position);
            }
        }

        return $expresionWordEntities;
    }
    private function tokenizeExpressionWord($word, $position)
    {
        $wordEntity     = $this->tokenizeWord($word);
        $expressionWord = $this->builder->create(ExpressionWord::class)->build(['word' => $wordEntity, 'position' => $position])->getConcrete();

        return $expressionWord;
    }
    private function tokenizeWord($word)
    {
        $wordEntity   = $this->builder->create(Word::class)->build(['word' => $word])->getConcrete();
        $corpusEntity = $this->builder->create(Corpus::class)->build($this->morph->lexiconLookup($word))->getConcrete();
        $wordEntity->setCorpus($corpusEntity);

        return $wordEntity;
    }
    private function maxScore(array $ngramPartialResult, $feedback)
    {
        $max      = null;
        $maxScore = 0;
        while ($current  = array_shift($ngramPartialResult)) {
            if ($current->getFeedback() == $feedback &&
                    $current->getScore() >= $maxScore) {
                $maxScore = $current->getScore();
                $max      = $current;
            }
        }

        return $max;
    }
    private function getRelevantExpressions(array $fragments)
    {

        return array_filter($fragments, function ($expr) {
            var_dump($expr->toArray());
            die($expr->getExpression());
            return true; //$expr->hasAnyClasses(['noun', 'adjective', 'negative']); // && $expr->hasAllClasses(['noun', 'adjective']);
        });
    }
    private function getCriteria($topics, $word)
    {
        return $word->getClass() == 'adjective' || $word->getClass() == 'negative' ||
                (preg_match('/(^' . implode('$|^', $topics) . '$)/', $word->getStem()) == 1 && $word->getClass() == 'noun');
    }
    private function ngramSentenceParser($subExpression, $window, $topics)
    {
        $candidateExpressions = [];
        $expressionWords      = $this->tokenizeExpression($subExpression);

        $count  = count($expressionWords);
        $size   = min((2 * $window) + 1, $count);
        for ($i = $offset = 0; $i < $count; $i++) {
            $word   = $expressionWords[$i];
            $offset = $i - $window;
            $start  = max(0, min($offset, $count - $size));
            // $end    = min($count, $start + (2 * $window) + 1);
            // var_dump("pointer $i {$word->getWordInExpression()}. $start::$end out of $count");
            if ($this->getCriteria($topics, $word)) {
                $nGram       = array_slice($expressionWords, max(0, min($offset, $count - $size)), $size);
                $nGramString = trim(implode(' ', array_map(function (SemanticEntityHolder $exprWord) {
                                    return $exprWord->getWordInExpression();
                                }, $nGram)));
                //var_dump($nGramString);
                $candidateExpressions[] = $this->builder->create(Expression::class)->build(['expression' => $nGramString, 'hash' => md5($nGramString), 'wordsInExpression' => $nGram] + $this->morph->sentimentAnalyzer($nGramString))->getConcrete();
            }
        }
        return $candidateExpressions;
    }
}
