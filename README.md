Magento Image Wrapper
===========
When you have many products on a page, Magento will process all thumbnails when the page is called : it will need several seconds, and the user will have to wait.

This small hack will move this process to the call of the image itself : image render will be parallelized, and the page will output more quickly.

This is also useful when you have a distant storage (like NFS mount or ISCSI), as latency increase time needed to render a page (because Magento does a lot of stat() calls).

INSTALLATION
------------
Install is tricky, because you'll have to replace one file in Magento core, and alter a security feature ...
If you have any suggestion on how to make this hack clean, just tell me :)

Copy .htaccess and img.php to media/catalog/product/cache
Modify .htaccess to use the right handler for PHP, because in media/ there is a .htaccess file that deactivate any php or cgi script.
Erase existing Mage/Catalog/Model/Product/Image.php with the one provided.

HOW IT WORKS
------------
class Mage_Catalog_Model_Product_Image handles image products and gives URI to print on html file. But the path has several parameters encoded (with md5) and with just the URL, one cannot know what the parameter were (it was done on purpose by magento developers).
We modify this class several way : 
- Do not check for cache files needed (improve latency observed with NFS)
- Return an understandable path

With this path, we now have to intercept calls to image with a rewrite on media/catalog/product/cache
If thumb was already generated before, the file exists and your web server returns it with no call to PHP.
If it does not exist, img.php is called and use the same processor as Magento to create the tumbnail, and write it to storage.


LIMITATIONS
-----------
This wrapper has several limitations : 
- it does not handle watermark addition
- any visitor with some skill could see the original image file.
These could be adressed with some code (ie encode parameter / key for example). If you have time to do it, I will be happy to merge ;)
