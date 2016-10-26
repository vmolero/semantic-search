<?php

namespace Semantics\RatingBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Semantics\RatingBundle\Interfaces\SemanticEntityHolder;

/**
 * Description of Review
 *
 * @author Víctor Molero
 *
 * @ORM\Entity(repositoryClass="Semantics\RatingBundle\Repository\ReviewRepository")
 * @ORM\Table(name="ss_review",indexes={@ORM\Index(name="hash_idx", columns={"hash"})})
 */
class Review extends SemanticEntity
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    protected $id;
    /**
     * @ORM\Column(type="string", nullable=true, unique=true)
     */
    protected $hash;
    /**
     * @ORM\Column(type="string")
     */
    protected $review;
    /**
     * @ORM\Column(type="decimal", precision=5, scale=2, nullable=false, options={"default" : 0})
     */
    protected $score;
    /**
     * @ORM\Column(type="integer")
     */
    protected $feedback;
    /**
     * @ORM\Column(type="integer", name="positiveCount", options={"default" : 0})
     */
    protected $positiveCount        = 0;
    /**
     * @ORM\Column(type="integer", name="negativeCount", options={"default" : 0})
     */
    protected $negativeCount        = 0;
    /**
     * @ORM\OneToMany(targetEntity="Expression", mappedBy="review", cascade={"persist"}, orphanRemoval=true)
     */
    protected $lines;
    /**
     * @var ArrayCollection
     */
    protected $topics;
    protected $methodRenderPatterns = ['/(?!^getTopics$)^get[a-zA-Z]+$/'];

    public function __construct()
    {
        $this->lines  = new ArrayCollection();
        $this->topics = new ArrayCollection();
    }
    public function getTopics()
    {
        return $this->topics;
    }
    public function setTopics($topics)
    {
        $this->topics = $topics;
        return $this;
    }
    public function getLines()
    {
        return $this->lines;
    }
    public function setLines($lines)
    {
        $this->lines = $lines;
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
        if (count($this->getLines())) {
            return array_sum(array_map(function (SemanticEntityHolder $line) {
                        return $line->getFeedback() == 1 ? 1 : 0;
                    }, $this->getLines()));
        }
        return 0;
    }
    public function getNegativeCount()
    {
        if (count($this->getLines())) {
            return array_sum(array_map(function (SemanticEntityHolder $line) {
                        return $line->getFeedback() == -1 ? 1 : 0;
                    }, $this->getLines()));
        }
        return 0;
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
