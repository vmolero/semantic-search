<?php

namespace Semantics\RatingBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Description of Word
 *
 * @author Víctor Molero
 * @ORM\Entity(repositoryClass="Semantics\RatingBundle\Repository\ExpressionRepository")
 * @ORM\Table(name="ss_expression",
 *            indexes={@ORM\Index(name="hash_idx2", columns={"hash", "review_id"})},
 *            uniqueConstraints={@ORM\UniqueConstraint(name="unique_hash_review_1", columns={"hash", "review_id"})}
 *           )
 */
class Expression extends SemanticEntity
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
     * @ORM\Column(type="string")
     */
    private $hash;
    /**
     * @var integer
     *
     * @ORM\Column(type="integer", name="review_id")
     */
    private $reviewId;
    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    private $expression;
    /**
     * @var integer
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $expressionId;
    /**
     * @ORM\OneToMany(targetEntity="Expression", mappedBy="sentence", cascade={"persist"}, orphanRemoval=true)
     */
    private $fragments;
    /**
     * @ORM\ManyToOne(targetEntity="Expression", inversedBy="fragments")
     * @ORM\JoinColumn(name="expression_id", referencedColumnName="id")
     */
    private $sentence;
    /**
     * @ORM\Column(type="decimal", precision=5, scale=2, nullable=false, options={"default" : 0})
     */
    private $score;
    /**
     * @ORM\Column(type="integer")
     */
    private $feedback;
    /**
     * @ORM\ManyToOne(targetEntity="Review", inversedBy="lines")
     * @ORM\JoinColumn(name="review_id", referencedColumnName="id")
     */
    private $review;

    public function __construct()
    {
        $this->fragments = new ArrayCollection();
    }
    public function getFragments()
    {
        return $this->fragments;
    }
    public function setFragments($fragments)
    {
        $this->fragments = $fragments;
        return $this;
    }
    public function getSentence()
    {
        return $this->sentence;
    }
    public function setSentence($sentence)
    {
        $this->sentence = $sentence;
        return $this;
    }
    public function getReview()
    {
        return $this->review;
    }
    public function setReview($review)
    {
        $this->review = $review;
        return $this;
    }
    public function getId()
    {
        return $this->id;
    }
    public function getHash()
    {
        return $this->hash;
    }
    public function setHash($hash)
    {
        $this->hash = $hash;
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
}
