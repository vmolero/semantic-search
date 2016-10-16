<?php

namespace Semantics\RatingBundle\Services;

use Semantics\RatingBundle\Interfaces\SemanticEntityHolder;

/**
 * Description of RepositoryBuilderService
 *
 * @author Víctor Molero
 */
final class RepositoryBuilder
{
    /**
     *
     * @var SemanticEntityHolder
     */
    private $entity;

    public function create($className)
    {
        $entity = new $className();
        if ($entity instanceof SemanticEntityHolder) {
            $this->entity = $entity;
        }
        return $this;
    }
    public function build(array $setters)
    {
        if ($this->entity instanceof SemanticEntityHolder) {
            foreach ($setters as $name => $value) {
                if (method_exists($this->entity, 'set' . ucfirst($name))) {
                    $this->entity->{'set' . ucfirst($name)}($value);
                }
            }
        }
        return $this;
    }
    public function getConcrete()
    {
        return $this->entity;
    }
}
