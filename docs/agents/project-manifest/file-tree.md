# File Tree

```
application-utils/
├── composer.json                  # Package definition and autoloading
├── phpstan.neon                   # PHPStan config (level 6)
├── phpunit.xml                    # PHPUnit config
├── run-tests.bat                  # Windows test runner
├── changelog.md                   # Version history
├── README.md                      # Project overview and helpers list
├── LICENSE                        # GPL-3.0-or-later
│
├── src/                           # ── Source code (this package's own classes) ──
│   ├── functions.php              # Global helper functions (valBool, isCLI, etc.)
│   ├── CSVHelper.php              # CSV reading/parsing facade
│   ├── IniHelper.php              # INI file reader/editor
│   ├── LipsumHelper.php           # Lipsum dummy text detection entry point
│   ├── PaginationHelper.php       # Pagination math calculator
│   ├── Request.php                # HTTP request parameter access & building
│   ├── Value.php                  # Abstract base for value objects
│   ├── ZIPHelper.php              # ZIP archive abstraction
│   │
│   ├── ConvertHelper/
│   │   └── ThrowableInfo/         # (empty — classes moved to core package)
│   │       └── Call/              # (empty remnant directory)
│   │
│   ├── CSVHelper/
│   │   ├── Builder.php            # Fluent CSV string builder
│   │   └── Exception.php          # CSVHelper-specific exception
│   │
│   ├── IniHelper/
│   │   ├── INILine.php            # Single line in an INI file (value, comment, section header…)
│   │   ├── Section.php            # Named section within an INI file
│   │   └── Exception.php          # IniHelper-specific exception
│   │
│   ├── LipsumHelper/
│   │   └── LipsumDetector.php     # Detection logic, word matching, configurable threshold
│   │
│   ├── Request/
│   │   ├── AcceptHeader.php       # Single parsed Accept header entry
│   │   ├── AcceptHeaders.php      # Collection of parsed Accept headers
│   │   ├── Exception.php          # Request-specific exception
│   │   ├── RefreshParams.php      # URL refresh parameter manager
│   │   ├── RequestParam.php       # Registered request parameter (validation + filters)
│   │   ├── URLComparer.php        # Compare two URLs including query params
│   │   │
│   │   ├── Param/
│   │   │   ├── Filter.php         # Abstract base for parameter filters
│   │   │   ├── Filter/
│   │   │   │   ├── Boolean.php        # Converts to boolean
│   │   │   │   ├── CommaSeparated.php # Splits comma-separated string to array
│   │   │   │   ├── String.php         # Casts to string
│   │   │   │   └── StripWhitespace.php# Removes all whitespace
│   │   │   │
│   │   │   ├── Validator.php      # Abstract base for parameter validators
│   │   │   └── Validator/
│   │   │       ├── Alnum.php      # Alphanumeric validation
│   │   │       ├── Alpha.php      # Alphabetic validation
│   │   │       ├── Array.php      # Array type validation
│   │   │       ├── Callback.php   # Custom callback validation
│   │   │       ├── Enum.php       # Allowed-values-list validation
│   │   │       ├── Integer.php    # Integer validation
│   │   │       ├── Json.php       # JSON string validation
│   │   │       ├── Numeric.php    # Numeric validation
│   │   │       ├── Regex.php      # Regex pattern validation
│   │   │       ├── Url.php        # URL syntax validation
│   │   │       └── Valueslist.php # Multi-value list validation
│   │   │
│   │   └── RefreshParams/
│   │       ├── Exclude.php        # Abstract exclusion rule
│   │       └── Exclude/
│   │           ├── Callback.php   # Exclude by callable
│   │           └── Name.php       # Exclude by parameter name
│   │
│   ├── URLBuilder/
│   │   ├── URLBuilder.php         # Fluent URL construction
│   │   ├── URLBuilderException.php# URLBuilder-specific exception
│   │   └── URLBuilderInterface.php# Interface for URL builders
│   │
│   ├── Value/
│   │   ├── Bool.php               # Mutable boolean value object
│   │   └── Bool/
│   │       ├── True.php           # Sticky-true boolean (one-way false→true)
│   │       └── False.php          # Sticky-false boolean (one-way true→false)
│   │
│   └── ZIPHelper/
│       └── Exception.php          # ZIPHelper-specific exception
│
├── tests/
│   ├── bootstrap.php              # Test bootstrapper (defines TESTS_ROOT, loads autoloader)
│   ├── config.dist.php            # Template: TESTS_WEBSERVER_URL constant
│   ├── config.php                 # Local test config (gitignored copy of dist)
│   ├── tests.php.ini              # PHP INI overrides for test runs
│   │
│   ├── AppUtilsTests/             # PHPUnit test cases
│   │   ├── CSVHelperTest.php
│   │   ├── ImageTests.php
│   │   ├── IniHelperTest.php
│   │   ├── LipsumHelperTest.php
│   │   ├── PaginationHelperTests.php
│   │   ├── ReturnValuesTest.php
│   │   ├── TransliterationTest.php
│   │   ├── URLBuilderTests.php
│   │   ├── RequestTests/
│   │   │   ├── RefreshParamsTest.php
│   │   │   ├── RegisteredParamTests.php
│   │   │   ├── RequestHelperTest.php
│   │   │   └── RequestTest.php
│   │   └── XMLHelper/
│   │       ├── DOMErrorTests.php
│   │       ├── HTMLLoaderTests.php
│   │       └── SimpleXMLTests.php
│   │
│   ├── TestClasses/               # Shared test infrastructure
│   │   ├── BaseTestCase.php       # Base PHPUnit case (clears superglobals)
│   │   ├── RequestTestCase.php    # Abstract base for Request tests
│   │   └── Stubs/
│   │       ├── StubCustomURLBuilder.php
│   │       └── StubCustomURLBuilderInterface.php
│   │
│   ├── assets/                    # Test fixture files
│   │   ├── CSVHelper/             # CSV sample files
│   │   └── RequestHelper/         # Request test fixtures
│   │
│   └── phpstan/
│       └── constants.php          # Constant stubs for PHPStan
│
├── docs/
│   ├── agents/
│   │   └── project-manifest/      # This manifest (AI agent source-of-truth)
│   ├── libxml/                    # Code-gen for LibXML error constants
│   │   ├── class.php.tpl
│   │   ├── generate-errorcodes.php
│   │   ├── LibXML.php
│   │   └── libxmlerrors.txt
│   ├── phpstan/
│   │   └── output.txt             # Saved PHPStan output
│   └── phpunit/                   # (empty — saved test output destination)
│
├── examples/                      # Web-based usage examples
│   ├── composer.json              # Separate Composer install for examples
│   ├── readme.md
│   ├── htdocs/
│   │   ├── index.php              # Examples index page
│   │   ├── prepend.php            # Autoloader for examples
│   │   ├── css/                   # Example stylesheets
│   │   └── URLInfo/               # URL-related examples
│   └── vendor/                    # (examples Composer vendor — collapsed)
│
├── localization/                  # Translation UI and locale files
│   ├── composer.json
│   ├── index.php                  # Translation editor entry point
│   ├── storage.json               # Translation storage
│   ├── *-client.ini               # Client-side locale strings (de_DE, fr_FR)
│   ├── *-server.ini               # Server-side locale strings (de_DE, fr_FR)
│   └── vendor/                    # (localization Composer vendor — collapsed)
│
└── vendor/                        # Main Composer vendor (collapsed)
```
