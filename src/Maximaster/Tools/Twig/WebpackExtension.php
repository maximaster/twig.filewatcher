<?php
namespace Maximaster\Tools\Twig;

use Exception;
use Twig_Extension;
use Twig_SimpleFunction;

class WebpackExtension extends Twig_Extension
{
    /**
     * @var string Директория в которую webpack генерирует сборку
     */
    protected $buildDir;

    /**
     * @var string Префикс адреса при генерации ссылок на файлы точки входа
     */
    protected $webDir;

    /**
     * @var array Массив для точек сборки
     */
    protected $entries = [];

    protected $manifestFilename = 'webpack-assets.json';

    protected $debug = false;

    /**
     * @var self
     */
    static protected $instance;

    static function getInstance()
    {
        return self::$instance;
    }

    /**
     * WebpackExtension constructor.
     * @param string $buildDir Папка webpack
     * @param string $webDir Префикс адреса
     * @param string|null $manifestFilename Файл где перечисляются сгенерированные ресурсы по именам точек входа
     */
    function __construct($buildDir, $webDir = '', $manifestFilename = null)
    {
        $this->buildDir = $buildDir;
        $this->webDir = $webDir;
        if ($manifestFilename) {
            $this->manifestFilename = $manifestFilename;
        }
        self::$instance = $this;
    }

    function getName()
    {
        return 'maximaster_tools_twig_webpack_extension';
    }

    function getFunctions()
    {
        $arFunctions = [
            'showEntry' => [
                'is_safe' => ['html'],
            ],
            'getEntry' => [],
        ];

        $functions = [];
        foreach($arFunctions as $func => $funcOptions) {
            $functions[] = new Twig_SimpleFunction($func, [__CLASS__, $func], $funcOptions);
        }

        return $functions;
    }

    /**
     * @return array
     * @throws Exception
     */
    static function getEntries()
    {
        $ext = self::getInstance();

        if ( $ext->entries ) {
            return $ext->entries;
        }

        $jsonFile = "{$ext->buildDir}/{$ext->manifestFilename}";
        if ( ! is_file($jsonFile) ) {
            throw new Exception(__METHOD__." failed: `{$jsonFile}` not found");
        }

        $ext->entries = json_decode(file_get_contents($jsonFile), 1);
        return $ext->entries;
    }

    /**
     * Возвращает список файлов точки входа
     *
     * @param string $name Имя точки входа
     * @param string $type Фильтр по типу файла
     * @return array|bool Список файлов точки входа
     * @throws Exception
     */
    static function getEntry($name, $type = null)
    {
        $ext = self::getInstance();

        $entries = $ext->getEntries();
        if ( ! ($entry = $entries[$name]) ) {
            return false;
        }

        $files = [];
        array_walk_recursive($entry, function($file) use(&$files, $type) {
            if ( $type !== null && pathinfo($file, PATHINFO_EXTENSION) != $type )
                return;

            $files[] = $file;
        });

        return $files;
    }

    /**
     * Возвращает html-код для подключения файлов точки входа {$name} типа {$type}
     *
     * @param string $name Имя точки входа
     * @param string $type Фильтр по типу файла
     * @return bool|string <p>
     * false в случае отсутствия подходящих файлов точки входа, html-код подключения данных файлов в противном случае
     * </p>
     */
    static function showEntry($name, $type = null)
    {
        $ext = self::getInstance();

        try {
            $files = $ext->getEntry($name, $type);
            if (!$files) {
                if ($ext->debug) {
                    ?><!-- entries not found --><?
                }
                return false;
            }
        } catch (Exception $e) {
            if ($ext->debug) {
                ?><!-- <?=$e->getMessage()?> --><?
            }
            return false;
        }

        ob_start();
        foreach($files as $file) {
            switch(strtolower(pathinfo($file, PATHINFO_EXTENSION)))
            {
                case 'js':
                    ?><script src="<?=$ext->webDir.$file?>" type="text/javascript"></script><?php
                    break;

                case 'css':
                    ?><link href="<?=$ext->webDir.$file?>" rel="stylesheet" type="text/css"><?php
                    break;
            }
        }
        $html = ob_get_clean();

        return $html;
    }

    /**
     * @return $this
     */
    public function enableDebug()
    {
        $this->debug = true;
        return $this;
    }
}