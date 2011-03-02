<?php

require_once 'Zend/Paginator/Adapter/Interface.php';

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