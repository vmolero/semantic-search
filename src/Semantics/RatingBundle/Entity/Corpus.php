<?php

namespace Semantics\RatingBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Semantics\RatingBundle\Repository\CorpusRepository;

/**
 * Description of Corpus
 *
 * @author Víctor Molero
 *
 * @ORM\Entity(repositoryClass="CorpusRepository")
 * @ORM\Table(name="ss_corpus")
 */
final class Corpus extends Entity
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;
    /**
     * @ORM\Column(type="string", unique=true)
     */
    private $stem;
    /**
     * @ORM\Column(type="string", unique=true)
     */
    private $lemma;
    /**
     * @ORM\Column(type="decimal", precision=5, scale=2, nullable=false, options={"default" : 0})
     */
    private $score;
    /**
     * @ORM\Column(type="integer")
     */
    private $feedback;
    /**
     * @ORM\Column(type="string")
     */
    private $class;

    public function getId()
    {
        return $this->id;
    }
    public function getStem()
    {
        return $this->stem;
    }
    public function getLemma()
    {
        return $this->lemma;
    }
    public function getFeedback()
    {
        return $this->feedback;
    }
    public function getScore()
    {
        return $this->score;
    }
    public function getClass()
    {
        return $this->class;
    }
    public function setScore($score)
    {
        $this->score = $score;
        return $this;
    }
    public function setClass($class)
    {
        $this->class = $class;
        return $this;
    }
    public function setFeedback($feedback)
    {
        $this->feedback = $feedback;
        return $this;
    }
    public function setLemma($lemma)
    {
        $this->lemma = $lemma;
        return $this;
    }
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }
    public function setStem($stem)
    {
        $this->stem = $stem;
        return $this;
    }
}
