<?php
/**
 * Spiral Framework.
 *
 * @license MIT
 * @author  Valentin V (Vvval)
 */
declare(strict_types=1);

namespace Cycle\ORM\Promise\Declaration\Declaration;

use Cycle\ORM\Promise\Declaration\DeclarationInterface;

final class ClassDeclaration implements DeclarationInterface
{
    /** @var string */
    private $shortName;

    /** @var string|null */
    private $namespace;

    public function __construct(string $name)
    {
        $this->shortName = $this->makeShortName($name);
        $this->namespace = $this->makeNamespaceName($name);
    }

    public function getShortName(): string
    {
        return $this->shortName;
    }

    public function getNamespaceName(): ?string
    {
        return $this->namespace;
    }

    public function getFullName(): string
    {
        if (empty($this->namespace)) {
            return "\\{$this->shortName}";
        }

        return "{$this->namespace}\\{$this->shortName}";
    }

    private function makeShortName(string $class): string
    {
        $class = rtrim($class, '\\');
        $lastPosition = mb_strripos($class, '\\');
        if ($lastPosition === false) {
            return $class;
        }

        return mb_substr($class, $lastPosition + 1);
    }

    private function makeNamespaceName(string $class): ?string
    {
        $class = rtrim($class, '\\');
        $lastPosition = mb_strripos($class, '\\');
        if ($lastPosition === 0) {
            return null;
        }

        if ($lastPosition !== false) {
            return ltrim(mb_substr($class, 0, $lastPosition), '\\');
        }

        return null;
    }
}