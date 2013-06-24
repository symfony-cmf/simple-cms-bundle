<?php

namespace Symfony\Cmf\Bundle\SimpleCmsBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class CmfSimpleCmsBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        if ($container->hasExtension('jms_di_extra')) {
            $container->getExtension('jms_di_extra')->blackListControllerFile(__DIR__ . '/Controller/PageAdminController.php');
        }
    }
}
