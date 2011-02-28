<?php
require_once 'Zend/Paginator/Adapter/Interface.php';
require_once 'Zend/Paginator.php';

class Joiner_ZendPaginatorAdapter implements Zend_Paginator_Adapter_Interface
{

	protected $joiner;

	function __construct(Joiner_Table $table) {
		$this->joiner = $table;
	}

	function count() {
		return $this->joiner->fetchCount();
	}

	function getItems($offset, $itemCountPerPage) {
		return $this->joiner->offset($offset)->limit($itemCountPerPage);
	}

}

class Paginator extends Zend_Paginator
{

	protected $isCacheInitialized = false;

	function __construct(Joiner_Table $table, $itemCountPerPage = 10, $currentPageNumber = 1, $scrollingStyle = 'Sliding') {

		parent::__construct(new Joiner_ZendPaginatorAdapter($table));
		
		$currentPageNumber = (int) $currentPageNumber;
		if (!$currentPageNumber)
			$currentPageNumber = 1;

		$this->setCurrentPageNumber($currentPageNumber);

		$this->setItemCountPerPage($itemCountPerPage);

		Zend_Paginator::setDefaultScrollingStyle($scrollingStyle);

	}

	function getPages($pageRange = 10) {
		$this->setPageRange($pageRange);
		return parent::getPages();
	}

	function setCacheEnable($bool) {

		if ($bool == true && !$this->isCacheInitialized) {
			$fO = array('lifetime' => 3600, 'automatic_serialization' => true);
			$bO = array('cache_dir'=>'/tmp');
			$cache = Zend_cache::factory('Core', 'File', $fO, $bO);

			Zend_Paginator::setCache($cache);

		}

		parent::setCacheEnable($bool);

	}

}