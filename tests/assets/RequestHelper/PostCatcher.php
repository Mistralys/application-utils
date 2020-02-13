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
    * @var array $data
    */
    $data = array(
        'request' => $_REQUEST,
        'files' => $_FILES
    );
    
    foreach($data['files'] as $idx => $file)
    {
        $data['files'][$idx]['content'] = file_get_contents($file['tmp_name']);
    }
    
    header('Content-Type:application/json');
    
    echo json_encode($data, JSON_PRETTY_PRINT);
