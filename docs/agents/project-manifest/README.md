# Project Manifest — Application Utils

> **Canonical "Source of Truth" for AI agent sessions.**
> Generated: 2026-02-23 | Package: `mistralys/application-utils` v3.1.x

## Overview

A drop-in PHP utility library aggregating several static helper classes for common tasks:
CSV handling, INI file editing, HTTP request processing, URL building, pagination, ZIP archives, lipsum detection, and boolean value objects.
Most heavy-lifting classes (FileHelper, ConvertHelper, StringBuilder, ImageHelper, etc.) live in the separate **core** and **image** sub-packages; this package re-exports them and adds the utilities listed here.

## Manifest Sections

| Section | File | Description |
|---|---|---|
| Tech Stack & Patterns | [tech-stack.md](tech-stack.md) | Runtime, dependencies, architecture, build tools. |
| File Tree | [file-tree.md](file-tree.md) | Annotated directory structure. |
| API Surface — CSVHelper | [api-csvhelper.md](api-csvhelper.md) | CSV reading, writing, parsing. |
| API Surface — IniHelper | [api-inihelper.md](api-inihelper.md) | INI file reader/editor. |
| API Surface — Request | [api-request.md](api-request.md) | HTTP request parameter handling, validation, filtering. |
| API Surface — URLBuilder | [api-urlbuilder.md](api-urlbuilder.md) | Fluent URL construction helper. |
| API Surface — PaginationHelper | [api-paginationhelper.md](api-paginationhelper.md) | Pagination math calculator. |
| API Surface — ZIPHelper | [api-ziphelper.md](api-ziphelper.md) | ZIP archive abstraction. |
| API Surface — Value & LipsumHelper | [api-value-lipsum.md](api-value-lipsum.md) | Boolean value objects & lipsum text detection. |
| API Surface — Global Functions | [api-functions.md](api-functions.md) | Namespace-level helper functions. |
| Key Data Flows | [data-flows.md](data-flows.md) | Main interaction paths through the system. |
| Constraints & Conventions | [constraints.md](constraints.md) | Rules, naming conventions, and gotchas. |
