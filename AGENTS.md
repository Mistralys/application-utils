# AGENTS.md — Application Utils

> **Rulebook for AI agents operating in this codebase.**
> Read this file in full before making any changes. Every directive here is mandatory unless explicitly marked SHOULD.

---

## 1. Project Manifest — Start Here!

**Location:** `docs/agents/project-manifest/`

The Project Manifest is the **canonical source of truth**. Read it before touching any source file.

| Document | Description |
|---|---|
| [README.md](docs/agents/project-manifest/README.md) | Manifest index — overview, section map, and quick-start ingestion path. |
| [tech-stack.md](docs/agents/project-manifest/tech-stack.md) | Runtime, dependencies, architecture patterns, autoloading, build tools. |
| [file-tree.md](docs/agents/project-manifest/file-tree.md) | Annotated directory structure for the entire repository. |
| [constraints.md](docs/agents/project-manifest/constraints.md) | Naming conventions, strict typing, exception handling, aggregation rules, gotchas. |
| [data-flows.md](docs/agents/project-manifest/data-flows.md) | Main interaction paths through every module (CSV, INI, Request, URL, ZIP, Lipsum, Pagination). |
| [api-csvhelper.md](docs/agents/project-manifest/api-csvhelper.md) | CSVHelper — reading, writing, parsing. |
| [api-inihelper.md](docs/agents/project-manifest/api-inihelper.md) | IniHelper — INI read/edit/create with round-trip fidelity. |
| [api-request.md](docs/agents/project-manifest/api-request.md) | Request — HTTP param access, validation, filtering, URL building. |
| [api-urlbuilder.md](docs/agents/project-manifest/api-urlbuilder.md) | URLBuilder — fluent URL construction. |
| [api-paginationhelper.md](docs/agents/project-manifest/api-paginationhelper.md) | PaginationHelper — pagination math calculator. |
| [api-ziphelper.md](docs/agents/project-manifest/api-ziphelper.md) | ZIPHelper — ZIP archive abstraction. |
| [api-value-lipsum.md](docs/agents/project-manifest/api-value-lipsum.md) | Value objects (sticky booleans) & LipsumHelper (dummy text detection). |
| [api-functions.md](docs/agents/project-manifest/api-functions.md) | Global helper functions (`valBool`, `isCLI`, etc.). |

### Quick Start Workflow

Follow this exact sequence when entering the project:

1. **Read** `docs/agents/project-manifest/README.md` — grasp scope and manifest layout.
2. **Read** `tech-stack.md` — understand runtime, dependencies, and architecture.
3. **Read** `constraints.md` — internalize naming conventions, error handling rules, and gotchas.
4. **Read** `file-tree.md` — locate files without filesystem scanning.
5. **Consult** the relevant `api-*.md` document(s) for the module you are working on.
6. **Consult** `data-flows.md` if you need to understand how data moves through a module.
7. **Only then** read source files.

---

## 2. Manifest Maintenance Rules

When you change code, you **MUST** update the corresponding manifest documents to prevent drift.

| Change Made | Documents to Update |
|---|---|
| New class or file added | `file-tree.md`, and the relevant `api-*.md` |
| Class or file removed/renamed | `file-tree.md`, and the relevant `api-*.md` |
| New public method added | The relevant `api-*.md` |
| Method signature changed | The relevant `api-*.md` |
| New error code constant added | The relevant `api-*.md` |
| Dependency added or removed | `tech-stack.md`, root `composer.json` |
| PHP version requirement changed | `tech-stack.md`, `constraints.md` |
| Directory restructured | `file-tree.md` |
| New data flow or major behavior change | `data-flows.md` |
| Naming convention or policy change | `constraints.md` |
| New global function added to `functions.php` | `api-functions.md`, `file-tree.md` |
| New test class or fixture added | `file-tree.md` |
| New module/helper created | `file-tree.md`, new `api-*.md`, `README.md` (manifest index), `data-flows.md` |
| Localization strings changed | `constraints.md` (if policy changes) |
| PHPStan or PHPUnit config changed | `tech-stack.md`, `constraints.md` |

---

## 3. Efficiency Rules — Search Smart

Do **not** scan the filesystem or grep source files when the answer is already in the manifest.

- **Finding a file?** Check `file-tree.md` FIRST.
- **Understanding a method or constant?** Check the relevant `api-*.md` FIRST.
- **Understanding architecture, patterns, or autoloading?** Check `tech-stack.md` FIRST.
- **Understanding naming conventions or error handling?** Check `constraints.md` FIRST.
- **Understanding how data flows through a module?** Check `data-flows.md` FIRST.
- **Only then** read source files for implementation details not covered by the manifest.

### Aggregation Package Awareness

This package is an **aggregation layer**. The heavy-lifting utility classes (`FileHelper`, `ConvertHelper`, `StringBuilder`, `XMLHelper`, `ClassHelper`, `URLInfo`, `ImageHelper`, `OperationResult`, etc.) live in **separate repositories** under `vendor/mistralys/`:

| Package | Contains |
|---|---|
| `mistralys/application-utils-core` | FileHelper, ConvertHelper, StringBuilder, URLInfo, XMLHelper, ClassHelper, RegexHelper, Traits/Interfaces |
| `mistralys/application-utils-image` | ImageHelper, RGBAColor, HSVColor, ImageSize |
| `mistralys/application-utils-result-handling` | OperationResult, OperationResultCollection |

**This repository's own code** (`src/`) contains only: CSVHelper, IniHelper, Request, URLBuilder, PaginationHelper, ZIPHelper, LipsumHelper, Value, and global functions. If you need to modify `FileHelper` or `ConvertHelper`, you are in the wrong repository.

---

## 4. Failure Protocol & Decision Matrix

| Scenario | Action | Priority |
|---|---|---|
| Ambiguous requirement | Use the most restrictive interpretation consistent with existing conventions. | MUST |
| Manifest and code conflict | Trust the manifest. Flag the code for a fix. | MUST |
| Missing manifest documentation | Flag the gap explicitly. Do not invent or assume undocumented behavior. | MUST |
| Untested code path | Proceed with caution. Add a test recommendation in your output. | SHOULD |
| Class appears to be missing | Check if it lives in a dependency package (`application-utils-core`, `-image`, `-result-handling`) before reporting it missing. | MUST |
| Empty directories in `src/ConvertHelper/ThrowableInfo/` | Ignore — these are remnants from a migration to the core package. | MUST |
| Deprecated class encountered (e.g., SVNHelper) | Do not use in new code. Reference the changelog or constraints for migration path. | MUST |
| Request singleton state leaking between tests | Ensure `BaseTestCase::setUp()`/`tearDown()` clears superglobals. Do not rely on `Request::getInstance()` state across tests. | MUST |
| Mixed PSR-0 (underscore) and PSR-4 (namespace) class names | Both styles coexist. Follow the style of the module you are editing. Check `constraints.md` for the naming convention rules. | MUST |
| Need to test a Request feature that requires a web server | Use `skipWebserverURL()` in the test to allow graceful skipping when no server is configured. Configure the URL in `tests/config.php`. | SHOULD |

---

## 5. Project Stats

| Item | Value |
|---|---|
| **Package** | `mistralys/application-utils` |
| **Language / Runtime** | PHP 7.4+ |
| **Type System** | `declare(strict_types=1)` in all source files |
| **Namespace Root** | `AppUtils` |
| **Architecture** | Aggregation package — static helper classes + factory methods |
| **Package Manager** | Composer |
| **Autoloading** | Classmap (`src/`) + files (`src/functions.php`) |
| **Test Framework** | PHPUnit ≥ 9.6 — `composer test` |
| **Static Analysis** | PHPStan ≥ 1.10 at level 6 — `composer analyze` |
| **License** | GPL-3.0-or-later |
| **Required Extensions** | mbstring, curl, json, gd, zip, dom, simplexml, libxml |
