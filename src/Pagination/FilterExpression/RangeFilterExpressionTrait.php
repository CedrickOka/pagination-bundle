<?php
namespace Oka\PaginationBundle\Pagination\FilterExpression;

trait RangeFilterExpressionTrait
{
	protected static function getExpressionPattern() :string
	{
		return '#^range(?<leftOperator>\[|\])(?<start>.*),(?<end>.*)(?<rightOperator>\[|\])$#i';
	}
}
