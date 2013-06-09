Magento Image Wrapper

When you have many products on a page, Magento will process all thumbnails when the page is called : it will need several seconds, and the user will have to wait.

This small hack will move this process to the call of the image itself : image render will be parallelized, and the page will output more quickly.

This is also useful when you have a distant storage (like NFS mount or ISCSI), as latency increased time needed to render a page (because Magento does a lot of stat() calls).


LIMITATIONS
===========
This wrapper has several limitations : 
- it does not handle watermark addition
- any visitor with some skill could see the original image file.

