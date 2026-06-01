<?php

declare(strict_types=1);

namespace Oka\PaginationBundle\Pagination;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 */
final class Filter
{
    public const LOCATIONS = ['query', 'request', 'files', 'headers'];

    // Compiled regex patterns for better performance
    private static array $typeCastPatterns = [
        'datetime' => true,
        'bool' => true,
        'boolean' => true,
        'array' => true,
        'int' => true,
        'integer' => true,
        'float' => true,
        'double' => true,
        'real' => true,
        'string' => true,
        'object' => true,
    ];

    /**
     * @var string
     */
    private $location;
    /**
     * @var string
     */
    private $propertyName;
    /**
     * @var string
     */
    private $castType;
    /**
     * @var bool
     */
    private $searchable;
    /**
     * @var bool
     */
    private $orderable;
    /**
     * @var bool
     */
    private $private;

    public function __construct(string $location, string $propertyName, string $castType, bool $searchable, bool $orderable, bool $private = false)
    {
        if (false === in_array($location, self::LOCATIONS, true)) {
            throw new \InvalidArgumentException(sprintf('The following options given "%s" for the arguments "$location" is not valid.', $location));
        }

        // Security: Validate a cast type
        if (!isset(self::$typeCastPatterns[$castType])) {
            throw new \InvalidArgumentException(sprintf('Unsupported cast type: "%s".', $castType));
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
        // Security: Validate value length before casting
        if (is_string($value) && strlen($value) > self::MAX_VALUE_LENGTH) {
            throw new \InvalidArgumentException(sprintf('Filter value too long (max %d characters)', self::MAX_VALUE_LENGTH));
        }

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

    /**
     * Check if a cast type is supported.
     */
    public static function isValidCastType(string $type): bool
    {
        return isset(self::$typeCastPatterns[$type]);
    }

    /**
     * Get supported cast types.
     *
     * @return string[]
     */
    public static function getSupportedCastTypes(): array
    {
        return array_keys(self::$typeCastPatterns);
    }
}
