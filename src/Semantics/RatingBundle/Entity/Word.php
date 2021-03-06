<?php

namespace Semantics\RatingBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Description of Word
 *
 * @author Víctor Molero
 * @ORM\Entity(repositoryClass="Semantics\RatingBundle\Repository\WordRepository")
 * @ORM\Table(name="ss_word", indexes={@ORM\Index(name="word_idx", columns={"word"})})
 */
class Word extends SemanticEntity
{
    /**
     * @var integer
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    protected $id;
    /**
     * @var string
     *
     * @ORM\Column(type="string", unique=true)
     */
    protected $word;
    /**
     * @var integer
     *
     * @ORM\Column(name="corpus_id", type="integer", nullable=true)
     */
    protected $corpusId;
    /**
     * @ORM\ManyToOne(targetEntity="Corpus", cascade={"persist"})
     * @ORM\JoinColumn(name="corpus_id", referencedColumnName="id")
     */
    protected $corpus;
    /**
     * @ORM\Column(type="decimal", precision=5, scale=2, nullable=true, options={"default" : 0})
     */
    protected $score;
    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $feedback;
    /**
     *
     * @ORM\OneToMany(targetEntity="ExpressionWord", mappedBy="word")
     */
    protected $expressionsContaingWord;
    protected $methodRenderPatterns = ['/(?!^getExpressionsContaingWord$)^get[a-zA-Z]+$/'];

    public function __construct()
    {
        $this->wordsInExpression = new ArrayCollection();
    }
    public function getCorpus()
    {
        return $this->corpus;
    }
    public function setCorpus($corpus)
    {
        $this->corpus = $corpus;
        return $this;
    }
    public function getExpressionsContaingWord()
    {
        return $this->expressionsContaingWord;
    }
    public function setExpressionsContaingWord($expressionsContaingWord)
    {
        $this->expressionsContaingWord = $expressionsContaingWord;
        return $this;
    }
    public function getScore()
    {
        return $this->score;
    }
    public function getFeedback()
    {
        return $this->feedback;
    }
    public function setScore($score)
    {
        $this->score = $score;
        return $this;
    }
    public function setFeedback($feedback)
    {
        $this->feedback = $feedback;
        return $this;
    }
    public function getId()
    {
        return $this->id;
    }
    public function getWord()
    {
        return $this->word;
    }
    public function getCorpusId()
    {
        return $this->corpusId ?: $this->corpus->getId();
    }
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }
    public function setWord($word)
    {
        $this->word = $word;
        return $this;
    }
    public function setCorpusId($corpusId)
    {
        $this->corpusId = $corpusId;
        return $this;
    }
    public function getClass()
    {
        return $this->corpus->getClass();
    }
    public function getLemma()
    {
        return $this->corpus->getLemma();
    }
    public function getStem()
    {
        return $this->corpus->getStem();
    }
}
