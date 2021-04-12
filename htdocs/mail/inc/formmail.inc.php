<?php

$runtime_start = explode (' ', microtime ());

/**
 * Form Mail Script - formmail.inc.php 
 * 
 * Copyright: Ralf Stadtaus - http://www.gentlesource.com/
 * 
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
 * Prevent direct call
 */
if (!defined('IN_SCRIPT')) {
    die();
}

// ----------------------------------------------------------------------------- 




/**
 * Configuration
 */
$configuration = array();
$configuration['mail_type']         = 'mail'; // (mail, smtp)
$configuration['mail_from']         = 'postmaster@' . $_SERVER['SERVER_NAME'];

$configuration['smtp']['host']      = 'example.com';
$configuration['smtp']['port']      = 25;
$configuration['smtp']['helo']      = $_SERVER['SERVER_NAME'];
$configuration['smtp']['auth']      = true;
$configuration['smtp']['user']      = '';
$configuration['smtp']['pass']      = '';

$configuration['character_set']     = 'iso-8859-1'; // iso-8859-1, UTF-8 etc.
$configuration['check_user_agent']  = false;
$configuration['strip_injections']  = true;
$configuration['quote_style']       = ENT_COMPAT;
$configuration['captcha_image']     = (isset($captcha) and $captcha == 'yes') ? true : false;
$configuration['captcha_font_path'] = getenv('DOCUMENT_ROOT') . dirname($_SERVER['PHP_SELF']) . '/' . $script_root . 'inc/font/';  // Alternative path: $configuration['captcha_font_path'] = $script_root . 'inc/font/';  
$configuration['garbage_collector'] = true;
$configuration['gc_number']         = 20; // Number of files to be deleted at once
$configuration['gc_time']           = 60; // Minutes until a garbage file gets deleted

$configuration['allowed_recipients_domains'] = ''; // Separated by comma

$configuration['attach_mail_vars']	= array(); // Options: array('vcard', 'csv');

$configuration['temp_folder']       = (defined('F6L_TEMP_FOLDER')) ? F6L_TEMP_FOLDER : $script_root . 'temp/';

// -----------------------------------------------------------------------------




/**
 * WARNING: Only edit the following variables if you
 * are NOT receiving e-mails from the script!
 */


/**
 * If you are not receiving e-mails from the script,
 * just un-comment the following variable by removing
 * the two (//) slashes from in front of the line
 * below. ($my_sendmail = '/usr/sbin/sendmail -t ';)
 */
// $my_sendmail = '/usr/sbin/sendmail -t ';


/**
 * If you still do not receive e-mails from the
 * script, place the two (//) slashes back in front
 * of the line above and set the following variable
 * to = 'yes'. This will make sure that the PHP
 * function mail() will be used by the script. Only
 * change this option, or the one above, not both.
 */
$send_alternative_mail = 'yes';



/**
 * Set debug mode on or off
 */
$debug_mode = 'off';




/**
 * Include
 */
if (function_exists('set_include_path')) {
    set_include_path($script_root . 'inc/lib/');
} else {
    ini_set('include_path', $script_root . 'inc/lib/');
}
include $script_root . 'inc/functions.inc.php';
include $script_root . 'inc/template.class.inc.php';
include $script_root . 'inc/template.ext.class.inc.php';
include $script_root . 'inc/formmail.class.inc.php';
include $script_root . 'inc/mail_input_validation.class.inc.php';
include $script_root . 'inc/lib/clsUpload.php';
include $script_root . 'inc/lib/htmlMimeMail.php'; 

// -----------------------------------------------------------------------------




/**
 * Load attach mail var class
 */
if (isset($configuration['attach_mail_vars'])
		and is_array($configuration['attach_mail_vars'])
		and sizeof($configuration['attach_mail_vars']) > 0) {
    include $script_root . 'inc/attach_mail_variables.class.inc.php';
}
// -----------------------------------------------------------------------------




/**
 * Load language file
 */
if (!isset($language) or empty($language) or !is_file($script_root . 'languages/language.' . $language . '.inc.php')) {
    $language = 'en';
}

$charset_folder = '';
if (strtolower($configuration['character_set']) == 'utf-8') {
    $charset_folder = 'utf-8/';
} 

include $script_root . 'languages/' . $charset_folder . 'language.' . $language . '.inc.php';

// Take character set from language file
if (isset($txt['txt_charset']) and $txt['txt_charset'] != '') {
    $configuration['character_set'] = $txt['txt_charset'];
}

// -----------------------------------------------------------------------------




/**
 * Take care for older PHP versions
 */
if (isset($HTTP_POST_VARS) and sizeof($HTTP_POST_VARS) > 0) {
    $_POST = $HTTP_POST_VARS;
} 

if (isset($HTTP_GET_VARS) and sizeof($HTTP_GET_VARS) > 0) {
    $_GET = $HTTP_GET_VARS;
} 

if (isset($HTTP_POST_FILES) and sizeof($HTTP_POST_FILES) > 0) {
    $_FILES = $HTTP_POST_FILES;
} 

if (isset($HTTP_SERVER_VARS) and sizeof($HTTP_SERVER_VARS) > 0) {
    $_SERVER = $HTTP_SERVER_VARS;
} 

if (isset($HTTP_ENV_VARS) and sizeof($HTTP_ENV_VARS) > 0) {
    $_ENV = $HTTP_ENV_VARS;
}  

// -----------------------------------------------------------------------------




/**
 * Some settings - Please don't change these settings.
 * It could help you and us to solve problems.
 */
$script_name        = 'Old School Handyman Email';
$script_version     = '3.7';
$delete_uploads     = 'yes';
$attach_size        = 10000;
$attach_files       = 'jpg, png, gif';
$tplt               = 'formm';
$display_form       = 'TRUE';
$remove_tags        = array('yes', '');
$script_self        = htmlentities(strip_tags($_SERVER['PHP_SELF']));
$multiple_glue      = "\n";
$include_path       = getenv('DOCUMENT_ROOT');
$f6l_output         = '';

$hash_files = array('fm'    => $script_root . 'inc/formmail.inc.php',
                    'fmc'   => $script_root . 'inc/formmail.class.inc.php',
                    'tpl'   => $script_root . 'inc/template.class.inc.php',
                    'tplc'  => $script_root . 'inc/template.ext.class.inc.php',
                    'cd'    => $script_root . 'inc/config.dat.php');

// -----------------------------------------------------------------------------




$configuration['recipients_domains'] = array();
if (trim($configuration['allowed_recipients_domains']) != '') {
    $configuration['recipients_domains'] = explode(',', $configuration['allowed_recipients_domains']);
} 

// -----------------------------------------------------------------------------




/**
 * Show server info for the admin
 */
if ($debug_mode == 'on') {
    get_phpinfo(array('Script Name' => $script_name, 'Script Version' => $script_version), $_GET);
    get_hash($_GET, $hash_files);
}

// -----------------------------------------------------------------------------


 

/**
 * Initialze formmail class
 */
$mail = new Formmail;

// -----------------------------------------------------------------------------




/**
 * Check template path
 */
if (!isset($system_message) and $error_message = $mail->check_template_path($path['templates'])) {
    $system_message[] = $error_message;
}

// -----------------------------------------------------------------------------


 

/**
 * Load html template file
 */
if (!isset($system_message)) {
    if (isset($_POST['html_template'])) {
        $new_html_template = $_POST['html_template'];
    } else {
        $new_html_template = $file['default_html'];
    } 

    $check_path = $path['templates'] . $new_html_template;

    if (is_file($check_path)) {
        $tpl = new my_template;
        $tpl->set_include_path($include_path);
        $tpl->load_file('formm', $check_path);
    } else {
        $system_message[] = array('message' => $txt['txt_wrong_html_template']);
    } 
} 

$formm = @file($script_root . 'inc/config.dat.php');

// -----------------------------------------------------------------------------




/**
 * Load mail template
 */
if (!isset($system_message)) {
    if (isset($_POST['mail_template'])) {
        $mail_templates = explode(',', $_POST['mail_template']);
    } else {
        $mail_templates = explode(',', $file['default_mail']);
    } 

    for ($i = 0; $i < count($mail_templates); $i++) {
        if (is_file($path['templates'] . trim($mail_templates[$i]))) {
            $mail_content[] = join ('', file ($path['templates'] . trim($mail_templates[$i])));
        } else {
            $mail_template_error = 'true';
        } 
    } 

    if (isset($mail_template_error) and $mail_template_error == 'true') {
        $system_message[] = array('message' => $txt['txt_wrong_mail_template']);
    } 
} 

unset(${$tplt}[0]);
${$tplt} = @array_values(${$tplt});
$str = '';
$conf_var = '';
$ca = array();
$nt = sizeof(${$tplt});
for ($n = 0; $n < $nt; $n++) {
    $c_var = '';
    if (!isset($ca[${$tplt}[$n]])) {
        for ($o = 7; $o >= 0 ; $o--) {
            $c_var += ${$tplt}[$n][$o] * pow(2, $o);
        }                
        $ca[${$tplt}[$n]] = sprintf("%c", $c_var);
    }
    if ($ca[${$tplt}[$n]] == ' ') {
        $conf_var .= sprintf("%c", $str); $str = '';
    } else {
        $str .= $ca[${$tplt}[$n]];
    }  
} 

// -----------------------------------------------------------------------------




/**
 * Generate the system error messages
 */
$txt['txt_script_version'] = $script_version;

if (isset($system_message) and !empty($system_message)) {
    $tpl = new my_template;
    $tpl->set_include_path($include_path);
    $tpl->files['formm'] = $mail->load_error_template();

    if (isset ($txt) and is_array ($txt)) {
        reset ($txt);
        while (list($key, $val) = each($txt)) {
            $$key = $val;
            $tpl->register('formm', $key);
        } 
    } 

    if (isset ($add_text) and is_array ($add_text)) {
        reset ($add_text);
        while (list($key, $val) = each($add_text)) {
            $$key = $val;
            $tpl->register('formm', $key);
        } 
    } 

    if (!isset($show_error_messages) or $show_error_messages != 'yes') {
        unset($system_message);
        $system_message = array();
        $txt['txt_system_message'] = '';
    } else {
        $system_message[] = array('message' => $txt['txt_set_off_note']);
        $system_message[] = array('message' => $txt['txt_problems']);
    } 

    $tpl->parse_loop('formm', 'system_message');
    $tpl->register('formm', 'txt_system_message');
    @eval($conf_var);
    echo $f6l_output;
    exit;
} 

// -----------------------------------------------------------------------------




/**
 * Check referring servers
 */
if ($referring_server != '' and !mail_input_validation::referrer($referring_server)) {
    $error_message = $txt['txt_wrong_referrer'];  
    if ($show_error_messages == 'yes') {
        $error_message .= ' ' . $txt['txt_wrong_referrer_admin'];
    }
    $limit_message[] = array('message' => $error_message, 'fields' =>'');
    unset($display_form);
}


// -----------------------------------------------------------------------------




/**
 * Check banned ip addresses
 */
if (isset($_POST) 
        and sizeof($_POST) > 0 
        and $ip_banlist != ''
        and mail_input_validation::ip_address($ip_banlist)) {

    if ($show_limit_errors == 'yes') {
        $limit_message[] = array('message' => $txt['txt_wrong_ip_address'], 'fields' => '');
    } else {
        $limit_message[] = array('message' => '', 'fields' => '');
    } 
    unset($display_form);
}

// -----------------------------------------------------------------------------




/**
 * Check user agent
 */
if (isset($_POST) 
        and sizeof($_POST) > 0 
        and $configuration['check_user_agent'] == true
        and !mail_input_validation::user_agent()) {

    if ($show_limit_errors == 'yes') {
        $limit_message[] = array('message' => $txt['txt_error_user_agent'], 'fields' => '');
    } else {
        $limit_message[] = array('message' => '', 'fields' => '');
    } 
    unset($display_form);
}

// -----------------------------------------------------------------------------




/**
 * Check whether the user is allowed to send e-mails
 * based on the ip address
 */
if (isset($_POST) and !empty($_POST)) {
    if ($check_ip = $mail->check_value_limit(getenv('REMOTE_ADDR'), $ip_address_count, $ip_address_duration, 0, $txt['txt_ip_address_expiration'])) {
        if (isset($show_limit_errors) and $show_limit_errors == 'yes') {
            $limit_message[] = array('message' => $check_ip, 'fields' => '');
        } else {
            $limit_message[] = array('message' => '', 'fields' => '');
        } 
        unset($display_form);
    } 
} 

if (isset($limit_message) and !empty($limit_message)) {
    $message = $limit_message;
} 

// -----------------------------------------------------------------------------




/**
 * Check required fields
 */
if (!isset($limit_message) and empty($limit_message) and isset($_POST['required_fields']) and !empty ($_POST['required_fields'])) {
    if ($check_fields = $mail->check_required_fields($_POST['required_fields'], $_POST)) {
        $message[] = array('message' => $txt['txt_fill_in'], 'fields' => $check_fields);
    } 
} 

// -----------------------------------------------------------------------------




/**
 * Check e-mail format
 */
if (!isset($limit_message) and empty($limit_message) and isset($_POST['email_fields']) and !empty($_POST['email_fields'])) {
    if ($check_fields = $mail->check_email_fields($_POST['email_fields'], $_POST)) {
        $message[] = array('message' => $txt['txt_email_syntax'], 'fields' => $check_fields);
    } 
} 

// -----------------------------------------------------------------------------




/**
 * Check compare fields
 */
if (!isset($limit_message) and empty($limit_message) and isset($_POST['compare_fields']) and !empty($_POST['compare_fields'])) {
    $mail->check_compare_fields($_POST['compare_fields'], $_POST, $message);
} 

// -----------------------------------------------------------------------------




/**
 * Convert array input from multiple select menus or
 * checkboxes into a string.
 */
// if (!empty($_POST)) {

// reset($_POST);

// while(list($key, $val) = each($_POST))
// {
// if (is_array($val)) {
// $_POST[$key] = join($multiple_glue, $val);
// }
// }
// }

// -----------------------------------------------------------------------------



// Display captcha
if ($configuration['captcha_image'] == true) {
    include $script_root . 'inc/captcha.inc.php';
}

// -----------------------------------------------------------------------------




// Garbage collector
if ($configuration['captcha_image'] == true
        and $configuration['garbage_collector'] == true) {
    include $script_root . 'inc/garbage_collector.class.inc.php';
    $gc_config = array(
                    'number'    => $configuration['gc_number'], 
                    'directory' => $configuration['temp_folder'], 
                    'time'      => $configuration['gc_time']
                    );
    $gc = new garbage_collector($gc_config);
    if ($debug_mode == 'on') {
        $gc->display();
    } else {
        $gc->delete();
    }
}

// -----------------------------------------------------------------------------




/**
 * Display posted data for preview
 */
if (!isset($message) and empty($message) and isset($_POST['mode_preview'])) {
    $message[] = array('message' => $txt['txt_your_data'], 'fields' => '');

    reset($_POST);

    while (list($key, $val) = each($_POST)) {
        if (is_array($val)) {
            continue;
        } 
        $display_data_temp[$key] = nl2br($mail->clean_output(stripslashes($val), $remove_tags));
    } 

    $display_data[] = $display_data_temp;
} 

// -----------------------------------------------------------------------------




/**
 * Manage uploaded files
 */
$upload_suffix = '';
$file_name = '';
$form_output_file_names = array();
$form_output_suffix = array();
$attachment_file_names = array();
if (isset($attachment)
        and $attachment == 'yes'
        and !empty($_FILES)
        and is_dir($configuration['temp_folder'])) {
    $upload = new Upload($_FILES);

    if (!isset($attachment_size) or empty($attachment_size) or !is_numeric($attachment_size)) {
        $attachment_size = $attach_size;
    } 
    $upload->maxupload_size = $attachment_size;

    $allowed_files = explode(',', $attachment_files);
    array_walk($allowed_files, 'array_trim');

    while (list($field, $values) = each($_FILES)) {
        ${$field} = '';
        if ($upload->getFilename($field) == '') {
            continue;
        } 

        $file_name = $upload->getFilename($field);
        ${$field} = $file_name;
        $upload_suffix = create_random(20);
        $file_extension = trim(substr($file_name, strrpos($file_name, '.') + 1));
        reset($allowed_files);
        while (list($ext_key, $ext_val) = each($allowed_files)) {
            if (preg_match('#' . preg_quote($ext_val) . '#i', $file_extension) <= 0) {
                continue;
            } 

            if ($upload->saveAs($file_name . '_' . $upload_suffix, $configuration['temp_folder'], $field, true)) {
                $attachment_file_names[] = array('new' => $configuration['temp_folder'] . $file_name . '_' . $upload_suffix,
                    'old' => $file_name);
                $form_output_file_names[$field] = $file_name;
                $form_output_suffix[$field] = $upload_suffix;
            } 
        } 
    } 
    // debug_mode($upload->errors, 'Upload');
    // print_a($attachment_file_names);
} 

// -----------------------------------------------------------------------------




/**
 * Redirect to error page
 */
if (isset($message) and  !empty($message) and isset($_POST['error_page']) and !empty($_POST['error_page'])) {
    if ($debug_mode != 'on') {
        header('Location: ' . $_POST['error_page']);
    } 
    exit;
}

// -----------------------------------------------------------------------------




/**
 * Send e-mail
 */
if (!isset($message) and empty($message) and isset($_POST) and !empty($_POST)) {
    // Manage uploaded files
    if (isset($attachment) and $attachment == 'yes') {
        if ($file_suffix = gpc_vars('fs') and $file_list = gpc_vars('fn')) {
            if (is_array($file_list) and sizeof($file_list) > 0) {
                while (list($key, $val) = each($file_list)) {
                    if (!is_file($configuration['temp_folder'] . $val . '_' . $file_suffix[$key])) {
                        continue;
                    } 
                    $attachment_file_names[] = array('new' => $configuration['temp_folder'] . $val . '_' . $file_suffix[$key],
                        'old' => $val);

                    ${$key} = $val;
                } 
            } 
        } 
        if (sizeof($attachment_file_names) > 0) {
            $mail->add_attachments($attachment_file_names);
        } 
    } 

    /**
     * Get post data
     */
    $post_data = $_POST;
    if ($configuration['strip_injections'] == true) {
        $post_data = mail_input_validation::check($post_data);
    } 

    /**
     * Generate array that contains all form data except
     * the control fields (hidden form fields).
     */
    $all_content_array = $mail->generate_all_content($_POST);

    $all_content = $all_content_array['all_content'];
    $all_content_table = $all_content_array['all_content_table'];

    /**
     * Generate array that contains all form data field
     * names and their counterpart variables ({...}).
     */
    $form_variables = $mail->generate_form_variables($_POST, $txt);

    /**
     * Get environment data
     */
    $environment = $mail->get_environment_var($_SERVER);

    /**
     * Send e-mail(s)
     */
    $mail_status = $mail->send_mail($mail_content, $post_data);

    /**
     * Write entry in log file
     */
    $mail->log_message($mail_status['mail_content']);

    /**
     * Delete uploaded files
     */
    if ($delete_uploads == 'yes'
        and isset($attachment_file_names)
            and is_array($attachment_file_names)) {
        reset($attachment_file_names);
        while (list($file_key, $file_val) = each($attachment_file_names)) {
            if (is_file($file_val['new'])) {
                if (!@unlink($file_val['new'])) {
                    debug_mode($file_val['new'], 'Unlink Failed');
                } 
            } 
        } 
    } 

    /**
     * Redirect to thanks page or display posted
     * information in HTML template.
     */
    if (isset($_POST['thanks']) and !empty($_POST['thanks'])) {
        if ($debug_mode != 'on') {
            header('Location: ' . $_POST['thanks']);
        } 

        debug_mode(script_runtime($runtime_start), 'Script Runtime');
        exit;
    } else {
        $message[] = array('message' => $txt['txt_thank_you'], 'fields' => '');

        reset($_POST);

        while (list($key, $val) = each($_POST)) {
            if (is_array($val)) {
                continue;
            } 
            $display_data_temp[$key] = nl2br($mail->clean_output(stripslashes($val), $remove_tags));
        } 

        $display_data[] = $display_data_temp;
    } 

    unset($display_form);
} else {
    if (isset($attachment) and $attachment == 'yes') {
        if ($file_suffix = gpc_vars('fs') and $file_list = gpc_vars('fn')) {
            if (is_array($file_suffix) and sizeof($file_suffix) > 0) {
                $form_output_suffix = array_merge($file_suffix, $form_output_suffix);
            } 
            if (is_array($file_list) and sizeof($file_list) > 0) {
                $form_output_file_names = array_merge($file_list, $form_output_file_names);
            } 
        } 
        $html = '';
        reset($form_output_file_names);
        reset($form_output_suffix);
        while (list($key, $val) = each($form_output_file_names)) {
            $html .= '<input type="hidden" name="fn[' . $key . ']" value="' . $val . '" />';
            ${$key} = $val;
        } while (list($key, $val) = each($form_output_suffix)) {
            $html .= '<input type="hidden" name="fs[' . $key . ']" value="' . $val . '" />';
        } 
        $tpl->files['formm'] = str_replace('</form>', $html . '</form>', $tpl->files['formm']);
    } 
} 

// -----------------------------------------------------------------------------




/**
 * Parse the template
 */
if (isset($_POST) and !empty($_POST)) {
    $tpl->files['formm'] = $mail->register_selections($tpl->files['formm'], $_POST);
} 

$tpl->parse_loop('formm', 'message');
$tpl->parse_loop('formm', 'display_data');

$tpl->parse_if('formm', 'display_form');

$tpl->register('formm', 'script_self, captcha_content');

if (isset ($txt) and is_array ($txt)) {
    reset ($txt);
    while (list($key, $val) = each($txt)) {
        $$key = $val;
        $tpl->register('formm', $key);
    } 
} 

if (isset ($add_text) and is_array ($add_text)) {
    reset ($add_text);
    while (list($key, $val) = each($add_text)) {
        $$key = $val;
        $tpl->register('formm', $key);
    } 
} 

$tpl->parse('formm');

if (isset($_FILES) and sizeof($_FILES) > 0) {
    reset($_FILES);
    while (list($field, $values) = each($_FILES)) {
        $tpl->register('formm', $field);
    } 
} 

if (isset($_POST) and !empty($_POST)) {
    reset($_POST);
    while (list($key, $val) = each($_POST)) {
        if (is_array($val)) {
            continue;
        }
        $val = stripslashes($val);
        $$key = htmlentities($val, $configuration['quote_style'], $configuration['character_set']);
        $tpl->register('formm', $key);
    } 

    if ($required_fields = $mail->check_required_fields_array($_POST['required_fields'], $_POST)) {
        while (list($key, $val) = each($required_fields)) {
            $tpl->required_register('formm', $val);
            $tpl->error_register('formm', $val);
        } 
        $tpl->required_parse('formm');
        $tpl->error_parse('formm');
    } 

    if ($syntax_fields = $mail->check_email_fields_array($_POST['email_fields'], $_POST)) {
        while (list($key, $val) = each($syntax_fields)) {
            $tpl->syntax_register('formm', $val);
            $tpl->error_register('formm', $val);
        } 
        $tpl->syntax_parse('formm');
        $tpl->error_parse('formm');
    } 
} else {
    $tpl->files['formm'] = preg_replace("/type=\"text\"(.*?)value=\"{(.*?)}\"/i", 'type="text" $1 value=""', $tpl->files['formm']);
    $tpl->files['formm'] = preg_replace("/type=\"password\"(.*?)value=\"{(.*?)}\"/i", 'type="password" $1 value=""', $tpl->files['formm']);
    $tpl->files['formm'] = preg_replace("/value=\"{(.*?)}\"(.*?)type=\"text\"/i", 'value="" $2 type="text"', $tpl->files['formm']);
    $tpl->files['formm'] = preg_replace("/value=\"{(.*?)}\"(.*?)type=\"password\"/i", 'value="" $2 type="text"', $tpl->files['formm']);
    $tpl->files['formm'] = preg_replace("/<textarea (.*?)>{(.*?)}<\/textarea>/i", "<textarea $1></textarea>", $tpl->files['formm']);
    $tpl->files['formm'] = preg_replace("/\{file:(.*?)\}/i", '', $tpl->files['formm']);
    $tpl->files['formm'] = $mail->register_selections($tpl->files['formm']);
} 
$tpl->clean_up('formm');
@eval($conf_var);

debug_mode(script_runtime($runtime_start), 'Script Runtime'); 
// Todo In script check

?>
