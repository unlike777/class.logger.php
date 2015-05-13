class.logger.php Copyright 2014

PHP Класс для ведения файловых логов

##Example

```php
$log = new Logger('logs/test.log');
$log->add('Пользователь авторизовался на сайте');
$log->add('Пользователь вышел из системы');
$log->save();
```