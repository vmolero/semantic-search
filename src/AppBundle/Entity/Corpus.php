<?php

namespace AppBundle\Entity;

/**
 * Description of Corpus
 *
 * @author Víctor Molero
 *
 * @ORM\Entity
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
     * @ORM\Column(type="string" unique="true")
     */
    private $stem;
    /**
     * @ORM\Column(type="integer")
     */
    private $prescore;

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
        return ['id' => $this->getId(), 'stem' => $this->getStem(), 'prescore' => $this->getPrescore()];
    }
    public function __toString()
    {
        return json_encode($this->toArray());
    }
}
