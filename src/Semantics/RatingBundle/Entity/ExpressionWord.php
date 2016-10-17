<?php

namespace Semantics\RatingBundle\Entity;

use AppBundle\Entity\ReviewWord;
use Doctrine\ORM\Mapping as ORM;

/**
 * ReviewWord
 *
 * @ORM\Table(name="ss_expression_word")
 * @ORM\Entity(repositoryClass="Semantics\RatingBundle\Repository\ExpressionWordRepository")
 */
class ExpressionWord extends SemanticEntity
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
    /**
     * @var int
     *
     * @ORM\Column(name="word_id", type="integer")
     */
    private $wordId;
    /**
     * @ORM\ManyToOne(targetEntity="Word", inversedBy="expressionsContaingWord")
     * @ORM\JoinColumn(name="word_id", referencedColumnName="id")
     */
    private $word;
    /**
     * @var int
     *
     * @ORM\Column(name="expression_id", type="integer")
     */
    private $expressionId;
    /**
     * @ORM\ManyToOne(targetEntity="Expression", inversedBy="wordsInExpression")
     * @ORM\JoinColumn(name="expression_id", referencedColumnName="id")
     */
    private $expression;
    /**
     * @ORM\Column(type="integer")
     */
    private $position;

    /**
     * Get id
     *
     * @return integer
     */
    public function getPosition()
    {
        return $this->position;
    }
    public function setPosition($position)
    {
        $this->position = $position;
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
    public function getExpression()
    {
        return $this->expression;
    }
    public function setWord($word)
    {
        $this->word = $word;
        return $this;
    }
    public function setExpression($expression)
    {
        $this->expression = $expression;
        return $this;
    }
    /**
     * Set wordId
     *
     * @param integer $wordId
     * @return ReviewWord
     */
    public function setWordId($wordId)
    {
        $this->wordId = $wordId;

        return $this;
    }
    /**
     * Get wordId
     *
     * @return integer
     */
    public function getWordId()
    {
        return $this->wordId;
    }
    public function getExpressionId()
    {
        return $this->expressionId;
    }
    public function setExpressionId($expressionId)
    {
        $this->expressionId = $expressionId;
        return $this;
    }
    public function setId($id)
    {
        $this->id = $id;
    }
    public function getClass()
    {
        $this->word->getClass();
    }
    public function getWordInExpression()
    {
        $this->word->getWord();
    }
}
