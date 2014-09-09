class.logger.php Copyright 2014

PHP Класс для ведения файловых логов

##Example

```php
$log = new Logger(ROOT_DIR.'/test.log');
$log->add('Пользователь авторизовался на сайте');
$log->add('Пользователь вышел из системы');
$log->save();
```