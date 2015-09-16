<?php

/**
 * @Author: Rudi Rocha <rudi.rocha@gmail.com>
 * @Project: RubiusDataTablesBundle
 */
namespace Rubius\DataTablesBundle;

use Rubius\DataTablesBundle\DependencyInjection\CompilerPass\DataTablesCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class RubiusDataTablesBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->addCompilerPass(new DataTablesCompilerPass());
    }

}
