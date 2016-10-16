<?php

namespace Semantics\RatingBundle\Interfaces;

/**
 *
 * @author Víctor Molero
 */
interface StrategyDigestor
{
    public function digest(SemanticEntityHolder $review);
}
