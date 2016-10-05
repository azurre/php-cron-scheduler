# azurre/scheduler [![Latest Version](https://img.shields.io/github/release/azurre/scheduler.svg?style=flat-square)](https://github.com/azurre/scheduler/releases)
Simple cron jobs manager. Keep your project cron jobs in your project!

# Installation

Install composer in your project:
```
curl -s https://getcomposer.org/installer | php
```

Require the package with composer:
```
composer require azurre/scheduler
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

$php  = '/usr/bin/php';
$path = dirname(__FILE__) . '/';

$Scheduler = new Scheduler();
$Scheduler
    ->setJobPath($path)
    ->setLogsPath($path);

$Scheduler->addJob('* * * * *', function($logsPath){
    // just do something
    file_put_contents($logsPath . 'log.log', 'OK', FILE_APPEND);
});

$Scheduler->addJob('*/2 * * * *', function ($logsPath, $jobPath) use ($php) {
    // run standalone php script
    $cmd = "{$php} {$jobPath}calculate.php >> {$logsPath}calculate.log 2>&1";
    $result = `$cmd`;
});

// Do something ...

echo date('d-m-Y H:i:s') . ' Run scheduler...' . PHP_EOL;
$Scheduler->run();
```
