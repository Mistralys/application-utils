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
    * @var int
    */
    protected $adjacentPages = 3;
    
   /**
    * @var int
    */
    protected $offsetEnd = 0;
    
   /**
    * @var int
    */
    protected $offsetStart = 0;
    
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
    * Sets the amount of adjacent pages to display next to the
    * current one when using the pages list.
    *
    * @param int $amount
    * @return PaginationHelper
    */
    public function setAdjacentPages(int $amount) : PaginationHelper
    {
        $this->adjacentPages = $amount;
        return $this;
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
    
    public function getCurrentPage() : int
    {
        return $this->current;
    }
    
   /**
    * Retrieves a list of page numbers for a page
    * navigator, to quickly jump between pages.
    *
    * @return int[]
    */
    public function getPageNumbers() : array
    {
        $adjacent = $this->adjacentPages;

        // adjust the adjacent value if it exceeds the
        // total amount of pages
        $adjacentTotal = ($adjacent * 2) + 1;
        if($adjacentTotal > $this->last) 
        {
            $adjacent = (int)floor($this->last / 2);
        }
        
        // determine the maximum amount of 
        // pages that one can go forward or
        // back from the current position.
        $maxBack = $this->current - 1;
        $maxFwd = $this->last - $this->current;
        $back = 0;
        $fwd = 0;
        
        if($maxBack >= $adjacent) {
            $back = $adjacent; 
        } else {
            $back = $maxBack;
        }
        
        if($maxFwd >= $adjacent)  {
            $fwd = $adjacent;
        } else {
            $fwd = $maxFwd;
        }
        
        // now calculate the amount of pages to add
        // left or right, depending on whether we
        // are at the beginning of the list, or at
        // the end.
        $backDiff = $adjacent - $back;
        $fwdDiff = $adjacent - $fwd;
        
        $fwd += $backDiff;
        $back += $fwdDiff;
        
        if($fwd > $maxFwd) { $fwd = $maxFwd; }
        if($back > $maxBack) { $back = $maxBack; }
        
        // calculate the first and last page in the list
        $prev = $this->current - $back;
        $next = $this->current + $fwd;
        
        // failsafe so we stay within the bounds
        if($prev < 1) { $prev = 1; }
        if($next > $this->last) { $next = $this->last; }
        
        // create and return the page numbers list
        $numbers = range($prev, $next);

        /*
        print_r(array(
            'current' => $this->current,
            'totalPages' => $this->last,
            'adjacent' => $adjacent,
            'maxBack' => $maxBack,
            'maxFwd' => $maxFwd,
            'back' => $back,
            'fwd' => $fwd,
            'backDiff' => $backDiff,
            'fwdDiff' => $fwdDiff,
            'prev' => $prev,
            'next' => $next,
            'numbers' => $numbers
        ));*/
        
        return $numbers;
    }
    
   /**
    * Whether the specified page number is the current page.
    * 
    * @param int $pageNumber
    * @return bool
    */
    public function isCurrentPage(int $pageNumber) : bool
    {
        return $pageNumber === $this->current;
    }
    
   /**
    * Retrieves the 1-based starting offset of
    * items currently displayed in the page.
    * 
    * Note: Use this to create a text like 
    * "showing entries x to y".
    * 
    * @return int
    * @see PaginationHelper::getOffsetEnd()
    */
    public function getItemsStart() : int
    {
        return $this->getOffsetStart() + 1;
    }

   /**
    * Retrieves the 1-based ending offset of
    * items currently displayed in the page.
    * 
    * Note: Use this to create a text like 
    * "showing entries x to y".
    * 
    * @return int
    * @see PaginationHelper::getOffsetStart()
    */
    public function getItemsEnd() : int
    {
        return $this->getOffsetEnd() + 1;
    }
    
   /**
    * Retrieves the 0-based starting offset of
    * items currently displayed in the page.
    * 
    * @return int
    * @see PaginationHelper::getItemsStart()
    */
    public function getOffsetStart() : int
    {
        return $this->offsetStart;
    }
    
   /**
    * Retrieves the 0-based ending offset of
    * items currently displayed in the page.
    * 
    * @return int
    * @see PaginationHelper::getItemsEnd()
    */
    public function getOffsetEnd() : int
    {
        return $this->offsetEnd;
    }
    
    protected function calculate()
    {
        $pages = (int)ceil($this->total / $this->perPage);
        
        if($this->current < 1)
        {
            $this->current = 1;
        }
        else if($this->current > $pages)
        {
            $this->current = $pages;
        }
        
        $offset = ($this->current-1) * $this->perPage;
        $this->last = $pages;
        
        $start = $offset;
        if($start === 0) {
            $start = 1;
        }
        
        $nextPage = $this->current + 1;
        if($nextPage <= $pages) {
            $this->next = $nextPage;
        }
        
        $prevPage = $this->current - 1;
        if($prevPage > 0) {
            $this->prev = $prevPage;
        }
        
        $this->offsetStart = ($this->current - 1) * $this->perPage;
        
        $this->offsetEnd = $this->offsetStart + $this->perPage;
        if($this->offsetEnd > ($this->total - 1)) {
            $this->offsetEnd = ($this->total - 1);
        }
    }
}