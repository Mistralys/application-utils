# Key Data Flows

This document traces the main interaction paths through the utilities provided by this package.

---

## 1. CSV File Parsing

```
User calls CSVHelper::parseFile($path)
  → CSVHelper::createParser() instantiates ParseCsv\Csv (third-party)
  → Parser reads and decodes the file
  → CSVHelper returns array<int, array<int, mixed>>
```

**Alternate flow — instance-based with header control:**

```
$csv = new CSVHelper()
  → $csv->setHeadersTop()     // configure header position
  → $csv->loadFile($path)     // delegates to ParseCsv\Csv internally
  → $csv->getData()           // returns structured rows
  → $csv->getHeaders()        // returns header names from first row
```

**CSV generation:**

```
CSVHelper::createBuilder()
  → CSVHelper_Builder instance (fluent)
  → $builder->setSeparatorChar(';')
  → $builder->addLine('col1', 'col2', 'col3')  // repeated
  → $builder->render()  // returns CSV string
```

---

## 2. INI File Round-Trip (Read → Edit → Save)

```
IniHelper::createFromFile($path)
  → Reads file, detects EOL character
  → Parses each line into INILine objects (value, comment, section header, empty, invalid)
  → Groups lines into IniHelper_Section objects
  → Returns IniHelper instance

$ini->setValue('SectionName/keyName', 'newValue')
  → Locates or creates the section
  → Finds or creates the INILine for the key
  → Calls INILine::setValue()

$ini->saveToFile($outputPath)
  → Iterates all sections
  → Each section calls toString() on its INILine objects
  → Joins with detected EOL character
  → Writes to disk
```

---

## 3. HTTP Request Parameter Registration & Validation

```
$request = Request::getInstance()   // singleton, reads $_REQUEST/$_GET/$_POST

$param = $request->registerParam('page')
  → Returns RequestParam instance
  → $param->setInteger()           // sets Validator to Request_Param_Validator_Integer
  → $param->addFilterTrim()        // adds Request_Param_Filter (StripWhitespace + String stack)
  → $param->makeRequired()

$request->validate()
  → For each registered param:
       → Applies filter chain: filter1.filter(filter2.filter(rawValue))
       → Applies validator: validator.validate(filteredValue)
       → If required and null/empty → throws Request_Exception

$value = $request->getFilteredParam('page')
  → Returns the validated + filtered value
```

---

## 4. URL Building with URLBuilder

```
URLBuilder::create()
  → new URLBuilder([])             // empty params
  → calls overridable init()       // hook for subclasses

$builder->dispatcher('index.php')
  → Sets base dispatcher path

$builder->string('action', 'view')
  → Stores typed parameter

$builder->int('id', 42)
  → Stores typed parameter

$builder->get()
  → Resolves dispatcher
  → Sorts parameters alphabetically
  → Builds query string via http_build_query()
  → Returns "index.php?action=view&id=42"
```

**From existing URL:**

```
URLBuilder::createFromURL('https://example.com/page?foo=bar')
  → Parses via URLInfo
  → Imports host, path, and existing params
  → Returns builder pre-populated for modification
```

---

## 5. Refresh URL Generation (Request)

```
$request->createRefreshParams()
  → Returns Request_RefreshParams instance
  → $rp->excludeParamByName('session_id')    // adds Name exclude rule
  → $rp->excludeParamByCallback($fn)         // adds Callback exclude rule  
  → $rp->overrideParam('page', 2)            // forces page=2

$rp->getParams()
  → Iterates $_REQUEST
  → For each param: checks all Exclude rules via isExcluded()
  → Applies overrides
  → Returns filtered param array

$request->buildRefreshURL()
  → Calls getRefreshParams() internally
  → Builds URL from base URL + filtered params
```

---

## 6. Pagination Calculation

```
PaginationHelper::factory(totalItems: 250, itemsPerPage: 10, currentPage: 3)
  → Computes totalPages = ceil(250/10) = 25
  → $helper->setAdjacentPages(2)   // show 2 pages on each side

$helper->getPageNumbers()
  → Returns [1, 2, 3, 4, 5]  (window around current page)

$helper->getOffsetStart()  → 20   (for SQL OFFSET)
$helper->getOffsetEnd()    → 29
$helper->hasNextPage()     → true
$helper->getNextPage()     → 4
```

---

## 7. ZIP Archive Creation & Download

```
$zip = new ZIPHelper('/tmp/archive.zip')
  → $zip->addFile('/path/to/document.pdf')
  → $zip->addFile('/path/to/image.png', 'images/photo.png')  // custom path in archive
  → $zip->addJSON(['key' => 'value'], 'data/config.json')    // serialize to JSON

$zip->save()
  → Opens ZipArchive
  → Adds all queued files
  → Closes archive → writes to /tmp/archive.zip

$zip->download('export.zip')
  → Sends HTTP headers (Content-Type, Content-Disposition)
  → Streams file to browser
```

---

## 8. Lipsum Detection

```
LipsumHelper::containsLipsum($text)
  → Returns LipsumDetector instance
  → $detector->setMinWords(3)     // require at least 3 lipsum words
  → $detector->isDetected()       // true if threshold met
  → $detector->getDetectedWords() // ['lorem', 'ipsum', 'dolor']
```
