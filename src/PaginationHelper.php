<?php
/**
 * File containing the {@link PaginationHelper} class
 *
 * @access public
 * @package Application Utils
 * @subpackage Misc
 * @see PaginationHelper
 */

declare(strict_types=1);

namespace AppUtils;

/**
 * Simple pagination calculator that can be used to
 * determine previous / next pages depending on the
 * amount of items.
 * 
 * @access public
 * @package Application Utils
 * @subpackage Misc
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class PaginationHelper
{
   /**
    * @var int
    */
    protected $total;
    
   /**
    * @var int
    */
    protected $perPage;
    
   /**
    * @var int
    */
    protected $current;
    
   /**
    * @var int
    */
    protected $next = 0;
    
   /**
    * @var int
    */
    protected $prev = 0;
    
   /**
    * @var int
    */
    protected $last = 0; 
    
   /**
    * @param int $totalItems The total amount of items available.
    * @param int $itemsPerPage How many items to display per page.
    * @param int $currentPage The current page number (1-based)
    */
    public function __construct(int $totalItems, int $itemsPerPage, int $currentPage)
    {
        $this->total = $totalItems;
        $this->perPage = $itemsPerPage;
        $this->current = $currentPage;
        
        $this->calculate();
    }
    
   /**
    * Whether there is a next page after the current page.
    * @return bool
    */
    public function hasNextPage() : bool
    {
        return $this->next > 0;
    }
    
   /**
    * The next page number. Returns the last page
    * number if there is no next page.
    *  
    * @return int
    */
    public function getNextPage() : int
    {
        if($this->next === 0) {
            return $this->last;
        }
        
        return $this->next;
    }
    
   /**
    * Whether there is a previous page before the current page.
    * @return bool
    */
    public function hasPreviousPage() : bool
    {
        return $this->prev > 0;
    }
    
   /**
    * The previous page number. Returns the first page
    * number if there is no previous page.
    * 
    * @return int
    */
    public function getPreviousPage() : int
    {
        if($this->prev === 0) {
            return 1;
        }
        
        return $this->prev;
    }
    
   /**
    * Retrieves the last page number.
    * @return int
    */
    public function getLastPage() : int
    {
        return $this->last;
    }
    
   /**
    * Whether there is more than one page, i.e. whether
    * pagination is required at all.
    *  
    * @return bool
    */
    public function hasPages() : bool
    {
        return $this->last > 1;
    }
    
    protected function calculate()
    {
        $page = $this->current;
        if($page < 1) {
            $page = 1;
        }
        
        $offset = ($page-1) * $this->perPage;
        $pages = ceil($this->total / $this->perPage);
        
        $start = $offset;
        if($start === 0) {
            $start = 1;
        }
        
        $nextPage = $page + 1;
        if($nextPage <= $pages) {
            $this->next = $nextPage;
        }
        
        $prevPage = $page - 1;
        if($prevPage > 0) {
            $this->prev = $prevPage;
        }
        
        $this->last = $page * $this->perPage;
        if($this->last > $this->total) {
            $this->last = $this->total;
        }
    }
}