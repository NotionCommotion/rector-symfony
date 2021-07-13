<?php

declare(strict_types=1);

use Rector\Php80\Rector\Class_\AnnotationToAttributeRector;
use Rector\Php80\ValueObject\AnnotationToAttribute;
use Rector\Php74\Rector\Property\TypedPropertyRector;
use Rector\Php80\Rector\Class_\DoctrineAnnotationClassToAttributeRector;
use Rector\Privatization\Rector\Class_\FinalizeClassesWithoutChildrenRector;

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;
use Rector\Set\ValueObject\SetList;
//use Rector\Core\Configuration\Option;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(DoctrineAnnotationClassToAttributeRector::class)
        ->call('configure', [[
            DoctrineAnnotationClassToAttributeRector::REMOVE_ANNOTATIONS => true,
        ]]);
};