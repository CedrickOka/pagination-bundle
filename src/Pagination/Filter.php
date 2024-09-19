<?php

namespace Oka\PaginationBundle\Pagination;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 */
final class Filter
{
    public const LOCATIONS = ['query', 'request', 'files', 'headers'];

    private $location;
    private $propertyName;
    private $castType;
    private $searchable;
    private $orderable;
    private $private;

    public function __construct(string $location, string $propertyName, string $castType, bool $searchable, bool $orderable, bool $private = false)
    {
        if (false === in_array($location, self::LOCATIONS, true)) {
            throw new \InvalidArgumentException(sprintf('The following options given "%s" for the arguments "$location" is not valid.', $location));
        }

        $this->propertyName = $propertyName;
        $this->castType = $castType;
        $this->searchable = $searchable;
        $this->orderable = $orderable;
        $this->private = $private;
        $this->location = $location;
    }

    public function getLocation(): string
    {
        return $this->location;
    }

    public function getPropertyName(): string
    {
        return $this->propertyName;
    }

    public function getCastType(): string
    {
        return $this->castType;
    }

    public function isSearchable(): bool
    {
        return $this->searchable;
    }

    public function isOrderable(): bool
    {
        return $this->orderable;
    }

    public function isPrivate(): bool
    {
        return $this->private;
    }

    public static function castTo($value, string $type)
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
