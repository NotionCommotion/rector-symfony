<?php

declare (strict_types=1);
namespace Rector\Nette\FormControlTypeResolver;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Type\TypeWithClassName;
use Rector\Core\PhpParser\AstResolver;
use Rector\Core\PhpParser\Node\Value\ValueResolver;
use Rector\Core\Reflection\ReflectionResolver;
use Rector\Core\ValueObject\MethodName;
use Rector\Nette\Contract\FormControlTypeResolverInterface;
use Rector\Nette\NodeResolver\MethodNamesByInputNamesResolver;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
final class GetComponentMethodCallFormControlTypeResolver implements \Rector\Nette\Contract\FormControlTypeResolverInterface
{
    /**
     * @var \Rector\Nette\NodeResolver\MethodNamesByInputNamesResolver
     */
    private $methodNamesByInputNamesResolver;
    /**
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @var \Rector\NodeTypeResolver\NodeTypeResolver
     */
    private $nodeTypeResolver;
    /**
     * @var \Rector\Core\PhpParser\Node\Value\ValueResolver
     */
    private $valueResolver;
    /**
     * @var \Rector\Core\Reflection\ReflectionResolver
     */
    private $reflectionResolver;
    /**
     * @var \Rector\Core\PhpParser\AstResolver
     */
    private $astResolver;
    public function __construct(\Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \Rector\NodeTypeResolver\NodeTypeResolver $nodeTypeResolver, \Rector\Core\PhpParser\Node\Value\ValueResolver $valueResolver, \Rector\Core\Reflection\ReflectionResolver $reflectionResolver, \Rector\Core\PhpParser\AstResolver $astResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->valueResolver = $valueResolver;
        $this->reflectionResolver = $reflectionResolver;
        $this->astResolver = $astResolver;
    }
    /**
     * @required
     */
    public function autowireGetComponentMethodCallFormControlTypeResolver(\Rector\Nette\NodeResolver\MethodNamesByInputNamesResolver $methodNamesByInputNamesResolver) : void
    {
        $this->methodNamesByInputNamesResolver = $methodNamesByInputNamesResolver;
    }
    /**
     * @return array<string, string>
     * @param \PhpParser\Node $node
     */
    public function resolve($node) : array
    {
        if (!$node instanceof \PhpParser\Node\Expr\MethodCall) {
            return [];
        }
        if (!$this->nodeNameResolver->isName($node->name, 'getComponent')) {
            return [];
        }
        $createComponentClassMethodName = $this->createCreateComponentMethodName($node);
        $staticType = $this->nodeTypeResolver->getStaticType($node);
        if (!$staticType instanceof \Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType) {
            return [];
        }
        // combine constructor + method body name
        $constructorClassMethodData = [];
        $constructorClassMethod = $this->astResolver->resolveClassMethod($staticType->getClassName(), \Rector\Core\ValueObject\MethodName::CONSTRUCT);
        if ($constructorClassMethod !== null) {
            $constructorClassMethodData = $this->methodNamesByInputNamesResolver->resolveExpr($constructorClassMethod);
        }
        $callerType = $this->nodeTypeResolver->getStaticType($node->var);
        if (!$callerType instanceof \PHPStan\Type\TypeWithClassName) {
            return $constructorClassMethodData;
        }
        $createComponentClassMethodData = [];
        $createComponentClassMethod = $this->astResolver->resolveClassMethod($callerType->getClassName(), $createComponentClassMethodName);
        if ($createComponentClassMethod !== null) {
            $createComponentClassMethodData = $this->methodNamesByInputNamesResolver->resolveExpr($createComponentClassMethod);
        }
        return \array_merge($constructorClassMethodData, $createComponentClassMethodData);
    }
    private function createCreateComponentMethodName(\PhpParser\Node\Expr\MethodCall $methodCall) : string
    {
        $firstArgumentValue = $methodCall->args[0]->value;
        return 'createComponent' . \ucfirst($this->valueResolver->getValue($firstArgumentValue));
    }
}
