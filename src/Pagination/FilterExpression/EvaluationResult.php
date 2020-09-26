<?php
namespace Oka\PaginationBundle\Pagination\FilterExpression;

/**
 *
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 *
 */
class EvaluationResult
{
	private $expr;
	private $parameters;
	
	public function __construct($expr, array $parameters = [])
	{
		$this->expr = $expr;
		$this->parameters = $parameters;
	}
	
	public function getExpr()
	{
		return $this->expr;
	}
	
	public function getParameters() :array
	{
		return $this->parameters;
	}
	
	public function addParameter($name, $value) :self
	{
		$this->parameters[$name] = $value;
		return $this;
	}
	
	public function setParameters(array $parameters) :self
	{
		$this->parameters = $parameters;
		return $this;
	}
}
