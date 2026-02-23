# API Surface — CSVHelper

> **Module:** CSV reading, writing, and parsing.
> **Namespace:** `AppUtils`
> **Files:** `src/CSVHelper.php`, `src/CSVHelper/Builder.php`, `src/CSVHelper/Exception.php`

---

## `CSVHelper`

**File:** `src/CSVHelper.php`

Facade for loading, parsing, and inspecting CSV data. Supports top, left, or no headers.

### Constants

| Constant | Type | Value | Description |
|---|---|---|---|
| `ERROR_INVALID_HEADERS_POSITION` | int | `561002` | Invalid header position specified |
| `ERROR_INVALID_FILE_ENCODING` | int | `561003` | File encoding not supported |
| `ERROR_FILE_PARSING_FAILED` | int | `561004` | CSV file could not be parsed |
| `ERROR_CSV_FILE_NOT_READABLE` | int | `561005` | File is not readable |
| `ERROR_STRING_PARSING_FAILED` | int | `561006` | CSV string could not be parsed |
| `DELIMITER_AUTO` | string | `'auto'` | Auto-detect delimiter |
| `HEADERS_LEFT` | string | `'hleft'` | Headers in first column |
| `HEADERS_TOP` | string | `'htop'` | Headers in first row |
| `HEADERS_NONE` | string | `'hnone'` | No headers |

### Methods

```php
public function __construct()

// Factory
public static function createBuilder(): CSVHelper_Builder
public static function createParser(string $delimiter = self::DELIMITER_AUTO): Csv

// Loading
public function loadString(string $string): self
public function loadFile(string $file): self
public function reset(): self

// Header configuration
public function setHeadersTop(): self
public function setHeadersLeft(): self
public function setHeadersNone(): self
public function setHeadersPosition(string $position): self
public function isHeadersLeft(): bool
public function isHeadersTop(): bool
public function isHeadersNone(): bool
public function isHeadersPosition(string $position): bool

// Data access
public function getData(): array             // array<int, array<int, mixed>>
public function getRow(int $index): array    // array<int, mixed>
public function rowExists(int $index): bool
public function countRows(): int
public function countColumns(): int
public function getHeaders(): array          // string[]
public function getColumn(int $index): array // string[]
public function columnExists(int $index): bool

// Error handling
public function hasErrors(): bool
public function getErrorMessages(): array    // string[]

// Static parsing shortcuts
public static function parseFile(string $path): array    // array<int, array<int, mixed>>
public static function parseString(string $string): array // array<int, array<int, mixed>>
```

---

## `CSVHelper_Builder`

**File:** `src/CSVHelper/Builder.php`

Fluent builder for generating CSV-formatted strings.

### Methods

```php
public function getDefaultOptions(): array
public function setSeparatorChar(string $char): self
public function setTrailingNewline(bool $useNewline = true): self
public function addLine(mixed ...$args): self
public function render(): string

// Option management (from Optionable)
public function setOption(string $name, mixed $value): self
public function setOptions(array $options): self
public function getOption(string $name, mixed $default = null): mixed
public function hasOption(string $name): bool
public function getOptions(): array
public function isOption(string $name, mixed $value): bool
```

---

## `CSVHelper_Exception`

**File:** `src/CSVHelper/Exception.php`
**Extends:** `AppUtils\BaseException`

No additional public members.
