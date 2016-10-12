<?php

namespace Semantics\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Description of Corpus
 *
 * @author Víctor Molero
 *
 * @ORM\Entity(repositoryClass="Semantics\Repository\CorpusRepository")
 * @ORM\Table(name="ss_corpus")
 */
final class Corpus
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
     * @ORM\Column(type="float")
     */
    private $prescore;
    /**
     * @ORM\Column(type="integer")
     */
    private $feedback;

    public function getId()
    {
        return $this->id;
    }
    public function getStem()
    {
        return $this->stem;
    }
    public function getPrescore()
    {
        return $this->prescore;
    }
    public function getLemma()
    {
        return $this->lemma;
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
    public function setPrescore($prescore)
    {
        $this->prescore = $prescore;
        return $this;
    }
    public function toArray()
    {
        return ['id' => $this->getId(), 'stem' => $this->getStem(), 'lemma' => $this->getLemma(), 'prescore' => $this->getPrescore()];
    }
    public function __toString()
    {
        return json_encode($this->toArray());
    }
}
