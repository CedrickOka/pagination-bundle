<?php

namespace Oka\PaginationBundle\Pagination;

use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 */
class ConfigurationBag extends ParameterBag
{
    public function getDefaults(): ?Configuration
    {
        return $this->get('_defaults');
    }

    public function setDefaults(Configuration $configurtion): void
    {
        $this->set('_defaults', $configurtion);
    }
}
