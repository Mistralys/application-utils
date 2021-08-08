<?php
/**
 * Translation UI for the localizable strings in the package.
 *
 * @package Application Utils
 * @subpackage Localization
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */

    declare(strict_types=1);

    use AppLocalize\Localization;
    use function AppLocalize\t;

    $root = __DIR__;
    $autoload = $root.'/vendor/autoload.php';
    
    // we need the autoloader to be present
    if($autoload === false) 
    {
        die('<b>ERROR:</b> Autoloader not present. Run composer update first.');
    }
    
    /**
     * The composer autoloader
     */
    require_once $autoload;
    
    // add the locales we wish to manage (en_UK is always present)
    Localization::addAppLocale('de_DE');
    Localization::addAppLocale('fr_FR');
    
    // has to be called last after all sources and locales have been configured
    Localization::configure($root.'/storage.json', '');

    $installFolder = realpath(__DIR__.'/../');

    // Register the classes as a localization source,
    // so they can be found, and use the bundled localization
    // files.
    Localization::addSourceFolder(
        'application-utils-translation-ui',
        'Application Utils translation UI',
        'Composer Packages',
        $installFolder.'/localization',
        $root
    )->excludeFolder('vendor');

    // create the editor UI and start it
    $editor = Localization::createEditor();

    $editor->setAppName(t('AppUtils translation UI'));

    $editor->setBackURL(
        'https://github.com/Mistralys/application-utils',
        t('Project Github page')
    );

    $editor->display();
