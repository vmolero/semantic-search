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
        $initReview = $this->persister->initReview($review)->getReview();
        $scoredEntity = $this->strategy->digest($initReview);
        print_r($scoredEntity->toArray());
        die;
        return $this->persister->saveReview($scoredEntity)->getReview();
    }
}
