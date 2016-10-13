<?php

namespace Semantics\RatingBundle\Services;

use Semantics\RatingBundle\Interfaces\IEntity;

/**
 * Description of RepositoryBuilderService
 *
 * @author Víctor Molero
 */
final class RepositoryBuilderService
{
    /**
     *
     * @var IEntity
     */
    private $entity;

    public function create($className)
    {
        $entity = new $className();
        if ($entity instanceof IEntity) {
            $this->entity = $entity;
        }
        return $this;
    }
    public function build(array $setters)
    {
        if ($this->entity instanceof IEntity) {
            foreach ($setters as $name => $value) {
                $this->entity->{'set' . ucfirst($name)}($value);
            }
        }
        return $this;
    }
    public function getConcrete()
    {
        return $this->entity;
    }
}
