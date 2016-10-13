<?php

namespace Semantics\RatingBundle\Services\Datagrid;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Thrace\DataGridBundle\DataGrid\DataGridFactoryInterface;

/**
 * Description of TopicManagementBuilder
 *
 * @author Víctor Molero
 */
class TopicManagementBuilder
{
    const IDENTIFIER = 'topic_management';

    protected $factory;
    protected $translator;
    protected $router;
    protected $em;

    public function __construct(DataGridFactoryInterface $factory, TranslatorInterface $translator, RouterInterface $router, EntityManager $em)
    {
        $this->factory    = $factory;
        $this->translator = $translator;
        $this->router     = $router;
        $this->em         = $em;
    }
    public function build()
    {

        $dataGrid = $this->factory->createDataGrid(self::IDENTIFIER);
        $dataGrid
                ->setCaption($this->translator->trans('topic_management_datagrid.caption'))
                ->setColNames(array(
                    $this->translator->trans('column.topic'),
                    $this->translator->trans('column.tag'),
                ))
                ->setColModel(array(
                    array(
                        'name' => 'topic', 'index' => 'u.topic', 'width' => 200,
                        'align' => 'left', 'sortable' => true, 'search' => true,
                    ),
                    array(
                        'name' => 'tag', 'index' => 'u.tag', 'width' => 200,
                        'align' => 'left', 'sortable' => true, 'search' => true,
                    ),
                        /* array(
                          'name' => 'total', 'index' => 'total', 'width' => 200, 'aggregated' => true,
                          'align' => 'left', 'sortable' => true, 'search' => true,
                          'formatter' => 'currency',
                          ),
                          array(
                          'name' => 'enabled', 'index' => 'u.enabled', 'width' => 30,
                          'align' => 'left', 'sortable' => true, 'search' => true,
                          'formatter' => 'checkbox', 'search' => true, 'stype' => 'select',
                          'searchoptions' => array(
                          'value' => array(
                          1 => 'enable',
                          0 => 'disabled',
                          )
                          )
                          ), */
                ))
                ->setQueryBuilder($this->getQueryBuilder())
                ->enableSearchButton(true)
                ->enableSortable(true)
                ->enableAddButton(true)
                ->enableEditButton(true)
        ;

        return $dataGrid;
    }
    protected function getQueryBuilder()
    {
        $qb = $this->em->getRepository('RatingBundle:Topic')->createQueryBuilder('u');
        $qb->select('u.id, u.topic, u.tag, u');

        return $qb;
    }
}
