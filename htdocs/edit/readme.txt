
Usage
-----

First you need to create your editable content blocks.  For example, if you want to have an editable block on your About Us page, create a completely blank .html file called about.html and drop it in the "blocks" folder.  This block will now appear in the pulse admin ready for editing.  You can add content and save the block.

To embed this block in a page on your website, use the PHP include provided on the edit block page.  It will look something like this: 

<?php include("pulse/blocks/About.html"); ?>

The one condition is that the page you are embedding this into must be a .php page in order to work.  If your site uses .html or .htm files instead you have two options.  The easiest thing would be to rename the pages in .php format, then the supplied PHP include will work.  If you don't want to rename your files you can force your server to parse html as php.  Simply add the following code to your .htaccess file.

AddType application/x-httpd-php .html .htm


Extra Tips
----------

If you want to change the default folder name from "pulse" to something else for better security, change $pulse_dir in "config.php" and also in "gallery.php(Twice)".



Troubleshooting
---------------

If you are having problems writing to blocks or creating backups, set permissions to 755 on the 'blocks' folder and all its contents (including backups). 

If you are having trouble uploading images, set permissions to 755 on the 'blocks/img' folder.



RELEASE NOTES
-------------

1.18
-Updated CKEditor to version 3.1

1.17
-An image gallery function has been added (With Lightbox).
-Image manager now generates thumbnails instead of resizing.
-All PHP Includes are now absolute URLs.

1.16
-Added config.php file.  Allows user to change password and set default directory for pulse.

1.15
-Added delete function to image manager

1.1
-Added Image Manager and create block function. (Thanks to Michael Li for code contribution)

1.01
-Fixed a backup problem


1.0
-Initial Release
