<?php

namespace Semantics\RatingBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Description of Review
 *
 * @author Víctor Molero
 *
 * @ORM\Entity
 * @ORM\Table(name="ss_review")
 */
final class Review extends Entity
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;
    /**
     * @ORM\Column(type="string")
     */
    private $review;
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

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Word", mappedBy="ss_review")

      private $words; */
    public function __construct()
    {
        // $this->words = new ArrayCollection();
    }
    public function getId()
    {
        return $this->id;
    }
    public function getReview()
    {
        return $this->review;
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
    public function setFeedback($feedback)
    {
        $this->feedback = $feedback;
        return $this;
    }
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }
    public function setReview($review)
    {
        $this->review = $review;
        return $this;
    }
    public function setScore($score)
    {
        $this->score = $score;
        return $this;
    }
}
