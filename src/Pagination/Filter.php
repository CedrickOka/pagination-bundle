<?php
namespace Oka\PaginationBundle\Pagination;

/**
 *
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 *
 */
final class Filter
{
	private $propertyName;
	private $castType;
	private $searchable;
	private $ordering;
	
	public function __construct(string $propertyName, string $castType, bool $searchable, array $ordering)
	{
		$this->propertyName = $propertyName;
		$this->castType = $castType;
		$this->searchable = $searchable;
		$this->ordering = $ordering;
	}
	
	public function getPropertyName() :string
	{
		return $this->propertyName;
	}
	
	public function getCastType() :string
	{
		return $this->castType;
	}
	
	public function isSearchable() :bool
	{
		return $this->searchable;
	}
	
	public function getOrdering() :array
	{
		return $this->ordering;
	}
	
	public static function castTo(string $value, string $type)
	{
		switch (true) {
			case 'datetime' === $type:
				return !$value instanceof \DateTime ? new \DateTime(is_int($value) ? '@'.$value : $value) : $value;
				
			case 'bool' === $type || 'boolean' === $type:
				return false === $value || 'false' === $value || '0' === $value ? false : true;
				
			default:
				settype($value, $type);
				break;
		}
		
		return $value;
	}
}
