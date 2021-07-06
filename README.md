# PHP Cron Scheduler [![Latest Version](https://img.shields.io/github/release/azurre/php-cron-scheduler.svg?style=flat-square)](https://github.com/azurre/php-cron-scheduler/releases)

Simple cron jobs manager. Keep your project cron jobs in your project!

# Installation

Require the package with composer:

```
composer require azurre/php-cron-scheduler
```

# Usage

Add scheduler starter to cron:

```bash
$ crontab -e
```

```
* * * * * /usr/bin/php /path/to/project/scheduler.php >> /path/to/project/scheduler.log 2>&1
```

Sample of scheduler.php

```php
$loader = require_once __DIR__ . '/vendor/autoload.php';

use Azurre\Component\Cron\Scheduler;
use Azurre\Component\Cron\Expression;

$e = new Expression();

echo $e->monthly(28); // 0 0 28 * *
echo $e->weekly($e::FRIDAY)->at('05:30'); // 30 5 * * 5
echo $e->daily('06:10'); // 10 6 * * *

echo Expression::create()  // */5 0 16 1 5
    ->setMinute('*/5')
    ->setHour('*')
    ->setDayOfMonth(16)
    ->setDayOfWeek('fri')
    ->setMonth('Jan');

// ------------

$testFunc = function () {
    echo 'TEST OK';
};
$scheduler = new Scheduler();
$scheduler
    ->addJob('* * * * *', function() {
        // just do something
    })->addJob('0 0 * * * *', $testFunc);
$scheduler->run();

// -----------

$logPath = '/path/to/log.log';
$scheduler = new Scheduler('2021-07-05 06:10:00');
$scheduler->addJob($e, function () use($logPath) {
    // run standalone php script
    $cmd = "/usr/bin/php /path/to/script.php >> {$logPath} 2>&1";
    system($cmd);
});
$scheduler->run();
```
