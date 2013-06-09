<?php
/***
 * Image wrapper
 * @author Olivier Doucet <olivier at oxeva dot fr>
 */
 
// Autoload / Insert into Magento core
$bp = dirname(__FILE__).'/../../../../';
$paths[] = $bp . DIRECTORY_SEPARATOR . 'app';
$paths[] = $bp . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'code' . DIRECTORY_SEPARATOR . 'local';
$paths[] = $bp . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'code' . DIRECTORY_SEPARATOR . 'community';
$paths[] = $bp . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'code' . DIRECTORY_SEPARATOR . 'core';
$paths[] = $bp . DIRECTORY_SEPARATOR . 'lib';
$appPath = implode(PATH_SEPARATOR, $paths);
set_include_path(implode(PATH_SEPARATOR, $paths) . PATH_SEPARATOR . get_include_path());
include_once 'Mage/Core/functions.php';
include_once 'Varien/Autoload.php';
Varien_Autoload::register();
Mage::init();
//---------------------------------------------

// We will rewrite part of Mage_Catalog_Model_Product_Image previously modified.
require 'Mage/Catalog/Model/Product/Image.php';
class OD_Product_Image extends Mage_Catalog_Model_Product_Image
{
    /**
     * Remove QUERY_STRING
     */
    public function getBaseFile()
    {
        if (strpos($this->_baseFile, '?') > 0)
            $this->_baseFile =  substr($this->_baseFile, 0, strpos($this->_baseFile, '?'));
        return $this->_baseFile;

    }
    
    /**
     * Remove QUERY_STRING
     */
    public function getNewFile()
    {
        if (strpos($this->_newFile, '?') > 0)
            $this->_newFile = substr($this->_newFile, 0, strpos($this->_newFile, '?'));
        return $this->_newFile;
    }
}
//------------------------------------------------------------------------

$z = preg_match('@/product/cache/([0-9]{1,})/([^/]{1,})/(([0-9x]{3,})/)?'.
    '([a-z0-9]{1})/([a-z0-9]{1})/([^/]{1,})\?@', $_SERVER['REQUEST_URI'], $preg);

if (!$z) {
    noimg();
}
// Extract parameters from URL
$params = array (
    'cacheId' => $preg[1],
    'type'    => $preg[2],
    'size'    => $preg[4],
    'path'    => $preg[5].'/'.$preg[6].'/'.$preg[7],
);

parse_str(str_replace(';','&', $_SERVER['QUERY_STRING']), $args);
$params = array_merge($args, $params);

unset($args);

$img = new OD_Product_Image();
if ($params['size'] != '')
        $img->setSize($params['size']);

$img->setDestinationSubdir($params['type'])
    ->setQuality($params['quality'])
    ->setKeepAspectRatio($params['kar'])
    ->setKeepFrame($params['kf'])
    ->setBaseFile($params['path']);
    // @todo
    // ->setBackgroundColor($params['bg'])

if ($params['size'] != '') {
    $img->resize();
    $img->saveFile();
    $filePath = $img->getNewFile();
} else {
    // no resize, return original image
    $filePath = $img->getBaseFile();
}

switch( strtolower(substr($filePath, strrpos($filePath, ".")+1)) ) {
    case "gif": $ctype="image/gif"; break;
    case "png": $ctype="image/png"; break;
    case "jpeg":
    case "jpg": $ctype="image/jpg"; break;
    default:
}

header('Content-Type: '.$ctype);
header('X-Source: ImageWrapper 1.0');
echo file_get_contents($filePath);

function noimg()
{
    // @todo 404 image
    header('Location: /media/shim.gif');
    exit;
}

