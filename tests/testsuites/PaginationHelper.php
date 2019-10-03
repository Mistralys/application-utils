<?php

use PHPUnit\Framework\TestCase;

use AppUtils\PaginationHelper;

final class PaginationHelperTest extends TestCase
{
    protected $tests = array(
        array(
            'label' => 'No pagination required',
            'total' => 5,
            'perPage' => 10,
            'current' => 0,
            
            'prev' => 1,
            'next' => 1,
            'last' => 1,
            'hasPages' => false,
            'hasPrevious' => false,
            'hasNext' => false,
            'actualCurrent' => 1
        ),
        array(
            'label' => 'Current page out of bounds',
            'total' => 5,
            'perPage' => 10,
            'current' => 100,
            
            'prev' => 1,
            'next' => 1,
            'last' => 1, 
            'hasPages' => false,
            'hasPrevious' => false,
            'hasNext' => false,
            'actualCurrent' => 1
        ),
        array(
            'label' => 'Total pages',
            'total' => 1000,
            'perPage' => 50,
            'current' => 10,
            
            'prev' => 9,
            'next' => 11,
            'last' => 20,
            'hasPages' => true,
            'hasPrevious' => true,
            'hasNext' => true,
            'actualCurrent' => 10
        ),
        array(
            'label' => 'No next page',
            'total' => 100,
            'perPage' => 10,
            'current' => 10,
            
            'prev' => 9,
            'next' => 10,
            'last' => 10,
            'hasPages' => true,
            'hasPrevious' => true,
            'hasNext' => false,
            'actualCurrent' => 10
        ),
        array(
            'label' => 'No previous page',
            'total' => 100,
            'perPage' => 10,
            'current' => 1,
            
            'prev' => 1,
            'next' => 2,
            'last' => 10,
            'hasPages' => true,
            'hasPrevious' => false,
            'hasNext' => true,
            'actualCurrent' => 1
        ),
        array(
            'label' => 'Adjacent pages',
            'total' => 100,
            'perPage' => 10,
            'current' => 1,
            
            'prev' => 1,
            'next' => 2,
            'last' => 10,
            'hasPages' => true,
            'hasPrevious' => false,
            'hasNext' => true,
            'actualCurrent' => 1,
            'pageNumbers' => array(1, 2, 3, 4, 5, 6, 7)
        ),
        array(
            'label' => 'Adjacent pages, single page',
            'total' => 100,
            'perPage' => 100,
            'current' => 1,
            
            'prev' => 1,
            'next' => 1,
            'last' => 1,
            'hasPages' => false,
            'hasPrevious' => false,
            'hasNext' => false,
            'actualCurrent' => 1,
            'pageNumbers' => array(1)
        ),
        array(
            'label' => 'Adjacent pages, not enough room',
            'total' => 90,
            'perPage' => 30,
            'current' => 2,
            
            'prev' => 1,
            'next' => 3,
            'last' => 3,
            'hasPages' => true,
            'hasPrevious' => false,
            'hasNext' => true,
            'actualCurrent' => 2,
            'pageNumbers' => array(1, 2, 3)
        )
    );
    
    public function test_calculation()
    {
        foreach($this->tests as $def)
        {
            $pager = new PaginationHelper(
                $def['total'], 
                $def['perPage'], 
                $def['current']
            );
            
            $this->assertEquals(
                $def['last'], 
                $pager->getLastPage(), 
                $def['label'].': Last page number'
            );
            
            $this->assertEquals(
                $def['next'], 
                $pager->getNextPage(), 
                $def['label'].': Next page number'
            );
            
            $this->assertEquals(
                $def['prev'], 
                $pager->getPreviousPage(), 
                $def['label'].': Previous page number'
            );
            
            $this->assertEquals(
                $def['hasPages'], 
                $pager->hasPages(), 
                $def['label'].': Has pages'
            );
            
            $this->assertEquals(
                $def['actualCurrent'], 
                $pager->getCurrentPage(), 
                $def['label'].': Current page number'
            );
            
            if(isset($def['pageNumbers'])) 
            {
                $this->assertEquals(
                    $def['pageNumbers'],
                    $pager->getPageNumbers(),
                    $def['label'].': Pagination page numbers'
                );
            }
        }
    }
}
