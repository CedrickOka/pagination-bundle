<?php

declare(strict_types=1);

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

    public function setDefaults(Configuration $configuration): void
    {
        $this->set('_defaults', $configuration);
    }
}
