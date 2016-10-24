<?php

namespace Semantics\RatingBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Description of Corpus
 *
 * @author Víctor Molero
 *
 * @ORM\Entity(repositoryClass="Semantics\RatingBundle\Repository\CorpusRepository")
 * @ORM\Table(name="ss_corpus")
 * @UniqueEntity("lemma")
 */
class Corpus extends SemanticEntity
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    protected $id;
    /**
     * @ORM\Column(type="string", unique=true)
     */
    protected $lemma;
    /**
     * @ORM\Column(type="string")
     */
    protected $class;
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $stem;

    public function getId()
    {
        return $this->id;
    }
    public function getLemma()
    {
        return $this->lemma;
    }
    public function getClass()
    {
        return $this->class;
    }
    public function getStem()
    {
        return $this->stem;
    }
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }
    public function setLemma($lemma)
    {
        $this->lemma = $lemma;
        return $this;
    }
    public function setClass($class)
    {
        $this->class = $class;
        return $this;
    }
    public function setStem($stem)
    {
        $this->stem = $stem;
        return $this;
    }
}
