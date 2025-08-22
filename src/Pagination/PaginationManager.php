<?php

namespace Oka\PaginationBundle\Pagination;

use Oka\PaginationBundle\Event\PageEvent;
use Oka\PaginationBundle\Exception\SortAttributeNotAvailableException;
use Oka\PaginationBundle\OkaPaginationEvents;
use Oka\PaginationBundle\Pagination\FilterExpression\FilterExpressionHandler;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 */
class PaginationManager
{
    private $registryLocator;
    private $configurations;
    private $filterHandler;
    private $dispatcher;

    public function __construct(ServiceLocator $registryLocator, ConfigurationBag $configurations, FilterExpressionHandler $filterHandler, EventDispatcherInterface $dispatcher)
    {
        $this->registryLocator = $registryLocator;
        $this->configurations = $configurations;
        $this->filterHandler = $filterHandler;
        $this->dispatcher = $dispatcher;
    }

    public function getConfiguration(string $managerName): Configuration
    {
        if (true === $this->configurations->has($managerName)) {
            return $this->configurations->get($managerName);
        }

        if (true === class_exists($managerName)) {
            return $this->configurations->getDefaults();
        }

        throw new \InvalidArgumentException(sprintf('The "%s" configuration name is not attached to a pagination manager.', $managerName));
    }

    public function paginate(string $managerName, Request $request, array $criteria = [], array $orderBy = [], bool $strictMode = true): Page
    {
        $configuration = $this->getConfiguration($managerName);
        $query = $this->createInternalQuery($managerName, $configuration, $request, $criteria, $orderBy, $strictMode);
        $page = $query->execute();

        $this->dispatcher->dispatch(new PageEvent($managerName, $configuration, $page), OkaPaginationEvents::PAGE);

        return $page;
    }

    public function createQuery(string $managerName, Request $request, array $criteria = [], array $orderBy = [], bool $strictMode = true): Query
    {
        return $this->createInternalQuery($managerName, $this->getConfiguration($managerName), $request, $criteria, $orderBy, $strictMode);
    }

    protected function createInternalQuery(string $managerName, Configuration $configuration, Request $request, array $criteria = [], array $orderBy = [], bool $strictMode = true): Query
    {
        $queryMappings = $configuration->getQueryMappings();
        $filters = $configuration->getFilters();
        $sort = $configuration->getSort();

        // Extract pagination criteria and sort in request
        $sortAttributes = $this->parseQueryToArray($request, $queryMappings['sort'], $sort['delimiter']);
        $descAttributes = $this->parseQueryToArray($request, $queryMappings['desc'], $sort['delimiter']);

        /** @var Filter $filter */
        foreach ($filters as $key => $filter) {
            if (true === $filter->isPrivate()) {
                continue;
            }

            if (true === $filter->isSearchable()) {
                if (null !== ($value = $request->{$filter->getLocation()}->get($key))) {
                    $criteria[$key] = $value;
                }
            }

            if (false === $filter->isOrderable()) {
                continue;
            }

            $sortAssert = in_array($key, $sortAttributes);

            if (false === $sortAssert && false === isset($sort['order'][$key])) {
                continue;
            }

            if (true === $sortAssert) {
                $orderBy[$key] = true === in_array($key, $descAttributes) ? 'DESC' : 'ASC';
            } else {
                $orderBy[$key] = true === in_array($key, $descAttributes) ? 'DESC' : $sort['order'][$key];
            }

            if (false !== ($sortKey = array_search($key, $sortAttributes))) {
                unset($sortAttributes[$sortKey]);
            }
        }

        if (false === empty($sortAttributes)) {
            throw new SortAttributeNotAvailableException($sortAttributes, sprintf('Invalid request sort attributes "%s" not available.', implode(',', $sortAttributes)));
        }

        /** @var \Doctrine\Persistence\ManagerRegistry $registry */
        $registry = $this->registryLocator->get($configuration->getDBDriver());
        /** @var \Doctrine\Persistence\ObjectManager $objectManager */
        $objectManager = $registry->getManager($configuration->getObjectManagerName());

        $query = new Query(
            $objectManager,
            $this->filterHandler,
            $configuration->getClassName() ?? $managerName,
            (int) $request->query->get($queryMappings['item_per_page'], $configuration->getItemPerPage()),
            $configuration->getMaxPageNumber(),
            $filters,
            (int) $request->query->get($queryMappings['page'], '1'),
            $criteria,
            $orderBy
        );

        $query->addQueryPart('select', $this->parseQueryToArray($request, $queryMappings['fields'], ','));
        $query->addQueryPart('distinct', $request->query->has($queryMappings['distinct']));

        return $query;
    }

    protected function parseQueryToArray(Request $request, string $key, ?string $delimiter = null, $defaultValue = null): array
    {
        $value = $request->query->get($key, $defaultValue);

        if ($value && null !== $delimiter) {
            $value = array_map(function ($value) {
                return $this->sanitizeQuery($value);
            }, explode($delimiter, $value));
        }

        return $value ?: [];
    }

    protected function sanitizeQuery(string $query): string
    {
        return trim(rawurldecode($query));
    }
}
