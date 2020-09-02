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
	private $values;
	
	public function __construct($expr, array $values = [])
	{
		$this->expr = $expr;
		$this->values = $values;
	}
	
	public function getExpr()
	{
		return $this->expr;
	}
	
	public function getValues() :array
	{
		return $this->values;
	}
	
	public function addParameter($value) :self
	{
		$this->values[] = $value;
		return $this;
	}
	
	public function setValues(array $values) :self
	{
		$this->values = $values;
		return $this;
	}
}
