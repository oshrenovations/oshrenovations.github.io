<?php

/**
 * Title........: Form Mail Script
 * Filename.....: formmail.class.inc.php
 * Author.......: Ralf Stadtaus
 * Homepage.....: http://www.gentlesource.com/
 */

/**
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY 
 * OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT
 * LIMITED   TO  THE WARRANTIES  OF  MERCHANTABILITY,
 * FITNESS    FOR    A    PARTICULAR    PURPOSE   AND 
 * NONINFRINGEMENT.  IN NO EVENT SHALL THE AUTHORS OR 
 * COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES 
 * OR  OTHER  LIABILITY,  WHETHER  IN  AN  ACTION  OF 
 * CONTRACT,  TORT OR OTHERWISE, ARISING FROM, OUT OF 
 * OR  IN  CONNECTION WITH THE SOFTWARE OR THE USE OR 
 * OTHER DEALINGS IN THE SOFTWARE.
 */

/**
 */
class Formmail 
{
    var $error_template;
    var $new_html_template;
    var $all_content;
    var $replace_content;
    var $environment;
    var $wrap_content;
    var $mail_content;
    var $mail_headers;
    var $multiple; 
    // -re- join multiple values by this string
    var $multiple_glue;
    var $selection_content;
    var $control_fields;
    var $table_start;
    var $table_end;
    var $hide_empty_fields;
    var $attachments = array();

    /**
     * Initialize some settings
     */
    function Formmail($config = '')
    {
        $this->hide_empty_fields = 'no'; // yes/no                    
        $this->table_start = '<table border="0" cellpadding="2" cellspacing="1">';
        $this->table_end = '</table>';
        $this->multiple = 'multiple';
        $this->multiple_glue = ', ';
        $this->mail_headers = array('From:',
            'Content-Type:',
            'Content-Transfer-Encoding:',
            'Cc:',
            'Bcc:',
            'Reply-To:',
            'X-Mailer:',
            'MIME-Version:',
            'User-Agent:',
            'Sender:',
            'X-Envelope-From:',
            'X-Envelope-To:',
            'Envelope-To:',
            'X-Sender:',
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
            'Priority:',
            'Sensitivity:',
            'Encoding:',
            'boundary=',
            'Return-path:');
        $this->control_fields = array('error_page', 'thanks', 'required_fields', 'email_fields', 'compare_fields', 'html_template', 'mail_template', 'send', 'mail_input_validation_result'); 
        // Extract configuration array
        if (!empty($config) and is_array($config)) {
            $valid = array_keys(get_class_vars(get_class($this)));
            while (list($key, $val) = each($config)) {
                if (in_array($key, $valid)) {
                    $this->$key = $val;
                } 
            } 
        } 
    } 

    /**
     * Check template path
     */
    function check_template_path($path)
    {
        global $txt;

        if (!is_dir($path)) {
            return array('message' => $txt['txt_wrong_template_path']);
        } 
    } 

    /**
     * Check required fields
     */
    function check_required_fields($required_fields, $data)
    {
        global $txt;

        $check_required_fields = explode(',', $required_fields);
        reset ($check_required_fields);
        $err_msg = '';

        while (list ($key, $val) = each ($check_required_fields)) {
            $val = trim($val);

            if (!isset($txt['txt_' . $val]) or $txt['txt_' . $val] == '') {
                $txt['txt_' . $val] = $val;
            } 
            if (!isset($data[$val]) or $data[$val] == '') {
                $err_msg .= $txt['txt_' . $val] . '<br />';
            } 
        } 

        if (!empty($err_msg)) {
            return $err_msg;
        } 
    } 

    function check_required_fields_array($required_fields, $data)
    {
        $check_required_fields = explode(',', $required_fields);
        reset ($check_required_fields);

        while (list ($key, $val) = each ($check_required_fields)) {
            $val = trim($val); 
            // -re- TODO: accept string '0' as NOT empty!!!
            if (empty ($data[$val])) {
                $fields[] = $val;
            } 
        } 

        if (!empty($fields)) {
            return $fields;
        } 
    } 

    /**
     * Check email fields
     */
    function check_email_fields($email_fields, $data)
    {
        global $txt;

        $check_email_fields = explode(',', $email_fields);
        reset ($check_email_fields);
        $err_msg = '';

        while (list ($key, $val) = each ($check_email_fields)) {
            $val = trim($val);

            if (!empty ($data[$val]) and !preg_match("/^([a-z0-9._-](\+[a-z0-9])*)+@[a-z0-9.-]+\.[a-z]{2,6}$/i", $data[$val])) {
                if (empty($txt['txt_' . $val])) {
                    $txt['txt_' . $val] = $val;
                } 
                $err_msg .= $txt['txt_' . $val] . '<br />';
            } 
        } 

        if (!empty($err_msg)) {
            return $err_msg;
        } 
    } 

    function check_email_fields_array($email_fields, $data)
    {
        global $txt;

        $check_email_fields = explode(',', $email_fields);
        reset ($check_email_fields);

        while (list ($key, $val) = each ($check_email_fields)) {
            $val = trim($val);

            if (isset($data[$val]) and !empty($data[$val]) and !preg_match("/^([a-z0-9._-](\+[a-z0-9])*)+@[a-z0-9.-]+\.[a-z]{2,6}$/i", $data[$val])) {
                $fields[] = $val;
            } 
        } 

        if (!empty($fields)) {
            return $fields;
        } 
    } 

    /**
     * Check compare fields
     */
    function check_compare_fields($fields, $data, &$message)
    {
        global $txt;

        if (!is_array($fields)) {
            return false;
        }

        $num = sizeof($fields);    
        $err_fields = '';            
        for ($i = 0; $i < $num; $i++)
        {
            $field_names = explode(',', $fields[$i]);
            if (sizeof($field_names) < 2) {
                continue;
            }

            $numj = sizeof($field_names);
            for ($j = 1; $j < $numj; $j++)
            {
                $field_names[$j-1] = trim($field_names[$j-1]);
                $field_names[$j] = trim($field_names[$j]);
                if ($data[$field_names[$j-1]] != $data[$field_names[$j]]) {
                    if (!isset($txt['txt_' . $field_names[$j-1]]) or $txt['txt_' . $field_names[$j-1]] == '') {
                        $txt['txt_' . $field_names[$j-1]] = $field_names[$j-1];
                    }
                    if (!isset($txt['txt_' . $field_names[$j]]) or $txt['txt_' . $field_names[$j]] == '') {
                        $txt['txt_' . $field_names[$j]] = $field_names[$j];
                    }
                    $err_fields .= $txt['txt_' . $field_names[$j-1]] . $txt['txt_compare_and'] . $txt['txt_' . $field_names[$j]] . '<br />';
                }
            }
        }
        if ($err_fields != '') {
            $message[] = array('message' => $txt['txt_error_compare'], 'fields' => $err_fields);                
        }
    } 

    /**
     * Check whether the user is allowed to send e-mails
     * based on the sender or recipient e-mail address
     */
    function check_value_limit($check_value, $count, $duration, $field, $err_msg)
    {
        global $path;

        if (!empty($count) and $count > 0) {
            if (is_file ($path['logfile'])) {
                $check = '';
                $limit_errors_flag = '';

                $read_logfile = fopen ($path['logfile'], "r");

                while ($line = fgets($read_logfile, 4096)) {
                    $data = explode('%%', $line);

                    if ($check_value == trim($data[$field]) and $data[1] > mktime() - $duration * 60 * 60) {
                        $check++;
                    } 
                } 
                fclose ($read_logfile);

                if ($count > 0 and !empty ($count) and $check >= $count) {
                    return $err_msg;
                } 
            } 
        } 
    } 

    /**
     * Generate array that contains all form data except
     * the control fields (hidden form fields).
     * 
     * -re- Enhanced to handle multiple selects and several checkboxes with the same name.
     * -re- This requires checkbox/select names to be written as ... name="my_name[]" ...
     * -re- to force their delivery as arrays into $_POST. No other changes are required in form and form template.
     */
    function generate_all_content($data)
    {
        $match_content = implode('|', $this->control_fields);
        $match_content = "/$match_content/i";
        $all_content = array();
        $all_content_table[] = $this->table_start;
        $count = 0;

        while (list($key, $val) = each($data)) {
            if (preg_match($match_content, $key) > 0) {
                continue;
            } 

            if (is_array($val)) {
                $val = join($this->multiple_glue, $val);
            } 

            if ($this->hide_empty_fields == 'yes' and $val == '') {
                continue;
            } 

            $all_content[] = $key . ': ' . $val;
            $even_odd = (($count % 2) == 0) ? 'even' : 'odd';
            $all_content_table[] = '<tr class="' . $even_odd . '"><td class="key">' . $key . '</td><td class="val">' . nl2br($val) . '</td></tr>';
            $count++;
        } 

        $all_content_table[] = $this->table_end;

        $content = array('all_content' => join("\n", $all_content),
            'all_content_table' => join("\n", $all_content_table));

        return $content;
    } 

    /**
     * Generate array that contains all form data field
     * names and their counterpart variables ({...}).
     */
    function generate_form_variables($data, $txt)
    {
        $match_content = implode('|', $this->control_fields);
        $match_content = "/$match_content/i";
        $form_variables = '';

        while (list($key, $val) = each($data)) {
            if (!preg_match($match_content, $key)) {
                if (isset($txt['txt_' . $key])) {
                    $my_key = $txt['txt_' . $key];
                } else {
                    $my_key = $key;
                } 

                $form_variables .= $my_key . ': {' . $key . "}\n";
            } 
        } 

        return $form_variables;
    } 

    /**
     * Replace placeholders with string or array values
     * replace_values( string subject, mixed replacement 
     * variable name)
     */
    function replace_values($content, $values)
    {
        global ${$values};

        $this->replace_content = $content;

        if (is_array(${$values})) {
            reset(${$values});

            while (list ($key, $val) = each(${$values})) {
                // -re- account for array values (multiple select/checkbox)
                if (is_array($val)) {
                    $val = join($this->multiple_glue, $val);
                } 
                $this->replace_content = preg_replace('#\{(checkbox:|select:|radiobutton:|)' . preg_quote($key, '#') . '\}#', preg_quote($val, '#'), $this->replace_content);
            } 
        } else if (is_string(${$values})) {
            // -re- account for array values (multiple select/checkbox)
            // if (is_array($val)) {
            // $val = join($this->multiple_glue, $val);
            // }
            $this->replace_content = preg_replace('#\{(checkbox:|select:|radiobutton:|)' . preg_quote($values, '#') . '\}#', preg_quote(${$values}, '#'), $this->replace_content);
        } 

        return $this->replace_content;
    } 

    /**
     * Remove unselected cheboxes and radio buttons from
     * template.
     */
    function remove_values($content)
    {
        $this->replace_content = preg_replace('#\{(checkbox|select|radiobutton):(.*?)\}#', '', $content);

        return $this->replace_content;
    } 

    /**
     * Generate environment variables
     */
    function get_environment_var($input)
    {
        $time = explode(' ', date("Y m d H i s", mktime()));

        $this->environment = array('env_user_agent' => $input['HTTP_USER_AGENT'] , 'env_remote_address' => $input['REMOTE_ADDR'] , 'env_remote_host' => @gethostbyaddr($input['REMOTE_ADDR']) , 'env_year' => $time[0] , 'env_month' => $time[1] , 'env_day' => $time[2] , 'env_hour' => $time[3] , 'env_minute' => $time[4] , 'env_second' => $time[5] , 'env_iso_date' => $time[0] . '-' . $time[1] . '-' . $time[2] . ' (' . $time[3] . ':' . $time[4] . ':' . $time[5] . ')'

            );

        return $this->environment;
    } 

    /**
     * In case $text_wrap contains a value and that value
     * consists only on numbers wrap the text.
     * text_wrap(string str, int width [, string break])
     */
    function wrap_content($content, $width, $break = "\n")
    {
        $this->wrap_content = $content;

        if (isset($width) and !empty($width) and preg_match("/^[0-9]+$/", $width)) {
            $this->wrap_content = wordwrap($this->wrap_content, $width, $break);
        } 

        return $this->wrap_content;
    } 

    /**
     */
    function validate_to($content)
    {
        $header_info = explode("\n", $content);

        for($k = 0; $k < count($header_info); $k++) {
            $clean_header = trim($header_info[$k]);
            if (empty($clean_header)) {
                break;
            } 

            if (preg_match("/To:/i", $header_info[$k])) {
                $mail_recipient = trim(preg_replace("/To:/i", '', $header_info[$k]));
            } 
        } 

        if (!isset($mail_recipient)) {
            return false;
        } 

        if (preg_match("/[a-z0-9_-]+(\.[a-z0-9_-]+)*@([0-9a-z][0-9a-z-]*[0-9a-z]\.)+([a-z]{2,10})/i", $mail_recipient)) {
            return true;
        } 

        return false;
    } 

    /**
     */
    function collect_multiples($fields)
    {
        if (!empty($fields) and is_array($fields)) {
            while (list($key, $val) = each($fields)) {
                if (preg_match("#^" . $this->multiple . "_(.*?)_([0-9])$#i", $key, $matches) > 0) {
                    if (isset($matches[2]) and is_numeric($matches[2])) {
                        $multiple[$matches[2]][$matches[1]] = $val;
                    } 
                } 
            } 
        } 

        if (!isset($multiple)) {
            $multiple = array();
        } 

        return $multiple;
    } 

    /**
     * -re- TODO: Hmmm, maybe this must be adapted to multiple selects and multiple checkboxes too?
     * -re-       I don't have the time to do that now, maybe Ralf does?
     */
    function replace_multiples($content, $values)
    {
        for ($i = 0, $num = count($content); $i < $num; $i++) {
            if (is_array($values)) {
                reset($values);

                while (list ($key, $val) = each($values)) {
                    $temp_content = $content[$i];

                    while (list ($k, $v) = each($val)) {
                        $temp_content = preg_replace('#\{(checkbox:|select:|radiobutton:|)' . $this->multiple . '_' . $k . '_\?\}#', '{' . $this->multiple . '_' . $k . '_' . $key . '}', $temp_content);
                    } 
                    if ($old_content == $temp_content) {
                        continue;
                    } 
                    $new_content[] = $temp_content;
                    $old_content = $temp_content;
                } 
            } 
        } 

        if (isset($new_content)) {
            return $new_content;
        } else {
            return $content;
        } 
    } 

    /**
     * Add name of files to be attached to the script
     */
    function add_attachments($arr)
    {
        $this->attachments = $arr;
    }

    /**
     * Replace placeholders in mail templates and send
     * e-mails.
     */
    function send_mail($mail_content, $post_data = null)
    {
        global $text_wrap, $my_sendmail, $send_alternative_mail,
        $all_content, $debug_mode, $sender_count,
        $sender_duration, $recipient_count,
        $recipient_duration, $txt, $remove_tags, $tplt,
        $all_content_table, $configuration;

        $multiple = $this->collect_multiples($post_data);
        $mail_content = $this->replace_multiples($mail_content, $multiple);

        for ($i = 0; $i < count($mail_content); $i++) {
            /**
             * Replace placeholder with form, text (from language
             * file) and environment data
             */
            $mail_content[$i] = $this->replace_values($mail_content[$i], 'post_data');

            $mail_content[$i] = $this->replace_values($mail_content[$i], 'txt');

            $mail_content[$i] = $this->replace_values($mail_content[$i], 'add_text');

            $mail_content[$i] = $this->replace_values($mail_content[$i], 'environment');

            $mail_content[$i] = $this->replace_values($mail_content[$i], 'all_content');

            $mail_content[$i] = $this->replace_values($mail_content[$i], 'all_content_table');

            $mail_content[$i] = $this->replace_values($mail_content[$i], 'form_variables');

            $mail_content[$i] = $this->replace_values($mail_content[$i], 'url');

            /**
             * Remove unselected value placeholders
             */
            $mail_content[$i] = $this->remove_values($mail_content[$i]);

            /**
             * Remove tags
             */ 
            // $mail_content[$i] = $this->clean_output($mail_content[$i], $remove_tags);
            /**
             * Strip slashes
             */
            $final_mail_content = stripslashes($mail_content[$i]);

            if ($this->validate_to($final_mail_content) == false) {
                continue;
            } 

            /**
             * Check whether the user is allowed to send e-mails
             * with or to a certain e-mail address.
             */
            $sender = $this->get_header_info($mail_content[$i], 'From');
            $recipient = $this->get_header_info($mail_content[$i], 'To');

            // Check if recipient is blocked by domain
            if (sizeof($configuration['recipients_domains']) > 0) {
                foreach ($configuration['recipients_domains'] AS $recipient_domain)
                {
                    $domain = '#' . preg_quote(trim($recipient_domain)) . '#';
                    if (preg_match($domain, $recipient) === 1) {
                        return array('status' => 'failed', 'message' => array(), 'mail_content' => $mail_content[$i]);
                    }
                }
            }
            if ($tplt == 'recom') {                
                
                if ($limit = $this->check_value_limit($sender, $sender_count, $sender_duration, 4, $txt['txt_sender_expiration'])) {
                    $message[] = array('message' => $limit, 'fields' => '');
                } 

                if ($limit = $this->check_value_limit($recipient, $recipient_count, $recipient_duration, 5, $txt['txt_recipient_expiration'])) {
                    $message[] = array('message' => $limit, 'fields' => '');
                } 

                if (isset($message) and !empty($message)) {
                    return array('status' => 'failed', 'message' => $message);
                } 
            } 

            /**
             * Start attachment handling
             */
            $send_attachments = false;
            if (!empty($this->attachments) and is_array($this->attachments)) {
                $send_alternative_mail = 'yes';
                $send_attachments = true;
            }             
			if (isset($configuration['attach_mail_vars'])
					and is_array($configuration['attach_mail_vars'])
					and sizeof($configuration['attach_mail_vars']) > 0) {
                $send_alternative_mail = 'yes';
                $send_attachments = true;
			}

            /**
             * Get sendmail path from php ini settings or use the
             * value of $my_sendmail.
             */
            if ($send_alternative_mail != 'yes') {
                $sendmail = @ini_get('sendmail_path');

                debug_mode($sendmail, 'ini_get()');

                if (empty($sendmail)) {
                    $sendmail = "/usr/sbin/sendmail -t ";
                    debug_mode($sendmail, 'empty($sendmail)');
                } 

                if (isset($my_sendmail) and !empty($my_sendmail)) {
                    $sendmail = $my_sendmail;
                    debug_mode(array('$my_sendmail', $sendmail));
                } 

                /**
                 * Try to send e-mail by using popen() to access
                 * sendmail.
                 */
                if ($fd = @popen($sendmail, "w")) {
                    if (!@fputs($fd, $final_mail_content . "\n")) {
                        $send_alternative_mail = 'yes';
                        debug_mode(array($txt['txt_popen_error'] . ' - fputs()', gettype($fd), $fd));
                    } 
                    pclose($fd);
                } else {
                    $send_alternative_mail = 'yes';
                    debug_mode(array($txt['txt_popen_error'] . ' - popen()', gettype($fd), $fd));
                } 

                debug_mode($final_mail_content, 'Mail Content popen()');
            } 

            /**
             * If popen() - or fputs() - fails, extract mail
             * header from the template and use the PHP  function
             * mail().
             */
            if ($send_alternative_mail == 'yes') {
                $header_info = explode("\n", $final_mail_content);
                $mail_subject = '';
                $mail_header = $this->mail_headers;
                $mail_header = join($mail_header, '|');

                unset($additional_headers);
                $attachment_headers = array();

                for($k = 0; $k < count($header_info); $k++) 
                {
                    $clean_header = trim($header_info[$k]);
                    if (empty($clean_header)) {
                        break;
                    } 

                    if (preg_match("/^From:/i", $header_info[$k])) {
                        $mail_from = trim(preg_replace("/From:/i", '', $header_info[$k]));
                        $attachment_headers['From'] = $mail_from;
                        unset($header_info[$k]);
                        continue;
                    } 

                    if (preg_match("/^To:/i", $header_info[$k])) {
                        $mail_recipient = trim(preg_replace("/^To:/i", '', $header_info[$k]));
                        unset($header_info[$k]);
                        continue;
                    } 

                    if (preg_match("/^Subject:/i", $header_info[$k])) {
                        $mail_subject = trim(preg_replace("/^Subject:/i", '', $header_info[$k]));
                        unset($header_info[$k]);
                        continue;
                    } 

                    if (preg_match("/^X-Form-Mail-Attachment:/i", $header_info[$k])) {
                        if (trim(str_replace('X-Form-Mail-Attachment:', '', $header_info[$k])) == 'no') {
                            $send_attachments = false;
                        } 
                        unset($header_info[$k]);
                        continue;
                    } 

                    if (preg_match("/^" . $mail_header . "/i", $header_info[$k], $match)) {
                        $additional_headers[] = $header_info[$k];
                        $attachment_headers[str_replace(':', '', $match[0])] = trim(preg_replace("/" . $match[0] . "/i", '', $header_info[$k]));
                        unset($header_info[$k]);
                        continue;
                    } 
                } 

                if (isset($header_info) and is_array($header_info)) {
                    $new_mail_content = trim(implode($header_info, "\n"));
                    $new_mail_content = str_replace("\r", '', $new_mail_content);
                } else {
                    $new_mail_content = '';
                } 

                if (isset($additional_headers) and is_array($additional_headers)) {
                    $additional_headers = implode($additional_headers, "\n");
                } else {
                    $additional_headers = '';
                }


                /**
                 * Wrap mail content (and only mail content - not
                 * headers).
                 */
                $new_mail_content = $this->wrap_content($new_mail_content, $text_wrap);
                


				/**
				 * Send mail using simple mail function
				 */
                if (!$send_attachments and $debug_mode != 'on' and $mail_recipient != '') {
                    //  @mail ($mail_recipient, $mail_subject, $new_mail_content, $additional_headers);
                    send_mail($mail_recipient, $mail_subject, $new_mail_content, $mail_from, $attachment_headers);
                } 

				/**
				 * Send mail using mail class
				 */
                if ($send_attachments and $debug_mode != 'on' and $mail_recipient != '') {
                    $att = new htmlMimeMail(); 
                    // Switch to smtp mode
                    if ($configuration['mail_type'] == 'smtp') {
                        $type = 'smtp';
                        $smtp = $configuration['smtp'];
                        $att->setSMTPParams($smtp['host'],
                            $smtp['port'],
                            $smtp['helo'],
                            $smtp['auth'],
                            $smtp['user'],
                            $smtp['pass']);
                    } else {
                        $type = 'mail';
                    } 
                    // Register file attachments in mime class
                    foreach($this->attachments as $file_name) 
                    {
                        $att->addAttachment($att->getFile($file_name['new']), $file_name['old']);
                    } 
                	
	                //Manage mail var attachments
	                if (isset($configuration['attach_mail_vars'])
	                		and is_array($configuration['attach_mail_vars'])
	                		and sizeof($configuration['attach_mail_vars']) > 0) {
	               		foreach ($configuration['attach_mail_vars'] AS $attach_type)
	               		{
	               		    if ($attach_type == 'vcard') {
                                $attach_config = array(
                                                    'mailvars'  => $post_data,
                                                    'type'      => G10E_ATTACH_TYPE_VCARD,
                                                    'control'   => $this->control_fields
                                                    );
	               		    	if ($res = attach_mail_variables::get_content($attach_config)) {
	               		    	    $att->addAttachment($res, 'vcard.vcf');
	               		    	}
	               		    }
	               		    if ($attach_type == 'csv') {
                                $attach_config = array(
                                                    'mailvars'  => $post_data,
                                                    'type'      => G10E_ATTACH_TYPE_CSV,
                                                    'control'   => $this->control_fields
                                                    );
	               		    	if ($res = attach_mail_variables::get_content($attach_config)) {
	               		    	    $att->addAttachment($res, 'csv.csv');
	               		    	}
	               		    }
	               		}
	                }  
                    // Register headers in mime class
                    $html = false;
                    if (isset($attachment_headers) and is_array($attachment_headers)) {
                        foreach($attachment_headers as $key => $val) 
                        {
                            $att->setHeader($key, $val);
                            if ($key == 'Content-Type' and preg_match('#text/html#i', $val)) {
                                $att->setHtml($new_mail_content);
                                $html = true;
                            }
                        } 
                    } 
                    $att->setSubject($mail_subject);
                    if ($html != true) {    
                        $att->setText($new_mail_content);
                    }
                    $att->send(array($mail_recipient), $type);
                } 

                debug_mode($mail_recipient, 'Mail Recipient mail()');
                debug_mode($mail_subject, 'Mail Subject mail()');
                debug_mode($new_mail_content, 'Mail Content mail()');
                debug_mode($additional_headers, 'Mail Additional Headers mail()');
            } 
        } // -re- for
        $this->mail_content = $mail_content[0];

        return array('status' => 'ok', 'mail_content' => $this->mail_content);
    } 

    /**
     * Write entry in log file - logfile format: IP - Unix timestamp - date - time - sender - recipient - mail content
     */
    function log_message($mail_content)
    {
        global $log_messages, $ip_address_count, $path, $txt, $tplt, $sender_count, $recipient_count;

        $message_sender = $this->get_header_info($mail_content, 'From');
        $message_recipient = $this->get_header_info($mail_content, 'To');

        if ($log_messages == 'yes' or $ip_address_count > 0 or ($tplt == 'recom' and ($sender_count > 0 or $recipient_count > 0))) {
            if ($logfile = @fopen($path['logfile'], 'a')) {
                @flock($logfile, 2) or debug_mode($txt['txt_cannot_lock_file'] . $path['logfile']);
                $current_time = mktime();
                $logfile_content = array(getenv('REMOTE_ADDR'), $current_time, date ("Y-m-d", $current_time), date("H:i:s", $current_time), $message_sender, $message_recipient, str_replace ("\n", ' ', str_replace ("\r", '', $mail_content)));
                $logfile_content = join('  %%  ', $logfile_content);
                @fputs ($logfile, $logfile_content . "\n");
                @fclose ($logfile);
            } else {
                debug_mode($path['logfile'], $txt['txt_cannot_open_file']);
            } 
        } 
    } 

    /**
     * Get mail header information
     */
    function get_header_info($mail_content, $header)
    {
        preg_match("#" . $header . ":(.*?)<(.*?)>#i", $mail_content, $match);

        if (!isset($match[2]) or empty($match[2])) {
            preg_match("#$" . $header . ":(.*?)\n#i", $mail_content, $match);
            $match = $match[1];
        } else {
            $match = $match[2];
        } 

        return trim($match);
    } 

    /**
     * Check selected checkboxes, radio buttons and select
     * menu options and replace values in template.
     * 
     * -re- Enhanced to handle multiple selects and several checkboxes with the same name.
     * -re- This requires checkbox/select names to be written as ... name="my_name[]" ...
     * -re- to force their delivery as arrays into $_POST. No other changes are required in form and form template.
     */
    function register_selections($content, $data = '')
    {
        if (isset($data) and !empty($data)) {
            reset($data);

            while (list($key, $val) = each($data)) {
                if (is_array($val)) {
                    foreach ($val as $multiple) {
                        /**
                         * -re- Check multiple selected checkboxes with the same name and multiple select menu options.
                         */
                        $content = preg_replace("#\{checkbox:" . preg_quote($key, '#') . "=" . preg_quote($multiple, '#') . "\}#i", 'checked="checked"', $content);
                        $content = preg_replace("#\{select:" . preg_quote($key, '#') . "=" . preg_quote($multiple, '#') . "\}#i", 'selected="selected"', $content);
                    } 

                    /**
                     * -re- Replace values in template with comma separated list.
                     */
                    $multiple = join($this->multiple_glue, $val);
                    $content = preg_replace("#\{checkbox:" . preg_quote($key, '#') . "\}#i", preg_quote($multiple, '#'), $content);
                    $content = preg_replace("#\{select:" . preg_quote($key, '#') . "\}#i", preg_quote($multiple, '#'), $content);
                } else {
                    /**
                     * Check selected checkboxes, radio buttons and select
                     * menu options.
                     */
                    $content = preg_replace("#\{checkbox:" . preg_quote($key, '#') . "=" . preg_quote($val, '#') . "\}#i", 'checked="checked"', $content);
                    $content = preg_replace("#\{radiobutton:" . preg_quote($key, '#') . "=" . preg_quote($val, '#') . "\}#i", 'checked="checked"', $content);
                    $content = preg_replace("#\{select:" . preg_quote($key, '#') . "=" . preg_quote($val, '#') . "\}#i", 'selected="selected"', $content);

                    /**
                     * Replace values in template.
                     */
                    $content = preg_replace("#\{checkbox:" . preg_quote($key, '#') . "\}#i", preg_quote($val, '#'), $content);
                    $content = preg_replace("#\{radiobutton:" . preg_quote($key, '#') . "\}#i", preg_quote($val, '#'), $content);
                    $content = preg_replace("#\{select:" . preg_quote($key, '#') . "\}#i", preg_quote($val, '#'), $content);
                } 
            } 
        } 

        $content = preg_replace("#\{checkbox:(.*?)\}#i", '', $content);
        $content = preg_replace("#\{radiobutton:(.*?)\}#i", '', $content);
        $content = preg_replace("#\{select:(.*?)\}#i", '', $content);

        $selection_content = stripslashes($content);

        return $selection_content;
    } 

    /**
     * Clean form output
     */
    function clean_output($content, $remove_tags)
    {
        if (isset($remove_tags[0]) and $remove_tags[0] == 'yes') {
            if (isset($remove_tags[1])) {
                $content = str_replace('-', '%%+_+%%', $content);
                $content = strip_tags($content, $remove_tags[1]);
                $content = str_replace('%%+_+%%', '-', $content);
            } 
        } 

        return $content;
    } 

    /**
     * Error HTML content
     */
    function load_error_template()
    {
        $this->error_template = '<?xml version="1.0"?>
<!doctype html public "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
                                                          <head>
                                                            <title>{txt_script_name} {txt_script_version}</title>
                                                            <meta http-equiv="Content-Type" content="text/html; {txt_charset}" />
                                                          </head>

                                                          <style type="text/css">
                                                          <!--
                                                            h4 {
                                                                font-family:Courier New,Sans-serif;
                                                                }

                                                            p, td, br, form, div, span {
                                                                font-family:Courier New,Sans-serif;
                                                                font-size:9.5pt;
                                                                }

                                                            .code {
                                                                font-family:Courier New,Sans-serif;
                                                                }

                                                            .code strong {
                                                                color:#F9F9F9;
                                                                }

                                                            #poweredby {
                                                                text-align:center;
								color:#E5E5E5;
                                                                }

                                                            #poweredby span {
                                                                font-family:Arial,Helvetica,Sans-serif;
								font-color:#E5E5E5;
                                                                }


                                                          -->
                                                          </style>

                                                          <body>

                                                          <p class="code"><strong>{txt_system_message}</strong></p>
                                                          <LOOP NAME="system_message">
                                                            <p class="code">{message}<br /><br /><br /></p>
                                                          </LOOP NAME="system_message">



                                                          <p>&nbsp;</p>
                                                          <p>&nbsp;</p>


                                                        </body>
                                                        </html>

                                                  ';

        return $this->error_template;
    } 
} 

?>
