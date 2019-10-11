<?php

/**
 * Spiral Framework. Cycle ProxyFactory
 *
 * @license MIT
 * @author  Valentin V (Vvval)
 */
declare(strict_types=1);

namespace Cycle\ORM\Promise;

use Cycle\ORM\Promise\Exception\ProxyFactoryException;
use PhpParser\Node;

/**
 * @param string $object
 * @param string $property
 * @return Node\Expr\FuncCall
 */
function unsetFuncExpr(string $object, string $property): Node\Expr\FuncCall
{
    return funcCall('unset', [
        new Node\Arg(new Node\Expr\PropertyFetch(new Node\Expr\Variable($object), $property))
    ]);
}

/**
 * @param string $object
 * @param string $property
 * @return Node\Expr\FuncCall
 */
function issetFuncExpr(string $object, string $property): Node\Expr\FuncCall
{
    return funcCall('isset', [
        new Node\Arg(new Node\Expr\PropertyFetch(new Node\Expr\Variable($object), $property))
    ]);
}

/**
 * @param string $name
 * @param string $object
 * @param string $haystackConst
 * @return Node\Expr\FuncCall
 */
function inConstArrayFunc(string $name, string $object, string $haystackConst): Node\Expr\FuncCall
{
    return funcCall('in_array', [
        new Node\Arg(new Node\Expr\Variable($name)),
        new Node\Arg(new Node\Expr\ClassConstFetch(new Node\Name($object), $haystackConst)),
        new Node\Arg(constFetch('true'))
    ]);
}

/**
 * @param Node\Expr $condition
 * @param Node\Stmt $stmt
 * @return Node\Stmt\If_
 */
function throwExceptionOnNull(Node\Expr $condition, Node\Stmt $stmt): Node\Stmt\If_
{
    $if = new Node\Stmt\If_(notNull($condition));
    $if->stmts[] = $stmt;
    $if->else = new Node\Stmt\Else_();
    $if->else->stmts[] = throwException(
        shortName(ProxyFactoryException::class),
        'Promise not loaded'
    );

    return $if;
}

/**
 * @param Node\Expr $expr
 * @return Node\Expr\BinaryOp\NotIdentical
 */
function notNull(Node\Expr $expr): Node\Expr\BinaryOp\NotIdentical
{
    return new Node\Expr\BinaryOp\NotIdentical($expr, constFetch('null'));
}

/**
 * @param string $var
 * @param string $object
 * @param string $property
 * @param string $method
 * @return Node\Stmt\Expression
 */
function resolveIntoVar(
    string $var,
    string $object,
    string $property,
    string $method
): Node\Stmt\Expression {
    return new Node\Stmt\Expression(
        new Node\Expr\Assign(
            new Node\Expr\Variable($var),
            resolveMethodCall($object, $property, $method)
        )
    );
}

/**
 * @param string $object
 * @param string $property
 * @param string $method
 * @return Node\Expr\MethodCall
 */
function resolveMethodCall(string $object, string $property, string $method): Node\Expr\MethodCall
{
    return new Node\Expr\MethodCall(resolvePropertyFetch($object, $property), $method);
}

/**
 * @param Node\Expr $expr
 * @return Node\Expr\BinaryOp\Identical
 */
function equalsFalse(Node\Expr $expr): Node\Expr\BinaryOp\Identical
{
    return new Node\Expr\BinaryOp\Identical($expr, constFetch('false'));
}

/**
 * @param string $name
 * @return Node\Expr\ConstFetch
 */
function constFetch(string $name): Node\Expr\ConstFetch
{
    return new Node\Expr\ConstFetch(new Node\Name($name));
}

/**
 * @param string $property
 * @return Node\Stmt\Expression
 */
function buildCloneExpression(string $property): Node\Stmt\Expression
{
    $fetchedProperty = resolvePropertyFetch('this', $property);

    return new Node\Stmt\Expression(
        new Node\Expr\Assign($fetchedProperty, new Node\Expr\Clone_($fetchedProperty))
    );
}

/**
 * @param string $object
 * @param string $property
 * @return Node\Expr\PropertyFetch
 */
function resolvePropertyFetch(string $object, string $property): Node\Expr\PropertyFetch
{
    return new Node\Expr\PropertyFetch(new Node\Expr\Variable($object), $property);
}

/**
 * @param string $name
 * @param array  $args
 * @param array  $attributes
 * @return Node\Expr\FuncCall
 * @internal
 */
function funcCall(string $name, array $args = [], array $attributes = []): Node\Expr\FuncCall
{
    return new Node\Expr\FuncCall(new Node\Name($name), $args, $attributes);
}

/**
 * @param string $class
 * @param string $message
 * @return Node\Stmt\Throw_
 * @internal
 */
function throwException(string $class, string $message): Node\Stmt\Throw_
{
    return new Node\Stmt\Throw_(
        new Node\Expr\New_(new Node\Name($class), [new Node\Arg(new Node\Scalar\String_($message))])
    );
}