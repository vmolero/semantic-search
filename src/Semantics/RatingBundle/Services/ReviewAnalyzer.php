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
            return $this->tokenizeWord($word->getTopic());
        }, $review->getTopics());
        array_walk($lines, function (SemanticEntityHolder &$lineEntity) use ($topics) {
            /* @var $exprEntity Expression  */
            $subordinates                  = preg_split(self::PHRASE_SPLITTER_REGEXP, $lineEntity->getExpression());
            $definitiveRelevantExpressions = [];
            foreach ($subordinates as $subExpression) {

                $relevantExpressions = $this->ngramSentenceParser($subExpression, self::WINDOW_SIZE, $topics);
                /* var_dump(array_map(function ($e) {
                  return $e->toArray();
                  }, $candidateExpressions));
                  die; */
                // $relevantExpressions = $this->getRelevantExpressions($matchingFeedbackExpressions);
                var_dump("----------------------");
                var_dump(array_map(function ($candidateFragment) {
                            var_dump("{$candidateFragment->getFeedback()} Candidate ({$candidateFragment->getScore()}): " . $candidateFragment->getExpression());
                        }, $relevantExpressions));

                $definitiveRelevantExpressions += $relevantExpressions;
            }

            if (count($relevantExpressions)) {
                $lineEntity->setFragments($relevantExpressions);
            }
        });
        die;
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
        $expressionWord = $this->builder->create(ExpressionWord::class)->build(['word' => $this->tokenizeWord($word), 'position' => $position])->getConcrete();
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
                    $current->getScore() >= $maxScore &&
                    $current->hasAllClasses(['noun', 'adjective'])) {
                $adjectiveWords = array_filter($current->getWordsInExpression(), function (SemanticEntityHolder $word) {
                    return $word->getClass == 'adjective';
                });
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
    private function ngramSentenceParser($subExpression, $window, $topics)
    {
        $candidateExpressions = [];
        $expressionWords      = $this->tokenizeExpression($subExpression);
        $count                = count($expressionWords);
        $size                 = min((2 * $window) + 1, $count);
        for ($i = $offset               = 0; $i < $count; $i++) {
            $word   = $expressionWords[$i];
            $offset = $i - $window;
            $start  = max(0, min($offset, $count - $size));
            // $end    = min($count, $start + (2 * $window) + 1);
            // var_dump("pointer $i {$word->getWordInExpression()}. $start::$end out of $count");
            if ($word->getClass() == 'adjective' || $word->getClass() == 'negative' ||
                    (preg_match('/(^' . implode('$|^', array_map(function ($w) {
                                        return $w->getStem();
                                    }, $topics)) . '$)/', $word->getStem()) == 1 && $word->getClass() == 'noun')) {
                $nGram       = array_slice($expressionWords, max(0, min($offset, $count - $size)), $size);
                $nGramString = trim(implode(' ', array_map(function (SemanticEntityHolder $exprWord) {
                                    echo $exprWord;
                                    die;
                                    return $exprWord->getWordInExpression();
                                }, $nGram)));
                // var_dump($nGramString);
                $candidateExpressions[] = $this->builder->create(Expression::class)->build(['expression' => $nGramString, 'hash' => md5($nGramString), 'wordsInExpression' => $nGram] + $this->morph->sentimentAnalyzer($nGramString))->getConcrete();
            }
        }
        return $candidateExpressions;
    }
    private function selectWordsByClass($expression, $class)
    {
        return array_filter($expression, function ($w) use ($class) {
            return $w->getClass() == $class;
        });
    }
    private function s($word, $topic, $start, $size)
    {
        if ($word->getClass() == 'adjective' ||
                preg_match('/(^' . implode('$|^', $topics) . '$)/', $word->getWord()) == 1) { //$this->criteria->classMatch($word->getClass)
            $nGram       = array_slice($expressionWords, $start, $size);
            $nGramString = trim(implode(' ', array_map(function (SemanticEntityHolder $exprWord) {
                                return $exprWord->getWordInExpression();
                            }, $nGram)));
            var_dump($nGramString);
            $candidateExpressions[] = $this->builder->create(Expression::class)->build(['expression' => $nGramString, 'hash' => md5($nGramString), 'wordsInExpression' => $nGram] + $this->morph->sentimentAnalyzer($nGramString))->getConcrete();
        }
    }
}
