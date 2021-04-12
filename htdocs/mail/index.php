<?php

  /*****************************************************
  ** Title........: Form Mail Script
  ** Filename.....: index.php
  ** Author.......: GentleSource
  ** Homepage.....: http://www.gentlesource.com/
  ** Notes........: This file contains the configuration
  ** Last changed.:
  ** Last change..:
  *****************************************************/

  /*****************************************************
  **
  ** THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY
  ** OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT
  ** LIMITED   TO  THE WARRANTIES  OF  MERCHANTABILITY,
  ** FITNESS    FOR    A    PARTICULAR    PURPOSE   AND
  ** NONINFRINGEMENT.  IN NO EVENT SHALL THE AUTHORS OR
  ** COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES
  ** OR  OTHER  LIABILITY,  WHETHER  IN  AN  ACTION  OF
  ** CONTRACT,  TORT OR OTHERWISE, ARISING FROM, OUT OF
  ** OR  IN  CONNECTION WITH THE SOFTWARE OR THE USE OR
  ** OTHER DEALINGS IN THE SOFTWARE.
  **
  *****************************************************/




  /*****************************************************
  ** Script configuration - for a documentation of the
  ** following variables please take a look at the
  ** documentation file in the 'docu' directory.
  *****************************************************/
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

          $attachment            = 'yes';    // (yes, no) -- make folder "temp" writable with: chmod 777 temp
          $attachment_files      = 'jpg, gif, png, zip, txt, pdf, doc, ppt, tif, bmp, mdb, xls, txt, vcf, csv';
          $attachment_size       =  9000000;
          
          $captcha               = 'yes';   // (yes, no) -- make folder "temp" writable with: chmod 777 temp

          $path['logfile']       = $script_root . 'logfile/logfile.txt';
          $path['templates']     = $script_root . 'templates/';

          $file['default_html']  = 'form.tpl.html';
          $file['default_mail']  = 'mail.tpl.txt';




  /*****************************************************
  ** Add further words, text, variables and stuff
  ** that you want to appear in the templates here.
  ** The values are displayed in the HTML output and
  ** the e-mail.
  *****************************************************/
          $add_text = array(
                              'txt_additional' => 'Additional', //  {txt_additional}
                              'txt_more'       => 'More'        //  {txt_more}

                            );




  /*****************************************************
  ** Do not edit below this line - Ende der Einstellungen
  *****************************************************/














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
