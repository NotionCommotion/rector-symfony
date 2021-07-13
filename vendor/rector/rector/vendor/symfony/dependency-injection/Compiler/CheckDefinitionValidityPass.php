<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix20210710\Symfony\Component\DependencyInjection\Compiler;

use RectorPrefix20210710\Symfony\Component\DependencyInjection\ContainerBuilder;
use RectorPrefix20210710\Symfony\Component\DependencyInjection\Exception\EnvParameterException;
use RectorPrefix20210710\Symfony\Component\DependencyInjection\Exception\RuntimeException;
use RectorPrefix20210710\Symfony\Component\DependencyInjection\Loader\FileLoader;
/**
 * This pass validates each definition individually only taking the information
 * into account which is contained in the definition itself.
 *
 * Later passes can rely on the following, and specifically do not need to
 * perform these checks themselves:
 *
 * - non synthetic, non abstract services always have a class set
 * - synthetic services are always public
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class CheckDefinitionValidityPass implements \RectorPrefix20210710\Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface
{
    /**
     * Processes the ContainerBuilder to validate the Definition.
     *
     * @throws RuntimeException When the Definition is invalid
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     */
    public function process($container)
    {
        foreach ($container->getDefinitions() as $id => $definition) {
            // synthetic service is public
            if ($definition->isSynthetic() && !$definition->isPublic()) {
                throw new \RectorPrefix20210710\Symfony\Component\DependencyInjection\Exception\RuntimeException(\sprintf('A synthetic service ("%s") must be public.', $id));
            }
            // non-synthetic, non-abstract service has class
            if (!$definition->isAbstract() && !$definition->isSynthetic() && !$definition->getClass() && !$definition->hasTag('container.service_locator') && (!$definition->getFactory() || !\preg_match(\RectorPrefix20210710\Symfony\Component\DependencyInjection\Loader\FileLoader::ANONYMOUS_ID_REGEXP, $id))) {
                if ($definition->getFactory()) {
                    throw new \RectorPrefix20210710\Symfony\Component\DependencyInjection\Exception\RuntimeException(\sprintf('Please add the class to service "%s" even if it is constructed by a factory since we might need to add method calls based on compile-time checks.', $id));
                }
                if (\class_exists($id) || \interface_exists($id, \false)) {
                    if (0 === \strpos($id, '\\') && 1 < \substr_count($id, '\\')) {
                        throw new \RectorPrefix20210710\Symfony\Component\DependencyInjection\Exception\RuntimeException(\sprintf('The definition for "%s" has no class attribute, and appears to reference a class or interface. Please specify the class attribute explicitly or remove the leading backslash by renaming the service to "%s" to get rid of this error.', $id, \substr($id, 1)));
                    }
                    throw new \RectorPrefix20210710\Symfony\Component\DependencyInjection\Exception\RuntimeException(\sprintf('The definition for "%s" has no class attribute, and appears to reference a class or interface in the global namespace. Leaving out the "class" attribute is only allowed for namespaced classes. Please specify the class attribute explicitly to get rid of this error.', $id));
                }
                throw new \RectorPrefix20210710\Symfony\Component\DependencyInjection\Exception\RuntimeException(\sprintf('The definition for "%s" has no class. If you intend to inject this service dynamically at runtime, please mark it as synthetic=true. If this is an abstract definition solely used by child definitions, please add abstract=true, otherwise specify a class to get rid of this error.', $id));
            }
            // tag attribute values must be scalars
            foreach ($definition->getTags() as $name => $tags) {
                foreach ($tags as $attributes) {
                    foreach ($attributes as $attribute => $value) {
                        if (!\is_scalar($value) && null !== $value) {
                            throw new \RectorPrefix20210710\Symfony\Component\DependencyInjection\Exception\RuntimeException(\sprintf('A "tags" attribute must be of a scalar-type for service "%s", tag "%s", attribute "%s".', $id, $name, $attribute));
                        }
                    }
                }
            }
            if ($definition->isPublic() && !$definition->isPrivate()) {
                $resolvedId = $container->resolveEnvPlaceholders($id, null, $usedEnvs);
                if (null !== $usedEnvs) {
                    throw new \RectorPrefix20210710\Symfony\Component\DependencyInjection\Exception\EnvParameterException([$resolvedId], null, 'A service name ("%s") cannot contain dynamic values.');
                }
            }
        }
        foreach ($container->getAliases() as $id => $alias) {
            if ($alias->isPublic() && !$alias->isPrivate()) {
                $resolvedId = $container->resolveEnvPlaceholders($id, null, $usedEnvs);
                if (null !== $usedEnvs) {
                    throw new \RectorPrefix20210710\Symfony\Component\DependencyInjection\Exception\EnvParameterException([$resolvedId], null, 'An alias name ("%s") cannot contain dynamic values.');
                }
            }
        }
    }
}