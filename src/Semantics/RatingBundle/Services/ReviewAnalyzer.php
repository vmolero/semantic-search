<?php

namespace Semantics\RatingBundle\Services;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Util\Debug;
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

    const CLASS_DISCRIMINATOR    = ['noun', 'adjective', 'negative', 'adverb']; // 'pronoun', 'verb'
    const PHRASE_SPLITTER_REGEXP = '/[\.,;:]+/';
    const WORD_SEPARATOR_REGEXP  = '/[\s]+/';
    const CLEANUP_REGEXP         = '/[^a-zA-Z\.:,;\s\']+/';
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
        $topics = array_map(function (SemanticEntityHolder $word) {
            return $this->tokenizeWord($word->getTopic())->getStem();
        }, $review->getTopics());

        $subordinates = preg_split(self::PHRASE_SPLITTER_REGEXP, $this->cleanup($review->getReview()));
        $relevantExpressions = [];
        foreach ($subordinates as $eligible) {
            $relevantExpressions = array_merge($relevantExpressions, array_filter($this->ngramSentenceParser(trim($eligible), self::WINDOW_SIZE, $topics)));
            // $max =  $this->maxScore($relevantExpressions, $review->getFeedback());
            if (false && !is_null($max)) {
                $max->setReview($review);
                $relevant[] = $max;
            }
        }
        foreach($relevantExpressions as &$expr) 
        {
            $expr->setReview($review);
        }
        if (count($relevantExpressions)) {
            $review->setLines(new ArrayCollection($relevantExpressions));
        }

        return $review;
    }
    /**
     *
     * @param string $text
     * @return string
     */
    private function cleanup($text)
    {
        return trim(preg_replace(self::CLEANUP_REGEXP, ' ', $text));
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
    private function getCriteria($topics, $word)
    {
        return $word->getClass() == 'adjective' || $word->getClass() == 'negative' ||
                (preg_match('/(^' . implode('$|^', $topics) . '$)/', $word->getStem()) == 1 && $word->getClass() == 'noun');
    }
    private function ngramSentenceParser($eligible, $window, $topics)
    {
        $candidateExpressions = [];
        $words = preg_split(self::WORD_SEPARATOR_REGEXP, $eligible);
        $count = count($words);
        $size = min($count, (2*$window)+1);
        for ($i = 0; $i < $count; $i++) {
            $word   = $words[$i];
            $offset = $i - $window;
            $start  = max(0, min($offset, $count - $size));
            if ($this->getCriteria($topics, $this->tokenizeWord($word))) {
                $nGram       = array_slice($words, max(0, min($offset, $count - $size)), $size);
                $nGramString = implode(' ', $nGram);
                $candidateExpressions[md5($nGramString)] = $this->builder->create(Expression::class)->build(['expression' => $nGramString, 'hash' => md5($nGramString)] + $this->morph->sentimentAnalyzer($nGramString))->getConcrete();
                
            }
        }
        return $candidateExpressions;
    }

    private function parseExpressionWord($cadena, $expression)
    {
        $words = preg_split(self::WORD_SEPARATOR_REGEXP, trim($cadena));
        $i     = 0;
        return array_map(function ($word) use (&$i, $expression) {
            return $this->initExpressionWord($this->initWord($word), $i++, $expression);
        }, $words);
    }
    private function initExpressionWord($wordEntity, $position, $exprEntity = null)
    {
        $expressionWord = $this->builder->create(ExpressionWord::class)->build(['word' => $wordEntity, 'position' => $position, 'expression' => $exprEntity])->getConcrete();

        return $expressionWord;
    }
}
