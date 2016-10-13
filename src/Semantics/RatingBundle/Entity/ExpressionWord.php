<?php

namespace Semantics\RatingBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Semantics\RatingBundle\Repository\ExpressionWordRepository;

/**
 * ReviewWord
 *
 * @ORM\Table(name="ss_expression_word")
 * @ORM\Entity(repositoryClass="ExpressionWordRepository")
 */
class ExpressionWord extends Entity
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
     * @ORM\ManyToOne(targetEntity="Semantics\RatingBundle\Entity\Expression", inversedBy="ss_expression", cascade={"all"})
     */
    private $wordId;
    /**
     * @var int
     *
     * @ORM\Column(name="expression_id", type="integer")
     * @ORM\ManyToOne(targetEntity="Semantics\RatingBundle\Entity\Word", inversedBy="ss_word", cascade={"all"})
     */
    private $expressionId;

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
    public function getExpressionId()
    {
        return $this->expressionId;
    }
    public function setExpressionId($expressionId)
    {
        $this->expressionId = $expressionId;
        return $this;
    }
    public function setId($id)
    {
        $this->id = $id;
    }
}
