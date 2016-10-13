<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Semantics\RatingBundle\Entity;

use ReflectionClass;
use ReflectionMethod;
use Semantics\RatingBundle\Interfaces\IEntity;

/**
 * Description of RatingEntity
 *
 * @author Víctor Molero
 */
abstract class Entity implements IEntity
{
    public function toArray()
    {
        $me      = new ReflectionClass($this);
        $methods = array_filter($me->getMethods(ReflectionMethod::IS_PUBLIC), function (ReflectionMethod $method) {
            return preg_match('/^get[a-zA-Z]+$/', $method->getName());
        });
        $array = [];
        foreach ($methods as $method) {
            /** @var ReflectionMethod $method */
            $array[lcfirst(preg_replace('/^get/', '', $method->getName()))] = $method->invoke($this);
        }
        return $array;
    }
    public function __toString()
    {
        return json_encode($this->toArray());
    }
}
