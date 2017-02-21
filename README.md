[![Build Status](https://travis-ci.org/minhd/php-solr-client.svg?branch=master)](https://travis-ci.org/minhd/php-solr-client)

# PHP Solr Client
This is a PHP library designed to simplify interaction with a SOLR server

## Installation
The prefered method is via composer
```
composer require minhd\php-solr-client
```
Be sure to include the autoloader
```
require_once "vendor/autoload.php"
```

## Usage
### Quick Start
```php
$client = new SolrClient('localhost', '8983');
$client->setCore('gettingstarted');

// Adding document
$client->add(
    new SolrDocument([
        'id' => 1,
        'title_s' => 'Title'
    ]);
);
$client->commit();

// Searching document
$result = $client->query('title_s:title');
echo $result->getNumFound(); // 1
foreach ($result->getDocs() as $doc) {
    echo $doc->title_s; // 'Title'
}

// Getting a single document
$doc = $client->get(1);
echo $doc->title_s; // 'Title'
```

### Searching
Search parameters can be pass to the `search()` function which would return an instance of `SolrSearchResult`

```php
// Search with custom parameters
$result = $client->search([
    'q' => '+title:fish -description:shark',
    'rows' => 15,
    'start' => 0
]);

// Pagination Support
$nextPage = $result->next(15, $client);

// Facet support
$result = $solr->setFacet('subject')->query('*:*');
$subjectFacet = $result->getFacet('subject');
```

### Indexing
```php
// autocommit enabled to commit after every add
$doc = new SolrDocument;
$doc->id = 2;
$doc->title_s = "Second Document";
$doc->description_s = "Some description";
$client->add($doc);
```

### Delete
```php
// delete by id field
$client->remove(2);

// delete by query
$client->removeByQuery('id:2');
```

### Cursor
To make use of SOLR CursorMark functionality, useful for exporting records
```php
// search for all documents that has a subject field match aquatic, by 10 at a time
$search = [
    'q' => 'subject:aquatic'
];

while($payload->getNextCursorMark() != $payload->getCursorMark()) {
   $payload = $client->cursor($payload->getNextCursorMark(), 10, $search);
   print_r($payload);
}
```



### Commands
To assist with common operation on an existing SOLR server
```
// List of help commands
bin/console help

// List of help commands for solr:run command
bin/console solr:run -h

// Run optimize on default SOLR instance
bin/console solr:run -d optimize

// Exports all records from collection1 to /tmp/export/ directory
bin/console solr:export -s localhost -p 8983 -c collection1 -t /tmp/export/
// Import the records to another SOLR instance
bin/console solr:import -s example.org -p 8983 -c collection2 -t /tmp/export/
```
