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
     * @var array Массив для точек сборки
     */
    protected $entries = [];

    /**
     * @var self
     */
    static protected $instance;

    static function getInstance()
    {
        return self::$instance;
    }

    function __construct($buildDir)
    {
        $this->buildDir = $buildDir;
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

    static function getEntries()
    {
        $ext = self::getInstance();

        if ( $ext->entries ) {
            return $ext->entries;
        }

        $jsonFile = $ext->buildDir.'/webpack-assets.json';
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

        $files = $ext->getEntry($name, $type);
        if ( ! $files )
            return false;

        ob_start();
        foreach($files as $file) {
            switch(strtolower(pathinfo($file, PATHINFO_EXTENSION)))
            {
                case 'js':
                    ?><script src="<?=$file?>" type="text/javascript"></script><?php
                    break;

                case 'css':
                    ?><link href="<?=$file?>" rel="stylesheet" type="text/css"><?php
                    break;
            }
        }
        $html = ob_get_clean();

        return $html;
    }
}