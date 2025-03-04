<?php
/**
 * @package Application Utils Tests
 * @subpackage PaginationHelper
 */

declare(strict_types=1);

namespace AppUtilsTests;

use PHPUnit\Framework\TestCase;

use AppUtils\PaginationHelper;
use TestClasses\BaseTestCase;

/**
 * @package Application Utils Tests
 * @subpackage PaginationHelper
 */
final class PaginationHelperTests extends BaseTestCase
{
    /**
     * @var array<int,array{
     *     label:string,
     *     total:int,
     *     perPage:int,
     *     current:int,
     *     prev:int,
     *     next:int,
     *     last:int,
     *     hasPages:bool,
     *     hasPrevious:bool,
     *     hasNext:bool,
     *     actualCurrent:int,
     *     pageNumbers?:int[]
     * }>
     */
    protected array $tests = array(
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
            'label' => 'Total pages, uneven number',
            'total' => 333,
            'perPage' => 50,
            'current' => 5,

            'prev' => 4,
            'next' => 6,
            'last' => 7, // 6.66 rounded up
            'hasPages' => true,
            'hasPrevious' => true,
            'hasNext' => true,
            'actualCurrent' => 5
        ),
        array(
            'label' => 'Single page',
            'total' => 3,
            'perPage' => 1,
            'current' => 3,

            'prev' => 2,
            'next' => 3,
            'last' => 3,
            'hasPages' => true,
            'hasPrevious' => true,
            'hasNext' => true,
            'actualCurrent' => 3
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
        ),
        array(
            'label' => 'No pages at all',
            'total' => 0,
            'perPage' => 30,
            'current' => 1,

            'prev' => 1,
            'next' => 1,
            'last' => 1,
            'hasPages' => false,
            'hasPrevious' => false,
            'hasNext' => false,
            'actualCurrent' => 1,
            'pageNumbers' => array(1)
        )
    );

    public function test_calculation() : void
    {
        foreach ($this->tests as $def) {
            $pager = new PaginationHelper(
                $def['total'],
                $def['perPage'],
                $def['current']
            );

            $this->assertEquals(
                $def['last'],
                $pager->getLastPage(),
                $def['label'] . ': Last page number'
            );

            $this->assertEquals(
                $def['next'],
                $pager->getNextPage(),
                $def['label'] . ': Next page number'
            );

            $this->assertEquals(
                $def['prev'],
                $pager->getPreviousPage(),
                $def['label'] . ': Previous page number'
            );

            $this->assertEquals(
                $def['hasPages'],
                $pager->hasPages(),
                $def['label'] . ': Has pages'
            );

            $this->assertEquals(
                $def['actualCurrent'],
                $pager->getCurrentPage(),
                $def['label'] . ': Current page number'
            );

            if (isset($def['pageNumbers'])) {
                $this->assertEquals(
                    $def['pageNumbers'],
                    $pager->getPageNumbers(),
                    $def['label'] . ': Pagination page numbers'
                );
            }
        }
    }
}
