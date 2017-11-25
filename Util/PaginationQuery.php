<?php
namespace Oka\PaginationBundle\Util;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\AbstractQuery;
use Oka\PaginationBundle\Exception\ObjectManagerNotSupportedException;
use Twig\Environment;
use Oka\PaginationBundle\Twig\OkaPaginationExtension;

/**
 *
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 *
 */
class PaginationQuery
{
	const HYDRATE_OBJECT = 0;
	const HYDRATE_ARRAY = 1;
	const TWIG_GLOBAL_VAR_NAME = 'oka_pagination';
	const DEFAULT_TEMPLATE = 'OkaPaginationBundle:Pagination:paginate.html.twig';
	
	/**
	 * @var ObjectManager $objectManager
	 */
	protected $objectManager;
	
	/**
	 * @var Environment $twig
	 */
	protected $twig;
	
	/**
	 * @var string $managerName
	 */
	protected $managerName;
	
	/**
	 * @var array $options
	 */
	protected $options;
	
	/**
	 * @var array $config
	 */
	protected $config;
	
	/**
	 * @var int $page
	 */
	protected $page;
	
	/**
	 * @var array $criteria
	 */
	protected $criteria;
	
	/**
	 * @var array $orderBy
	 */
	protected $orderBy;
	
	/**
	 * @var string $className
	 */
	protected $className;
	
	/**
	 * @var int $itemPerPage
	 */
	protected $itemPerPage;
	
	/**
	 * @var int $maxPageNumber
	 */
	protected $maxPageNumber;
	
	/**
	 * @var int $fullyItems
	 */
	protected $fullyItems;
	
	/**
	 * @var AbstractQuery $selectQuery
	 */
	protected $selectQuery;
	
	/**
	 * @var AbstractQuery $countQuery
	 */
	protected $countQuery;
	
	/**
	 * @var \Closure $selectItemsCallable
	 */
	protected $selectItemsCallable;
	
	/**
	 * @var \Closure $countItemsCallable
	 */
	protected $countItemsCallable;
	
	/**
	 * @var PaginationResultSet $resultSet
	 */
	protected $resultSet;
	
	/**
	 * @var array $items
	 */
	protected $items;
	
	/**
	 * Constructor.
	 * 
	 * @param ObjectManager $objectManager
	 * @param Environment $twig
	 * @param string $managerName
	 * @param array $options
	 * @param array $config
	 * @param int $page
	 * @param array $criteria
	 * @param array $orderBy
	 * @throws \InvalidArgumentException
	 */
	public function __construct(ObjectManager $objectManager, Environment $twig, $managerName, array $options, array $config, $page, array $criteria = [], array $orderBy = [])
	{
		if (!empty($options)) {
			if ($diff = array_diff(array_keys($options), ['twig_extension_enabled', 'strict_mode'])) {
				throw new \InvalidArgumentException(sprintf('The following options are not supported "%s"', implode(', ', $diff)));
			}
		} else {
			$options = [
					'twig_extension_enabled' 	=> true,
					'strict_mode' 				=> true
			];
		}
		
		$this->objectManager = $objectManager;
		$this->twig = $twig;
		
		$this->managerName = $managerName;
		$this->options = $options;
		$this->config = $config;
		$this->criteria = $criteria;
		$this->orderBy = $orderBy;
		$this->fullyItems = 0;
		
		$this->loadConfig($config);
		$this->setPage($page);
	}
	
	/**
	 * @return int
	 */
	public function getPage()
	{
		return $this->page;
	}
	
	/**
	 * @param int $page
	 * @return \Oka\PaginationBundle\Util\PaginationQuery
	 */
	public function setPage($page)
	{
		$this->page = $this->maxPageNumber < $page ? $this->maxPageNumber : (int) $page;
		return $this;
	}
	
	/**
	 * @return int
	 */
	public function getItemPerPage()
	{
		return $this->itemPerPage;
	}
	
	/**
	 * @param int $itemPerPage
	 * @return \Oka\PaginationBundle\Util\PaginationQuery
	 */
	public function setItemPerPage($itemPerPage)
	{
		$this->itemPerPage = $itemPerPage;
		return $this;
	}
	
	/**
	 * @return int
	 */
	public function getMaxPageNumber()
	{
		return $this->maxPageNumber;
	}
	
	/**
	 * @param AbstractQuery $query
	 * @return \Oka\PaginationBundle\Util\PaginationQuery
	 */
	public function setCountQuery(AbstractQuery $query)
	{
		$this->countQuery = $query;
		return $this;
	}
	
	/**
	 * @param AbstractQuery $query
	 * @return \Oka\PaginationBundle\Util\PaginationQuery
	 */
	public function setSelectQuery(AbstractQuery $query)
	{
		$this->selectQuery = $query;
		return $this;
	}
	
	/**
	 * @param \Closure $closure
	 * @return \Oka\PaginationBundle\Util\PaginationQuery
	 */
	public function setCountItemsCallable(\Closure $closure)
	{
		$this->countItemsCallable = $closure;
		return $this;
	}
	
	/**
	 * @param \Closure $closure
	 * @return \Oka\PaginationBundle\Util\PaginationQuery
	 */
	public function setSelectItemsCallable(\Closure $closure)
	{
		$this->selectItemsCallable = $closure;
		return $this;
	}
	
	/**
	 * Fetch page
	 * 
	 * @param integer $hydrationMode
	 * @throws \LogicException
	 * @throws \UnexpectedValueException
	 * @return \Oka\PaginationBundle\Util\PaginationResultSet
	 */
	public function fetch($hydrationMode = self::HYDRATE_OBJECT)
	{
		if (null === $this->resultSet) {
			$items = [];
			$objectRepository = $this->objectManager->getRepository($this->className);
			
			if ($this->countItemsCallable instanceof \Closure) {
				$fn = $this->countItemsCallable;
				$this->fullyItems = $fn($objectRepository, $this->criteria);
					
				if (!is_integer($this->fullyItems)) {
					throw new \UnexpectedValueException('The closure "countItemsCallable" returned an unexcepted value.');
				}
			} elseif ($this->countQuery instanceof \Doctrine\ORM\Query/* || $this->countQuery instanceof \Doctrine\ODM\MongoDB\Query\Query*/) {
				$this->fullyItems = $this->countQuery->getSingleScalarResult();
			} else {
				$this->internalCountQuery = $this->createCountQuery($this->criteria);
				$this->fullyItems = (int) ($this->internalCountQuery instanceof \Doctrine\ORM\Query ?
						$this->internalCountQuery->getSingleScalarResult() : $this->internalCountQuery->execute());
			}
			
			if ($this->fullyItems > 0) {
				if ($this->selectItemsCallable instanceof \Closure) {
					$fn = $this->selectItemsCallable;
					$items = $fn($objectRepository, $this->criteria, $this->orderBy, $this->itemPerPage, $this->getItemOffset());
			
					if (!is_array($items)) {
						throw new \UnexpectedValueException('The closure "selectItemsCallable" returned an unexcepted value.');
					}
				} elseif ($this->selectQuery instanceof \Doctrine\ORM\Query) {
					$items = $this->selectQuery->setFirstResult($this->getItemOffset())
												->setMaxResults($this->itemPerPage)
												->getResult();
				} else {
					$this->internalSelectQuery = $this->createSelectQuery($this->criteria);
					$items = $this->internalSelectQuery instanceof \Doctrine\ORM\Query ?
					$this->internalSelectQuery->getResult() : $this->internalSelectQuery->execute()->toArray(false);
				}
			}
			
			// Set pagination result set
			$this->setResultSet($items);
			
			if (isset($this->options['twig_extension_enabled']) && true === $this->options['twig_extension_enabled']) {
				$globals = $this->twig->getGlobals();
					
				if (isset($globals[OkaPaginationExtension::TWIG_GLOBAL_VAR_NAME])) {
					$globals[OkaPaginationExtension::TWIG_GLOBAL_VAR_NAME][$this->managerName] = $this->resultSet->toArray(['items']);
					$this->twig->addGlobal(OkaPaginationExtension::TWIG_GLOBAL_VAR_NAME, $globals[OkaPaginationExtension::TWIG_GLOBAL_VAR_NAME]);
				}
			}
		}
		
		return $hydrationMode == self::HYDRATE_ARRAY ? $this->resultSet->toArray() : $this->resultSet;
	}
	
	/**
	 * Load config
	 * 
	 * @param array $config
	 * @throws \InvalidArgumentException
	 * @return array
	 */
	protected function loadConfig(array $config)
	{
		$this->className = $config['class'];
		$this->itemPerPage = (int) $config['item_per_page'];
		$this->maxPageNumber = (int) $config['max_page_number'];		
	}
	
	/**
	 * Create internal count items query
	 * 
	 * @param array $criteria
	 * @return \Doctrine\ORM\AbstractQuery|\Doctrine\ODM\MongoDB\Query\Query
	 */
	protected function createCountQuery(array $criteria = [])
	{
		if ($this->objectManager instanceof \Doctrine\ORM\EntityManager) {
			/** @var \Doctrine\ORM\QueryBuilder $builder */
			$builder = $this->objectManager->createQueryBuilder()
							->select('COUNT(DISTINCT p)')
							->from($this->className, 'p');
			
			foreach ($criteria as $key => $value) {
				$builder->andWhere(sprintf('p.%1$s = :%1$s', $key))
						->setParameter($key, $value);
			}
		} elseif ($this->objectManager instanceof \Doctrine\ODM\MongoDB\DocumentManager) {
			$builder = $this->objectManager->createQueryBuilder($this->className)
							->count();
			
			foreach ($criteria as $key => $value) {
				$builder->field($key)->equals($value);
			}
		} else {
			throw new ObjectManagerNotSupportedException(sprintf('Doctrine object manager class "%s" is not supported.', get_class($this->objectManager)));
		}
		
		return $builder->getQuery();
	}
	
	/**
	 * Create internal select items query
	 * 
	 * @param array $criteria
	 * @param array $orderBy
	 * @return \Doctrine\ORM\AbstractQuery|\Doctrine\ODM\MongoDB\DocumentManager
	 */
	protected function createSelectQuery(array $criteria = [])
	{
		if ($this->objectManager instanceof \Doctrine\ORM\EntityManager) {
			$builder = $this->objectManager->createQueryBuilder()
							->select('p')
							->from($this->className, 'p')
							->setFirstResult($this->getItemOffset())
							->setMaxResults($this->itemPerPage);
			
			foreach ($criteria as $key => $value) {
				$builder->andWhere(sprintf('p.%1$s = :%1$s', $key))
						->setParameter($key, $value);
			}
			
			foreach ($this->orderBy as $key => $value) {
				$builder->orderBy(sprintf('p.%s', $key), $value);
			}
		} elseif ($this->objectManager instanceof \Doctrine\ODM\MongoDB\DocumentManager) {
			$builder = $this->objectManager->createQueryBuilder($this->className)
							->skip($this->getItemOffset())
							->limit($this->itemPerPage);
			
			foreach ($criteria as $key => $value) {
				$builder->field($key)->equals($value);
			}
			
			foreach ($this->orderBy as $key => $value) {
				$builder->sort($key, $value);
			}
		} else {
			throw new ObjectManagerNotSupportedException(sprintf('Doctrine object manager class "%s" is not supported.', get_class($this->objectManager)));
		}
		
		return $builder->getQuery();
	}
	
	/**
	 * @return int
	 */
	protected function getItemOffset()
	{
		return $this->page < 2 ? 
			0 : $this->itemPerPage * ($this->maxPageNumber < $this->page ? $this->maxPageNumber - 1 : $this->page - 1);
	}
	
	/**
	 * Get page number
	 * 
	 * @return int
	 */
	protected function countPage()
	{
		$pageNumber = 0;
		$items = $this->fullyItems - $this->itemPerPage;
		
		while ($items > 0) {
			++$pageNumber;
			$items -= $this->itemPerPage;
		}
		++$pageNumber;
		
		return $pageNumber;
	}
	
	protected function setResultSet(array $items) {
		$this->resultSet = new PaginationResultSet(
				$this->page, 
				$this->itemPerPage, 
				$this->criteria, 
				$this->orderBy, 
				$this->getItemOffset(), 
				$this->fullyItems, 
				$this->countPage(), 
				$items
		);
		return $this;
	}
}
