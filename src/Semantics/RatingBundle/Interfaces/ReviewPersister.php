<?php

namespace Semantics\RatingBundle\Interfaces;

/**
 *
 * @author Víctor Molero
 */
interface ReviewPersister
{
    public function saveReview($review);
    public function initReview($review);
    public function getReview();
}
