<?php
namespace Maximaster\Tools\Twig;

use Twig_Extension;
use Twig_Environment;
use Twig_SimpleFunction;

class FilewatcherExtension extends Twig_Extension
{
    function getName()
    {
        return 'maximaster_tools_twig_filewatcher_extension';
    }

    function getFunctions()
    {
        return [
            new Twig_SimpleFunction('getMessage', [__CLASS__, 'getMessage'], ['needs_environment' => true]),
        ];
    }

    /**
     * Возвращает языковую константу из глобальных данных под ключём messages
     * Поиск производится в подключе по названию текущего шаблона, а если фразы
     * там нет, то в default-секции
     *
     * @param Twig_Environment $env
     * @param string $messageCode
     * @return bool|string Языковая константа или false в случае если она не была найдена
     */
    function getMessage(Twig_Environment $env, $messageCode)
    {
        $globals = $env->getGlobals();
        if ( ! isset($globals['messages']) || ! is_array($globals['messages']) )
            return false;

        $arSources = ['default'];
        if ( isset($globals['compiler']['filename']) ) {
            array_unshift($arSources, $globals['compiler']['filename']);
        }

        foreach($arSources as $sourceKey) {
            if ( isset($globals['messages'][$sourceKey][$messageCode]) ) {
                return $globals['messages'][$sourceKey][$messageCode];
            }
        }

        return false;
    }
}