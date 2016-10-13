<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Semantics\RatingBundle\Interfaces;

/**
 *
 * @author Víctor Molero
 */
interface IEntity
{
    public function getId();
    public function setId($id);
    public function toArray();
}
