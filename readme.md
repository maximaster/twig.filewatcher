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
use Maximaster\Tools\Twig\FilewatcherExtension;
use Maximaster\Tools\Twig\WebpackExtension;

$watcher = new Filewatcher;
$watcher
    ->setInputDir(__DIR__.'/twig')
    ->setOutputDir(realpath(__DIR__.'/../'))
    ->setGlobalsFromFile(__DIR__.'/twig/.context.php')
    ->addExtension(new FilewatcherExtension)
    ->addExtension(new WebpackExtension(__DIR__.'/assets/build'))
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

Расширение WebpackExtension
-
Позволяет воспользоваться функциями `showEntry` и `getEntry`, которые позволяют подключить файлы точки входа сгенерированной webpack'ом и получить их в виде массива соответственно.