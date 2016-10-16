<?php

namespace Semantics\RatingBundle\Interfaces;

/**
 *
 * @author Víctor Molero
 */
interface Serializable
{
    public function toArray();
    public function __toString();
}
