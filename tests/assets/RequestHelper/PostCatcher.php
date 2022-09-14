<?php
/**
 * Simple script that is used by the RequestHelper testsuite
 * to test sending files via POST.
 * 
 * @package Application Utils
 * @subpackage Tests
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */

require_once __DIR__.'/../../../vendor/autoload.php';

use AppUtils\ConvertHelper\JSONConverter;
use AppUtils\FileHelper;

/**
* @var array<string,array<mixed>> $data
*/
$data = array(
    'request' => $_REQUEST,
    'files' => $_FILES
);

// Security: Only allow files with the exact same content
// as the allowed files to be uploaded.
$allowedContents = array(
    FileHelper::readContents(__DIR__.'/upload.html'),
    FileHelper::readContents(__DIR__.'/upload.txt')
);

foreach($data['files'] as $idx => $file)
{
    $content = file_get_contents($file['tmp_name']);

    if(!in_array($content, $allowedContents, true)) {
        die('Invalid file uploaded.');
    }

    $data['files'][$idx]['content'] = file_get_contents($file['tmp_name']);
}

header('Content-Type:application/json');

echo JSONConverter::var2json($data, JSON_PRETTY_PRINT);
