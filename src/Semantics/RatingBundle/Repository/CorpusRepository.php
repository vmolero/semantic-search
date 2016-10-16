<?php

namespace Semantics\RatingBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Semantics\RatingBundle\Entity\Corpus;
use Semantics\RatingBundle\Interfaces\SemanticEntityHolder;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * CorpusRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class CorpusRepository extends EntityRepository
{
    /**
     *
     * @param RegistryInterface $orm
     * @param IEntity $corpusEntity
     * @return IEntity
     */
    public function save(RegistryInterface $orm, SemanticEntityHolder $corpusEntity)
    {
        if (strlen($corpusEntity->getLemma()) > 0) {
            $corpusMatches        = $this->findBy(['lemma' => $corpusEntity->getLemma()]);
            $existingCorpusEntity = array_shift($corpusMatches);
            if (!$existingCorpusEntity instanceof Corpus) {
                $orm->getManager()->persist($corpusEntity);
                $orm->getManager()->flush();
                $existingCorpusEntity = $corpusEntity;
            }
        }
        return $existingCorpusEntity;
    }
}
