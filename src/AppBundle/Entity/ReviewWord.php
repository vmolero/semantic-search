<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ReviewWord
 *
 * @ORM\Table(name="review_word")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ReviewWordRepository")
 */
class ReviewWord
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
     * @var int
     *
     * @ORM\Column(name="review_id", type="integer")
     */
    private $reviewId;


    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
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

    /**
     * Set reviewId
     *
     * @param integer $reviewId
     * @return ReviewWord
     */
    public function setReviewId($reviewId)
    {
        $this->reviewId = $reviewId;

        return $this;
    }

    /**
     * Get reviewId
     *
     * @return integer 
     */
    public function getReviewId()
    {
        return $this->reviewId;
    }
}
