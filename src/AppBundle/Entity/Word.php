<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * Description of Word
 *
 * @author Víctor Molero
 * @ORM\Entity
 * @ORM\Table(name="ss_corpus")
 */
final class Word
{
    /**
     * @var integer
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;
    /**
     * @var string
     *
     * @ORM\Column(type="string" unique="true")
     */
    private $word;
    /**
     * @var integer
     *
     * @ORM\Column(type="integer")
     * @ORM\OneToOne(targetEntity="Corpus")
     * @ORM\JoinColumn(name="corpusId", referencedColumnName="id")
     */
    private $corpusId;
    /**
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="Review" inversedBy="ss_word")
     * @ORM\JoinTable(name="ss_word_review")
     */
    private $reviews;

    public function __construct()
    {
        $this->reviews = new ArrayCollection();
    }
    public function getId()
    {
        return $this->id;
    }
    public function getWord()
    {
        return $this->word;
    }
    public function getCorpusId()
    {
        return $this->corpusId;
    }
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }
    public function setWord($word)
    {
        $this->word = $word;
        return $this;
    }
    public function setCorpusId($corpusId)
    {
        $this->corpusId = $corpusId;
        return $this;
    }
    public function toArray()
    {
        return ['id' => $this->getId(), 'word' => $this->getWord(), 'corpusId' => $this->getCorpusId()];
    }
    public function __toString()
    {
        return json_encode($this->toArray());
    }
}
