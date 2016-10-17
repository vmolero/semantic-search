<?php

namespace Semantics\RatingBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Description of Corpus
 *
 * @author Víctor Molero
 *
 * @ORM\Entity(repositoryClass="Semantics\RatingBundle\Repository\CorpusRepository")
 * @ORM\Table(name="ss_corpus")
 */
final class Corpus extends SemanticEntity
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
    private $lemma;
    /**
     * @ORM\Column(type="string")
     */
    private $class;

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
    public function setClass($class)
    {
        $this->class = $class;
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
}
