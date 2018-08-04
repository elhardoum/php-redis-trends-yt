## Creating a Trends App with PHP and Redis

#### Install dependencies

```bash
composer require predis/predis
```

#### Usage

```php
// incrementing topics (@return void)
TrendsApp::incrementTopic( string $topic, int $increment_by_score=1 );
// e.g
TrendsApp::incrementTopic( 'test', 2 );

// listing topics (@return array)
TrendsApp::getTopics( int $limit );
// e.g top 10
TrendsApp::getTopics( 10 );
```

#### Config

```php
const SET_NAME = '_topics'; # set name in the redis store, it'll be suffixed with the date (explained). Alter this for use on a variety of subjects
const TRENDS_INTERVAL = 60 *60; # default time range for topics is 1 hour
const DATE_SUFFIX_FORMAT = 'Hi'; # `Hi` allows for setting 1 set every minute, H every hour, depending on your interval.
const CONNECTION_ARGS = 'tcp://cache:6379'; # Predis/Client connection args, string|array or null
```
