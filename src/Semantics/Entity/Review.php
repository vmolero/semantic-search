<?php

namespace Semantics\Entity;

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
final class Review
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
     * @ORM\Column(type="float")
     */
    private $score;
    /**
     * @ORM\Column(type="integer")
     */
    private $feedback;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Semantics\Entity\Word", mappedBy="ss_review")

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
    public function toArray()
    {
        return ['id' => $this->getID(), 'review' => $this->getReview(), 'score' => $this->getScore(), 'feedback' => $this->getFeedback()];
    }
    public function __toString()
    {
        return json_encode($this->toArray());
    }
}
