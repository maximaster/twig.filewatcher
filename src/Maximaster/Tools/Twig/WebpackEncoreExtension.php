<?php
namespace Maximaster\Tools\Twig;

use Exception;
use Twig_Extension;
use Twig_SimpleFunction;

class WebpackEncoreExtension extends WebpackExtension
{
    protected $manifestFilename = 'manifest.json';

    public static function getEntry($name, $type = null)
    {
        $ext = self::getInstance();

        $files = [];
        foreach ($ext->getEntries() as $entryName => $entryFile) {
            $entry = pathinfo($entryName);

            if (($type && $type !== $entry['extension']) ||
                $entry['filename'] !== $name
            ) {
                continue;
            }

            $files[] = $entryFile;
        }

        return $files;
    }
}
