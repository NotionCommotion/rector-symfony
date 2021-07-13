<?php

declare (strict_types=1);
namespace Rector\Core\PhpParser\Comparing;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Else_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\If_;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
final class ConditionSearcher
{
    /**
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @var \Rector\Core\PhpParser\Comparing\NodeComparator
     */
    private $nodeComparator;
    public function __construct(\Rector\Core\PhpParser\Node\BetterNodeFinder $betterNodeFinder, \Rector\Core\PhpParser\Comparing\NodeComparator $nodeComparator)
    {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->nodeComparator = $nodeComparator;
    }
    public function searchIfAndElseForVariableRedeclaration(\PhpParser\Node\Expr\Assign $assign, \PhpParser\Node\Stmt\If_ $if) : bool
    {
        $elseNode = $if->else;
        if (!$elseNode instanceof \PhpParser\Node\Stmt\Else_) {
            return \false;
        }
        /** @var Variable $varNode */
        $varNode = $assign->var;
        if (!$this->searchForVariableRedeclaration($varNode, $if->stmts)) {
            return \false;
        }
        foreach ($if->elseifs as $elseifNode) {
            if (!$this->searchForVariableRedeclaration($varNode, $elseifNode->stmts)) {
                return \false;
            }
        }
        return $this->searchForVariableRedeclaration($varNode, $elseNode->stmts);
    }
    /**
     * @param Stmt[] $stmts
     */
    private function searchForVariableRedeclaration(\PhpParser\Node\Expr\Variable $variable, array $stmts) : bool
    {
        foreach ($stmts as $stmt) {
            if ($this->checkIfVariableUsedInExpression($variable, $stmt)) {
                return \false;
            }
            if ($this->checkForVariableRedeclaration($variable, $stmt)) {
                return \true;
            }
        }
        return \false;
    }
    private function checkIfVariableUsedInExpression(\PhpParser\Node\Expr\Variable $variable, \PhpParser\Node\Stmt $stmt) : bool
    {
        if ($stmt instanceof \PhpParser\Node\Stmt\Expression) {
            $node = $stmt->expr instanceof \PhpParser\Node\Expr\Assign ? $stmt->expr->expr : $stmt->expr;
        } else {
            $node = $stmt;
        }
        return (bool) $this->betterNodeFinder->findFirst($node, function (\PhpParser\Node $subNode) use($variable) : bool {
            return $this->nodeComparator->areNodesEqual($variable, $subNode);
        });
    }
    private function checkForVariableRedeclaration(\PhpParser\Node\Expr\Variable $variable, \PhpParser\Node\Stmt $stmt) : bool
    {
        if (!$stmt instanceof \PhpParser\Node\Stmt\Expression) {
            return \false;
        }
        if (!$stmt->expr instanceof \PhpParser\Node\Expr\Assign) {
            return \false;
        }
        $assignVar = $stmt->expr->var;
        if (!$assignVar instanceof \PhpParser\Node\Expr\Variable) {
            return \false;
        }
        if ($variable->name !== $assignVar->name) {
            return \false;
        }
        return \true;
    }
}
