# API Surface — URLBuilder

> **Module:** Fluent URL construction helper.
> **Namespace:** `AppUtils\URLBuilder`
> **Files:** `src/URLBuilder/URLBuilder.php`, `src/URLBuilder/URLBuilderInterface.php`, `src/URLBuilder/URLBuilderException.php`

---

## `URLBuilderInterface`

**File:** `src/URLBuilder/URLBuilderInterface.php`
**Extends:** `AppUtils\Interfaces\RenderableInterface`

Contract for all URL builders. Designed to be extended for application-specific URL structures.

### Methods

```php
public function remove(string $name): self
public function inheritParam(string $name): self
public function import(array $params): self
public function importURL(string $url): self
public function importURLInfo(URLInfo $info): self
public function auto(string $name, string|int|float|bool|null $value): self
public function int(string $name, int $value): self
public function float(string $name, float $value): self
public function string(string $name, ?string $value): self
public function bool(string $name, bool $value, bool $yesNo = false): self
public function arrayJSON(string $name, array $data): self
public function dispatcher(string $dispatcher): self
public function get(): string
public function getParams(): array
public function getParam(string $name): string|int|float|bool|null|array
public function hasParam(string $name): bool
```

---

## `URLBuilder`

**File:** `src/URLBuilder/URLBuilder.php`
**Implements:** `URLBuilderInterface`
**Uses trait:** `AppUtils\Traits\RenderableTrait`

Concrete implementation. Builds URLs with typed parameter setters and a fluent interface. Supports creating from arrays, URL strings, or `URLInfo` objects. Designed to be extended — provides an overridable `init()` method for custom initialization.

### Methods

```php
// Construction
final public function __construct(array $params = array())
public static function create(array $params = array()): self
public static function createFromURL(string $url): self
public static function createFromURLInfo(URLInfo $info): self

// Dispatcher
public function dispatcher(string $dispatcher): self
public function getDispatcher(): string

// Parameter management
public function remove(string $name): self
public function keepOnly(string|string[] ...$paramNames): self
public function inheritParam(string $name): self
public function import(array $params): self
public function importURL(string $url): self
public function importURLInfo(URLInfo $info): self

// Typed setters
public function auto(string $name, string|int|float|bool|null $value): self
public function int(string $name, int $value): self
public function float(string $name, float $value): self
public function string(string $name, ?string $value): self
public function bool(string $name, bool $value, bool $yesNo = false): self
public function arrayJSON(string $name, array $data): self

// Output
public function get(): string
public function render(): string
public function getParams(): array
public function getParam(string $name): mixed
public function hasParam(string $name): bool
```

---

## `URLBuilderException`

**File:** `src/URLBuilder/URLBuilderException.php`
**Extends:** `AppUtils\BaseException`

### Constants

| Constant | Type | Value |
|---|---|---|
| `ERROR_INVALID_HOST` | int | `169601` |
