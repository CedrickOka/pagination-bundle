<?php

declare(strict_types=1);

namespace Oka\PaginationBundle\Pagination\FilterExpression;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 */
class EvaluationResult
{
    public function __construct(private $expr, private array $parameters = [])
    {
    }

    public function getExpr()
    {
        return $this->expr;
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function addParameter($name, $value): self
    {
        $this->parameters[$name] = $value;

        return $this;
    }

    public function setParameters(array $parameters): self
    {
        $this->parameters = $parameters;

        return $this;
    }
}
