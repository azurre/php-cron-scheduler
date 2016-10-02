# azurre/scheduler
Keep your project cron jobs in your project

# Installation

[Composer](http://getcomposer.org/):

```json
{
  "repositories": [
    {
      "type": "vcs",
      "url": "https://github.com/azurre/scheduler"
    }
  ],
  "require": {
    "azurre/php-scheduler": "master"
  }
}
```

# Usage

```php
$loader = require_once __DIR__ . '/vendor/autoload.php';

use Azurre\Component\Cron\Scheduler;

$php      = '/usr/bin/php';
$path = dirname(__FILE__) . '/';

$Scheduler = new Scheduler();
$Scheduler
    ->setJobPath($path)
    ->setLogsPath($path);

$Scheduler->addJob('* * * * *', function($logsPath){
    file_put_contents($logsPath . 'log.log', 'OK');
});

$Scheduler->addJob('*/2 * * * *', function ($logsPath, $jobPath) use ($php) {
    $cmd = "{$php} {$jobPath}calculate.php >> {$logsPath}calculate.log 2>&1";
    echo `$cmd`;
});

// Do something ...

echo date('d-m-Y H:i:s') . ' Run scheduler...' . PHP_EOL;
$Scheduler->run();
```
