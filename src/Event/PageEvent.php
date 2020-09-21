<?php
namespace Oka\PaginationBundle\Event;

use Oka\PaginationBundle\Pagination\Configuration;
use Oka\PaginationBundle\Pagination\Page;
use Symfony\Contracts\EventDispatcher\Event;

/**
 *
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 *
 */
class PageEvent extends Event
{
	private $managerName;
	private $configuration;
	private $page;
	
	public function __construct(string $managerName, Configuration $configuration, Page $page)
	{
		$this->managerName = $managerName;
		$this->configuration = $configuration;
		$this->page = $page;
	}
	
	public function getManagerName() :string
	{
		return $this->managerName;
	}
	
	public function getConfiguration() :Configuration
	{
		return $this->configuration;
	}
	
	public function getPage() :Page
	{
		return $this->page;
	}
}
