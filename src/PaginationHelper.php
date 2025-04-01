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
 * number of items.
 * 
 * @access public
 * @package Application Utils
 * @subpackage Misc
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class PaginationHelper
{
    protected int $total;
    protected int $perPage;
    protected int $current;
    protected int $next = 0;
    protected int $prev = 0;
    protected int $last = 0;
    protected int $adjacentPages = 3;
    protected int $offsetEnd = 0;
    protected int $offsetStart = 0;

    /**
     * @var array<string,int|int[]>|NULL
     */
    protected ?array $pagesDebug = null;

    /**
    * @param int $totalItems The total number of items available.
    * @param int $itemsPerPage How many items to display per page.
    * @param int $currentPage The current page number (1-based). Defaults to 1.
    */
    public function __construct(int $totalItems, int $itemsPerPage, int $currentPage=1)
    {
        $this->total = $totalItems;
        $this->perPage = $itemsPerPage;
        $this->current = $currentPage;
        
        $this->calculate();
    }

    /**
     * Creates an instance of the helper. Useful for chaining methods.
     *
     * @param int $totalItems
     * @param int $itemsPerPage
     * @param int $currentPage
     * @return PaginationHelper
     */
    public static function factory(int $totalItems, int $itemsPerPage, int $currentPage=1) : PaginationHelper
    {
        return new PaginationHelper($totalItems, $itemsPerPage, $currentPage);
    }

    /**
     * Sets/updates the current page number.
     *
     * NOTE: Causes all calculations to be run again.
     *
     * @param int $page
     * @return $this
     */
    public function setCurrentPage(int $page) : self
    {
        $this->current = $page;

        $this->calculate();

        return $this;
    }

    public function getTotalItems() : int
    {
        return $this->total;
    }

    public function getItemsPerPage() : int
    {
        return $this->perPage;
    }
    
   /**
    * Sets the number of adjacent pages to display next to the
    * current one when using the page list.
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
     * Alias for {@see self::getLastPage()}.
     * @return int
     */
    public function getTotalPages() : int
    {
        return $this->getLastPage();
    }
    
   /**
    * Whether there is more than one page, i.e., whether
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
        // total number of pages
        $adjacentTotal = ($adjacent * 2) + 1;
        if($adjacentTotal > $this->last) 
        {
            $adjacent = (int)floor($this->last / 2);
        }
        
        // determine the maximum number of
        // pages that one can go forward or
        // back from the current position.
        $maxBack = $this->current - 1;
        $maxFwd = $this->last - $this->current;

        $back = (int)min($maxBack, $adjacent);
        $fwd = (int)min($maxFwd, $adjacent);
        
        // now calculate the number of pages to add
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

        $this->pagesDebug = array(
            'adjacent' => $adjacent,
            'maxBack' => $maxBack,
            'maxFwd' => $maxFwd,
            'back' => $back,
            'fwd' => $fwd,
            'backDiff' => $backDiff,
            'fwdDiff' => $fwdDiff,
            'prev' => $prev,
            'next' => $next
        );
        
        return $numbers;
    }

    /**
     * @return array{current:int, total:int, perPage:int, next:int, prev:int, last:int, hasPages:bool, hasPrevious:bool, hasNext:bool, pageNumbers:int[], pagesDebug:array<string,int|int[]>}
     */
    public function getDump() : array
    {
        return array(
            'current' => $this->getCurrentPage(),
            'total' => $this->getTotalPages(),
            'perPage' => $this->getItemsPerPage(),
            'next' => $this->getNextPage(),
            'prev' => $this->getPreviousPage(),
            'last' => $this->getLastPage(),
            'hasPages' => $this->hasPages(),
            'hasPrevious' => $this->hasPreviousPage(),
            'hasNext' => $this->hasNextPage(),
            'pageNumbers' => $this->getPageNumbers(),
            'pagesDebug' => $this->pagesDebug ?? array(),
        );
    }

    /**
     * Echos debugging information with details on the
     * calculations performed internally.
     *
     * > NOTE: Automatically switches to HTML output if
     * > the script is not running in CLI mode.
     *
     * @return void
     */
    public function dump() : void
    {
        if(!isCLI()) {
            echo
                '<pre>'.
                'PaginationHelper Debug:<br>'.
                print_r($this->getDump(), true).
                '</pre>';
            return;
        }

        echo
            PHP_EOL.
            'PaginationHelper Debug:'.PHP_EOL.
            print_r($this->getDump(), true).
            PHP_EOL;
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

    private function reset() : void
    {
        $this->next = 0;
        $this->prev = 0;
        $this->last = 0;
    }
    
    protected function calculate() : void
    {
        $this->reset();

        $pages = (int)ceil($this->total / $this->perPage);
        if($pages < 1) {
            $pages = 1;
        }
        
        if($this->current < 1)
        {
            $this->current = 1;
        }
        else if($this->current > $pages)
        {
            $this->current = $pages;
        }

        $this->last = $pages;
        
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
