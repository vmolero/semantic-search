<?php

namespace Semantics\RatingBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

// use Doctrine\Common\Collections\ArrayCollection;

/**
 * Description of Word
 *
 * @author Víctor Molero
 * @ORM\Entity
 * @ORM\Table(name="ss_expression")
 */
final class Expression extends Entity
{
    /**
     * @var integer
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;
    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    private $expression;
    /**
     * @var integer
     *
     * @ORM\Column(type="integer")
     * @ORM\ManyToOne(targetEntity="Semantics\RatingBundle\Entity\Review")
     * @ORM\JoinColumn(name="review_id", referencedColumnName="id")
     */
    private $reviewId;
    /**
     * @ORM\Column(type="decimal", precision=5, scale=2, nullable=false, options={"default" : 0})
     */
    private $score;
    /**
     * @ORM\Column(type="integer")
     */
    private $feedback;
    /**
     * @ORM\Column(type="integer", name="positiveCount")
     */
    private $positiveCount;
    /**
     * @ORM\Column(type="integer", name="negativeCount")
     */
    private $negativeCount;

    public function __construct()
    {
        // $this->reviews = new ArrayCollection();
    }
    public function getId()
    {
        return $this->id;
    }
    public function getExpression()
    {
        return $this->expression;
    }
    public function getReviewId()
    {
        return $this->reviewId;
    }
    public function getScore()
    {
        return $this->score;
    }
    public function getFeedback()
    {
        return $this->feedback;
    }
    public function getPositiveCount()
    {
        return $this->positiveCount;
    }
    public function getNegativeCount()
    {
        return $this->negativeCount;
    }
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }
    public function setExpression($expression)
    {
        $this->expression = $expression;
        return $this;
    }
    public function setReviewId($reviewId)
    {
        $this->reviewId = $reviewId;
        return $this;
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
    public function setPositiveCount($positiveCount)
    {
        $this->positiveCount = $positiveCount;
        return $this;
    }
    public function setNegativeCount($negativeCount)
    {
        $this->negativeCount = $negativeCount;
        return $this;
    }
}
