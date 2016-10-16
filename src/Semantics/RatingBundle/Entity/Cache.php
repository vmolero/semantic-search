<?php

namespace Semantics\RatingBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Semantics\RatingBundle\Interfaces\SemanticEntityHolder;

/**
 * @ORM\Entity
 * @ORM\Table(name="ss_cache")
 */
class Cache implements SemanticEntityHolder
{
    /**
     * @ORM\Column(type="string", name="ckey")
     * @ORM\Id
     */
    private $key;
    /**
     * @ORM\Column(type="string", name="cvalue", nullable=false)
     */
    private $value;

    public function getKey()
    {
        return $this->key;
    }
    public function getValue()
    {
        return $this->value;
    }
    public function setKey($key)
    {
        $this->key = $key;
        return $this;
    }
    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }
    public function getId()
    {
        return $this->getKey();
    }
    public function setId($id)
    {
        return $this->setKey();
    }
    public function toArray()
    {
        return [$this->key => $this->value];
    }
}
