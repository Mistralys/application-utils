# API Surface — PaginationHelper

> **Module:** Pagination math calculator.
> **Namespace:** `AppUtils`
> **File:** `src/PaginationHelper.php`

---

## `PaginationHelper`

Calculates all values needed for a pagination widget: page numbers, next/previous, offsets, and adjacent page windows. No rendering — pure math.

### Methods

```php
// Construction
public function __construct(int $totalItems, int $itemsPerPage, int $currentPage = 1)
public static function factory(int $totalItems, int $itemsPerPage, int $currentPage = 1): PaginationHelper

// Configuration
public function setCurrentPage(int $page): self
public function setAdjacentPages(int $amount): PaginationHelper

// Totals
public function getTotalItems(): int
public function getItemsPerPage(): int
public function getLastPage(): int
public function getTotalPages(): int
public function hasPages(): bool

// Navigation
public function getCurrentPage(): int
public function isCurrentPage(int $pageNumber): bool
public function hasNextPage(): bool
public function getNextPage(): int
public function hasPreviousPage(): bool
public function getPreviousPage(): int
public function getPageNumbers(): array   // int[] — windowed page numbers

// Item offsets (for SQL LIMIT / OFFSET)
public function getItemsStart(): int
public function getItemsEnd(): int
public function getOffsetStart(): int
public function getOffsetEnd(): int

// Debugging
public function getDump(): array
public function dump(): void
```
