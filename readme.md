maximaster/twig.filewatcher
=

Назначение
-
Генерация html-шаблонов из twig-шаблонов

Использование
-
Подключается через composer как maximaster/twig.filewatcher

Не зарегистрирован на packagist.org, нужно указать в конфигурации данный git-репозиторий.

После подключения в проект необходимо создать собственный скрипт для file watcher'а, который должен сконфигурировать решение.

Пример:

```php
<?php
include_once __DIR__.'/vendor/autoload.php';

use Maximaster\Tools\TwigFilewatcher;

$watcher = new TwigFilewatcher;
$watcher
    ->setContextFromFile(__DIR__.'/../twig/.context.php')
    ->addExtension(new \MyVendor\MyProjectTwigExtension)
    ->compile();
```

Скрипт можно запускать вручную, но удобно настроить File watcher в IDE. На примере JetBrains IDE (PhpStorm, Webstorm):

File &rarr; Settings &rarr; Tools &rarr; File Watchers &rarr; + &rarr; &lt;custom&gt;

**File type:** Twig

**Program:** `<путь к php.exe>`

**Arguments:** `-f "$ProjectFileDir$<относительный путь от проекта к php-скрипту компиляции>"`

**Working directory:** `$ProjectFileDir$`




Принцип работы
-
Находит twig-файлы, находящиеся непосредственно в *директории исходников* и для каждого генерирует html-шаблон, сохраняя его в *директорию результатов*.
В шаблоны передаются данные, которые можно задать методами `setContextFromFile`, `setContext`.
В шаблонах можно использовать собственные функции и фильтры, если добавлять расширения через метод `addExtension`.


Значения по умолчанию
-
Директория результатов - рабочая директория (`<?php getcwd()`)

Директория исходников - Директория результатов + /src