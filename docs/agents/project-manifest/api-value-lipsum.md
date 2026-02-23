# API Surface — Value & LipsumHelper

> **Namespace:** `AppUtils`

---

## Value Classes

Boolean value objects with optional one-way "sticky" semantics.

### `Value` (abstract)

**File:** `src/Value.php`

Empty abstract base class for all value types.

---

### `Value_Bool`

**File:** `src/Value/Bool.php`
**Extends:** `Value`

Mutable wrapper around a boolean.

```php
public function __construct(bool $value = false)
public function set(bool $value): Value_Bool
public function get(): bool
public function isTrue(): bool
public function isFalse(): bool
```

---

### `Value_Bool_True`

**File:** `src/Value/Bool/True.php`
**Extends:** `Value_Bool`

**Sticky true**: starts as `false`. Once set to `true`, subsequent calls to `set(false)` are silently ignored.

```php
public function set(bool $value): Value_Bool  // only transitions false → true
```

---

### `Value_Bool_False`

**File:** `src/Value/Bool/False.php`
**Extends:** `Value_Bool`

**Sticky false**: starts as `true`. Once set to `false`, subsequent calls to `set(true)` are silently ignored.

```php
public function __construct(bool $value = true)
public function set(bool $value): Value_Bool  // only transitions true → false
```

---

## LipsumHelper

Detects whether a string contains Lorem Ipsum placeholder text.

### `LipsumHelper`

**File:** `src/LipsumHelper.php`

Static entry point.

```php
public static function containsLipsum(string $subject): LipsumDetector
```

---

### `LipsumHelper\LipsumDetector`

**File:** `src/LipsumHelper/LipsumDetector.php`

Performs word-level matching against known lipsum vocabulary with a configurable minimum-word threshold.

```php
public function __construct(string $subject)
public function setMinWords(int $min): self
public function isDetected(): bool
public function getDetectedWords(): array   // string[]
public function countDetectedWords(): int
```
