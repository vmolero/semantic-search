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

    const CLASS_DISCRIMINATOR    = ['noun', 'adjective', 'negative', 'adverb', 'verb'];
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
    private function createTopicRegExpr($topics)
    {
        return '/(' . implode('|', $topics) . ')/';
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
                $wordsInExpression = $this->tokenizeSentence($subExpression);

                $relevantExpressions = $this->ngramSentenceParser($wordsInExpression, self::WINDOW_SIZE, $topics, $lineEntity->getFeedback());

                $relevantAndMatchingFeedback   = $this->sameFeedbackAsLine($relevantExpressions, $lineEntity->getFeedback());
                $definitiveRelevantExpressions += $relevantAndMatchingFeedback;
            }
            if (count($relevantAndMatchingFeedback)) {
                $lineEntity->setFragments($relevantAndMatchingFeedback);
            }
        });
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
    private function sameFeedbackAsLine(array $ngramResult, $feedback)
    {
        return array_filter($ngramResult, function (SemanticEntityHolder $linesSegment) use ($feedback) {
            return $linesSegment->getFeedback() == $feedback;
        });
    }
    private function maxScore(array $ngramPartialResult, $feedback)
    {
        $max      = null;
        $maxScore = 0;
        while ($current  = array_shift($ngramPartialResult)) {
            if ($current->getFeedback() == $feedback && $current->getScore() > $maxScore) {
                $maxScore = $current->getScore();
                $max      = $current;
            }
        }
        return $max;
    }
    private function ngramSentenceParser(array $expressionWords, $window, $topics, $lineFeedback)
    {
        $break               = false;
        $relevantExpressions = [];
        $partialSet          = [];
        for ($i = 0; $i < count($expressionWords) && !$break; $i++) {
            $nGram       = array_slice($expressionWords, $i, $window);
            $nGramString = trim(implode(' ', array_map(function (SemanticEntityHolder $exprWord) use ($topics) {
                                return in_array($exprWord->getClass(), self::CLASS_DISCRIMINATOR) || preg_match($this->createTopicRegExpr($topics), $exprWord->getWord()) == 1 ? $exprWord->getWord() : '';
                            }, $nGram)));
            if (strlen($nGramString) > 0) {
                $exp = $this->builder->create(Expression::class)->build(['expression' => $nGramString, 'hash' => md5($nGramString)] + $this->morph->sentimentAnalyzer($nGramString))->getConcrete();
                print_r($exp);
                if ($exp->getFeedback() == $lineFeedback) {
                    if ($i > 0 && $i % self::WINDOW_SIZE != 0) {
                        $partialSet[] = $exp;
                    } else {
                        $maxScoredPartial      = $this->maxScore($partialSet, $lineFeedback);
                        $maxScoredPartial->setWordsInExpression($nGram);
                        $relevantExpressions[] = $maxScoredPartial;
                        $partialSet            = [];
                    }
                }
            }
            $break = ($i + self::WINDOW_SIZE) == count($expressionWords);
        }
        die;
        return $relevantExpressions;
    }
}
