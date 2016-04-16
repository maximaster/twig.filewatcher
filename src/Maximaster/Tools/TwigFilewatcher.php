<?php
namespace Maximaster\Tools;

use Twig_Environment;
use Twig_Loader_Filesystem;
use Twig_ExtensionInterface;
use Exception;

class TwigFilewatcher
{
    /**
     * @var string Папка с twig-файлами
     */
    protected $inputDir;

    /**
     * @var string Директория результатов для html-файлов
     */
    protected $outputDir;

    /**
     * @var Twig_Environment Объект twig-среды
     */
    protected $twig;

    /**
     * @var array Данные, передаваемые в шаблон
     */
    protected $context;

    /**
     * @var array Результаты выполнения
     */
    protected $result;

    /**
     * TwigFilewatcher constructor.
     * @param array $arTwigEnvOptions Опции для настойки Twig_Environment
     */
    function __construct($arTwigEnvOptions = [])
    {
        $this->outputDir = getcwd();
        $this->inputDir = $this->outputDir.'/src';

        $this->twig = new Twig_Environment(new Twig_Loader_Filesystem($this->inputDir), $arTwigEnvOptions + [
            'cache' => false,
        ]);
    }

    /**
     * Покдключает {$file} с массивом данных, передаваемых в шаблон
     * Файл должен делать return array
     *
     * @param string $file Файл с данными
     * @return $this
     * @throws Exception Файл может быть не найден, не являться php-файлом или не возвращать массив как ожидается
     */
    function setContextFromFile($file)
    {
        if ( ! file_exists($file) ) {
            throw new Exception(__METHOD__." failed: file `{$file}` not found");
        }

        if ( pathinfo($file, PATHINFO_EXTENSION) !== 'php' ) {
            throw new Exception(__METHOD__." failed: file `{$file}` must be `.php`");
        }

        /** @noinspection PhpIncludeInspection */
        $this->context = include $file;
        if ( ! is_array($this->context) ) {
            throw new Exception(__METHOD__." failed: file `{$file}` does not return an array as expected");
        }

        return $this;
    }

    /**
     * Устанавливает данные, передаваемые в шаблон из массива
     *
     * @param array $context
     * @return $this
     */
    function setContext($context)
    {
        $this->context = $context;
        return $this;
    }

    /**
     * Возвращает внутренний объект Twig_Environment
     * @return Twig_Environment
     */
    function getEnvironment()
    {
        return $this->twig;
    }

    /**
     * Устанавливает директорию поиска twig-файлов
     *
     * @param string $dir
     * @return $this
     * @throws Exception Директория {$dir} может быть не найдена
     */
    function setInputDir($dir)
    {
        if ( ! is_dir($dir) ) {
            throw new Exception(__METHOD__." failed: `{$dir}` not found");
        }

        $this->inputDir = $dir;
        return $this;
    }

    /**
     * Устанавливает директорию для сохранения html-файлов
     *
     * @param string $dir
     * @return $this
     * @throws Exception Директория {$dir} может быть не найдена
     */
    function setOutputDir($dir)
    {
        if ( ! is_dir($dir) ) {
            throw new Exception(__METHOD__." failed: `{$dir}` not found");
        }

        $this->outputDir = $dir;
        return $this;
    }

    /**
     * Добавляет расширение для внутреннего twig-объекта
     *
     * @param Twig_ExtensionInterface $twigExt
     * @return $this
     */
    function addExtension(Twig_ExtensionInterface $twigExt)
    {
        $this->twig->addExtension($twigExt);
        return $this;
    }

    /**
     * Запускает процедуру компиляции html-файлов из twig-шаблонов
     */
    function compile()
    {
        $this->result = [];

        // Т.к. один файл может зависеть от другого, нужно перегенерировать все, а не только изменённый файл
        foreach(scandir($this->inputDir) as $templatePath)
        {
            $arPath = pathinfo($templatePath);
            if ( $arPath['extension'] !== 'twig' )
                continue;

            $template = $this->twig->loadTemplate($templatePath);

            $bytesSaved = file_put_contents(
                ($outputFile = $this->outputDir.'/'.$arPath['filename'].'.html'),
                $template->render($this->context)
            );

            $this->result[] = [
                'input' => $templatePath,
                'output' => $outputFile,
                'success' => $bytesSaved !== false,
            ];
        }
    }

    /**
     * Возвращает информацию по результатам выполнения
     *
     * @return array
     */
    function getResult()
    {
        return $this->result;
    }
}