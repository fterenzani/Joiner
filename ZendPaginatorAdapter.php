<?php
/**
 * @author Francesco Terenzani <f.terenzani@gmail.com>
 * @copyright Copyright (c) 2011, Francesco Terenzani
 * @package Joiner
 */

/**
 * Zend_Paginator_Adapter_Interface
 */
require_once 'Zend/Paginator/Adapter/Interface.php';

/**
 * Joiner doesn't provide a way to paginate resultset but instead provide an
 * inteface for the Zend Framework Paginator.
 *
 * How to use it in five minutes:
 *
 *
 * Step 1: Set up
 *
 * <code>
 * require_once 'Zend/Paginator.php';
 * require_once 'Joiner/ZendPaginatorAdapter.php';
 *
 * $items = Joiner::getAdapter()->getTable('Item')->where('a = ?', 'b');
 *
 * $paginator = new Zend_Paginator(Joiner_ZendPaginatorAdapter($items));
 * $paginator->setCurrentPageNumber(1);
 * $paginator->setItemCountPerPage(10);
 * $paginator->setPageRange(10);
 * </code>
 *
 *
 * Step 2: Iteration
 *
 * <code>
 * foreach ($paginator as $record) {
 *   $record instanceOf Joiner_Model; // true
 *
 * }
 * </code>
 *
 *
 * Step 3: Render a pagination controls
 *
 * <code>
 * <?php
 * $pages = $paginator->getPages();
 * ?>
 *
 * <?php if ($pages->pageCount): ?>
 * <div class="paginationControl">
 * <!-- Previous page link -->
 * <?php if (isset($pages->previous)): ?>
 *   <a href="?page=<?php echo $pages->previous); ?>">
 *     &lt; Previous
 *   </a> |
 * <?php else: ?>
 *   <span class="disabled">&lt; Previous</span> |
 * <?php endif; ?>
 *
 * <!-- Numbered page links -->
 * <?php foreach ($pages->pagesInRange as $page): ?>
 *   <?php if ($page != $pages->current): ?>
 *     <a href="?page=<?php echo $page); ?>">
 *         <?php echo $page; ?>
 *     </a> |
 *   <?php else: ?>
 *     <?php echo $page; ?> |
 *   <?php endif; ?>
 * <?php endforeach; ?>
 *
 * <!-- Next page link -->
 * <?php if (isset($pages->next)): ?>
 *   <a href="?page=<?php echo $pages->next); ?>">
 *     Next &gt;
 *   </a>
 * <?php else: ?>
 *   <span class="disabled">Next &gt;</span>
 * <?php endif; ?>
 * </div>
 * <?php endif; ?>
 * </code>
 *
 * Read more about the Zend Paginator
 * {@link http://framework.zend.com/manual/en/zend.paginator.html}
 *
 * @package Joiner
 */
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