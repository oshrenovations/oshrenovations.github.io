<?php

          $script_root           = './';

          $referring_server      = 'www.oshrenovations.com';       // Example: $referring_server = 'example.com, www.example.com';

          $language              = 'en';     // see folder /languages/

          $ip_banlist            = '';

          $ip_address_count      = '0';
          $ip_address_duration   = '48';

          $show_limit_errors     = 'yes';    // (yes, no)

          $log_messages          = 'yes';     // (yes, no) -- make folder "temp" writable with: chmod 777 temp

          $text_wrap             = '72';

          $show_error_messages   = 'yes';

          $attachment            = 'no';    // (yes, no) -- make folder "temp" writable with: chmod 777 temp
          $attachment_files      = 'jpg, gif, png, zip, txt, pdf, doc, ppt, tif, bmp, mdb, xls, txt, vcf, csv';
          $attachment_size       =  9000000;
          
          $captcha               = 'yes';   // (yes, no) -- make folder "temp" writable with: chmod 777 temp

          $path['logfile']       = $script_root . 'logfile/logfile.txt';
          $path['templates']     = $script_root . 'templates/';

          $file['default_html']  = 'email_us.html';
          $file['default_mail']  = 'email_us.txt';



          $add_text = array(
                              'txt_additional' => 'Additional', //  {txt_additional}
                              'txt_more'       => 'More'        //  {txt_more}

                            );



  /*****************************************************
  ** Send safety signal to included files
  *****************************************************/
          define('IN_SCRIPT', 'true');




  /*****************************************************
  ** Load formmail script code
  *****************************************************/
          include($script_root . 'inc/formmail.inc.php');
          
          echo $f6l_output;




?>
