<?php

  /*****************************************************
  ** Title........: Function Collection
  ** Filename.....: functions.inc.php
  ** Author.......: Ralf Stadtaus
  ** Homepage.....: http://www.stadtaus.com/
  ** Contact......: mailto:info@stadtaus.com
  ** Version......: 0.3
  ** Notes........:
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
  ** Print debug messages
  *****************************************************/
          function debug_mode($msg, $desc = '') {
              global $debug_mode;

              if ($debug_mode == 'on' and !empty($msg)) {
                  if (!is_array($msg)) {
                      $msg = (array) $msg;
                  }

                  for($i = 0; $i < count($msg); $i++)
                  {
                      echo '<pre><strong>' . $desc . '</strong>' . "\n\n" . htmlspecialchars($msg[$i]) . '</pre>.............................................................................<br />';
                  }
              }
          }



          
  /*****************************************************
  ** Display server info
  *****************************************************/
          function get_phpinfo($msg = '', $param = '')
          {
              if (isset ($param['ap']) and $param['ap'] == 'phpinfo') {
                  $additional_content = '';
                  if (!empty($msg)) {
                      if (!is_array($msg)) {
                          $msg = (array) $msg;
                      }

                      while(list($key, $val) = each($msg))
                      {
                          $dots = '';

                          for($i = 1; $i <= 35 - strlen($key); $i++)
                          {
                              $dots .= '.';
                          }
                          $additional_content .= $key . $dots . $val . "\n";
                      }
                  }

                  ob_start();
                  phpinfo();
                  $php_information = ob_get_contents();
                  ob_end_clean();
                  echo preg_replace("/<body(.*?)>/i", '<body' . "$1" . '><pre style="color:#CFCFCF;">' . $additional_content . '</pre><br /><br />', $php_information);

                  exit;
              }
          }




  /*****************************************************
  ** Output script runtime
  *****************************************************/
          function script_runtime($runtime_start)
          {
              $runtime_end = explode (' ', microtime ());
              $runtime_difference = $runtime_end[1]     - $runtime_start[1];
              $runtime_summe      = $runtime_difference + $runtime_end[0];
              $runtime            = $runtime_summe      - $runtime_start[0];

              return $runtime;
          }




  /*****************************************************
  ** Print Array
  *****************************************************/
          function print_a($ar)
          {
              echo '<pre>';

              print_r($ar);

              echo '</pre>';
          }




  /*****************************************************
  ** Create random character string
  *****************************************************/                  
          function create_random($length, $pool = '')
          {
              $random = '';
              
              if (empty($pool)) {
                  $pool    = 'abcdefghkmnpqrstuvwxyz';
                  $pool   .= '23456789';
              }
              
              srand ((double)microtime()*1000000);
                            
              for($i = 0; $i < $length; $i++) 
              {
                  $random .= substr($pool,(rand()%(strlen ($pool))), 1);
              }
              
              return $random;
          } 




/**
 * Get md5 hash of a file
 *
 */
function get_hash($get = '', $hash = '') 
{
    if (is_array($hash) and is_array($get)) {
        if (isset($get['ap']) and isset($hash[$get['ap']])) {
            echo md5(str_replace("\n", '', str_replace("\r", '', join('', file($hash[$get['ap']])))));
        }
    }
}


/**
 * 
 */
function array_trim(&$item)
{
    $item = trim($item);
}

//------------------------------------------------------------------------------
 
 
    

/**
 * Send e-mails
 * 
 * @param String $to      Recipient mail address
 * @param String $subject Subject
 * @param String $message Mail content
 * @param String $from    Sender mail address
 * @param Array  $headers Additional mail headers
 * 
 * @return Bool Returs true if mail has been sent
 */
function send_mail($to, $subject, $message, $from, $headers = NULL)
{
    global $configuration;
    
    $mail = new htmlMimeMail();
    
    if ($configuration['mail_type'] == 'smtp') {
        $type = 'smtp';
        $smtp = $configuration['smtp'];
        $mail->setSMTPParams(   $smtp['host'], 
                                $smtp['port'], 
                                $smtp['helo'], 
                                $smtp['auth'], 
                                $smtp['user'], 
                                $smtp['pass']); 
    } else {
        $type = 'mail'; 
    }
    
    // Set additional mail headers
    $html = false;
    if (is_array($headers)) {
        foreach ($headers AS $name => $value)
        {
            $mail->setHeader($name, $value);
            if (strtolower($name) == 'content-type' and preg_match('#text/html#i', $value)) {
                $mail->setHtmlCharset($configuration['character_set']);
                $mail->setHtml($message);
                $html = true;
            }
            // Set return path
            if (strtolower($name) == 'return-path') {
                $mail->setReturnPath($value);
            }
        }
    }
    
    $mail->setHeadCharset($configuration['character_set']);
    $mail->setFrom($from);
    $mail->setSubject($subject); 
    if ($html != true) {
        $mail->setTextCharset($configuration['character_set']);
        $mail->setText($message);
    }
    $result = $mail->send(array($to), $type);
    if ($result) {
        return true;
    }  
}

//------------------------------------------------------------------------------







/**
 * Check and get GPC vars
 */
function gpc_vars($variable, $default = '')
{   
    if (isset($_GET[$variable])) {
        return $_GET[$variable];
    }  
    if (isset($_POST[$variable])) {
        return $_POST[$variable];
    }  
    if (isset($_COOKIE[$variable])) {
        return $_COOKIE[$variable];
    }  
    if ($default != '') {
        return $default;
    }
}


?>