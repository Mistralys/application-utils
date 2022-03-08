<?php
/**
 * Example of URL syntax highlighting.
 *
 * @package Application Utils
 * @subpackage Examples
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */

declare(strict_types=1);

/**
* Examples environment config
*/
require_once '../prepend.php';

use function AppUtils\parseURL;
use AppUtils\URLInfo;
use function AppLocalize\pt;

$urls = array(
    'http://www.foo.com',
    'https://www.foo.com:3618/path/to/page',
    'https://username:password@www.foo.com/path/to/page?foo=bar&bar=foo',
    'https://www.foo.com/path/to/page#fragment',
);

?><!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
		<title><?php pt('URL syntax highlighting') ?></title>
		<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
		<link rel="stylesheet" type="text/css" href="../css/ui.css">
		<style><?php echo URLInfo::getHighlightCSS() ?></style>
	</head>
	<body>
		<p>
			<a href="../index.php">&laquo; <?php pt('Back') ?></a>
		</p>
		<br>
		<p>
			<?php pt('This example showcases the built-in syntax highlighting of URLs.') ?>
		</p>
		<br>
        <?php
        
            foreach($urls as $url)
            {
                $info = parseURL($url);
                
                echo '<p>'.$info->getHighlighted().'</p>';
            }
            
        ?>
	</body>
</html>
