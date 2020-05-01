# Log library

## First thing first
If root of the app folder does not contain `storage/logs` directory then create one with write permission
```console
$ mkdir storage && mkdir storage/logs
```

## Import the Log class
```php
use Fantom\Log\Log;
```

## Use Log methods
```php
Log::emergency($message);

Log::alert($message);

Log::critical($message);

Log::error($message);

Log::warning($message);

Log::notice($message);

Log::info($message);

Log::debug($message);
```

* Default storage location of log file is `storage/logs/app.log`
