<?php

namespace Oka\PaginationBundle\Exception;

/**
 *
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 *
 */
class SortAttributeNotAvailableException extends PaginationException
{
    private $sort;

    public function __construct(array $sort, $message = null, $code = null, $previous = null)
    {
        parent::__construct($message, $code, $previous);

        $this->sort = $sort;
    }

    public function getSort(): array
    {
        return $this->sort;
    }
}
