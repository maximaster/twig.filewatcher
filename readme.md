maximaster/twig.filewatcher
=

Назначение
-
Генерация html-шаблонов из twig-шаблонов

Использование
-
Подключается через composer как maximaster/twig.filewatcher

После подключения в проект необходимо создать собственный скрипт для file watcher'а, который должен сконфигурировать решение.

Пример:

```php
<?php
include_once __DIR__.'/vendor/autoload.php';

use Maximaster\Tools\Twig\Filewatcher;

$watcher = new Filewatcher;
$watcher
    ->setGlobalsFromFile(__DIR__.'/../src/.context.php')
    ->addExtension(new Maximaster\Tools\Twig\FilewatcherExtension)
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
В шаблоны передаются глобальные данные, которые можно задать в скрипте компиляции.
В шаблонах можно использовать собственные функции и фильтры, если добавлять расширения через метод `addExtension`.


Значения по умолчанию
-
Директория результатов - рабочая директория (`<?php getcwd()`)

Директория исходников - Директория результатов + /src

Глобальные данные - задаются врунчюу (`setGlobals` или `setGlobalsFromFile`) + `['compiler' => ['filename' => имя файла шаблона]`

Расширение FilewatcherExtension
-
Позволяет воспользоваться функцией `getMessage(code)` которая возвращает данные из глобального массива по адресу: `messages[compiler.filename].code` или `messages.default.code`