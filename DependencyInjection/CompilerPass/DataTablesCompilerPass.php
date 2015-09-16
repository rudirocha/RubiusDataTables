<?php

/**
 * @Author: Rudi Rocha <rudi.rocha@gmail.com>
 * @Project: RubiusDataTablesBundle
 */

namespace Rubius\DataTablesBundle\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class DataTablesCompilerPass
 * @package Rubius\DataTablesBundle\DependencyInjection\CompilerPass
 * @description: This compiler pass prepares all tables declared by service
 * as strategies of a dataTables factory
 */
class DataTablesCompilerPass implements CompilerPassInterface
{

    /**
     * Prepare all tables as strategies. This bundle works like a factory.
     *
     * @param ContainerBuilder $container
     *
     * @api
     */
    public function process(ContainerBuilder $container)
    {
        $alias = $container->findTaggedServiceIds('dataTable.strategy');
        $dataTableFactory = $container->findDefinition('dataTable.factory');

        foreach ($alias as $id => $tags) {
            $dataTableFactory->addMethodCall(
                'addTable',
                array(new Reference($id), current($tags)['alias'])
            );
        }
    }
}