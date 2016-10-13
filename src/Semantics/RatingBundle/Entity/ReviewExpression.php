<?php

namespace Semantics\RatingBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ReviewWord
 *
 * @ORM\Entity
 * @ORM\Table(name="ss_review_expression")
 */
class ReviewExpression extends Entity
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
     * @ORM\Column(name="review_id", type="integer")
     * @ORM\ManyToOne(targetEntity="Expression", inversedBy="ss_expression", cascade={"all"})
     */
    private $reviewId;
    /**
     * @var int
     *
     * @ORM\Column(name="expression_id", type="integer")
     * @ORM\ManyToOne(targetEntity="Review", inversedBy="ss_review", cascade={"all"})
     */
    private $expressionId;
    /**
     * @ORM\Column(type="decimal", precision=5, scale=2, nullable=false, options={"default" : 0})
     */
    private $score    = 0;
    /**
     * @ORM\Column(type="integer", nullable=false, options={"default" : 0})
     */
    private $feedback = 0;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }
    public function getReviewId()
    {
        return $this->reviewId;
    }
    public function setReviewId($reviewId)
    {
        $this->reviewId = $reviewId;
        return $this;
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
}
