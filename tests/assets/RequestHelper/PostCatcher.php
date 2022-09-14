<?php
/**
 * Simple script that is used by the RequestHelper testsuite
 * to test sending files via POST.
 * 
 * @package Application Utils
 * @subpackage Tests
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */

   /**
    * @var array<string,array<mixed>> $data
    */

use AppUtils\ConvertHelper\JSONConverter;

$data = array(
        'request' => $_REQUEST,
        'files' => $_FILES
    );
    
    foreach($data['files'] as $idx => $file)
    {
        $data['files'][$idx]['content'] = file_get_contents($file['tmp_name']);
    }
    
    header('Content-Type:application/json');
    
    echo JSONConverter::var2json($data, JSON_PRETTY_PRINT);
