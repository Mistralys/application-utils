# API Surface — IniHelper

> **Module:** INI file reading, editing, and creating.
> **Namespace:** `AppUtils`
> **Files:** `src/IniHelper.php`, `src/IniHelper/Section.php`, `src/IniHelper/INILine.php`, `src/IniHelper/Exception.php`

---

## `IniHelper`

**File:** `src/IniHelper.php`

Read, edit, and create INI files with section support. Preserves comments and formatting on round-trip.

### Constants

| Constant | Type | Value | Description |
|---|---|---|---|
| `SECTION_DEFAULT` | string | `'__inihelper_section_default'` | Name of the implicit default section |
| `ERROR_TARGET_FILE_NOT_READABLE` | int | `41802` | Target file cannot be read |

### Methods

```php
// Factory
public static function createFromFile(string $iniPath): IniHelper
public static function createFromString(string $iniContent): IniHelper
public static function createNew(): IniHelper

// Sections
public function addSection(string $name): IniHelper_Section
public function getSection(string $name): ?IniHelper_Section
public function sectionExists(string $name): bool
public function getDefaultSection(): IniHelper_Section

// Values
public function setValue(string $path, mixed $value): IniHelper
public function addValue(string $path, mixed $value): self
public function getLinesByVariable(string $path): array   // INILine[]

// Serialization
public function toArray(): array
public function saveToFile(string $filePath): IniHelper
public function saveToString(): string
public function getEOLChar(): string
```

---

## `IniHelper_Section`

**File:** `src/IniHelper/Section.php`

A named section within an INI file. The default (unnamed) section holds values declared before any `[section]` header.

### Methods

```php
public function __construct(IniHelper $ini, string $name)
public function getName(): string
public function isDefault(): bool

// Line management
public function addLine(INILine $line): IniHelper_Section
public function deleteLine(INILine $toDelete): IniHelper_Section

// Values
public function setValue(string $name, mixed $value): IniHelper_Section
public function addValue(string $name, mixed $value): IniHelper_Section
public function getLinesByVariable(string $name): array    // INILine[]

// Serialization
public function toArray(): array    // array<string, array<int, string>>
public function toString(): string
```

---

## `IniHelper\INILine`

**File:** `src/IniHelper/INILine.php`

Represents a single parsed line in an INI file — value assignment, section declaration, comment, empty line, or invalid line.

### Constants

| Constant | Type | Value | Description |
|---|---|---|---|
| `TYPE_SECTION_DECLARATION` | string | `'section'` | `[SectionName]` header |
| `TYPE_COMMENT` | string | `'comment'` | `;` or `#` prefixed line |
| `TYPE_EMPTY` | string | `'empty'` | Blank line |
| `TYPE_INVALID` | string | `'invalid'` | Unparseable line |
| `TYPE_VALUE` | string | `'value'` | `key = value` assignment |
| `ERROR_UNHANDLED_LINE_TYPE` | int | `41901` | Unknown line type encountered |
| `ERROR_NON_SCALAR_VALUE` | int | `41902` | Non-scalar value set on a line |

### Methods

```php
public function __construct(string $text, int $lineNumber)

// Accessors
public function getVarName(): string
public function getVarValue(): string
public function getQuotedVarValue(): string
public function getText(): string
public function getLineNumber(): int
public function getSectionName(): string

// Type checks
public function isSection(): bool
public function isComment(): bool
public function isValue(): bool
public function isValid(): bool
public function isEmpty(): bool

// Mutation
public function setValue(mixed $value): INILine

// Serialization
public function toString(): string
```

---

## `IniHelper_Exception`

**File:** `src/IniHelper/Exception.php`
**Extends:** `AppUtils\BaseException`

No additional public members.
