<?php

namespace Semantics\RatingBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Semantics\RatingBundle\Interfaces\SemanticEntityHolder;

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
    protected $hash;
    /**
     * @var integer
     *
     * @ORM\Column(type="integer", name="review_id")
     */
    protected $reviewId;
    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    protected $expression;
        
    /**
     * @ORM\Column(type="decimal", precision=5, scale=2, nullable=false, options={"default" : 0})
     */
    protected $score;
    /**
     * @ORM\Column(type="integer")
     */
    protected $feedback;
    /**
     * @ORM\ManyToOne(targetEntity="Review", inversedBy="lines")
     * @ORM\JoinColumn(name="review_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $review;
    /**
     * @ORM\OneToMany(targetEntity="ExpressionWord", mappedBy="expression", cascade={"persist"}, orphanRemoval=true)
     */
    protected $wordsInExpression;
    /**
     *
     * @var array
     */
    protected $methodRenderPatterns = ['/(?!(^getReview$))^get[a-zA-Z]+$/'];

    public function __construct()
    {
        $this->wordsInExpression = new ArrayCollection();
    }
    public function getId()
    {
        return $this->id;
    }
    public function getHash()
    {
        return $this->hash;
    }
    public function getReviewId()
    {
        return $this->reviewId;
    }
    public function getExpression()
    {
        return $this->expression;
    }
    public function getScore()
    {
        return $this->score;
    }
    public function getFeedback()
    {
        return $this->feedback;
    }
    public function getReview()
    {
        return $this->review;
    }
    public function getWordsInExpression()
    {
        return $this->wordsInExpression;
    }
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }
    public function setHash($hash)
    {
        $this->hash = $hash;
        return $this;
    }
    public function setReviewId($reviewId)
    {
        $this->reviewId = $reviewId;
        return $this;
    }
    public function setExpression($expression)
    {
        $this->expression = $expression;
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
    public function setReview($review)
    {
        $this->review = $review;
        return $this;
    }
    public function setWordsInExpression($wordsInExpression)
    {
        $this->wordsInExpression = $wordsInExpression;
        return $this;
    }
    public function getPosition()
    {
        $words = $this->getWordsInExpression();
        if (is_array($words)) {
            reset($words);
            $first = current($words);
            return $first->getPosition();
        }
    }
    public function getLength()
    {
        return count($this->wordsInExpression);
    }
    public function hasTopic(array $topics)
    {
        return preg_match('/(^' . implode('$|^', $topics) . '$)/', $this->getExpression()) == 1;
    }
    public function hasClass($class)
    {
        if (is_string($class)) {
            $classes = array_map(function (SemanticEntityHolder $wExpr) {
                return $wExpr->getClass();
            }, $this->getWordsInExpression());
            return in_array($class, $classes);
        }
        return false;
    }
    public function hasAllClasses(array $classes)
    {
        return count($classes) ? $this->hasClass(array_shift($classes)) && $this->hasAllClasses($classes) : false;
    }
    public function hasAnyClasses(array $classes)
    {
        return count($classes) ? $this->hasClass(array_shift($classes)) || $this->hasAnyClasses($classes) : false;
    }
}
