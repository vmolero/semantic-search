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
    const WINDOW_SIZE            = 4;

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
            return $word->getTopic();
        }, $review->getTopics());
        array_walk($lines, function (SemanticEntityHolder &$lineEntity) use ($topics) {
            /* @var $exprEntity Expression  */
            $subordinates                  = preg_split(self::PHRASE_SPLITTER_REGEXP, $lineEntity->getExpression());
            $definitiveRelevantExpressions = [];
            foreach ($subordinates as $subExpression) {
                $wordsInExpression           = $this->tokenizeSentence($subExpression);
                $matchingFeedbackExpressions = $this->ngramSentenceParser($wordsInExpression, self::WINDOW_SIZE, $topics, $lineEntity->getFeedback());

                $relevantExpressions = $this->getRelevantExpressions($matchingFeedbackExpressions);
                var_dump("----------------------");
                var_dump(array_map(function ($candidateFragment) {
                            var_dump("Candidate ({$candidateFragment->getScore()}): " . $candidateFragment->getExpression());
                        }, $matchingFeedbackExpressions));

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
    private function tokenizeSentence($sentence)
    {
        $words                 = preg_split(self::WORD_SEPARATOR, trim($sentence));
        $expresionWordEntities = [];
        foreach ($words as $position => $word) {
            if (strlen(trim($word)) > 0) {
                $expresionWordEntities[] = $this->tokenizeExpressionWord(trim($word), $position);
            }
        }
        /* var_dump(array_map(function ($e) {
          return $e->toArray();
          }, $expresionWordEntities));
          die; */
        return $expresionWordEntities;
    }
    private function tokenizeExpressionWord($word, $position)
    {
        $wordEntity     = $this->builder->create(Word::class)->build(['word' => $word])->getConcrete();
        $corpusEntity   = $this->builder->create(Corpus::class)->build($this->morph->lexiconLookup($word))->getConcrete();
        $wordEntity->setCorpus($corpusEntity);
        $expressionWord = $this->builder->create(ExpressionWord::class)->build(['word' => $wordEntity, 'position' => $position])->getConcrete();
        return $expressionWord;
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
    private function ngramSentenceParser(array $expressionWords, $window, $topics, $lineFeedback)
    {
        $break                = false;
        $candidateExpressions = [];
        for ($i = 0; $i <= count($expressionWords) && !$break; $i++) {
            $break       = ($i + self::WINDOW_SIZE) >= count($expressionWords);
            $nGram       = array_slice($expressionWords, $i, $window);
            $nGramString = trim(implode(' ', array_map(function (SemanticEntityHolder $exprWord) {
                                return $exprWord->getWordInExpression();
                            }, $nGram)));
            if (strlen($nGramString) > 0) {
                $candidateFragment = $this->builder->create(Expression::class)->build(['expression' => $nGramString, 'hash' => md5($nGramString), 'wordsInExpression' => $nGram] + $this->morph->sentimentAnalyzer($nGramString))->getConcrete();
                if ($candidateFragment->getFeedback() == $lineFeedback) {
// && (($candidateFragment->hasAnyClasses(['noun', 'adjective', 'negative'])) || $candidateFragment->hasTopic($topics))) { //$candidateFragment->hasAllClasses(['noun', 'adjective']) &&
                    // var_dump("$i Candidate ({$candidateFragment->getScore()}): " . $candidateFragment->getExpression());
                    $candidateExpressions[] = $candidateFragment;
                }
            }
        }
        return $candidateExpressions;
    }
}
