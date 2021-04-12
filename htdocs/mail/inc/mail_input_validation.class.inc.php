<?php

/** 
 * Mail Input Validation - mail_input_validation.class.inc.php
 * 
 * (C) Ralf Stadtaus http://www.gentlesource.com/
 * 
 * @version 0.3
 */

/*
    
    THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY
    OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT
    LIMITED   TO  THE WARRANTIES  OF  MERCHANTABILITY,
    FITNESS    FOR    A    PARTICULAR    PURPOSE   AND
    NONINFRINGEMENT.  IN NO EVENT SHALL THE AUTHORS OR
    COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES
    OR  OTHER  LIABILITY,  WHETHER  IN  AN  ACTION  OF
    CONTRACT,  TORT OR OTHERWISE, ARISING FROM, OUT OF
    OR  IN  CONNECTION WITH THE SOFTWARE OR THE USE OR
    OTHER DEALINGS IN THE SOFTWARE.

*/

/*

Features of this class:

  convert_html:    Convert HTML tags into entities
  strip_html:      Strip HTML from input
  remove_new_lines:Remove carriage returns (\r), new lines (\n)
  remove_headers:  Remove mail headers such as Bcc, Cc, To


Configuration Example:

$config = array(
					'convert_html' 		=> false, // true, false (default)
					'strip_html' 		=> true,  // true (default), false
                    'remove_new_lines'  => true,  // true (default), false
                    'remove_headers' 	=> true,  // true (default), false
                 	
				);

Check input with configuration:

mail_input_validation::check($config);


You can also call following static methods: 

mail_input_validation::email_syntax($address)
    Check the syntax of a given e-mail address.
    
mail_input_validation::method(METHOD_POST|METHOD_GET|METHOD_BOTH)
    Allow a certain method like only POST or GET or both
    
mail_input_validation::referrer($referrers)
    Check the referring server, if provided by the user agent
    You can list ($referrers) all allowed server names that are 
    different from the server name the script is running on

mail_input_validation::ip_address($blacklist)
    Check, if the script is called from a given list of IP addresses


*/



/**
 * Constants
 */
define('METHOD_POST', 1);
define('METHOD_GET',  2);
define('METHOD_BOTH', 3);




/**
 * Mail input, referrer and syntax validation class
 * 	
 */
class mail_input_validation
{      
    /**
     * Convert HTML
     * @var bool
     * @access private
     */
    var $convert_html = false;
    
    
    /**
     * Strip HTML
     * @var bool
     * @access private
     */
    var $strip_html = true;
    
    
    /**
     * Remove \r(eturn), \n(ew line)
     * @var bool
     * @access private
     */
    var $remove_new_lines = false;
    
    
    /**
     * Remove mail headers such as Cc, Bcc, To
     * @var bool
     * @access private
     */
    var $remove_headers = true;
    
    
    /**
     * Mail headers to be removed
     * @var array
     * @access private
     */
    var $mail_headers = array();
    
    
    /**
     * Result of validation
     * @var array
     * @access private
     */
    var $result = array();

// -----------------------------------------------------------------------------




    /**
     * Constructor - reads configuration
     * @param array $config
     */
    function mail_input_validation($config = array())
    {   
        // Extract configuration array
        if (is_array($config)) {
            $valid = array_keys(get_class_vars(get_class($this)));
            while (list($key, $val) = each($config))
            {
                if (in_array($key, $valid)) {
                    $this->$key = $val;
                }
            }
        }
    }
    
// -----------------------------------------------------------------------------




    /**
     * Check input
     * @param mixed $data Input string or array to be checked
     * @param array $config Configuration
     * @static
     * @public
     */
    function check($data, $config = array())
    {
        $c = new mail_input_validation($config);
        
        if (is_array($data)) {
            foreach ($data AS $key => $val)
            {
                if (is_array($val)) {
                    foreach ($val AS $skey => $sval)
                    {
                        $val[$skey] = $c->process($sval);
                    }
                } elseif (is_string($val)) {
                    $val = $c->process($val);
                }
                $data[$key] = $val;
            }
            // Add result message
            $data['mail_input_validation_result'] = join(" \r\n", $c->result);
        } elseif (is_string($data)) {
            $data = $c->process($data);
        }
        
        return $data;
    }
    
// -----------------------------------------------------------------------------




    /**
     * Process validation checks
     * @param string $value Value to be checked
     * @return string Validated string
     * @private
     */
    function process($value)
    {        
        // Convert HTML
        if ($this->convert_html == true) {
            $value = $this->convert_html($value);
        }
        
        // Strip HTML
        if ($this->strip_html == true) {
            $value = $this->strip_html($value);
        }
        
        // Remove \r \n
        if ($this->remove_new_lines == true) {
            $value = $this->remove_rnst($value);
        }
        
        // Remove mail headers
        if ($this->remove_headers == true) {
            $value = $this->remove_headers($value);
        }
        
        // Return validated value
        return $value;
    }
    
// -----------------------------------------------------------------------------




    /**
     * Convert HTML tags
     * @param string $value Value to be checked
     * @return string Validated string
     * @access private
     */
    function convert_html($value, $quote_style = ENT_COMPAT, $charset = 'ISO-8859-1')
    {
        $new = htmlentities($value, $quote_style, $charset);
        if ($new != $value) {
            $this->result[] = 'HTML tags converted';
        }
        return $new;
    }

// -----------------------------------------------------------------------------




    /**
     * Remove HTML tags and script
     * @param string $value Value to be checked
     * @return string Validated string
     * @access private
     */
    function strip_html($value)
    {
        $new = strip_tags($value);
        if ($new != $value) {
            $this->result[] = 'HTML tags removed';
        }
        return $new;
    }

// -----------------------------------------------------------------------------




    /**
     * Remove \r \n 
     * @param string $value Value to be checked
     * @return string Validated string
     * @access private
     */
    function remove_new_lines($value)
    {
        $search = array("\r", "\n", "%0A", "%0D");
        $new = str_replace($search, '', $value);
        if ($new != $value) {
            $this->result[] = '\r \n %0A or %0D removed';
        }
        return $new;
    }

// -----------------------------------------------------------------------------




    /**
     * Remove mail headers
     * @param string $value Value to be checked
     * @return string Validated string
     * @access private
     */
    function remove_headers($value)
    {
        $headers = array(   'Bcc:',
                            'Cc:',
                            'From:',
                            'Content-Type:',
                            'Mime-Type:', 
                            'X-Envelope-From:', 
                            'X-Envelope-To:', 
                            'Envelope-To:', 
                            'X-Sender:',  
                            'Sender:',
                            'Content-Return:', 
                            'Disposition-Notification-Options:', 
                            'Disposition-Notification-To:', 
                            'Errors-To:', 
                            'Return-Receipt-To:', 
                            'Read-Receipt-To:', 
                            'X-Confirm-reading-to:', 
                            'Followup-To:', 
                            'Original-Recipient:', 
                            'In-Reply-To:',
                            'Reply-To:',
                            'To:', 
                            'Priority:', 
                            'Sensitivity:', 
                            'Encoding:', 
                            'boundary=', 
                            'Return-path:'
                            );
        $headers = array_merge($headers, $this->mail_headers);
        reset($headers);
        $new = $value;
        foreach ($headers AS $key => $val)
        {
            $new = preg_replace('/(%0A|%0D|\\n+|\\r+|\\r\\n|)(' . preg_quote($val) . ')/i', '', $new);
        }
        if ($new != $value) {
            $this->result[] = 'Mail headers removed';
        }
        return $new;
    }

// -----------------------------------------------------------------------------




    /**
     * E-mail syntax check
     * @param string $address Mail address to be checked
     * @return bool Returns true if address is a valid e-mail address 
     * @access public
     * @static
     */
    function email_syntax($address)
    {
        if (preg_match("/^[a-z0-9_-]+(\.[a-z0-9_-]+)*@([0-9a-z][0-9a-z-]*[0-9a-z]\.)+([a-z]{2,4}|museum)$/i", $address)) {
            return true;
        }
    }

// -----------------------------------------------------------------------------




    /**
     * Check method
     * @param int $method Parameter can be METHOD_POST|METHOD_GET|METHOD_BOTH
     * (default)
     * @return bool Returns true if correct method is used 
     * @access public
     * @static
     */
    function method($method = 3)
    {
        if ($method == METHOD_POST and getenv('REQUEST_METHOD') == 'POST') {
            return true;
        }
        if ($method == METHOD_GET and getenv('REQUEST_METHOD') == 'GET') {
            return true;
        }
        if ($method == METHOD_BOTH and 
            (getenv('REQUEST_METHOD') == 'POST' or
            getenv('REQUEST_METHOD') == 'GET')) {
                
            return true;
        }
    }

// -----------------------------------------------------------------------------




    /**
     * Check referrer
     * @param int $referrers List (separeted by comma) of allowed referrers
     * @return bool Returns true if referrer is empty or correct 
     * @access public
     * @static
     */
    function referrer($referrers = null)
    {
        // Skip empty referrers
        if (getenv('HTTP_REFERER') == '') {
            return true;
        }
        
        $http_referrer = parse_url(getenv('HTTP_REFERER'));
        $http_referrer = trim(str_replace('www.', '', strtolower($http_referrer['host'])));
        if ($referrers != null) {
            $referrers = explode (',', $referrers);
        } else {
            $referrers = array();
        }
        $referrers[] = getenv('HTTP_HOST');
        foreach ($referrers AS $key => $val)
        {
            $allowed = trim(preg_replace('/^www\./', '', strtolower($val)));
            if ($allowed == $http_referrer) {
                return true;
            }
        }
    }

// -----------------------------------------------------------------------------




    /**
     * Check user agent
     * @return bool Returns true if user agent is empty
     * @access public
     * @static
     */
    function user_agent()
    {
        if (getenv('HTTP_USER_AGENT') != '' or getenv('HTTP_USER_AGENT') == false) {
            return true;
        }
    }

// -----------------------------------------------------------------------------




    /**
     * Check for blacklisted IP addresses
     * @param array|string $blacklist Array or comma separated list of IP
     * adresses
     * @return bool Returns true if a blacklisted IP address is found
     * @access public
     * @static
     */
    function ip_address($blacklist)
    {        
        if (getenv('HTTP_X_FORWARDED_FOR')) {
            $ip = getenv('HTTP_X_FORWARDED_FOR');
        } else if (getenv('HTTP_CLIENT_IP')) {
            $ip = getenv('HTTP_CLIENT_IP');
        } else {
            $ip = getenv('REMOTE_ADDR');
        }
        $list = array();
        if (func_num_args() > 1) {
            $arg_list = func_get_args();
            foreach ($arg_list AS $key => $val) 
            {
                $list[] = $val;
            }
        } elseif (is_array($blacklist)) {
            $list = $blacklist;
        } elseif (strpos($blacklist, ',')) {
            $list = explode(',', $blacklist);
        } else {
            $list = array($blacklist);
        }
        foreach ($list AS $key => $val)
        {
            if ($ip == trim($val)) {
                return true;
            }
        }
    }

// -----------------------------------------------------------------------------




    /**
     * Check the domain of an e-mail address against the server host name or a
     * provided domain name
     * @param string $mail Mail address to be checked
     * @param string $server Server domain name to be check against
     * @return bool Returns true if mail domain and server/provided domain match
     * @access public
     * @static
     */
    function domain_match($mail, $server = null)
    {
        if ($server == null) {
            $server = getenv('HTTP_HOST');
        }

        $server = trim(preg_replace('/^www\./i', '', $server));
        $mail   = substr($mail, strpos($mail, '@') + 1);
        if (strcmp($mail, $server) == 0) {
            return true;
        }
    }

// -----------------------------------------------------------------------------




    



} // End of class




?>
