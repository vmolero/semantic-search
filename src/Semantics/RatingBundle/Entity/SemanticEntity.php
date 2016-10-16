<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Semantics\RatingBundle\Entity;

use Doctrine\Common\Collections\Collection;
use Exception;
use ReflectionClass;
use ReflectionMethod;
use Semantics\RatingBundle\Interfaces\Clonable;
use Semantics\RatingBundle\Interfaces\SemanticEntityHolder;
use Semantics\RatingBundle\Interfaces\Serializable;

/**
 * Description of RatingEntity
 *
 * @author Víctor Molero
 */
abstract class SemanticEntity implements SemanticEntityHolder, Clonable, Serializable
{
    abstract public function getId();
    public function copy(Clonable $copyFrom)
    {
        if (get_class($this) == get_class($copyFrom)) {
            $me      = new ReflectionClass($this);
            $methods = array_filter($me->getMethods(ReflectionMethod::IS_PUBLIC), function (ReflectionMethod $method) {
                return preg_match('/^[sg]etId$/', $method->getName()) != 1 && preg_match('/^set[a-zA-Z]+$/', $method->getName());
            });
            foreach ($methods as $method) {
                /* @var $method ReflectionMethod  */
                $this->{$method->getName()}($copyFrom->{preg_replace('/^s/', 'g', $method->getName())}());
            }
            return $this;
        }
        throw new Exception('Incompatible types on copy');
    }
    public function toArray()
    {
        $me      = new ReflectionClass($this);
        $methods = array_filter($me->getMethods(ReflectionMethod::IS_PUBLIC), function (ReflectionMethod $method) {
            return preg_match('/^get[a-zA-Z]+$/', $method->getName());
        });
        $array = [];
        foreach ($methods as $method) {
            $value = $method->invoke($this);
            if ($value instanceof Collection) {
                $value = array_map(function (SemanticEntityHolder $entity) {
                    return $entity->toArray();
                }, $value->getValues());
            }
            /* @var $method ReflectionMethod */
            $array[lcfirst(preg_replace('/^get/', '', $method->getName()))] = $value;
        }
        return $array;
    }
    public function __toString()
    {
        return json_encode($this->toArray());
    }
}
