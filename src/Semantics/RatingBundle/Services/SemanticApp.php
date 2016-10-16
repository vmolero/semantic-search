<?php

namespace Semantics\RatingBundle\Services;

use Semantics\RatingBundle\Interfaces\Handler;
use Semantics\RatingBundle\Interfaces\ReviewPersister;
use Semantics\RatingBundle\Interfaces\StrategyDigestor;

/**
 * Description of SemanticApp
 *
 * @author Víctor Molero
 */
class SemanticApp implements Handler
{
    private $persister;
    private $strategy;

    public function __construct(ReviewPersister $persister, StrategyDigestor $strategy)
    {
        $this->persister = $persister;
        $this->strategy  = $strategy;
    }
    public function handle($review)
    {
        $scoredEntity = $this->strategy->digest($this->persister->initReview($review)->getReview());
        return $this->persister->saveReview($scoredEntity)->getReview();
    }
}
