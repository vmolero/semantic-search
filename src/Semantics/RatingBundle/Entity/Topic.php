<?php

namespace Semantics\RatingBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Description of Topic
 *
 * @author Víctor Molero
 *
 * @ORM\Entity
 * @ORM\Table(name="ss_topic")
 */
final class Topic extends SemanticEntity
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
    private $topic;
    /**
     * @ORM\Column(type="string")
     */
    private $tag;

    public function getId()
    {
        return $this->id;
    }
    public function getTopic()
    {
        return $this->topic;
    }
    public function getTag()
    {
        return $this->tag;
    }
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }
    public function setTopic($topic)
    {
        $this->topic = $topic;
        return $this;
    }
    public function setTag($tag)
    {
        $this->tag = $tag;
        return $this;
    }
}
