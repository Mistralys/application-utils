# API Surface — ZIPHelper

> **Module:** ZIP archive abstraction over PHP's `ZipArchive`.
> **Namespace:** `AppUtils`
> **Files:** `src/ZIPHelper.php`, `src/ZIPHelper/Exception.php`

---

## `ZIPHelper`

**File:** `src/ZIPHelper.php`

Simplifies creating, populating, saving, downloading, and extracting ZIP archives.

### Constants

| Constant | Type | Value | Description |
|---|---|---|---|
| `ERROR_SOURCE_FILE_DOES_NOT_EXIST` | int | `338001` | File to add does not exist |
| `ERROR_NO_FILES_IN_ARCHIVE` | int | `338002` | Attempted to save an empty archive |
| `ERROR_OPENING_ZIP_FILE` | int | `338003` | Failed to open the ZIP file |
| `ERROR_CANNOT_SAVE_FILE_TO_DISK` | int | `338004` | File could not be written to disk |

### Methods

```php
// Construction
public function __construct(string $targetFile)
public function setOption(string $name, mixed $value): ZIPHelper

// Adding content
public function addFile(string $filePath, ?string $zipPath = null): bool
public function addString(string $contents, string $zipPath): bool
public function addJSON(mixed $data, string $zipPath): bool
public function countFiles(): int

// Output
public function save(): ZIPHelper
public function download(?string $fileName = null): string
public function downloadAndDelete(?string $fileName = null): ZIPHelper

// Extraction
public function extractAll(?string $outputFolder = null): bool

// Low-level access
public function getArchive(): ZipArchive
```

---

## `ZIPHelper_Exception`

**File:** `src/ZIPHelper/Exception.php`
**Extends:** `AppUtils\BaseException`

No additional public members.
