<?php
$FM_VERS = "9.10"; // script version

FMDebug('Submission to: ' . (isset($_SERVER['PHP_SELF']) ? $_SERVER['PHP_SELF'] : '') . ' from: ' .
        (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : ''));

if (isset($_SERVER['REQUEST_METHOD']) && strtoupper($_SERVER['REQUEST_METHOD']) === 'OPTIONS') {
	FMDebug('CORS OPTIONS request');
	CORS_Response();
	exit();
}

date_default_timezone_set('UTC'); 
$lNow = time();

ini_set('track_errors',1); 

$aAlertInfo = array();

$sLangID   = ""; 
$aMessages = array(); 

class ExecEnv
{

	private $_sPHPVersionString;

	private $_aPHPVersion;

	private $_sScript;

	function __construct()
	{
		$this->_Init();
		$this->_CheckVersion();
	}

	private function _Init()
	{
		$this->_sPHPVersionString = phpversion();
		$this->_aPHPVersion       = explode(".",$this->_sPHPVersionString);
	}

	public function getPHPVersion()
	{
		return $this->_aPHPVersion;
	}

	public function getPHPVersionString()
	{
		return $this->_sPHPVersionString;
	}

	private function _CheckVersion()
	{
		$s_req_string = "5.0.0"; 
		$a_too_old    = explode(".",$s_req_string);

		$i_cannot_use = ($a_too_old[0] * 10000) + ($a_too_old[1] * 100) + $a_too_old[2];

		$i_this_num = ($this->_aPHPVersion[0] * 10000) + ($this->_aPHPVersion[1] * 100) +
		              $this->_aPHPVersion[2];

		if ($i_this_num <= $i_cannot_use) {
			die(
			GetMessage(MSG_SCRIPT_VERSION,
			           array("PHPREQ" => $s_req_string,"PHPVERS" => $this->_sPHPVersionString)));
		}
	}


	public function IsPHPAtLeast($s_vers)
	{
		$a_test_version = explode(".",$s_vers);
		if (count($a_test_version) < 3) {
			return (false);
		}
		return ($this->_sPHPVersionString[0] > $a_test_version[0] || ($this->_sPHPVersionString[0] ==
		                                                              $a_test_version[0] &&
		                                                              ($this->_sPHPVersionString[1] >
		                                                               $a_test_version[1] ||
		                                                               $this->_sPHPVersionString[1] ==
		                                                               $a_test_version[1] &&
		                                                               $this->_sPHPVersionString[2] >=
		                                                               $a_test_version[2])));
	}

	public function GetScript()
	{
		if (!isset($this->_sScript)) {
			if (isset($_SERVER["PHP_SELF"]) &&
			    !empty($_SERVER["PHP_SELF"]) &&
			    isset($_SERVER["SERVER_NAME"]) &&
			    !empty($_SERVER["SERVER_NAME"])
			) {
				if (isset($_SERVER["SERVER_PORT"]) &&
				    $_SERVER["SERVER_PORT"] != 80
				) {
					if ($_SERVER["SERVER_PORT"] == 443) 
					{
						$this->_sScript = "https://" . $_SERVER["SERVER_NAME"] .
						                  $_SERVER["PHP_SELF"];
					} else
						
					{
						$this->_sScript = "http://" . $_SERVER["SERVER_NAME"] .
						                  ":" . $_SERVER["SERVER_PORT"] .
						                  $_SERVER["PHP_SELF"];
					}
				} else {
					$this->_sScript = "http://" . $_SERVER["SERVER_NAME"] .
					                  $_SERVER["PHP_SELF"];
				}
			} else {
				Error("no_php_self",GetMessage(MSG_NO_PHP_SELF),false,false);
			}
		}
		if ($b_with_qry) {
			return ($this->_sScript . (isset($_SERVER['QUERY_STRING']) && !empty($_SERVER['QUERY_STRING']) ?
					'?' . $_SERVER['QUERY_STRING'] : ''));
		} else {
			return ($this->_sScript);
		}
	}

	public function getINIBool($s_name)
	{
		$m_val = ini_get($s_name);
		if ($m_val !== null) {
			if (is_numeric($m_val)) {
				$m_val = (int)$m_val;
			} elseif (is_string($m_val)) {
				$m_val = strtolower($m_val);
				switch ($m_val) {
					case "1":
					case "on":
					case "true":
						$m_val = true;
						break;
					default:
						$m_val = false;
						break;
				}
			}
		}
		return ($m_val);
	}


	public function allowSessionURL()
	{
		$m_only_cookies = $this->getINIBool('session.use_only_cookies');
		if ($m_only_cookies === null) {
			$m_only_cookies = $ExecEnv->IsPHPAtLeast('5.3.0') ? true : false;
		}
		return (!$m_only_cookies);
	}
}

$ExecEnv = new ExecEnv();
if (!$ExecEnv->IsPHPAtLeast("5.3.0")) {

	@set_magic_quotes_runtime(0);
}

$aServerVars = &$_SERVER;
$aGetVars    = &$_GET;
$aFormVars   = &$_POST;
$aFileVars   = &$_FILES;
$aEnvVars    = &$_ENV;

$bIsGetMethod = false;
$bHasGetData  = false;

if (!isset($REAL_DOCUMENT_ROOT)) {
	SetRealDocumentRoot();
}

if (isset($aServerVars['SERVER_PORT'])) {
	$SCHEME = ($aServerVars['SERVER_PORT'] == 80) ? "http://" : "https://";
} else {
	$SCHEME = "";
}
if (isset($aServerVars['SERVER_NAME']) && $aServerVars['SERVER_NAME'] !== "") {
	$SERVER = $aServerVars['SERVER_NAME'];
} elseif (isset($aServerVars['SERVER_ADDR']) && $aServerVars['SERVER_ADDR'] !== "") {
	$SERVER = $aServerVars['SERVER_ADDR'];
} else {
	$SERVER = "";
}


$EMAIL_NAME = "^[-a-z0-9._]+"; 

$TARGET_EMAIL = array($EMAIL_NAME . "@oshrenovations\.com$");


$DEF_ALERT = "kmastin@sasktel.net";


$SITE_DOMAIN = "";


$SET_REAL_DOCUMENT_ROOT = ""; 


if (isset($SET_REAL_DOCUMENT_ROOT) && $SET_REAL_DOCUMENT_ROOT !== "") {
	$REAL_DOCUMENT_ROOT = $SET_REAL_DOCUMENT_ROOT;
}


$CONFIG_CHECK = array("TARGET_EMAIL");


$AT_MANGLE = "";


$TARGET_URLS = array(); 


$HEAD_CRLF = "\r\n";


$BODY_LF = "\r\n"; 



$FROM_USER = ""; 


$SENDMAIL_F_OPTION      = false;
$SENDMAIL_F_OPTION_LINE = __LINE__ - 1;


$FIXED_SENDER = "";


$SET_SENDER_FROM_EMAIL = false;


$INI_SET_FROM = false;


$LOGDIR = ""; 


$AUTORESPONDLOG = ""; 


$CSVDIR    = ""; 
$CSVSEP    = ","; 
$CSVINTSEP = ";"; 
$CSVQUOTE  = '"'; 
$CSVOPEN = ""; 
$CSVLINE = "\n"; 
$TEMPLATEDIR = ""; 
$TEMPLATEURL = ""; 
$MULTIFORMDIR = ""; 
$MULTIFORMURL = ""; 
$TEXT_SUBS = array(
	array("srch" => "/\\\\r\\\\n/","repl" => "\r\n",),
	array("srch" => "/\\\\n/","repl" => "\n",),
	array("srch" => "/\\\\t/","repl" => "\t",),
	array("srch" => "/\\[NL\\]/","repl" => "\n",),
	array("srch" => "/\\[TAB\\]/","repl" => "\t",),
	array("srch" => "/\\[NBSP\\]/","repl" => "&nbsp;",),
	array("srch" => "/\\[DQUOT\\]/","repl" => '"',),
	array("srch" => "/\\[SQUOT\\]/","repl" => "'",),
	array("srch" => "/\\[COLON\\]/","repl" => ":",),
	array("srch" => "/\\[SLOSH\\]/","repl" => "\\",),
	array("srch" => "/\\[OPCURL\\]/","repl" => "{",),
	array("srch" => "/\\[CLCURL\\]/","repl" => "}",),
	array("srch" => "/(on[a-z]*|href|src)\\s*=\\s*/i","repl" => ""), 
	array("srch" => "/<\\s*(table|tr|td|th|p|ul|ol|li|b|i|u|strong|pre|h[1-6]|em|dl|dd|dt|hr|span|br)(\\b[^>]*?)>/i",
	      "repl" => "<\$1\$2>",
	),
	array("srch" => "#<\\s*/\\s*(table|tr|td|th|p|ul|ol|li|b|i|u|strong|pre|h[1-6]|em|dl|dd|dt|hr|span|br)\\s*>#i",
	      "repl" => "</\$1>",
	),
);

$AUTHENTICATE = "";

$AUTH_USER = "";
$AUTH_PW   = "";
$FORM_INI_FILE = "";
$MODULEDIR = ".";
$FMCOMPUTE = "fmcompute.php";
$FMGEOIP = "fmgeoip.php";
$ADVANCED_TEMPLATES = false;
$LIMITED_IMPORT = true; 
$VALID_ENV = array('HTTP_REFERER','REMOTE_HOST','REMOTE_ADDR','REMOTE_USER',
                   'HTTP_USER_AGENT'
);

$FILEUPLOADS = false; 
$MAX_FILE_UPLOAD_SIZE = 0; 
$FILE_REPOSITORY = "";
$FILE_MODE = 0664;

$FILE_OVERWRITE = true;
$NEXT_NUM_FILE = "";
$PUT_DATA_IN_URL = true; 
$ALLOW_GET_METHOD = false;
$DB_SEE_INPUT = false; 
$DB_SEE_INI = false; 
$MAXSTRING = 1024;
$REQUIRE_CAPTCHA = ""; 
$RECAPTCHA_PRIVATE_KEY = "";
$bShowMesgNumbers = false;
$FILTERS = array("encode" => "$REAL_DOCUMENT_ROOT/cgi-bin/fmencoder -kpubkey.txt",
                 "null"   => "null",
                 "csv"    => "csv"
);

$SOCKET_FILTERS = array(
	"httpencode" => array("site"   => "YourSiteHere",
	                      "port"   => 80,
	                      "path"   => "/cgi-bin/fmencoder",
	                      "params" => array(array("name" => "key",
	                                              "file" => "$REAL_DOCUMENT_ROOT/cgi-bin/pubkey.txt"
	                                        )
	                      )
	),
	"sslencode"  => array("site"   => "ssl://YourSecureSiteHere",
	                      "port"   => 443,
	                      "path"   => "/cgi-bin/fmencoder",
	                      "params" => array(array("name" => "key",
	                                              "file" => "$REAL_DOCUMENT_ROOT/cgi-bin/pubkey.txt"
	                                        )
	                      )
	),
);

$FILTER_ATTRIBS = array("encode"     => "Strips,MIME=application/vnd.fmencoded,Encrypts",
                        "httpencode" => "Strips,MIME=application/vnd.fmencoded,Encrypts",
                        "sslencode"  => "Strips,MIME=application/vnd.fmencoded,Encrypts",
                        "csv"        => "Strips,MIME=text/csv",
);

$CHECK_FOR_NEW_VERSION = true;
$CHECK_DAYS            = 30;
$SCRATCH_PAD = "";
$CLEANUP_TIME = 60; 
$CLEANUP_CHANCE = 20; 
$PEAR_SMTP_HOST = "";
$PEAR_SMTP_PORT = 25;
$PEAR_SMTP_USER = "";
$PEAR_SMTP_PWD  = "";
$ALERT_ON_USER_ERROR = true;
$ENABLE_ATTACK_DETECTION = true;
$ATTACK_DETECTION_URL = "";
$ALERT_ON_ATTACK_DETECTION = false;
$ATTACK_DETECTION_MIME = true;
$ATTACK_DETECTION_JUNK                   = false;
$ATTACK_DETECTION_JUNK_CONSONANTS        = "bcdfghjklmnpqrstvwxz";
$ATTACK_DETECTION_JUNK_VOWELS            = "aeiouy";
$ATTACK_DETECTION_JUNK_CONSEC_CONSONANTS = 5;
$ATTACK_DETECTION_JUNK_CONSEC_VOWELS     = 4;
$ATTACK_DETECTION_JUNK_TRIGGER           = 2;
$ATTACK_DETECTION_JUNK_LANG_STRIP        = array(
	"aiia", /* Hawaiian */
	"aeoa", /* palaeoanthropic */
	"aeoe", /* palaeoethnic */
	"ayou", /* layout */
	"ayee", /* payee */
	"buyout", /* buyout */
	"clayey", /* clayey */
	"hooey", /* hooey */
	"ioau", /* radioautograph */
	"opoeia", /* pharmacopoeia, onomatopoeia */
	"ooee", /* cooee */
	"oyee", /* employee */
	"ioau", /* radioautograph */
	"uaia", /* guaiac */
	"uaya", /* uruguayan */
	"ueou", /* aqueous */
	"uiou", /* obsequious */
	"uoya", /* buoyant */
	"queue", /* queue, queueing */
	"earth", /* earthquake, earthslide */
	"cks", /* jockstrap, backscratcher */
	"ngth", /* strengths, length */
	"ndths", /* thousandths */
	"ght", /* nightclub, knightsbridge */
	"phth", /* ophthalmology */
	"sch", /* rothschild */
	"shch", /* borshch */
	"scr", /* corkscrew */
	"spr", /* wingspread, offspring */
	"str", /* armstrong, songstress */
	"sts", /* bursts, postscript */
	"tch", /* catchphrase, scratchproof */
	"thst", /* northstar, birthstone */
	"http", /* https, http */
	"html", /* HTML, XHTML */
);
$ATTACK_DETECTION_JUNK_IGNORE_FIELDS     = array();
$ATTACK_DETECTION_DUPS = array("realname","address1","address2","country","zip",
                               "phone","postcode","state","email"
);
$ATTACK_DETECTION_SPECIALS = true;
$ATTACK_DETECTION_SPECIALS_ONLY_EMAIL = array("derive_fields","required",
                                              "mail_options","good_url","bad_url","good_template",
                                              "bad_template"
);

$ATTACK_DETECTION_SPECIALS_ANY_EMAIL = array("subject");
$ATTACK_DETECTION_MANY_URLS = 0;
$ATTACK_DETECTION_MANY_URL_FIELDS = 0;
$ATTACK_DETECTION_URL_PATTERNS = array(
	'(^|[^-a-z_.0-9]+)(?<!@)([-a-z0-9]+\.)+(com|org|net|biz|info|name|pro|tel|asia|cat)\b',
	'(^|[^-a-z_.0-9]+)(?<!@)([-a-z0-9]+\.)+(com{0,1}|org|net)\.[a-z][a-z]\b'
);

$ATTACK_DETECTION_REVERSE_CAPTCHA = array();
$GEOIP_LIC = "";
$ZERO_IS_EMPTY = false;
$SESSION_NAME = "";
$SESSION_ACCESS = array();
$DESTROY_SESSION = true;
$HOOK_DIR = "";
class Settings
{

	private static function _check($s_name)
	{
		if (!array_key_exists($s_name,$GLOBALS)) {
			echo '<pre>';
			debug_print_backtrace();
			echo '</pre>';
			die("No FormMail setting called '$s_name' exists.");
		}
	}

	public static function isEmpty($s_name)
	{
		Settings::_check($s_name);
		if (gettype($GLOBALS[$s_name]) == 'string') {
			return ($GLOBALS[$s_name] === '');
		} else {
			return (empty($GLOBALS[$s_name]));
		}
	}

	public static function get($s_name)
	{
		Settings::_check($s_name);
		return ($GLOBALS[$s_name]);
	}

	public static function set($s_name,$m_value)
	{
		Settings::_check($s_name);
		if (($s_orig_type = gettype($GLOBALS[$s_name])) != ($s_new_type = gettype($m_value))) {
			echo '<pre>';
			debug_print_backtrace();
			echo '</pre>';
			die("You cannot set FormMail setting '$s_name' to type '$s_new_type'.  It should be type '$s_orig_type'.");
		}
		$GLOBALS[$s_name] = $m_value;
	}
}

define("EMAIL_NAME",Settings::get("EMAIL_NAME"));
define("DEF_ALERT",Settings::get("DEF_ALERT"));
define("AT_MANGLE",Settings::get("AT_MANGLE"));
define("HEAD_CRLF",Settings::get("HEAD_CRLF"));
define("BODY_LF",Settings::get("BODY_LF"));
define("SENDMAIL_F_OPTION",Settings::get("SENDMAIL_F_OPTION"));
define("SENDMAIL_F_OPTION_LINE",Settings::get("SENDMAIL_F_OPTION_LINE"));
define("SET_SENDER_FROM_EMAIL",Settings::get("SET_SENDER_FROM_EMAIL"));
define("INI_SET_FROM",Settings::get("INI_SET_FROM"));
define("ADVANCED_TEMPLATES",Settings::get("ADVANCED_TEMPLATES"));
define("LIMITED_IMPORT",Settings::get("LIMITED_IMPORT"));
define("FILEUPLOADS",Settings::get("FILEUPLOADS"));
define("MAX_FILE_UPLOAD_SIZE",Settings::get("MAX_FILE_UPLOAD_SIZE"));
define("FILE_MODE",Settings::get("FILE_MODE"));
define("FILE_OVERWRITE",Settings::get("FILE_OVERWRITE"));
define("PUT_DATA_IN_URL",Settings::get("PUT_DATA_IN_URL"));
define("DB_SEE_INPUT",Settings::get("DB_SEE_INPUT"));
define("DB_SEE_INI",Settings::get("DB_SEE_INI"));
define("MAXSTRING",Settings::get("MAXSTRING"));
define("CHECK_FOR_NEW_VERSION",Settings::get("CHECK_FOR_NEW_VERSION"));
define("CHECK_DAYS",Settings::get("CHECK_DAYS"));
define("ALERT_ON_USER_ERROR",Settings::get("ALERT_ON_USER_ERROR"));
define("ENABLE_ATTACK_DETECTION",Settings::get("ENABLE_ATTACK_DETECTION"));
define("ATTACK_DETECTION_URL",Settings::get("ATTACK_DETECTION_URL"));
define("ALERT_ON_ATTACK_DETECTION",Settings::get("ALERT_ON_ATTACK_DETECTION"));
define("ATTACK_DETECTION_MIME",Settings::get("ATTACK_DETECTION_MIME"));
define("ATTACK_DETECTION_JUNK",Settings::get("ATTACK_DETECTION_JUNK"));
define("ATTACK_DETECTION_JUNK_CONSONANTS",Settings::get("ATTACK_DETECTION_JUNK_CONSONANTS"));
define("ATTACK_DETECTION_JUNK_VOWELS",Settings::get("ATTACK_DETECTION_JUNK_VOWELS"));
define("ATTACK_DETECTION_JUNK_CONSEC_CONSONANTS",Settings::get("ATTACK_DETECTION_JUNK_CONSEC_CONSONANTS"));
define("ATTACK_DETECTION_JUNK_CONSEC_VOWELS",Settings::get("ATTACK_DETECTION_JUNK_CONSEC_VOWELS"));
define("ATTACK_DETECTION_JUNK_TRIGGER",Settings::get("ATTACK_DETECTION_JUNK_TRIGGER"));
define("ATTACK_DETECTION_SPECIALS",Settings::get("ATTACK_DETECTION_SPECIALS"));
define("ATTACK_DETECTION_MANY_URLS",Settings::get("ATTACK_DETECTION_MANY_URLS"));
define("ATTACK_DETECTION_MANY_URL_FIELDS",Settings::get("ATTACK_DETECTION_MANY_URL_FIELDS"));
define("ATTACK_DETECTION_IGNORE_ERRORS",Settings::get("ATTACK_DETECTION_IGNORE_ERRORS"));
define("ZERO_IS_EMPTY",Settings::get("ZERO_IS_EMPTY"));
define("DESTROY_SESSION",Settings::get("DESTROY_SESSION"));

if (IsAjax()) {
	Settings::set('ALLOW_GET_METHOD',true);
}

define('MSG_SCRIPT_VERSION',0); // This script requires at least PHP version...
define('MSG_END_VERS_CHK',1); // If you're happy...
define('MSG_VERS_CHK',2); // A later version of FormMail is available...
define('MSG_CHK_FILE_ERROR',3); // Unable to create check file...
define('MSG_UNK_VALUE_SPEC',4); // derive_fields: unknown value specification...
define('MSG_INV_VALUE_SPEC',5); // derive_fields: invalid value specification...
define('MSG_DERIVED_INVALID',6); // Some derive_fields specifications...
define('MSG_INT_FORM_ERROR',7); // Internal form error...
define('MSG_OPTIONS_INVALID',8); // Some mail_options settings...
define('MSG_PLSWAIT_REDIR',9); // Please wait while you are redirected...
define('MSG_IFNOT_REDIR',10); // If you are not redirected...
define('MSG_PEAR_OBJ',11); // Failed to create PEAR Mail object...
define('MSG_PEAR_ERROR',12); // PEAR Mail error...
define('MSG_NO_FOPT_ADDR',13); // You have specified "SendMailFOption"...
define('MSG_MORE_INFO',14); // More information...
define('MSG_INFO_STOPPED',15); // Extra alert information suppressed...
define('MSG_FM_ALERT',16); // FormMail alert
define('MSG_FM_ERROR',17); // FormMail script error
define('MSG_FM_ERROR_LINE',18); // The following error occurred...
define('MSG_USERDATA_STOPPED',19); // User data suppressed...
define('MSG_FILTERED',20); // This alert has been filtered...
define('MSG_TEMPLATES',21); // You must set either TEMPLATEDIR or TEMPLATEURL...
define('MSG_OPEN_TEMPLATE',22); // Failed to open template...
define('MSG_ERROR_PROC',23); // An error occurred while processing...
define('MSG_ALERT_DONE',24); // Our staff have been alerted...
define('MSG_PLS_CONTACT',25); // Please contact us directly...
define('MSG_APOLOGY',26); // We apologize for any inconvenience...
define('MSG_ABOUT_FORMMAIL',27); // Your form submission was processed by...
define('MSG_PREG_FAILED',28); // preg_match_all failed in FindCRMFields...
define('MSG_URL_INVALID',29); // CRM URL "$URL" is not valid...
define('MSG_URL_OPEN',30); // Failed to open Customer Relationship...
define('MSG_CRM_FAILED',31); // Failure report from CRM...
define('MSG_CRM_FORM_ERROR',32); // Your form submission was not...
define('MSG_OR',33); // "$ITEM1" or "$ITEM2"
define('MSG_NOT_BOTH',34); // not both "$ITEM1" and "$ITEM2"
define('MSG_XOR',35); // "$ITEM1" or "$ITEM2" (but not both)
define('MSG_IS_SAME_AS',36); // "$ITEM1" is the same as "$ITEM2"
define('MSG_IS_NOT_SAME_AS',37); // "$ITEM1" is not the same as "$ITEM2"
define('MSG_REQD_OPER',38); // Operator "$OPER" is not valid for "required"
define('MSG_PAT_FAILED',39); // Pattern operator "$OPER" failed: pattern...
define('MSG_COND_OPER',40); // Operator "$OPER" is not valid...
define('MSG_INV_COND',41); // Invalid "conditions" field...
define('MSG_COND_CHARS',42); // The conditions field "$FLD" is not valid...
define('MSG_COND_INVALID',43); // The conditions field "$FLD" is not valid...
define('MSG_COND_TEST_LONG',44); // Field "$FLD" has too many components...
define('MSG_COND_IF_SHORT',45); // Field "$FLD" has too few components for...
define('MSG_COND_IF_LONG',46); // Field "$FLD" has too many components for...
define('MSG_COND_UNK',47); // Field "$FLD" has an unknown command word...
define('MSG_MISSING',48); // Missing "$ITEM"...
define('MSG_NEED_ARRAY',49); // "$ITEM" must be an array...
define('MSG_SUBM_FAILED',50); // Your form submission has failed...
define('MSG_FILTER_WRONG',51); // Filter "$FILTER" is not properly...
define('MSG_FILTER_CONNECT',52); // Could not connect to site "$SITE"...
define('MSG_FILTER_PARAM',53); // Filter "$FILTER" has invalid parameter...
define('MSG_FILTER_OPEN_FILE',54); // Filter "$FILTER" cannot open file...
define('MSG_FILTER_FILE_ERROR',55); // Filter "$FILTER": read error on file...
define('MSG_FILTER_READ_ERROR',56); // Filter '$filter' failed: read error...
define('MSG_FILTER_NOT_OK',57); // Filter 'FILTER' failed...
define('MSG_FILTER_UNK',58); // Unknown filter...
define('MSG_FILTER_CHDIR',59); // Cannot chdir...
define('MSG_FILTER_NOTFOUND',60); // Cannot execute...
define('MSG_FILTER_ERROR',61); // Filter "$FILTER" failed...
define('MSG_SPARE',62); // this value is now spare
define('MSG_TEMPLATE_ERRORS',63); // Template "$NAME" caused the...
define('MSG_TEMPLATE_FAILED',64); // Failed to process template "$NAME"...
define('MSG_MIME_PREAMBLE',65); // (Your mail reader should not show this...
define('MSG_MIME_HTML',66); // This message has been generated by FormMail...
define('MSG_FILE_OPEN_ERROR',67); // Failed to open file "$NAME"...
define('MSG_ATTACH_DATA',68); // Internal error: AttachFile requires...
define('MSG_PHP_HTML_TEMPLATES',69); // HTMLTemplate option is only ...
define('MSG_PHP_FILE_UPLOADS',70); // For security reasons, file upload...
define('MSG_FILE_UPLOAD',71); // File upload attempt ignored...
define('MSG_FILE_UPLOAD_ATTACK',72); // Possible file upload attack...
define('MSG_PHP_PLAIN_TEMPLATES',73); // PlainTemplate option is only...
define('MSG_ATTACH_NAME',74); // filter_options: Attach must contain a name...
define('MSG_PHP_BCC',75); // Warning: BCC is probably not supported...
define('MSG_CSVCOLUMNS',76); // The "csvcolumns" setting is not...
define('MSG_CSVFILE',77); // The "csvfile" setting is not...
define('MSG_TARG_EMAIL_PAT_START',78); // Warning: Your TARGET_EMAIL pattern...
define('MSG_TARG_EMAIL_PAT_END',79); // Warning: Your TARGET_EMAIL pattern...
define('MSG_CONFIG_WARN',80); // The following potential problems...
define('MSG_PHP_AUTORESP',81); // Autorespond is only supported...
define('MSG_ALERT',82); // This is a test alert message...
define('MSG_NO_DEF_ALERT',83); // No DEF_ALERT value has been set....
define('MSG_TEST_SENT',84); // Test message sent.  Check your email.....
define('MSG_TEST_FAILED',85); // FAILED to send alert message...
define('MSG_NO_DATA_PAGE',86); // This URL is a Form submission program...
define('MSG_REQD_ERROR',87); // The form required some values that you...
define('MSG_COND_ERROR',88); // Some of the values you provided...
define('MSG_CRM_FAILURE',89); // The form submission did not succeed...
define('MSG_FOPTION_WARN',90); // Warning: You've used SendMailFOption in...
define('MSG_NO_ACTIONS',91); // The form has an internal error...
define('MSG_NO_RECIP',92); // The form has an internal error...
define('MSG_INV_EMAIL',93); // Invalid email addresses...
define('MSG_FAILED_SEND',94); // Failed to send email...
define('MSG_ARESP_EMAIL',96); // No "email" field was found. Autorespond...
define('MSG_ARESP_SUBJ',97); // Your form submission...
define('MSG_LOG_NO_VERIMG',98); // No VerifyImgString in session...
define('MSG_ARESP_NO_AUTH',99); // Failed to obtain authorization...
define('MSG_LOG_NO_MATCH',100); // User did not match image...
define('MSG_ARESP_NO_MATCH',101); // Your entry did not match...
define('MSG_LOG_FAILED',102); // Failed
define('MSG_ARESP_FAILED',103); // Autoresponder failed
define('MSG_LOG_OK',104); // OK
define('MSG_THANKS_PAGE',105); // Thanks!  We've received your....
define('MSG_LOAD_MODULE',106); // Cannot load module....
define('MSG_LOAD_FMCOMPUTE',107); // Cannot load FMCompute....
define('MSG_REGISTER_MODULE',108); // Cannot register module....
define('MSG_COMP_PARSE',109); // These parse errors occurred....
define('MSG_COMP_REG_DATA',110); // Failed to register data field....
define('MSG_COMP_ALERT',111); // The following alert messages....
define('MSG_COMP_DEBUG',112); // The following debug messages...
define('MSG_COMP_EXEC',113); // The following errors occurred....
define('MSG_REG_FMCOMPUTE',114); // Cannot register function...
define('MSG_USER_ERRORS',115); // A number of errors occurred...
define('MSG_CALL_PARAM_COUNT',116); // Invalid parameter count...
define('MSG_CALL_UNK_FUNC',117); // Unknown function...
define('MSG_SAVE_FILE',118); // Failed to save file....
define('MSG_CHMOD',119); // Failed to chmod file....
define('MSG_VERIFY_MISSING',120); // Image verification string missing...
define('MSG_VERIFY_MATCH',121); // Your entry did not match...
define('MSG_FILE_NAMES_INVALID',122); // Some file_names specifications...
define('MSG_FILE_NAMES_NOT_FILE',123); // Your file_names specification...
define('MSG_TEMPL_ALERT',124); // The following alert messages....
define('MSG_TEMPL_DEBUG',125); // The following debug messages...
define('MSG_TEMPL_PROC',126); // The following errors occurred....
define('MSG_SAVE_FILE_EXISTS',127); // Cannot save file....
define('MSG_EMPTY_ADDRESSES',128); // $COUNT empty addresses
define('MSG_CALL_INVALID_PARAM',129); // Invalid parameter....
define('MSG_INI_PARSE_WARN',130); // Warning: your INI
define('MSG_INI_PARSE_ERROR',131); // The FormMail INI...
define('MSG_RECAPTCHA_MATCH',132); // reCaptcha verification failed...

define('MSG_AND',133); // "$ITEM1" and "$ITEM2"
define('MSG_NEXT_PLUS_GOOD',134); // The form specifies both next_form and....
define('MSG_MULTIFORM',135); // You must set either MULTIFORMDIR or MULTIFORMURL...
define('MSG_MULTIFORM_FAILED',136); // Failed to process multi-page form template "$NAME"...
define('MSG_NEED_THIS_FORM',137); // Multi-page forms require "this_form" field...
define('MSG_NO_PHP_SELF',138); // PHP on the server is not providing "PHP_SELF"
define('MSG_RETURN_URL_INVALID',139); // Return "$URL" is not valid...
define('MSG_GO_BACK',140); // Cannot 'go back' if not a multi-page form...
define('MSG_OPEN_URL',141); // Cannot open URL...
define('MSG_CANNOT_RETURN',142); // Cannot return to page....
define('MSG_ATTACK_DETECTED',143); // Server attack detected....
define('MSG_ATTACK_PAGE',144); // Your form submission....
define('MSG_ATTACK_MIME_INFO',145); // The field "$FLD" contained...
define('MSG_ATTACK_DUP_INFO',146); // The fields "$FLD1" and...
define('MSG_ATTACK_SPEC_INFO',147); // Special field "$FLD"...
define('MSG_NEED_SCRATCH_PAD',148); // You need to set SCRATCH_PAD...
define('MSG_MULTI_UPLOAD',149); // File upload processing failed during multi-page form processing.
define('MSG_OPEN_SCRATCH_PAD',150); // Cannot open directory...
define('MSG_NO_NEXT_NUM_FILE',151); // You cannot use the %nextnum% feature...
define('MSG_NEXT_NUM_FILE',152); // Cannot process next number...
define('MSG_ATTACK_MANYURL_INFO',153); // Field "$FLD"...
define('MSG_ATTACK_MANYFIELDS_INFO',154); // $NUM fields have URLs....
define('MSG_REV_CAP',155); // ATTACK_DETECTION_REVERSE_CAPTCHA setting....
define('MSG_ATTACK_REV_CAP_INFO',156); // The field "$FLD" contained...
define('MSG_ATTACK_JUNK_INFO',157); // The field "$FLD" contained...
define('MSG_ARESP_EMPTY',158); // The autoresponse...
define('MSG_LOG_RECAPTCHA',159); // reCaptcha process failed...

define('MSG_URL_PARSE',160); // URL parse failed
define('MSG_URL_SCHEME',161); // Unsupported URL scheme...
define('MSG_SOCKET',162); // Socket error ...
define('MSG_GETURL_OPEN',163); // Open URL failed: ...
define('MSG_RESOLVE',164); // Cannot resolve...

define('MSG_FORM_OK',170); // Form Submission Succeeded
define('MSG_FORM_ERROR',171); // Form Submission Error
define('MSG_GET_DISALLOWED',172); // GET method has...

define('MSG_FILE_UPLOAD_ERR_UNK',180); // Unknown error code.
define('MSG_FILE_UPLOAD_ERR1',181); // The uploaded file exceeds the upload_max_filesize directive in php.ini.
define('MSG_FILE_UPLOAD_ERR2',182); // The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the html form.
define('MSG_FILE_UPLOAD_ERR3',183); // The uploaded file was only partially uploaded.
define('MSG_FILE_UPLOAD_ERR4',184); // No file was uploaded.
define('MSG_FILE_UPLOAD_ERR6',186); // Missing a temporary folder.
define('MSG_FILE_UPLOAD_ERR7',187); // Failed to write file to disk.
define('MSG_FILE_UPLOAD_ERR8',188); // File upload stopped by extension.
define('MSG_FILE_UPLOAD_SIZE',189); // Uploaded file "$NAME" is too big... (not a PHP error code - internal maximum file size error)

define('MSG_DER_FUNC_ERROR',200); // derive_fields: invalid function....
define('MSG_DER_FUNC_SIZE_FMT',201); // function 'size' requires....
define('MSG_DER_FUNC_IF_FMT',202); // function 'if' requires....
define('MSG_DER_FUNC_NEXTNUM_FMT',203); // function 'nextnum' requires....
define('MSG_DER_FUNC_EXT_FMT',204); // function 'ext' requires....
define('MSG_DER_FUNC1_FMT',205); // function 'FUNC' requires....
define('MSG_DER_FUNC_SUBSTR_FMT',206); // function 'substr' requires....

define('MSG_USER_ATTACK_JUNK',220); // The following input ...
define('MSG_USER_ATTACK_REV_CAP',221); // Your input ...
define('MSG_USER_ATTACK_DUP',222); // You have ...
define('MSG_USER_ATTACK_MANY_URLS',223); // Your input ...
define('MSG_USER_ATTACK_MANY_URL_FIELDS',224); // Your input ...

function IsBuiltInLanguage()
{
	global $sLangID;

	return (strpos($sLangID,"builtin") !== false);
}

$sSavePath  = "";
$bPathSaved = false;

function AddIncludePath($s_dir = ".")
{
	global $sSavePath,$bPathSaved;

	$s_path     = ini_get('include_path');
	$i_path_len = strlen($s_path);
	$s_sep      = IsServerWindows() ? ";" : ":"; 
	$b_found = false;
	$i_pos   = 0;
	$i_len   = strlen($s_dir);
	while (!$b_found && ($i_pos = strpos($s_path,$s_dir,$i_pos)) !== false) {
		if ($i_pos == 0) {
			if ($i_len == $i_path_len) {
				$b_found = true;
			} 
			elseif ($s_path[$i_len] == $s_sep) {
				$b_found = true;
			}
		} elseif ($s_path[$i_pos - 1] == $s_sep &&
		          ($i_pos + $i_len == $i_path_len ||
		           $s_path[$i_pos + $i_len] == $s_sep)
		) {
			$b_found = true;
		}
		if (!$b_found) {
			$i_pos++;
		}
	}
	if (!$b_found) {
		
		if (!$bPathSaved) {
			$sSavePath = $s_path;
		}
		if (empty($s_path)) {
			$s_path = $s_dir;
		} else

		{
			$s_path = $s_dir . $s_sep . $s_path;
		}
		ini_set('include_path',$s_path);
		$bPathSaved = true;
	}
}

function ResetIncludePath()
{
	global $sSavePath,$bPathSaved;

	if ($bPathSaved) {
		ini_set('include_path',$sSavePath);
		$bPathSaved = false;
	}
}

function LoadLanguageFile()
{
	global $aMessages,$sLangID,$sHTMLCharSet;

	AddIncludePath();
	if (!@include("language.inc.php")) {
		@include("language.inc");
	}
	ResetIncludePath();
	if (isset($sHTMLCharSet) && $sHTMLCharSet !== "") {
		header("Content-Type: text/html; charset=$sHTMLCharSet");
	}
}

function LoadBuiltinLanguage()
{
	global $aMessages,$sLangID;

	$sLangID = "English (builtin)";

	$aMessages[MSG_SCRIPT_VERSION] = 'This script requires at least PHP version ' .
	                                 '$PHPREQ.  You have PHP version $PHPVERS.';

	$aMessages[MSG_END_VERS_CHK] = '***************************************************\n' .
	                               'If you are happy with your current version and want\n' .
	                               'to stop these reminders, edit formmail.php and\n' .
	                               'set CHECK_FOR_NEW_VERSION to false.\n' .
	                               '***************************************************\n';


	$aMessages[MSG_VERS_CHK] = 'A later version of FormMail is available from $TECTITE.\n' .
	                           'You are currently using version $FM_VERS.\n' .
	                           'The new version available is $NEWVERS.\n';

	$aMessages[MSG_CHK_FILE_ERROR] = 'Unable to create check file "$FILE": $ERROR';

	$aMessages[MSG_UNK_VALUE_SPEC] = 'derive_fields: unknown value specification ' .
	                                 '"$SPEC"$MSG';

	$aMessages[MSG_INV_VALUE_SPEC] = 'derive_fields: invalid value specification ' .
	                                 '"$SPEC" (possibly missing a "%")';

	$aMessages[MSG_DERIVED_INVALID] = 'Some derive_fields specifications are invalid $MNUM:\n';

	$aMessages[MSG_INT_FORM_ERROR] = 'Internal form error';

	$aMessages[MSG_OPTIONS_INVALID] = 'Some $OPT settings are undefined $MNUM:\n';

	$aMessages[MSG_PLSWAIT_REDIR] = 'Please wait while you are redirected...';

	$aMessages[MSG_IFNOT_REDIR] = 'If you are not automatically redirected, ' .
	                              'please <a href="$URL">click here</a>.';

	$aMessages[MSG_PEAR_OBJ] = 'Failed to create PEAR Mail object';

	$aMessages[MSG_PEAR_ERROR] = 'PEAR Mail error: $MSG';

	$aMessages[MSG_NO_FOPT_ADDR] = 'You have specified "SendMailFOption" in your ' .
	                               'form, but there is no email address to use';

	$aMessages[MSG_MORE_INFO] = 'More information:';

	$aMessages[MSG_INFO_STOPPED] = '(Extra alert information suppressed for ' .
	                               'security purposes. $MNUM)';

	$aMessages[MSG_FM_ALERT] = 'FormMail alert';

	$aMessages[MSG_FM_ERROR] = 'FormMail script error';

	$aMessages[MSG_FM_ERROR_LINE] = 'The following error occurred in FormMail $MNUM:';

	$aMessages[MSG_USERDATA_STOPPED] = '(User data suppressed for security ' .
	                                   'purposes. $MNUM)';

	$aMessages[MSG_FILTERED] = 'This alert has been filtered through "$FILTER" ' .
	                           'for security purposes.';

	$aMessages[MSG_TEMPLATES] = 'You must set either TEMPLATEDIR or TEMPLATEURL ' .
	                            'in formmail.php before you can specify ' .
	                            'templates in your forms.';

	$aMessages[MSG_OPEN_TEMPLATE] = 'Failed to open template "$NAME" $MNUM: $ERROR';

	$aMessages[MSG_ERROR_PROC] = 'An error occurred while processing the ' .
	                             'form $MNUM.\n\n';

	$aMessages[MSG_ALERT_DONE] = 'The staff at $SERVER have been alerted to the error $MNUM.\n';

	$aMessages[MSG_PLS_CONTACT] = 'Please contact us ($SERVER) directly since this form ' .
	                              'is not working $MNUM.\n';

	$aMessages[MSG_APOLOGY] = '$SERVER apologizes for any inconvenience this error ' .
	                          'may have caused.';

	$aMessages[MSG_ABOUT_FORMMAIL] = 'Your form submission was processed by ' .
	                                 '<a href="http://$TECTITE/">FormMail</a> ' .
	                                 '($FM_VERS), a PHP script available from ' .
	                                 '<a href="http://$TECTITE/">$TECTITE</a>.';

	$aMessages[MSG_PREG_FAILED] = 'preg_match_all failed in FindCRMFields';

	$aMessages[MSG_URL_INVALID] = 'The URL "$URL" to access the Customer ' .
	                              'Relationship Management System is not valid ' .
	                              '(see TARGET_URLS in formmail.php)';

	$aMessages[MSG_URL_OPEN] = 'Failed to open Customer Relationship ' .
	                           'Management System URL "$URL" $MNUM: $ERROR';

	$aMessages[MSG_CRM_FAILED] = 'Failure report from Customer Relationship ' .
	                             'Management System (url="$URL") $MNUM: $MSG';

	$aMessages[MSG_CRM_FORM_ERROR] = 'Your form submission was not accepted';

	$aMessages[MSG_AND] = '"$ITEM1" and "$ITEM2"';

	$aMessages[MSG_OR] = '"$ITEM1" or "$ITEM2"';

	$aMessages[MSG_NOT_BOTH] = 'not both "$ITEM1" and "$ITEM2"';

	$aMessages[MSG_XOR] = '"$ITEM1" or "$ITEM2" (but not both)';

	$aMessages[MSG_IS_SAME_AS] = '"$ITEM1" is the same as "$ITEM2"';

	$aMessages[MSG_IS_NOT_SAME_AS] = '"$ITEM1" is not the same as "$ITEM2"';

	$aMessages[MSG_REQD_OPER] = 'Operator "$OPER" is not valid for "required"';

	$aMessages[MSG_PAT_FAILED] = 'Pattern operator "$OPER" failed: pattern ' .
	                             '"$PAT", value searched was "$VALUE".';

	$aMessages[MSG_COND_OPER] = 'Operator "$OPER" is not valid for "conditions"';

	$aMessages[MSG_INV_COND] = 'Invalid "conditions" field "$FLD" - not a string or array.';

	$aMessages[MSG_COND_CHARS] = 'The conditions field "$FLD" is not valid. ' .
	                             'You must provide the two separator ' .
	                             'characters at the beginning. You had "$COND".';

	$aMessages[MSG_COND_INVALID] = 'The conditions field "$FLD" is not valid. ' .
	                               'There must be at least 5 components ' .
	                               'separated by "$SEP". Your value was "$COND".';

	$aMessages[MSG_COND_TEST_LONG] = 'Field "$FLD" has too many components for ' .
	                                 'a "TEST" command: "$COND".\nAre you missing ' .
	                                 'a "$SEP"?';

	$aMessages[MSG_COND_IF_SHORT] = 'Field "$FLD" has too few components for ' .
	                                'an "IF" command: "$COND".\nThere must be ' .
	                                'at least 6 components separated by "$SEP"';

	$aMessages[MSG_COND_IF_LONG] = 'Field "$FLD" has too many components for ' .
	                               'an "IF" command: "$COND".\nAre you missing ' .
	                               'a "$SEP"?';

	$aMessages[MSG_COND_UNK] = 'Field "$FLD" has an unknown command word ' .
	                           '"$CMD": "$COND".';

	$aMessages[MSG_MISSING] = 'Missing "$ITEM"';

	$aMessages[MSG_NEED_ARRAY] = '"$ITEM" must be an array';

	$aMessages[MSG_SUBM_FAILED] = 'Your form submission has failed due to ' .
	                              'an error on our server.';

	$aMessages[MSG_FILTER_WRONG] = 'Filter "$FILTER" is not properly defined: ' .
	                               '$ERRORS';

	$aMessages[MSG_FILTER_CONNECT] = 'Could not connect to site "$SITE" ' .
	                                 'for filter "$FILTER" ($ERRNUM): $ERRSTR';

	$aMessages[MSG_FILTER_PARAM] = 'Filter "$FILTER" has invalid parameter ' .
	                               '#$NUM: no "$NAME"';

	$aMessages[MSG_FILTER_OPEN_FILE] = 'Filter "$FILTER" cannot open file ' .
	                                   '"$FILE": $ERROR';

	$aMessages[MSG_FILTER_FILE_ERROR] = 'Filter "$FILTER": read error on file ' .
	                                    '"$FILE" after $NLINES lines: $ERROR';

	$aMessages[MSG_FILTER_READ_ERROR] = 'Filter "$FILTER" failed: read error: ' .
	                                    '$ERROR';

	$aMessages[MSG_FILTER_NOT_OK] = 'Filter "$FILTER" failed (missing ' .
	                                '__OK__ line): $DATA';

	$aMessages[MSG_FILTER_UNK] = 'Unknown filter "$FILTER"';

	$aMessages[MSG_FILTER_CHDIR] = 'Cannot chdir to "$DIR" to run filter ' .
	                               '"$FILTER": $ERROR';

	$aMessages[MSG_FILTER_NOTFOUND] = 'Cannot execute filter "$FILTER" with ' .
	                                  'command "$CMD": $ERROR';

	$aMessages[MSG_FILTER_ERROR] = 'Filter "$FILTER" failed (status $STATUS): ' .
	                               '$ERROR';

	$aMessages[MSG_SPARE] = '';

	$aMessages[MSG_TEMPLATE_ERRORS] = 'Template "$NAME" caused the ' .
	                                  'following errors $MNUM:\n';

	$aMessages[MSG_TEMPLATE_FAILED] = 'Failed to process template "$NAME"';

	$aMessages[MSG_MIME_PREAMBLE] = '(Your mail reader should not show this ' .
	                                'text.\nIf it does you may need to ' .
	                                'upgrade to more modern software.)';

	$aMessages[MSG_MIME_HTML] = 'This message has been generated by FormMail ' .
	                            'using an HTML template\ncalled "$NAME". The ' .
	                            'raw text of the form results\nhas been ' .
	                            'included below, but your mail reader should ' .
	                            'display the HTML\nversion only (unless it\'s ' .
	                            'not capable of doing so).';

	$aMessages[MSG_FILE_OPEN_ERROR] = 'Failed to open $TYPE file "$NAME": $ERROR';

	$aMessages[MSG_ATTACH_DATA] = 'Internal error: AttachFile requires ' .
	                              '"tmp_name" or "data"';

	$aMessages[MSG_PHP_HTML_TEMPLATES] = '';

	$aMessages[MSG_PHP_FILE_UPLOADS] = '';

	$aMessages[MSG_FILE_UPLOAD] = 'File upload attempt ignored';

	$aMessages[MSG_FILE_UPLOAD_ATTACK] = 'Possible file upload attack ' .
	                                     'detected: field="$FLD", name="$NAME" ' .
	                                     'temp name="$TEMP"';

	$aMessages[MSG_PHP_PLAIN_TEMPLATES] = '';

	$aMessages[MSG_ATTACH_NAME] = 'filter_options: Attach must contain a name ' .
	                              '(e.g. Attach=data.txt)';

	$aMessages[MSG_PHP_BCC] = '';

	$aMessages[MSG_CSVCOLUMNS] = 'The "csvcolumns" setting is not ' .
	                             'valid: "$VALUE"';

	$aMessages[MSG_CSVFILE] = 'The "csvfile" setting is not valid: "$VALUE"';

	$aMessages[MSG_TARG_EMAIL_PAT_START] = 'Warning: Your TARGET_EMAIL pattern ' .
	                                       '"$PAT" is missing a ^ at the ' .
	                                       'beginning.';

	$aMessages[MSG_TARG_EMAIL_PAT_END] = 'Warning: Your TARGET_EMAIL pattern ' .
	                                     '"$PAT" is missing a $ at the end.';

	$aMessages[MSG_CONFIG_WARN] = 'The following potential problems were found ' .
	                              'in your configuration:\n$MESGS\n\n' .
	                              'These are not necessarily errors, but you ' .
	                              'should review the documentation\n' .
	                              'inside formmail.php.  If you are sure your ' .
	                              'configuration is correct\n' .
	                              'you can disable the above messages by ' .
	                              'changing the CONFIG_CHECK settings.';

	$aMessages[MSG_PHP_AUTORESP] = '';

	$aMessages[MSG_ALERT] = 'This is a test alert message $MNUM\n' .
	                        'Loaded language is $LANG\n' .
	                        'PHP version is $PHPVERS\n' .
	                        'FormMail version is $FM_VERS\n' .
	                        'Server type: $SERVER\n' .
	                        '\n' .
	                        'DOCUMENT_ROOT: $DOCUMENT_ROOT\n' .
	                        'SCRIPT_FILENAME: $SCRIPT_FILENAME\n' .
	                        'PATH_TRANSLATED: $PATH_TRANSLATED\n' .
	                        'REAL_DOCUMENT_ROOT: $REAL_DOCUMENT_ROOT';

	$aMessages[MSG_NO_DEF_ALERT] = 'No DEF_ALERT value has been set.';

	$aMessages[MSG_TEST_SENT] = 'Test message sent.  Check your email.';

	$aMessages[MSG_TEST_FAILED] = 'FAILED to send alert message.  Check your ' .
	                              'server error logs.';

	$aMessages[MSG_NO_DATA_PAGE] = 'This URL is a Form submission program.\n' .
	                               'It appears the form is not working ' .
	                               'correctly as there was no data found.\n' .
	                               'You\'re not supposed to browse to this ' .
	                               'URL; it should be accessed from a form.';

	$aMessages[MSG_REQD_ERROR] = 'The form required some values that you ' .
	                             'did not seem to provide.';

	$aMessages[MSG_COND_ERROR] = 'Some of the values you provided are not valid.';

	$aMessages[MSG_CRM_FAILURE] = 'The form submission did not succeed due to ' .
	                              'a CRM failure. URL was \'$URL\'. ' .
	                              'Returned CRM data:\n$DATA';

	$aMessages[MSG_FOPTION_WARN] = 'Warning: You\'ve used SendMailFOption in ' .
	                               '"mail_options" in your form. This has been ' .
	                               'superseded with a configuration setting ' .
	                               'inside formmail.php.  Please update your ' .
	                               'formmail.php configuration (look for ' .
	                               'SENDMAIL_F_OPTION on line $LINE) and set ' .
	                               'it to "true", then remove SendMailFOption ' .
	                               'from your form(s).';

	$aMessages[MSG_NO_ACTIONS] = 'The form has an internal error - no actions ' .
	                             'or recipients were specified.';

	$aMessages[MSG_NO_RECIP] = 'The form has an internal error - no valid ' .
	                           'recipients were specified.';

	$aMessages[MSG_INV_EMAIL] = 'Invalid email addresses were specified ' .
	                            'in the form $MNUM:\n$ERRORS';

	$aMessages[MSG_FAILED_SEND] = 'Failed to send email';

	$aMessages[MSG_ARESP_EMAIL] = 'No "email" field was found. Autorespond ' .
	                              'requires the submitter\'s email address.';

	$aMessages[MSG_ARESP_SUBJ] = 'Your form submission';

	$aMessages[MSG_LOG_NO_VERIMG] = 'No VerifyImgString or turing_string in session, ' .
	                                'no reverse CAPTCHA, no reCaptcha';

	$aMessages[MSG_ARESP_NO_AUTH] = 'Failed to obtain authorization to send ' .
	                                'you email. This is probably a fault on ' .
	                                'the server.';

	$aMessages[MSG_LOG_NO_MATCH] = 'User did not match image';

	$aMessages[MSG_LOG_RECAPTCHA] = 'reCaptcha process failed ($ERR)';

	$aMessages[MSG_ARESP_NO_MATCH] = 'Your entry did not match the image';

	$aMessages[MSG_LOG_FAILED] = 'Failed';

	$aMessages[MSG_ARESP_FAILED] = 'Autoresponder failed';

	$aMessages[MSG_LOG_OK] = 'OK';

	$aMessages[MSG_THANKS_PAGE] = 'Thanks!  We\'ve received your information ' .
	                              'and, if it\'s appropriate, we\'ll be in ' .
	                              'contact with you soon.';

	$aMessages[MSG_LOAD_MODULE] = 'Cannot load module from file \'$FILE\': $ERROR';

	$aMessages[MSG_LOAD_FMCOMPUTE] = 'Cannot load FMCompute module from file ' .
	                                 '\'$FILE\': $ERROR';

	$aMessages[MSG_REGISTER_MODULE] = 'Cannot register module $NAME with ' .
	                                  'FMCompute: $ERROR';

	$aMessages[MSG_COMP_PARSE] = 'These parse errors occurred in the following ' .
	                             'code:\n$ERRORS\n$CODE';

	$aMessages[MSG_COMP_REG_DATA] = 'Failed to register data field \'$NAME\': ' .
	                                '$ERROR';

	$aMessages[MSG_COMP_ALERT] = 'The following alert messages were reported ' .
	                             'from the FMCompute module: $ALERTS';

	$aMessages[MSG_COMP_DEBUG] = 'The following debug messages were reported ' .
	                             'from the FMCompute module: $DEBUG';

	$aMessages[MSG_COMP_EXEC] = 'The following error messages were reported ' .
	                            'from the FMCompute module: $ERRORS';

	$aMessages[MSG_TEMPL_ALERT] = 'The following alert messages were reported ' .
	                              'from the Advanced Template Processor: $ALERTS';

	$aMessages[MSG_TEMPL_DEBUG] = 'The following debug messages were reported ' .
	                              'from the Advanced Template Processor: $DEBUG';

	$aMessages[MSG_TEMPL_PROC] = 'The following error messages were reported ' .
	                             'from the Advanced Template Processor: $ERRORS';

	$aMessages[MSG_REG_FMCOMPUTE] = 'Cannot register function "$FUNC" with ' .
	                                'FMCompute: $ERROR';

	$aMessages[MSG_USER_ERRORS] = 'One or more errors occurred in your form submission';

	$aMessages[MSG_CALL_PARAM_COUNT] = 'FMCompute called FormMail function ' .
	                                   '\'$FUNC\' with wrong number of ' .
	                                   'parameters: $COUNT';

	$aMessages[MSG_CALL_UNK_FUNC] = 'FMCompute called unknown FormMail function ' .
	                                '\'$FUNC\'';

	$aMessages[MSG_SAVE_FILE] = 'Failed to save file \'$FILE\' to \'$DEST\': $ERR';

	$aMessages[MSG_SAVE_FILE_EXISTS] = 'Cannot save file to repository as this would ' .
	                                   'overwrite \'$FILE\' and you have ' .
	                                   'set FILE_OVERWRITE to false.';

	$aMessages[MSG_EMPTY_ADDRESSES] = '$COUNT empty addresses';

	$aMessages[MSG_CALL_INVALID_PARAM] = 'FMCompute called FormMail function ' .
	                                     '\'$FUNC\' with an invalid parameter ' .
	                                     'number $PARAM. Correct values are: $CORRECT';

	$aMessages[MSG_INI_PARSE_WARN] = 'Warning: your INI file \'$FILE\' appears ' .
	                                 'to be empty.  This may indicate a syntax error.';

	$aMessages[MSG_INI_PARSE_ERROR] = 'The FormMail INI file \'$FILE\' has a syntax error';

	$aMessages[MSG_CHMOD] = 'Failed to change protection mode of file \'$FILE\' ' .
	                        'to $MODE: $ERR';

	$aMessages[MSG_VERIFY_MISSING] = 'Image verification string missing. This' .
	                                 ' is probably a fault on the server.';

	$aMessages[MSG_VERIFY_MATCH] = 'Your entry did not match the image';

	$aMessages[MSG_RECAPTCHA_MATCH] = 'reCaptcha verification failed ($ERR)';

	$aMessages[MSG_FILE_NAMES_INVALID] = 'Some file_names specifications are invalid $MNUM:\n';

	$aMessages[MSG_FILE_NAMES_NOT_FILE] = 'Your file_names specification has ' .
	                                      'an error. \'$NAME\' is not the name ' .
	                                      'of a file upload field\n';

	$aMessages[MSG_NEXT_PLUS_GOOD] = 'The form has specified both "next_form" ' .
	                                 'and "$WHICH" fields - the action to ' .
	                                 'to perform is ambiguous';

	$aMessages[MSG_MULTIFORM] = 'You must set either MULTIFORMDIR or MULTIFORMURL ' .
	                            'in formmail.php before you can use ' .
	                            'multi-page forms.';

	$aMessages[MSG_MULTIFORM_FAILED] = 'Failed to process multi-page form template "$NAME"';

	$aMessages[MSG_NEED_THIS_FORM] = 'Multi-page forms require "this_form" field';

	$aMessages[MSG_NO_PHP_SELF] = 'PHP on the server is not providing "PHP_SELF"';

	$aMessages[MSG_RETURN_URL_INVALID] = 'Return URL "$URL" is not valid';

	$aMessages[MSG_GO_BACK] = 'Cannot "go back" if not in a multi-page form ' .
	                          'sequence or at the first page of the form ' .
	                          'sequence';

	$aMessages[MSG_OPEN_URL] = 'Cannot open URL "$URL": $ERROR';

	$aMessages[MSG_CANNOT_RETURN] = 'Cannot return to page $TO.  The top page ' .
	                                'index is $TOPINDEX';

	$aMessages[MSG_ATTACK_DETECTED] = 'Server attack "$ATTACK" detected. ' .
	                                  'Your server is safe as FormMail is ' .
	                                  'invulnerable to this attack.  You can ' .
	                                  'disable these messages by setting ' .
	                                  'ALERT_ON_ATTACK_DETECTION to false ' .
	                                  'in FormMail\'s configuration section.' .
	                                  '\nMore information:\n$INFO';

	$aMessages[MSG_ATTACK_PAGE] = 'Your form submission has been rejected ' .
	                              'as it appears to be an abuse of our server (' .
	                              '$SERVER).<br />' .
	                              'Our supplier of forms processing software has ' .
	                              'provided <a href="http://www.tectite.com/serverabuse.php" ' .
	                              ' target="_blank">more information about this error</a>.<br /><br />' .
	                              '$USERINFO';

	$aMessages[MSG_ATTACK_MIME_INFO] = 'The field "$FLD" contained invalid ' .
	                                   'content "$CONTENT"';

	$aMessages[MSG_ATTACK_DUP_INFO] = 'The fields "$FLD1" and "$FLD2" contained ' .
	                                  'duplicate data';

	$aMessages[MSG_ATTACK_SPEC_INFO] = 'Special field "$FLD" contained an email address';

	$aMessages[MSG_ATTACK_MANYURL_INFO] = 'Field "$FLD" contained $NUM URLs';

	$aMessages[MSG_ATTACK_MANYFIELDS_INFO] = '$NUM fields contained URLs: $FLDS';

	$aMessages[MSG_REV_CAP] = 'ATTACK_DETECTION_REVERSE_CAPTCHA is not set correctly, ' .
	                          'and will be ignored. Please refer to the documentation ' .
	                          'to make the correct setting.';

	$aMessages[MSG_ATTACK_REV_CAP_INFO] = 'The field "$FLD" contained unexpected ' .
	                                      'content "$CONTENT".';

	$aMessages[MSG_ATTACK_JUNK_INFO] = 'The field "$FLD" contained junk ' .
	                                   'data "$JUNK"';

	$aMessages[MSG_ARESP_EMPTY] = 'The autoresponse is empty.  The form ' .
	                              'requested $TYPE';


	$aMessages[MSG_NEED_SCRATCH_PAD] = 'You need to set SCRATCH_PAD in the ' .
	                                   'configuration section to process ' .
	                                   'uploaded files.';

	$aMessages[MSG_OPEN_SCRATCH_PAD] = 'Cannot open SCRATCH_PAD directory ' .
	                                   '"$DIR".  Open failed: $ERR';


	$aMessages[MSG_NO_NEXT_NUM_FILE] = 'You cannot use the %nextnum% feature: ' .
	                                   'you have not configured NEXT_NUM_FILE';


	$aMessages[MSG_NEXT_NUM_FILE] = 'Cannot $ACT next number file ' .
	                                '\'$FILE\': $ERR';

	$aMessages[MSG_MULTI_UPLOAD] = 'File upload processing failed during ' .
	                               'multi-page form processing.';

	$aMessages[MSG_URL_PARSE] = 'Failed to parse URL';

	$aMessages[MSG_URL_SCHEME] = 'Unsupported URL scheme "$SCHEME"';

	$aMessages[MSG_SOCKET] = 'Socket error $ERRNO: $ERRSTR: $PHPERR';

	$aMessages[MSG_GETURL_OPEN] = 'Open URL failed: $STATUS URL=$URL';

	$aMessages[MSG_RESOLVE] = 'Cannot resolve host name "$NAME"';

	$aMessages[MSG_FORM_OK] = 'Form Submission Succeeded';

	$aMessages[MSG_FORM_ERROR] = 'Form Submission Error';

	$aMessages[MSG_GET_DISALLOWED] = 'GET method has been disabled.  Forms must use ' .
	                                 'the POST method. Alternatively, reconfigure ' .
	                                 'FormMail to allow the GET method.';

	$aMessages[MSG_FILE_UPLOAD_ERR1] = 'The uploaded file exceeds the upload_max_filesize directive in php.ini.';
	$aMessages[MSG_FILE_UPLOAD_ERR2] = 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the html form.';
	$aMessages[MSG_FILE_UPLOAD_ERR3] = 'The uploaded file was only partially uploaded.';
	$aMessages[MSG_FILE_UPLOAD_ERR4] = 'No file was uploaded.';
	$aMessages[MSG_FILE_UPLOAD_ERR6] = 'Missing a temporary folder.';
	$aMessages[MSG_FILE_UPLOAD_ERR7] = 'Failed to write file to disk.';
	$aMessages[MSG_FILE_UPLOAD_ERR8] = 'File upload stopped by extension.';

	$aMessages[MSG_FILE_UPLOAD_ERR_UNK] = 'Unknown file upload error code $ERRNO';

	$aMessages[MSG_FILE_UPLOAD_SIZE] = 'Uploaded file "$NAME" is too big (' .
	                                   '$SIZE bytes). The maximum permitted ' .
	                                   'size is $MAX kilobytes.';

	$aMessages[MSG_DER_FUNC_ERROR] = 'derive_fields: invalid function specification ' .
	                                 '"$SPEC": $MSG';

	$aMessages[MSG_DER_FUNC_SIZE_FMT] = '"size" function requires this format: ' .
	                                    'size(file_field)';

	$aMessages[MSG_DER_FUNC_IF_FMT] = '"if" function requires this format: ' .
	                                  'if(field;spec;spec)';

	$aMessages[MSG_DER_FUNC_NEXTNUM_FMT] = '"nextnum" function requires this format: ' .
	                                       'nextnum(pad) or nextnum(pad;base).  pad and base ' .
	                                       'must be numbers. base must be 2 to 36 inclusive';

	$aMessages[MSG_DER_FUNC_EXT_FMT] = '"ext" function requires this format: ' .
	                                   'ext(file_field)';

	$aMessages[MSG_DER_FUNC1_FMT] = '"$FUNC" function requires this format: ' .
	                                '$FUNC(fieldname)';

	$aMessages[MSG_DER_FUNC_SUBSTR_FMT] = '"substr" function requires this format: ' .
	                                      'substr(fieldname;start) or ' .
	                                      'substr(fieldname;start;length) - ' .
	                                      'start and length must be numbers.';

	$aMessages[MSG_USER_ATTACK_JUNK] = 'The following input looks like a junk attack ' .
	                                   'on our server.  Please avoid scientific ' .
	                                   'or technical terms with long sequences ' .
	                                   'of consonants or vowels: $INPUT';

	$aMessages[MSG_USER_ATTACK_REV_CAP] = 'Your input looks like an automated spambot ' .
	                                      'attacking our server.  Some automatic form ' .
	                                      'fillers can trigger this detection. Try ' .
	                                      'filling in our form manually. If you use the ' .
	                                      'back button to go back, make sure you ' .
	                                      'refresh the page before trying again.';

	$aMessages[MSG_USER_ATTACK_DUP] = 'You have input the same information in ' .
	                                  'several fields in the form. Please ' .
	                                  're-submit the form without duplication';

	$aMessages[MSG_USER_ATTACK_MANY_URLS] = 'Your input includes a number of URLs. ' .
	                                        'This server has been configured to reject ' .
	                                        'form submissions with too many URLs. ' .
	                                        'Please re-submit the form without URLs or ' .
	                                        'with fewer URLs.';

	$aMessages[MSG_USER_ATTACK_MANY_URL_FIELDS] = $aMessages[MSG_USER_ATTACK_MANY_URLS];
}
if (isset($aServerVars["REQUEST_METHOD"]) && $aServerVars["REQUEST_METHOD"] === "GET") {
	$bIsGetMethod = true;
	if (Settings::get('ALLOW_GET_METHOD')) {
		$aFormVars = &$_GET;
	} elseif (count($_GET) > 0) {
		$bHasGetData = true;
	}
}

function LoadLanguage()
{
	LoadBuiltinLanguage();
	LoadLanguageFile();
}

function CheckString($ss)
{
	return (isset($ss) ? $ss : "");
}

$aGetMessageSubstituteErrors   = array();
$aGetMessageSubstituteFound    = array();
$bGetMessageSubstituteNoErrors = false;

function GetMessageSubstituteParam($a_matches)
{
	global $aGetMessageValues,$aGetMessageSubstituteErrors;
	global $aGetMessageSubstituteFound,$bGetMessageSubstituteNoErrors;

	$s_name                       = $a_matches[1];
	$aGetMessageSubstituteFound[] = $s_name;
	$s_value                      = "";
	if (isset($aGetMessageValues[$s_name])) {
		$s_value = $aGetMessageValues[$s_name];
	} elseif ($bGetMessageSubstituteNoErrors) {
		$s_value = '$' . $s_name;
	} else {
		$aGetMessageSubstituteErrors[] = $s_name;
	}
	return ($s_value);
}

function GetMessage($i_msg_num,$a_params = array(),
                    $b_show_mnum = true,$b_no_errors = false)
{
	global $aMessages,$sLangID;

	if (!isset($aMessages[$i_msg_num])) {
		SendAlert("Unknown Message Number $i_msg_num was used",false,true);
		$s_text = "<UNKNOWN MESSAGE NUMBER>";
	} else {
		$s_text = $aMessages[$i_msg_num];
	}
	$s_mno = Settings::get('bShowMesgNumbers') ? "[M$i_msg_num]" : "";

	$s_orig_text = $s_text;

	if (strpos($s_text,'$') !== false) {
		global $aGetMessageValues,$aGetMessageSubstituteErrors;
		global $aGetMessageSubstituteFound,$bGetMessageSubstituteNoErrors;

		$aGetMessageSubstituteErrors   = array();
		$aGetMessageSubstituteFound    = array();
		$aGetMessageValues             = HTMLEntitiesArray($a_params,true);
		$bGetMessageSubstituteNoErrors = $b_no_errors;
		$aGetMessageValues["MNUM"]     = $s_mno; // add the message number

		$s_text = preg_replace_callback('/\$([a-z][a-z0-9_]*)/i',
		                                'GetMessageSubstituteParam',$s_text);
		if (count($aGetMessageSubstituteErrors) > 0) {
			SendAlert("Message Number $i_msg_num ('$s_orig_text') in language $sLangID " .
			          "specified the following unsupported parameters: " .
			          implode(',',$aGetMessageSubstituteErrors));
		}
		if (!in_array("MNUM",$aGetMessageSubstituteFound))

		{
			$s_text .= $b_show_mnum ? " $s_mno" : "";
		}
	} else

	{
		$s_text .= $b_show_mnum ? " $s_mno" : "";
	}

	return (str_replace('\n',"\n",$s_text));
}

function IsServerWindows()
{
	static $bGotAnswer = false;
	static $bAnswer;

	if (!$bGotAnswer) {
		if ((isset($_ENV["OS"]) && stristr($_ENV["OS"],"windows") !== false) ||
		    (isset($_SERVER["PATH"]) && stristr($_SERVER["PATH"],"winnt") !== false) ||
		    (isset($_SERVER["PATH"]) && stristr($_SERVER["PATH"],"windows") !== false) ||
		    (isset($_SERVER["SystemRoot"]) && stristr($_SERVER["SystemRoot"],"winnt") !== false) ||
		    (isset($_ENV["SystemRoot"]) && stristr($_ENV["SystemRoot"],"winnt") !== false) ||
		    (isset($_SERVER["SystemRoot"]) && stristr($_SERVER["SystemRoot"],"windows") !== false) ||
		    (isset($_ENV["SystemRoot"]) && stristr($_ENV["SystemRoot"],"windows") !== false) ||
		    (isset($_SERVER["Path"]) && stristr($_SERVER["Path"],"windows") !== false)
		) {
			$bAnswer = true;
		} else {
			$bAnswer = false;
		}
		$bGotAnswer = true;
	}
	return ($bAnswer);
}

function GetScratchPadFile($s_prefix)
{

	switch (substr(Settings::get('SCRATCH_PAD'),-1)) {
		case '/':
		case '\\':
			$s_dir = substr(Settings::get('SCRATCH_PAD'),0,-1);
			break;
		default:
			$s_dir = Settings::get('SCRATCH_PAD');
			break;
	}

	do {
		$i_rand = mt_rand(0,16777215); // 16777215 is FFFFFF in hex
		$s_name = $s_dir . "/" . $s_prefix . sprintf("%06X",$i_rand);
	} while (file_exists($s_name));
	return ($s_name);
}

function GetTempName($s_prefix)
{
	if (!Settings::isEmpty('SCRATCH_PAD')) {
		$s_name = GetScratchPadFile($s_prefix);
	} else {
		$s_name = tempnam("/tmp",$s_prefix);
	}
	return ($s_name);
}

function GetTempDir()
{
	$s_name = GetTempName("fm");
	if (file_exists($s_name)) {
		unlink($s_name);
	}
	$s_dir = dirname($s_name);
	return ($s_dir);
}

define('DEBUG',false); // for production

define('RFCLINELEN',76); // recommend maximum line length from RFC 2822

$sUserAgent = "FormMail/$FM_VERS (from www.tectite.com)";

if (DEBUG) {
	error_reporting(E_ALL); // trap everything!
	ini_set("display_errors","stdout");
	ini_set("display_startup_errors","1");
	assert_options(ASSERT_ACTIVE,true);
	assert_options(ASSERT_BAIL,true);
	LoadLanguage();
} else {
	$iOldLevel = error_reporting(E_ALL ^ E_WARNING);
	LoadLanguage();

	error_reporting(E_ALL ^ E_WARNING ^ E_NOTICE);
}

function SetRealDocumentRoot()
{
	global $aServerVars,$REAL_DOCUMENT_ROOT;

	if (isset($aServerVars['SCRIPT_FILENAME'])) {
		$REAL_DOCUMENT_ROOT = $aServerVars['SCRIPT_FILENAME'];
	} elseif (isset($aServerVars['PATH_TRANSLATED'])) {
		$REAL_DOCUMENT_ROOT = $aServerVars['PATH_TRANSLATED'];
	} else {
		$REAL_DOCUMENT_ROOT = "";
	}

	if (($i_pos = strpos($REAL_DOCUMENT_ROOT,"/www/")) !== false) {
		$REAL_DOCUMENT_ROOT = substr($REAL_DOCUMENT_ROOT,0,$i_pos + 4);
	} elseif (($i_pos = strpos($REAL_DOCUMENT_ROOT,"/public_html/")) !== false) {
		$REAL_DOCUMENT_ROOT = substr($REAL_DOCUMENT_ROOT,0,$i_pos + 12);
	} elseif (!empty($REAL_DOCUMENT_ROOT)) {
		$REAL_DOCUMENT_ROOT = dirname($REAL_DOCUMENT_ROOT);
	} elseif (isset($aServerVars['DOCUMENT_ROOT']) &&
	          !empty($aServerVars['DOCUMENT_ROOT'])
	) {
		$REAL_DOCUMENT_ROOT = $aServerVars['DOCUMENT_ROOT'];
	}
}

if (!Settings::isEmpty('HOOK_DIR')) {
	if (!@include(Settings::get('HOOK_DIR') . "/fmhookpreinit.inc.php")) {
		@include(Settings::get('HOOK_DIR') . "/fmhookpreinit.inc");
	}
}

if (!Settings::isEmpty('SESSION_NAME')) {
	session_name(Settings::get('SESSION_NAME'));
}

function GetSession($s_name)
{
	return (isset($_SESSION) ? $_SESSION[$s_name] : null);
}

function IsSetSession($s_name)
{
	return (isset($_SESSION) && isset($_SESSION[$s_name]));
}

function SetSession($s_name,$m_value)
{
	$_SESSION[$s_name] = $m_value;
}

function UnsetSession($s_name)
{
	$_SESSION[$s_name] = null;
	unset($_SESSION[$s_name]);
}

function ZapSession()
{
	global $aSessionVarNames;

	if (Settings::get('DESTROY_SESSION')) {
		if (session_id() != '') {
			session_destroy();
		}
	} else {
		foreach ($aSessionVarNames as $s_var_name) {
			UnsetSession($s_var_name);
		}
	}
}

$bReverseCaptchaCompleted = false; // records whether ATTACK_DETECTION_REVERSE_CAPTCHA has been completed successfully
session_start();

if (!Settings::isEmpty('HOOK_DIR')) {
	if (!@include(Settings::get('HOOK_DIR') . "/fmhookpostsess.inc.php")) {
		@include(Settings::get('HOOK_DIR') . "/fmhookpostsess.inc");
	}
}

$aSessionVarNames = array("FormError","FormErrorInfo","FormErrorCode",
                          "FormErrorItems","FormData","FormIsUserError",
                          "FormAlerted","FormSavedFiles","FormIndex",
                          "FormList","FormKeep","VerifyImgString",
                          "turing_string"
);

UnsetSession("FormError"); // start with no error
UnsetSession("FormErrorInfo"); // start with no error
UnsetSession("FormErrorCode"); // start with no error
UnsetSession("FormErrorItems"); // start with no error
UnsetSession("FormData"); // start with no data
UnsetSession("FormIsUserError"); // start with no data
UnsetSession("FormAlerted"); // start with no data

$SPECIAL_FIELDS = array(
	"email", // email address of the person who filled in the form
	"realname", // the real name of the person who filled in the form
	"recipients", // comma-separated list of email addresses to which we'll send the results
	"cc", // comma-separated list of email addresses to which we'll CC the results
	"bcc", // comma-separated list of email addresses to which we'll BCC the results
	"replyto", // comma-separated list of email addresses to whom replies should be sent
	"required", // comma-separated list of fields that must be found in the input
	"conditions", // complex condition tests
	"fmcompute", // computations
	"fmmodules", // list of modules required
	"fmmode", // mode of operation
	"mail_options", // comma-separated list of options
	"good_url", // URL to go to on success
	"good_template", // template file to display on success
	"bad_url", // URL to go to on error
	"bad_template", // template file to display on error
	"template_list_sep", // separator when expanding lists in templates
	"this_form", // the URL of the form (can be used by bad_url)
	"subject", // subject for the email
	"env_report", // comma-separated list of environment variables to report
	"filter", // a supported filter to use
	"filter_options", // options for using the filter
	"filter_fields", // list of fields to filter (default is to filter all fields)
	"filter_files", // list of file fields to filter (default is to filter no file fields)
	"logfile", // log file to write to
	"csvfile", // file to write CSV records to
	"csvcolumns", // columns to save in the csvfile
	"crm_url", // URL for sending data to the CRM; note that the
	// value must have a valid prefix specified in TARGET_URLS
	"crm_spec", // CRM specification (field mapping)
	"crm_options", // comma-separated list of options to control CRM processing
	"derive_fields", // a list of fields to derive from other fields
	"file_names", // specifies names for files being uploaded
	"autorespond", // specification for auto-responding
	"arverify", // verification field to allow auto-responding
	"imgverify", // verification field to allow submission
	"multi_start", // set this field on the first page of a multi-page form sequence
	"multi_keep", // set this field on the pages of a multi-page form sequence

	"next_form", // next form name or empty for last form
	"multi_go_back", // this field should be set when the user clicks the

	"alert_to", // email address to send alerts (errors) to

	"recaptcha_response_field", // verification field to allow submission
	"recaptcha_challenge_field", // challenge field

	"g-recaptcha-response",
);

$SPECIAL_MULTI = array(
	"conditions",
	"fmcompute",
);

$SPECIAL_ARRAYS = array(
	"recipients",
	"cc",
	"bcc",
	"replyto",
);

$SPECIAL_NOSTRIP = array(
	"conditions",
	"fmcompute",
	"recaptcha_response_field",
	"recaptcha_challenge_field",
);

$VALID_MAIL_OPTIONS = array(
	"AlwaysEmailFiles" => true,
	"AlwaysList"       => true,
	"CharSet"          => true,
	"DupHeader"        => true,
	"Exclude"          => true,
	"FromAddr"         => true,
	"FromLineStyle"    => true,
	"HTMLTemplate"     => true,
	"KeepLines"        => true,
	"NoEmpty"          => true,
	"NoPlain"          => true,
	"PlainTemplate"    => true,
	"SendMailFOption"  => true,
	"StartLine"        => true,
	"TemplateMissing"  => true,
);

$VALID_CRM_OPTIONS = array(
	"ErrorOnFail" => true,
);

$VALID_AR_OPTIONS = array(
	"Subject"         => true,
	"HTMLTemplate"    => true,
	"PlainTemplate"   => true,
	"TemplateMissing" => true,
	"PlainFile"       => true,
	"HTMLFile"        => true,
	"FromAddr"        => true,
);

$VALID_FILTER_OPTIONS = array(
	"Attach"       => true,
	"KeepInLine"   => true,
	"CSVHeading"   => true,
	"CSVSep"       => true,
	"CSVIntSep"    => true,
	"CSVQuote"     => true,
	"CSVEscPolicy" => true,
	"CSVRaw"       => true,
);

$SPECIAL_VALUES = array();

$MAIL_OPTS = array();

$CRM_OPTS = array();

$AR_OPTS = array();

$FILTER_OPTS = array();

foreach ($SPECIAL_FIELDS as $sFieldName) {
	$SPECIAL_VALUES[$sFieldName] = "";
}

$SPECIAL_VALUES['template_list_sep'] = ",";

$FORMATTED_INPUT = array();

$FILTER_ATTRIBS_LOOKUP = array();

$EMAIL_ADDRS = array();

class   BuiltinFunctions
{
	private $_aFunctions;

	function __construct()
	{
		$this->_aFunctions = array();
	}

	public function Add($s_name,$n_params)
	{
		$this->_aFunctions[$s_name] = array('nparams' => $n_params);
	}

	public function Call($s_name,$a_params,&$s_result)
	{
		if (!isset($this->_aFunctions[$s_name])) {
			$s_result = "Function '$s_name' is not a builtin function";
			return (false);
		}
		$a_func = $this->_aFunctions[$s_name];
		if (count($a_params) != $a_func['nparams']) {
			$s_result = "Function '$s_name' expects " . $a_func['nparams'] . " parameters, " . count($a_params) .
			            " given.";
			return (false);
		}
		$s_result = call_user_func_array($s_name,$a_params);
		return (true);
	}
}

$BuiltinFunctions = new BuiltinFunctions();

$reCaptchaProcessor = null;
if (Settings::get('RECAPTCHA_PRIVATE_KEY') !== "") {

	$bRecaptchaVersion = 1;
	if (isset($aFormVars['g-recaptcha-response']) && $aFormVars['g-recaptcha-response'] != '') {
		$bRecaptchaVersion = 2;
	}
	if ($bRecaptchaVersion == 1 && !include_once("recaptchalib.php")) {
		$bRecaptchaVersion = 2;
	}

	if ($bRecaptchaVersion == 2) {
		if (!function_exists('json_decode')) {
			SendAlert("reCaptcha version 2 requires PHP version 5.2.0 or later",false,false);
		}
		class   reCaptchaWrapperV2
		{
			var $_sPrivate; // the private key
			var $_bDone; // true when done
			var $_Resp; // the response from reCaptcha

			function    __construct($s_priv)
			{
				$this->_sPrivate = $s_priv;
				$this->_bDone    = false;
			}

			function    Check($s_response,$a_values,&$s_error)
			{
				if (!$this->_bDone) {
					$s_url     = 'https://www.google.com/recaptcha/api/siteverify?secret=' . $this->_sPrivate .
					             "&response=$s_response";
					$recaptcha = new HTTPGet($s_url);
					$s_resp    = $recaptcha->Read();
					if ($s_resp === false) {
						$s_resp = '{"success":false,"error_codes":["reCaptcha failed"]}';
					} else {
						$s_resp = implode('',$s_resp);
					}
					$this->_Resp = json_decode($s_resp,true);
				}
				$this->_bDone = true;
				$s_error      = "";
				if (!$this->_Resp['success']) {
					$s_error = $this->_Resp->error_codes[0];
					if (!isset($this->_Resp['error_codes']) || count($this->_Resp['error_codes']) == 0 ||
					    !$this->_Resp['error_codes'][0]
					) {
						$s_error = 'verification failed';
					}
				}
				return ($this->_Resp['success']);
			}
		}
		$reCaptchaProcessor = new reCaptchaWrapperV2(Settings::get('RECAPTCHA_PRIVATE_KEY'));
	} else {

		class   reCaptchaWrapper
		{
			var $_sPrivate; // the private key
			var $_bDone; // true when done
			var $_Resp; // the response from reCaptcha

			function    __construct($s_priv)
			{
				$this->_sPrivate = $s_priv;
				$this->_bDone    = false;
			}

			function    Check($s_response,$a_values,&$s_error)
			{
				if (!$this->_bDone) {
					$this->_Resp = recaptcha_check_answer($this->_sPrivate,
					                                      $_SERVER["REMOTE_ADDR"],
					                                      $a_values["recaptcha_challenge_field"],
					                                      $s_response);
				}
				$this->_bDone = true;
				$s_error      = "";
				if (!$this->_Resp->is_valid) {
					$s_error = $this->_Resp->error;
				}
				return ($this->_Resp->is_valid);
			}
		}
		$reCaptchaProcessor = new reCaptchaWrapper(Settings::get('RECAPTCHA_PRIVATE_KEY'));
	}
}

class EmailChecker
{

	var $_aAddresses; // valid email addresses (as keys)
	var $_aTargetPatterns; // valid email address patterns

	function EmailChecker($a_patterns = array())
	{
		$this->_aAddresses      = array();
		$this->_aTargetPatterns = $a_patterns;
	}

	function AddAddress($s_addr)
	{
		$this->_aAddresses[$s_addr] = true;
	}

	function AddAddresses($s_list)
	{
		$a_addrs = TrimArray(explode(",",$s_list));
		foreach ($a_addrs as $s_addr) {
			$this->AddAddress($s_addr);
		}
	}

	function CheckAddress($s_email)
	{
		$b_is_valid = false;
		if (isset($this->_aAddresses[$s_email])) {
			$b_is_valid = true;
		} else {
			for ($ii = 0 ; $ii < count($this->_aTargetPatterns) ; $ii++) {
				//
				// prepend / with \
				//
				$s_pat = "/" . str_replace('/','\\/',$this->_aTargetPatterns[$ii]) . "/i";
				if (preg_match($s_pat,$s_email)) {
					$b_is_valid = true;
					break;
				}
			}
		}
		return ($b_is_valid);
	}
}

$ValidEmails = new EmailChecker(Settings::get('TARGET_EMAIL'));

class FieldManager
{

	private $_aFields;

	private $_aFileFields;

	private $_sArraySep;

	private $_sArraySepValue;

	private $_nUnique;

	public function __construct($a_fields = array(),$a_file_fields = array())
	{
		$this->_sArraySepValue = $this->_sArraySep = "";
		$this->_aFields        = $this->_aFileFields = array();
		$this->_nUnique        = 0;
		$this->Init($a_fields,$a_file_fields);
	}

	public function Init($a_fields,$a_file_fields)
	{
		$this->_aFields     = $a_fields;
		$this->_aFileFields = $a_file_fields;
	}

	public function GetFieldValue($s_fld,$s_array_sep = ";")
	{
		if (!isset($this->_aFields[$s_fld])) {
			if (($s_name = GetFileName($s_fld)) === false) {
				$s_name = "";
			}
		}
		if (is_array($this->_aFields[$s_fld])) {
			$s_value = implode($this->_GetArraySep($s_array_sep),$this->_aFields[$s_fld]);
		} else {
			$s_value = (string)$this->_aFields[$s_fld];
		}
		return ($s_value);
	}

	public function GetSafeFieldValue($s_fld,$b_text_subs = false,$s_array_sep = ";")
	{

		if (isset($this->_aFields[$s_fld]) && is_array($this->_aFields[$s_fld])) {
			$s_value = implode($this->_GetArraySep($s_array_sep),
			                   HTMLEntitiesArray($this->_aFields[$s_fld],false,
			                                     GetMailOption("CharSet")));
		} else {
			if (!isset($this->_aFields[$s_fld])) {
				if (($s_name = GetFileName($s_fld)) === false) {
					$s_name = "";
				}
				$s_value = $s_name;
			} else {
				$s_value = (string)$this->_aFields[$s_fld];
			}
			if ($b_text_subs) {
				list ($s_value,$a_subs_data) = $this->_PrepareTextSubstitute($s_value);
			}
			$s_value = FixedHTMLEntities($s_value,GetMailOption("CharSet"));
			if ($b_text_subs) {
				$s_value = $this->_CompleteTextSubstitute($s_value,$a_subs_data);
			}
		}
		return ($s_value);
	}

	private function _PrepareTextSubstitute($s_value)
	{

		$a_subs_data = array();
		$a_text_subs = Settings::get('TEXT_SUBS');
		for ($ii = 0 ; $ii < count($a_text_subs) ; $ii++) {
			$a_match_data = array();
			if (($n_matches = preg_match_all($a_text_subs[$ii]["srch"],$s_value,$a_matches,
			                                 PREG_OFFSET_CAPTURE)) !== false && $n_matches > 0
			) {
				$a_match_data["srch"] = $a_text_subs[$ii]["srch"];
				$a_match_data["repl"] = $a_text_subs[$ii]["repl"];
				$s_value              = $this->_HTMLSafeSubstitute($s_value,$a_matches,$a_match_data);
			}
			$a_subs_data[$ii] = $a_match_data;
		}
		return (array($s_value,$a_subs_data));
	}

	private function _CompleteTextSubstitute($s_value,$a_subs_data)
	{

		for ($ii = count($a_subs_data) ; --$ii >= 0 ;) {
			$a_subs_list = $a_subs_data[$ii];
			for ($jj = count($a_subs_list) ; --$jj >= 0 ;) {
				$s_code  = $a_subs_list[$jj]["code"];
				$s_subs  = $a_subs_list[$jj]["subs"];
				$s_value = str_replace($s_code,$s_subs,$s_value);
			}
		}
		return ($s_value);
	}

	private function _MakeUniqueString($s_base)
	{
		$n_uniq = $this->_nUnique++;
		return ($s_base . "_" . str_pad("$n_uniq",5,"0",STR_PAD_LEFT));
	}

	private function _HTMLSafeSubstitute($s_value,$a_matches,&$a_match_data)
	{
		$a_matches = $a_matches[0]; // we're only interested in the full pattern

		$s_srch = $a_match_data["srch"];
		$s_repl = $a_match_data["repl"];

		usort($a_matches,create_function('$a,$b','return $b[1] - $a[1];'));
		$a_match_data = array();
		for ($ii = 0 ; $ii < count($a_matches) ; $ii++) {
			$s_match  = $a_matches[$ii][0];
			$i_offset = $a_matches[$ii][1];
			$i_len    = strlen($s_match);

			$s_subs = preg_replace($s_srch,$s_repl,$s_match);

			$s_code            = "!" . $this->_MakeUniqueString("SUBS") . "!";
			$a_match_data[$ii] = array("subs" => $s_subs,"code" => $s_code);

			$s_value = substr($s_value,0,$i_offset) . $s_code . substr($s_value,$i_offset + $i_len);
		}
		return ($s_value);
	}

	public function IsFieldSet($s_fld)
	{
		global $aFileVars; // temporary code until this class is complete

		if (isset($this->_aFields[$s_fld])) {
			return (true);
		}
		if (Settings::get('FILEUPLOADS')) {
			if (isset($aFileVars[$s_fld])) {
				return (true);
			}
			if (IsSetSession("FormSavedFiles")) {
				$a_saved_files = GetSession("FormSavedFiles");
				if (isset($a_saved_files[$s_fld])) {
					return (true);
				}
			}
		}
		return (false);
	}

	public function TestFieldEmpty($s_fld,&$s_mesg)
	{
		global $aFileVars; // temporary until code completed

		$s_mesg  = "";
		$b_empty = TRUE;
		if (!isset($this->_aFields[$s_fld])) {

			if (Settings::get('FILEUPLOADS')) {
				if (IsSetSession("FormSavedFiles")) {
					$a_saved_files = GetSession("FormSavedFiles");
					if (isset($a_saved_files[$s_fld])) {
						$a_upload = $a_saved_files[$s_fld];
					} elseif (isset($aFileVars[$s_fld])) {
						$a_upload = $aFileVars[$s_fld];
					}
				} elseif (isset($aFileVars[$s_fld])) {
					$a_upload = $aFileVars[$s_fld];
				}
			}
			if (isset($a_upload)) {
				if (isset($a_upload["tmp_name"]) && !empty($a_upload["tmp_name"]) &&
				    isset($a_upload["name"]) && !empty($a_upload["name"])
				) {
					if (IsUploadedFile($a_upload)) {
						$b_empty = false;
					}
				}
				if ($b_empty && isset($a_upload["error"])) {
					switch ($a_upload["error"]) {
						case 1:
							$s_mesg = GetMessage(MSG_FILE_UPLOAD_ERR1);
							break;
						case 2:
							$s_mesg = GetMessage(MSG_FILE_UPLOAD_ERR2);
							break;
						case 3:
							$s_mesg = GetMessage(MSG_FILE_UPLOAD_ERR3);
							break;
						case 4:
							$s_mesg = GetMessage(MSG_FILE_UPLOAD_ERR4);
							break;
						case 6:
							$s_mesg = GetMessage(MSG_FILE_UPLOAD_ERR6);
							break;
						case 7:
							$s_mesg = GetMessage(MSG_FILE_UPLOAD_ERR7);
							break;
						case 8:
							$s_mesg = GetMessage(MSG_FILE_UPLOAD_ERR8);
							break;
						default:
							$s_mesg = GetMessage(MSG_FILE_UPLOAD_ERR_UNK,
							                     array("ERRNO" => $a_upload["error"]));
							break;
					}
				}
			}
		} else {
			$b_empty = FieldManager::IsEmpty($this->_aFields[$s_fld]);
		}
		return ($b_empty);
	}

	public static function IsEmpty($s_value)
	{
		if (Settings::get('ZERO_IS_EMPTY') || is_array($s_value)) {
			return (empty($s_value));
		} else {
			return ($s_value === "");
		}
	}

	public static function Substitute($s_str)
	{

		$a_srch = $a_repl = array();
		foreach (Settings::get('TEXT_SUBS') as $a_sub) {
			if (isset($a_sub["srch"]) && isset($a_sub["repl"]) && $a_sub["srch"] !== "") {
				$a_srch[] = $a_sub["srch"];
				$a_repl[] = $a_sub["repl"];
			}
		}
		return (preg_replace($a_srch,$a_repl,$s_str));
	}

	private function _GetArraySep($s_sep)
	{

		if ($s_sep !== $this->_sArraySep) {
			$this->_sArraySep      = $s_sep;
			$this->_sArraySepValue = FieldManager::Substitute($this->_sArraySep);
		}
		return ($this->_sArraySepValue);
	}
}

function    LineFolding($s_str,$i_max_line,$s_before,$s_after,$s_fold)
{
	$i_str_len  = strlen($s_str);
	$ii         = $i_start = 0;
	$i_line_len = 0;
	while ($ii < $i_str_len) {
		if ($i_line_len == $i_max_line) {

			$b_done = false;
			for ($jj = $ii ; !$b_done && $jj > $i_start ; $jj--) {
				$b_found = false;
				if (strpos($s_before,$s_str[$jj]) !== false) {

					$b_found = true;
				} elseif (strpos($s_after,$s_str[$jj]) !== false) {

					$jj++;
					$b_found = true;
				}
				if ($b_found) {
					$s_str      = substr($s_str,0,$jj) . $s_fold . substr($s_str,$jj);
					$i_fold_len = strlen($s_fold);
					$i_str_len += $i_fold_len; // the additional chars we inserted
					$i_start = $jj + $i_fold_len; // start of the next line
					$b_done  = true;
				}
			}

			if ($b_done) {
				$ii = $i_start;
			} else {
				$i_start = $ii;
			}
			$i_line_len = 0;
		} elseif (substr($s_str,$ii,2) == "\r\n") {

			$i_line_len = 0;
			$ii += 2;
			$i_start = $ii;
		} else {
			$ii++;
			$i_line_len++;
		}
	}
	return ($s_str);
}

function    QPEncode($s_str,$i_max_line)
{

	$s_str = str_replace('%','=',rawurlencode($s_str));
	if ($i_max_line < 0) {
		return ($s_str);
	} else {
		$s_before = "="; // characters before which we can fold the line
		return (LineFolding($s_str,$i_max_line,$s_before,"","=\r\n"));
	}
}

function    HeaderFolding($s_str,$i_max_line = RFCLINELEN,$s_before = "<",$s_after = ">;, ")
{
	return (LineFolding($s_str,$i_max_line,$s_before,$s_after,"\r\n "));
}

function CheckVersion()
{
	global $FM_VERS;

	$http_get     = new HTTPGet("http://www.tectite.com/fmversion.txt");
	$php_errormsg = ""; // clear this out in case we get an error that doesn't set it
	FMDebug("CheckVersion");
	if (($a_lines = $http_get->Read()) !== false) {

		$s_version = "";
		$s_message = "";
		$s_line    = "";
		$b_in_mesg = false;
		foreach ($a_lines as $s_line) {
			if ($b_in_mesg) {
				$s_message .= $s_line;
			} else {
				$s_prefix = substr($s_line,0,8);
				if ($s_prefix == "Message=") {
					$s_message .= substr($s_line,8);
					$b_in_mesg = true;
				} elseif ($s_prefix == "Version=") {
					$s_version = substr($s_line,8);
				}
			}
		}
		$s_version   = str_replace("\r","",$s_version);
		$s_version   = str_replace("\n","",$s_version);
		$s_stop_mesg = GetMessage(MSG_END_VERS_CHK);
		FMDebug("CheckVersion: vers=$s_version");
		if ((float)$s_version > (float)$FM_VERS) {
			SendAlert(GetMessage(MSG_VERS_CHK,array(
				                                      "TECTITE" => "www.tectite.com",
				                                      "FM_VERS" => "$FM_VERS",
				                                      "NEWVERS" => $s_version,
			                                      )) .
			          "\n$s_message\n$s_stop_mesg",true,true);
		}
	}
}

function Check4Update($s_chk_file,$s_id = "")
{
	global $lNow,$php_errormsg;

	@   $l_last_chk = filemtime($s_chk_file);
	if ($l_last_chk === false || $lNow - $l_last_chk >= (Settings::get('CHECK_DAYS') * 24 * 60 * 60)) {
		CheckVersion();

		@   $fp = fopen($s_chk_file,"w");
		if ($fp !== false) {
			fwrite($fp,"FormMail version check " .
			           (empty($s_id) ? "" : "for identifier '$s_id' ") .
			           "at " . date("H:i:s d-M-Y",$lNow) . "\n");
			fclose($fp);
		} else {
			SendAlert(GetMessage(MSG_CHK_FILE_ERROR,array("FILE"  => $s_chk_file,
			                                              "ERROR" => CheckString($php_errormsg)
			)));
		}
	}
}

function OnExit()
{
	FMDebug("OnExit");

	if (Settings::get('CHECK_FOR_NEW_VERSION')) {
		global $SERVER;

		$a_targets = Settings::get('TARGET_EMAIL');
		if (isset($a_targets[0])) {

			$s_id = "";
			if (isset($SERVER) && !empty($SERVER)) {
				$s_id = $SERVER;
			}
			$s_dir      = GetTempDir();
			$s_md5      = md5($a_targets[0]);
			$s_uniq     = substr($s_md5,0,6);
			$s_chk_file = "fm" . "$s_uniq" . ".txt";
			Check4Update($s_dir . "/" . $s_chk_file,$s_id);
		}
	}
}

register_shutdown_function('OnExit');

function HTMLEntitiesArray($a_array,$b_equals_processing = false,$s_charset = NULL)
{
	foreach ($a_array as $m_key => $s_str) {

		if ($b_equals_processing && ($i_pos = strpos($s_str,'=')) !== false) {
			$a_array[$m_key] = substr($s_str,0,$i_pos + 1) .
			                   FixedHTMLEntities(substr($s_str,$i_pos + 1),$s_charset);
		} else {
			$a_array[$m_key] = FixedHTMLEntities($s_str,$s_charset);
		}
	}
	return ($a_array);
}

function    FixedHTMLEntities($s_str,$s_charset = NULL)
{
	global $sHTMLCharSet;

	if (isset($s_charset) && $s_charset != "") {
		return (htmlspecialchars($s_str,ENT_COMPAT,$s_charset));
	}
	if (isset($sHTMLCharSet) && $sHTMLCharSet != "") {
		return (htmlspecialchars($s_str,ENT_COMPAT,$sHTMLCharSet));
	}
	return (htmlentities($s_str));
}

function URLEncodeArray($a_array)
{
	foreach ($a_array as $m_key => $s_str) {

		if (($i_pos = strpos($s_str,'=')) !== false) {
			$a_array[$m_key] = substr($s_str,0,$i_pos + 1) .
			                   urlencode(substr($s_str,$i_pos + 1));
		} else {
			$a_array[$m_key] = urlencode($s_str);
		}
	}
	return ($a_array);
}

function    EncodeHeaderText($s_text,$i_max_line = -1)
{
	global $sHTMLCharSet;

	if ($i_max_line == 0) {
		$i_max_line = RFCLINELEN - 15;
	}
	$s_charset = "";
	if (isset($sHTMLCharSet) && $sHTMLCharSet != "") {
		$s_charset = $sHTMLCharSet;
	} else {
		if (IsMailOptionSet("CharSet")) {
			$s_charset = GetMailOption("CharSet");
		}
	}
	if ($s_charset != "") {

		$s_prefix = "=?" . $s_charset . "?Q?";
		$s_suffix = "?=";

		return ($s_prefix . QPEncode($s_text,$i_max_line - strlen($s_prefix)) . $s_suffix);
	} else {
		return ($s_text);
	}
}

function AddURLParams($s_url,$m_params,$b_encode = true)
{
	if (!empty($m_params)) {
		if (!is_array($m_params)) {
			$m_params = array($m_params);
		}
		$s_anchor = "";
		if (($i_pos = strpos($s_url,'#')) !== false) {

			$s_anchor = substr($s_url,$i_pos);
			$s_url    = substr($s_url,0,$i_pos);
		}
		if (strpos($s_url,'?') === false) {
			$s_url .= '?';
		} else {
			$s_url .= '&';
		}
		$s_url .= implode('&',($b_encode) ? URLEncodeArray($m_params) : $m_params);
		if ($s_anchor !== "") {
			$s_url .= "$s_anchor";
		}
	}
	return ($s_url);
}

function TrimArray($a_list)
{
	foreach ($a_list as $m_key => $m_item) {
		if (is_array($m_item)) {
			$a_list[$m_key] = TrimArray($m_item);
		} elseif (is_scalar($m_item)) {
			$a_list[$m_key] = trim("$m_item");
		} else {
			$a_list[$m_key] = "";
		}
	}
	return ($a_list);
}

function ParseDerivation($a_form_data,$s_fld_spec,$s_name,&$a_errors)
{
	$a_deriv = array();
	while (($i_len = strlen($s_fld_spec)) > 0) {

		$i_span = strcspn($s_fld_spec,'+*.');
		if ($i_span == 0) {
			$a_errors[] = $s_name;
			return (false);
		}
		$a_deriv[] = trim(substr($s_fld_spec,0,$i_span));
		if ($i_span < $i_len) {
			$a_deriv[]  = substr($s_fld_spec,$i_span,1);
			$s_fld_spec = substr($s_fld_spec,$i_span + 1);
		} else {
			$s_fld_spec = "";
		}
	}
	return ($a_deriv);
}

function IsAlpha($ch)
{
	return (strpos("abcdefghijklmnopqrstuvwxyz",strtolower($ch)) !== false);
}

function IsNumeric($ch)
{
	return (strpos("0123456789",$ch) !== false);
}

function IsAlnum($ch)
{
	return (IsAlpha($ch) || IsNumeric($ch));
}

function GetTokens($s_str,$s_quotes = "'\"")
{
	$b_allow_strings = ($s_quotes !== "") ? true : false;
	$ii              = 0;
	$i_len           = strlen($s_str);
	$a_toks          = array();

	while ($ii < $i_len) {
		switch ($ch = $s_str[$ii]) {
			case " ":
			case "\t":
			case "\n":
			case "\r":
				$ii++;
				continue;
		}

		$i_start = $ii;
		if ($ch == "_" || IsAlpha($ch)) {

			$i_count = 1;
			while (++$ii < $i_len &&
			       ($s_str[$ii] == "_" || IsAlnum($s_str[$ii]))) {
				++$i_count;
			}
			$a_toks[] = substr($s_str,$i_start,$i_count);
		} elseif (($ch == "." && $ii < ($i_len - 1) && IsNumeric($s_str[$ii + 1])) ||
		          IsNumeric($ch)
		) {

			$b_had_dot = ($ch == ".");
			$i_count   = 1;
			while (++$ii < $i_len) {
				if (IsNumeric($s_str[$ii])) {
					++$i_count;
				} elseif ($s_str[$ii] == "." && !$b_had_dot) {
					++$i_count;
					$b_had_dot = true;
				} else {
					break;
				}
			}
			$a_toks[] = substr($s_str,$i_start,$i_count);
		} elseif ($b_allow_strings && strpos($s_quotes,$ch) !== false) {
			$c_quote = $ch;

			while (++$ii < $i_len) {
				if ($s_str[$ii] == $c_quote) {
					++$ii; // include the terminating quote
					break;
				}
			}
			$a_toks[] = substr($s_str,$i_start,$ii - $i_start);
		} else {
			$s_punct = "~!@#$%^&*()-+={}[]|:;<>,.?/`\\";
			if (!$b_allow_strings) {
				$s_punct .= "'\"";
			}
			if (strpos($s_punct,$ch) !== false) {
				$a_toks[] = $ch;
			}
			++$ii;
		}
	}
	return ($a_toks);
}

function ValueSpec($s_spec,$a_form_data,&$a_errors)
{
	global $lNow;

	$s_value = "";
	switch (trim($s_spec)) {
		case 'date': // "standard" date format: DD-MMM-YYYY
			$s_value = date('d-M-Y',$lNow);
			break;
		case 'time': // "standard" time format: HH:MM:SS
			$s_value = date('H:i:s',$lNow);
			break;
		case 'ampm': // am or pm
			$s_value = date('a',$lNow);
			break;
		case 'AMPM': // AM or PM
			$s_value = date('A',$lNow);
			break;
		case 'dom0': // day of month with possible leading zero
			$s_value = date('d',$lNow);
			break;
		case 'dom': // day of month with no leading zero
			$s_value = date('j',$lNow);
			break;
		case 'day': // day name (abbreviated)
			$s_value = date('D',$lNow);
			break;
		case 'dayname': // day name (full)
			$s_value = date('l',$lNow);
			break;
		case 'daysuffix': // day number suffix for English (st for 1st, nd for 2nd, etc.)
			$s_value = date('S',$lNow);
			break;
		case 'moy0': // month of year with possible leading zero
			$s_value = date('m',$lNow);
			break;
		case 'moy': // month of year with no leading zero
			$s_value = date('n',$lNow);
			break;
		case 'month': // month name (abbreviated)
			$s_value = date('M',$lNow);
			break;
		case 'monthname': // month name (full)
			$s_value = date('F',$lNow);
			break;
		case 'year': // year (two digits)
			$s_value = date('y',$lNow);
			break;
		case 'fullyear': // year (full)
			$s_value = date('Y',$lNow);
			break;
		case 'rfcdate': // date formatted according to RFC 822
			$s_value = date('r',$lNow);
			break;
		case 'tzname': // timezone name
			$s_value = date('T',$lNow);
			break;
		case 'tz': // timezone difference from Greenwich +NNNN or -NNNN
			$s_value = date('O',$lNow);
			break;
		case 'hour120': // hour of day (01-12) with possible leading zero
			$s_value = date('h',$lNow);
			break;
		case 'hour240': // hour of day (00-23) with possible leading zero
			$s_value = date('H',$lNow);
			break;
		case 'hour12': // hour of day (1-12) with no leading zero
			$s_value = date('g',$lNow);
			break;
		case 'hour24': // hour of day (0-23) with no leading zero
			$s_value = date('G',$lNow);
			break;
		case 'min': // minute of hour (00-59)
			$s_value = date('i',$lNow);
			break;
		case 'sec': // seconds of minute (00-59)
			$s_value = date('s',$lNow);
			break;
		default:
			if ($s_spec[0] == "'") {

				if ($s_spec == "'''") {
					$s_value = "'";
				} elseif (substr($s_spec,-1,1) == "'") {
					$s_value = substr($s_spec,1,-1);
				} else

				{
					$s_value = substr($s_spec,1);
				}
			} elseif (strspn($s_spec,"0123456789ABCDEF") == 2) {

				$i_val   = intval(substr($s_spec,0,2),16);
				$s_value = chr($i_val);
			} else {

				$a_toks = GetTokens($s_spec);
				if (count($a_toks) > 0) {
					switch ($a_toks[0]) {
						case "if":

							if (($n_tok = count($a_toks)) < 6 ||
							    $a_toks[1] != "(" ||
							    $a_toks[3] != ";" ||
							    $a_toks[$n_tok - 1] != ")"
							) {
								SendAlert(GetMessage(MSG_DER_FUNC_ERROR,
								                     array("SPEC" => $s_spec,
								                           "MSG"  => GetMessage(MSG_DER_FUNC_IF_FMT)
								                     )));
							} else {
								$b_ok        = true;
								$s_fld_name  = $a_toks[2];
								$s_then_spec = $s_else_spec = "";
								for ($ii = 4 ; $ii < $n_tok && $a_toks[$ii] != ';' ; $ii++) {
									$s_then_spec .= $a_toks[$ii];
								}
								if ($ii == $n_tok) {
									$b_ok = false;
								} else {

									for (; ++$ii < $n_tok && $a_toks[$ii] != ')' ;) {
										$s_else_spec .= $a_toks[$ii];
									}
									if ($ii == $n_tok) {
										$b_ok = false;
									}
								}
								$s_mesg = "";
								if ($b_ok) {
									if (!TestFieldEmpty($s_fld_name,$a_form_data,$s_mesg)) {
										$s_fld_spec = $s_then_spec;
									} else {
										$s_fld_spec = $s_else_spec;
									}
									$s_value = GetDerivedValue($a_form_data,$s_fld_spec,$a_errors);
								} else {
									SendAlert(GetMessage(MSG_DER_FUNC_ERROR,
									                     array("SPEC" => $s_spec,
									                           "MSG"  => GetMessage(MSG_DER_FUNC_IF_FMT)
									                     )));
								}
							}
							break;
						case "size":

							if (count($a_toks) != 4 ||
							    $a_toks[1] != "(" ||
							    $a_toks[3] != ")"
							) {
								SendAlert(GetMessage(MSG_DER_FUNC_ERROR,
								                     array("SPEC" => $s_spec,
								                           "MSG"  => GetMessage(MSG_DER_FUNC_SIZE_FMT)
								                     )));
							} elseif (($i_size = GetFileSize($a_toks[2])) !== false) {
								$s_value = "$i_size";
							}
							break;
						case "ext":

							if (count($a_toks) != 4 ||
							    $a_toks[1] != "(" ||
							    $a_toks[3] != ")"
							) {
								SendAlert(GetMessage(MSG_DER_FUNC_ERROR,
								                     array("SPEC" => $s_spec,
								                           "MSG"  => GetMessage(MSG_DER_FUNC_EXT_FMT)
								                     )));
							} elseif (($s_name = GetFileName($a_toks[2])) !== false) {
								if (($i_pos = strrpos($s_name,".")) !== false) {
									$s_value = substr($s_name,$i_pos + 1);
								}
							}
							break;
						case "ucase":
						case "lcase":

							if (count($a_toks) != 4 ||
							    $a_toks[1] != "(" ||
							    $a_toks[3] != ")"
							) {
								SendAlert(GetMessage(MSG_DER_FUNC_ERROR,
								                     array("SPEC" => $s_spec,
								                           "MSG"  => GetMessage(MSG_DER_FUNC1_FMT,
								                                                array("FUNC" => $a_toks[0]))
								                     )));
							} elseif ($a_toks[0] == "ucase") {
								$s_value = strtoupper(GetFieldValue($a_toks[2],$a_form_data));
							} else {
								$s_value = strtolower(GetFieldValue($a_toks[2],$a_form_data));
							}
							break;
						case "ltrim":
						case "rtrim":
						case "trim":

							if (count($a_toks) != 4 ||
							    $a_toks[1] != "(" ||
							    $a_toks[3] != ")"
							) {
								SendAlert(GetMessage(MSG_DER_FUNC_ERROR,
								                     array("SPEC" => $s_spec,
								                           "MSG"  => GetMessage(MSG_DER_FUNC1_FMT,
								                                                array("FUNC" => $a_toks[0]))
								                     )));
							} else {
								$s_value = $a_toks[0](GetFieldValue($a_toks[2],$a_form_data));
							}
							break;
						case "ltrim0":

							if (count($a_toks) != 4 ||
							    $a_toks[1] != "(" ||
							    $a_toks[3] != ")"
							) {
								SendAlert(GetMessage(MSG_DER_FUNC_ERROR,
								                     array("SPEC" => $s_spec,
								                           "MSG"  => GetMessage(MSG_DER_FUNC1_FMT,
								                                                array("FUNC" => $a_toks[0]))
								                     )));
							} else {
								$s_value = GetFieldValue($a_toks[2],$a_form_data);
								$s_value = ltrim($s_value); // trim blanks on left
								$i_len   = strspn($s_value,"0");

								if ($i_len == strlen($s_value)) {
									if (--$i_len < 0) {
										$i_len = 0;
									}
								}
								$s_value = substr($s_value,$i_len);
							}
							break;
						case "nextnum":

							$i_pad  = 0; // no padding
							$i_base = 10; // base 10
							if (($n_tok = count($a_toks)) > 1) {
								if (($n_tok != 4 && $n_tok != 6) ||
								    $a_toks[1] != "(" ||
								    $a_toks[$n_tok - 1] != ")"
								) {
									SendAlert(GetMessage(MSG_DER_FUNC_ERROR,
									                     array("SPEC" => $s_spec,
									                           "MSG"  => GetMessage(MSG_DER_FUNC_NEXTNUM_FMT) . " (T1)"
									                     )));
								}
								if ($n_tok == 6 && $a_toks[3] != ";") {
									SendAlert(GetMessage(MSG_DER_FUNC_ERROR,
									                     array("SPEC" => $s_spec,
									                           "MSG"  => GetMessage(MSG_DER_FUNC_NEXTNUM_FMT) . " (T2)"
									                     )));
								}
								if (!is_numeric($a_toks[2]) ||
								    ($n_tok == 6 && !is_numeric($a_toks[4]))
								) {
									SendAlert(GetMessage(MSG_DER_FUNC_ERROR,
									                     array("SPEC" => $s_spec,
									                           "MSG"  => GetMessage(MSG_DER_FUNC_NEXTNUM_FMT) . " (T3)"
									                     )));
								}
								$i_pad = intval($a_toks[2]);
								if ($n_tok == 6) {
									$i_base = intval($a_toks[4]);
									if ($i_base < 2 || $i_base > 36) {
										SendAlert(GetMessage(MSG_DER_FUNC_ERROR,
										                     array("SPEC" => $s_spec,
										                           "MSG"  =>
											                           GetMessage(MSG_DER_FUNC_NEXTNUM_FMT) . " (T4)"
										                     )));
										$i_base = 10;
									}
								}
								$s_value = GetNextNum($i_pad,$i_base);
							} else {
								$s_value = GetNextNum($i_pad,$i_base);
							}
							break;
						case "substr":

							$i_start = 0; // default start
							$i_len   = null; // no length
							if (($n_tok = count($a_toks)) > 1) {
								if (($n_tok != 6 && $n_tok != 8) ||
								    $a_toks[1] != "(" ||
								    $a_toks[$n_tok - 1] != ")"
								) {
									SendAlert(GetMessage(MSG_DER_FUNC_ERROR,
									                     array("SPEC" => $s_spec,
									                           "MSG"  => GetMessage(MSG_DER_FUNC_SUBSTR_FMT) . " (T1)"
									                     )));
								}
								if ($n_tok == 8 &&
								    ($a_toks[3] != ";" || $a_toks[5] != ";")
								) {
									SendAlert(GetMessage(MSG_DER_FUNC_ERROR,
									                     array("SPEC" => $s_spec,
									                           "MSG"  => GetMessage(MSG_DER_FUNC_SUBSTR_FMT) . " (T2)"
									                     )));
								}
								if ($n_tok == 6 && $a_toks[3] != ";") {
									SendAlert(GetMessage(MSG_DER_FUNC_ERROR,
									                     array("SPEC" => $s_spec,
									                           "MSG"  => GetMessage(MSG_DER_FUNC_SUBSTR_FMT) . " (T3)"
									                     )));
								}
								if (!is_numeric($a_toks[4]) ||
								    ($n_tok == 8 && !is_numeric($a_toks[6]))
								) {
									SendAlert(GetMessage(MSG_DER_FUNC_ERROR,
									                     array("SPEC" => $s_spec,
									                           "MSG"  => GetMessage(MSG_DER_FUNC_SUBSTR_FMT) . " (T4)"
									                     )));
								}
								$i_start = intval($a_toks[4]);
								$s_value = GetFieldValue($a_toks[2],$a_form_data);
								if ($n_tok == 8) {
									$i_len = intval($a_toks[6]);
								}
								if (isset($i_len)) {
									$s_value = substr($s_value,$i_start,$i_len);
								} else {
									$s_value = substr($s_value,$i_start);
								}
								if ($s_value === FALSE) {
									$s_value = "";
								}
							} else {
								SendAlert(GetMessage(MSG_DER_FUNC_ERROR,
								                     array("SPEC" => $s_spec,
								                           "MSG"  => GetMessage(MSG_DER_FUNC_SUBSTR_FMT) . " (T5)"
								                     )));
							}
							break;
						case "call":

							if (($n_tok = count($a_toks)) < 3 ||
							    $a_toks[1] != "(" ||
							    $a_toks[$n_tok - 1] != ")"
							) {
								SendAlert(GetMessage(MSG_DER_FUNC_ERROR,
								                     array("SPEC" => $s_spec,
								                           "MSG"  => 'Incorrect format for call function'
								                     )));
							} else {
								$b_ok        = true;
								$s_func_name = $a_toks[2];
								$a_params    = array();
								if (3 < $n_tok && $a_toks[3] == ';') {
									$a_params = CollectParams($a_toks,$n_tok,3);
								}
								for ($ii = 0 ; $ii < count($a_params) ; $ii++) {
									$a_params[$ii] = GetDerivedValue($a_form_data,$a_params[$ii],$a_errors);
								}
								global $BuiltinFunctions;

								if (!$BuiltinFunctions->Call($s_func_name,$a_params,$s_result)) {
									SendAlert($s_result);
								} else {
									$s_value = $s_result;
								}
							}
							break;
						default:
							SendAlert(GetMessage(MSG_UNK_VALUE_SPEC,
							                     array("SPEC" => $s_spec,"MSG" => "")));
							break;
					}
				} else {
					SendAlert(GetMessage(MSG_UNK_VALUE_SPEC,array("SPEC" => $s_spec,
					                                              "MSG"  => ""
					)));
				}
			}
			break;
	}
	return ($s_value);
}

function CollectParams($a_toks,$n_tok,$i_tok)
{
	$a_params = array();
	$i_param  = 0;
	do {
		$i_tok++;
		$a_params[$i_param] = '';
		for (; $i_tok < $n_tok && $a_toks[$i_tok] != ';' && $a_toks[$i_tok] != ')' ; $i_tok++) {
			$a_params[$i_param] .= $a_toks[$i_tok];
		}
		$i_param++;
	} while ($a_toks[$i_tok] != ')');
	return ($a_params);
}

function GetNextNum($i_pad,$i_base)
{
	global $php_errormsg;

	if (Settings::isEmpty('NEXT_NUM_FILE') || Settings::get('NEXT_NUM_FILE') === "") {
		ErrorWithIgnore("next_num_config",GetMessage(MSG_NO_NEXT_NUM_FILE));
		exit;
	}
	if (($fp = @fopen(Settings::get('NEXT_NUM_FILE'),"r+")) === false) {
		Error("next_num_file",GetMessage(MSG_NEXT_NUM_FILE,
		                                 array("FILE" => Settings::get('NEXT_NUM_FILE'),
		                                       "ACT"  => "open",
		                                       "ERR"  => $php_errormsg
		                                 )));
		exit;
	}
	if (!flock($fp,defined("LOCK_EX") ? LOCK_EX : 2)) {
		Error("next_num_file",GetMessage(MSG_NEXT_NUM_FILE,
		                                 array("FILE" => Settings::get('NEXT_NUM_FILE'),
		                                       "ACT"  => "flock",
		                                       "ERR"  => $php_errormsg
		                                 )));
		exit;
	}

	if (!feof($fp)) {
		if (($s_line = fread($fp,1024)) === false) {
			$i_next = 1;
		} elseif (($i_next = intval($s_line)) <= 0) {
			$i_next = 1;
		}
	} else {
		$i_next = 1;
	}
	if (rewind($fp) == 0) {
		Error("next_num_file",GetMessage(MSG_NEXT_NUM_FILE,
		                                 array("FILE" => Settings::get('NEXT_NUM_FILE'),
		                                       "ACT"  => "rewind",
		                                       "ERR"  => $php_errormsg
		                                 )));
		exit;
	}
	$s_ret = strval($i_next++);
	if (fputs($fp,"$i_next\r\n") <= 0) {
		Error("next_num_file",GetMessage(MSG_NEXT_NUM_FILE,
		                                 array("FILE" => Settings::get('NEXT_NUM_FILE'),
		                                       "ACT"  => "fputs",
		                                       "ERR"  => $php_errormsg
		                                 )));
		exit;
	}
	fclose($fp);
	if ($i_base != 10) {
		$s_ret = base_convert($s_ret,10,$i_base);
		$s_ret = strtoupper($s_ret); // always upper case if alphas are used
	}
	if ($i_pad != 0) {
		$s_ret = str_pad($s_ret,$i_pad,"0",STR_PAD_LEFT);
	}
	return ($s_ret);
}

function GetObjectAsString($m_value)
{
	ob_start();
	print_r($m_value);
	$s_ret = ob_get_contents();
	ob_end_clean();
	return ($s_ret);
}

function GetEnvValue($s_name)
{
	global $aServerVars,$aEnvVars;

	if (isset($aEnvVars[$s_name])) {
		$m_value = $aEnvVars[$s_name];
	} elseif (isset($aServerVars[$s_name])) {
		$m_value = $aServerVars[$s_name];
	}

	if (isset($m_value) && !is_scalar($m_value)) {
		$m_value = GetObjectAsString($m_value);
	}
	return (isset($m_value) ? ((string)$m_value) : false);
}

function IsFieldSet($s_fld,$a_main_vars)
{
	global $aFileVars;

	if (isset($a_main_vars[$s_fld])) {
		return (true);
	}
	if (Settings::get('FILEUPLOADS')) {
		if (isset($aFileVars[$s_fld])) {
			return (true);
		}
		if (IsSetSession("FormSavedFiles")) {
			$a_saved_files = GetSession("FormSavedFiles");
			if (isset($a_saved_files[$s_fld])) {
				return (true);
			}
		}
	}
	return (false);
}

function    IsFileField($s_fld)
{
	global $aFileVars;

	return (isset($aFileVars[$s_fld]));
}

function DeleteFileInfo($s_fld)
{
	global $aFileVars;
	global $aCleanedValues,$aRawDataValues,$aAllRawValues;

	if (Settings::get('FILEUPLOADS')) {
		if (IsSetSession("FormSavedFiles")) {
			$a_saved_files = GetSession("FormSavedFiles");
			unset($a_saved_files[$s_fld]);
			SetSession("FormSavedFiles",$a_saved_files);
		}
		if (isset($aFileVars[$s_fld])) {
			unset($aFileVars[$s_fld]);
		}

		$s_name = "name_of_$s_fld";
		unset($aCleanedValues[$s_name]);
		unset($aRawDataValues[$s_name]);
		unset($aAllRawValues[$s_name]);
	}
}

function GetFileInfo($s_fld)
{
	global $aFileVars;

	if (Settings::get('FILEUPLOADS')) {

		if (isset($aFileVars[$s_fld]) && !empty($aFileVars[$s_fld])) {
			$a_upload = $aFileVars[$s_fld];
		} elseif (IsSetSession("FormSavedFiles")) {
			$a_saved_files = GetSession("FormSavedFiles");
			if (isset($a_saved_files[$s_fld])) {
				$a_upload = $a_saved_files[$s_fld];
			}
		}
	}
	if (isset($a_upload)) {
		if (isset($a_upload["tmp_name"]) && !empty($a_upload["tmp_name"]) &&
		    isset($a_upload["name"]) && !empty($a_upload["name"]) &&
		    IsUploadedFile($a_upload)
		) {
			return ($a_upload);
		}
	}
	return (false);
}

function GetFileName($s_fld)
{
	if (($a_upload = GetFileInfo($s_fld)) !== false) {
		return ($a_upload["name"]);
	}
	return (false);
}

function GetFileSize($s_fld)
{
	if (($a_upload = GetFileInfo($s_fld)) !== false) {
		return ($a_upload["size"]);
	}
	return (false);
}

function GetFieldValue($s_fld,$a_main_vars,$s_array_sep = ";")
{
	if (!isset($a_main_vars[$s_fld])) {
		if (($s_name = GetFileName($s_fld)) === false) {
			$s_name = "";
		}
		return ($s_name);
	}
	if (is_array($a_main_vars[$s_fld])) {
		return (implode($s_array_sep,$a_main_vars[$s_fld]));
	} else {
		return ((string)$a_main_vars[$s_fld]);
	}
}

function TestFieldEmpty($s_fld,$a_main_vars,&$s_mesg)
{
	global $aFileVars;

	$s_mesg  = "";
	$b_empty = TRUE;
	if (!isset($a_main_vars[$s_fld])) {

		if (Settings::get('FILEUPLOADS')) {
			if (IsSetSession("FormSavedFiles")) {
				$a_saved_files = GetSession("FormSavedFiles");
				if (isset($a_saved_files[$s_fld])) {
					$a_upload = $a_saved_files[$s_fld];
				} elseif (isset($aFileVars[$s_fld])) {
					$a_upload = $aFileVars[$s_fld];
				}
			} elseif (isset($aFileVars[$s_fld])) {
				$a_upload = $aFileVars[$s_fld];
			}
		}
		if (isset($a_upload)) {
			if (isset($a_upload["tmp_name"]) && !empty($a_upload["tmp_name"]) &&
			    isset($a_upload["name"]) && !empty($a_upload["name"])
			) {
				if (IsUploadedFile($a_upload)) {
					$b_empty = false;
				}
			}
			if ($b_empty && isset($a_upload["error"])) {
				switch ($a_upload["error"]) {
					case 1:
						$s_mesg = GetMessage(MSG_FILE_UPLOAD_ERR1);
						break;
					case 2:
						$s_mesg = GetMessage(MSG_FILE_UPLOAD_ERR2);
						break;
					case 3:
						$s_mesg = GetMessage(MSG_FILE_UPLOAD_ERR3);
						break;
					case 4:
						$s_mesg = GetMessage(MSG_FILE_UPLOAD_ERR4);
						break;
					case 6:
						$s_mesg = GetMessage(MSG_FILE_UPLOAD_ERR6);
						break;
					case 7:
						$s_mesg = GetMessage(MSG_FILE_UPLOAD_ERR7);
						break;
					case 8:
						$s_mesg = GetMessage(MSG_FILE_UPLOAD_ERR8);
						break;
					default:
						$s_mesg = GetMessage(MSG_FILE_UPLOAD_ERR_UNK,
						                     array("ERRNO" => $a_upload["error"]));
						break;
				}
			}
		}
	} else {
		$b_empty = FieldManager::IsEmpty($a_main_vars[$s_fld]);
	}
	return ($b_empty);
}

function GetDerivedValue($a_form_data,$s_word,&$a_errors)
{
	$s_value = "";

	if (substr($s_word,0,1) == '%') {
		if (substr($s_word,-1,1) != '%') {
			SendAlert(GetMessage(MSG_INV_VALUE_SPEC,array("SPEC" => $s_word)));
			$s_value = $s_word;
		} else {
			$s_spec  = substr($s_word,1,-1);
			$s_value = ValueSpec($s_spec,$a_form_data,$a_errors);
		}
	} else {
		$s_fld_name = $s_word;

		if (IsFieldSet($s_fld_name,$a_form_data)) {
			$s_value = GetFieldValue($s_fld_name,$a_form_data);
		} elseif (($s_value = GetEnvValue($s_fld_name)) === false) {
			$s_value = "";
		}
		$s_value = trim($s_value);
	}
	return ($s_value);
}

function DeriveValue($a_form_data,$a_value_spec,$s_name,&$a_errors)
{
	$s_value = "";
	for ($ii = 0 ; $ii < count($a_value_spec) ; $ii++) {
		switch ($a_value_spec[$ii]) {
			case '+':

				if ($ii < count($a_value_spec) - 1) {
					$s_temp = GetDerivedValue($a_form_data,$a_value_spec[$ii + 1],$a_errors);
					if (!FieldManager::IsEmpty($s_temp)) {
						$s_value .= ' ';
					}
				}
				break;
			case '.':

				break;
			case '*':

				$s_value .= ' ';
				break;
			default:

				$s_value .= GetDerivedValue($a_form_data,$a_value_spec[$ii],$a_errors);
				break;
		}
	}
	return ($s_value);
}

function CreateDerived($a_form_data)
{
	if (isset($a_form_data["derive_fields"])) {
		$a_errors = array();

		$a_list = TrimArray(explode(",",$a_form_data["derive_fields"]));
		foreach ($a_list as $s_fld_spec) {
			if ($s_fld_spec === "")

			{
				continue;
			}
			if (($i_pos = strpos($s_fld_spec,"=")) === false) {
				$a_errors[] = $s_fld_spec;
				continue;
			}
			$s_name     = trim(substr($s_fld_spec,0,$i_pos));
			$s_fld_spec = substr($s_fld_spec,$i_pos + 1);

			if (($a_value_spec = ParseDerivation($a_form_data,$s_fld_spec,
			                                     $s_name,$a_errors)) === false
			) {
				break;
			}
			$a_form_data[$s_name] = DeriveValue($a_form_data,$a_value_spec,$s_name,$a_errors);
		}
		if (count($a_errors) > 0) {
			SendAlert(GetMessage(MSG_DERIVED_INVALID) . implode("\n",$a_errors));
			Error("derivation_failure",GetMessage(MSG_INT_FORM_ERROR));
		}
	}
	return ($a_form_data);
}

function SetFileNames($s_name_spec,$a_order,$a_fields,$a_raw_fields,$a_all_raw_values,$a_file_vars)
{
	$a_errors = array();

	$a_list = TrimArray(explode(",",$s_name_spec));
	foreach ($a_list as $s_fld_spec) {
		if ($s_fld_spec === "")

		{
			continue;
		}
		if (($i_pos = strpos($s_fld_spec,"=")) === false) {
			$a_errors[] = $s_fld_spec;
			continue;
		}
		$s_name     = trim(substr($s_fld_spec,0,$i_pos));
		$s_fld_spec = substr($s_fld_spec,$i_pos + 1);

		if (($a_value_spec = ParseDerivation($a_raw_fields,$s_fld_spec,
		                                     $s_name,$a_errors)) === false
		) {
			break;
		}
		if (isset($a_file_vars[$s_name]) && IsUploadedFile($a_file_vars[$s_name])) {

			$a_file_vars[$s_name]["new_name"] = DeriveValue($a_raw_fields,
			                                                $a_value_spec,$s_name,
			                                                $a_errors);

			ProcessField("name_of_$s_name",$a_file_vars[$s_name]["new_name"],
			             $a_order,$a_fields,$a_raw_fields);
			$a_all_raw_values["name_of_$s_name"] = $a_file_vars[$s_name]["new_name"];
		}

	}
	if (count($a_errors) > 0) {
		SendAlert(GetMessage(MSG_FILE_NAMES_INVALID) . implode("\n",$a_errors));
		Error("file_names_derivation_failure",GetMessage(MSG_INT_FORM_ERROR));
	}
	return (array($a_order,$a_fields,$a_raw_fields,$a_all_raw_values,$a_file_vars));
}

$aProcessSpecsFormData  = array();
$sProcessSpecsFieldName = "";

function    ProcessSpecsMatch($a_matches)
{
	global $aProcessSpecsFormData,$sProcessSpecsFieldName;

	$s_spec   = substr($a_matches[0],1,-1);
	$a_errors = array();
	$s_value  = ValueSpec($s_spec,$aProcessSpecsFormData,$a_errors);
	return ($s_value);
}

function    ProcessSpecs($s_fld_name,$a_form_data,$s_value)
{
	global $aProcessSpecsFormData,$sProcessSpecsFieldName;

	$aProcessSpecsFormData  = $a_form_data;
	$sProcessSpecsFieldName = $s_fld_name;

	$s_value                = preg_replace_callback('/%.+?%/',"ProcessSpecsMatch",$s_value);
	$aProcessSpecsFormData  = array();
	$sProcessSpecsFieldName = "";
	return ($s_value);
}

function ProcessAttributeList($s_fld_name,$a_form_data,$a_list,&$a_attribs,&$a_errors,
                              $a_valid_attribs = array())
{
	$b_got_valid_list = (count($a_valid_attribs) > 0);
	foreach ($a_list as $s_attrib) {

		if (($i_pos = strpos($s_attrib,"=")) === false) {
			$s_name = trim($s_attrib);
			if (empty($s_name) || $s_name[0] == '.') {
				continue;
			}

			$a_attribs[$s_name] = true;
		} else {
			$s_name = trim(substr($s_attrib,0,$i_pos));
			if (empty($s_name) || $s_name[0] == '.') {
				continue;
			}
			$s_value_list = substr($s_attrib,$i_pos + 1);
			if (($i_pos = strpos($s_value_list,";")) === false)

			{
				$a_attribs[$s_name] = ProcessSpecs($s_fld_name,$a_form_data,trim($s_value_list));
			} else

			{
				$a_attribs[$s_name] = TrimArray(explode(";",$s_value_list));
			}
		}
		if ($b_got_valid_list && !isset($a_valid_attribs[$s_name])) {
			$a_errors[] = $s_name;
		}
	}
}

function ProcessOptions($s_name,$a_form_data,&$a_options,$a_valid_options)
{
	$a_errors  = array();
	$a_options = array();
	if (isset($a_form_data[$s_name])) {

		$a_list = TrimArray(explode(",",$a_form_data[$s_name]));
		ProcessAttributeList($s_name,$a_form_data,$a_list,$a_options,$a_errors,$a_valid_options);
	}
	if (count($a_errors) > 0) {
		SendAlert(GetMessage(MSG_OPTIONS_INVALID,array("OPT" => $s_name)) .
		          implode("\n",$a_errors));
	}
}

function ProcessMailOptions($a_form_data)
{
	global $MAIL_OPTS,$VALID_MAIL_OPTIONS;

	ProcessOptions("mail_options",$a_form_data,$MAIL_OPTS,$VALID_MAIL_OPTIONS);
}

function IsMailOptionSet($s_name)
{
	global $MAIL_OPTS;

	return (isset($MAIL_OPTS[$s_name]));
}

function GetMailOption($s_name)
{
	global $MAIL_OPTS;

	return (isset($MAIL_OPTS[$s_name]) ? $MAIL_OPTS[$s_name] : NULL);
}

function ProcessCRMOptions($a_form_data)
{
	global $CRM_OPTS,$VALID_CRM_OPTIONS;

	ProcessOptions("crm_options",$a_form_data,$CRM_OPTS,$VALID_CRM_OPTIONS);
}

function IsCRMOptionSet($s_name)
{
	global $CRM_OPTS;

	return (isset($CRM_OPTS[$s_name]));
}

function GetCRMOption($s_name)
{
	global $CRM_OPTS;

	return (isset($CRM_OPTS[$s_name]) ? $CRM_OPTS[$s_name] : NULL);
}

function IsMailExcluded($s_name)
{
	$a_list = GetMailOption("Exclude");
	if (!isset($a_list)) {
		return (false);
	}
	if (is_array($a_list)) {
		return (in_array($s_name,$a_list));
	} else {
		return ($s_name === $a_list);
	}
}

function ProcessAROptions($a_form_data)
{
	global $AR_OPTS,$VALID_AR_OPTIONS;

	ProcessOptions("autorespond",$a_form_data,$AR_OPTS,$VALID_AR_OPTIONS);
}

function IsAROptionSet($s_name)
{
	global $AR_OPTS;

	return (isset($AR_OPTS[$s_name]));
}

function GetAROption($s_name)
{
	global $AR_OPTS;

	return (isset($AR_OPTS[$s_name]) ? $AR_OPTS[$s_name] : NULL);
}

function ProcessFilterOptions($a_form_data)
{
	global $FILTER_OPTS,$VALID_FILTER_OPTIONS;

	ProcessOptions("filter_options",$a_form_data,$FILTER_OPTS,$VALID_FILTER_OPTIONS);
}

function IsFilterOptionSet($s_name)
{
	global $FILTER_OPTS;

	return (isset($FILTER_OPTS[$s_name]));
}

function GetFilterOption($s_name)
{
	global $FILTER_OPTS;

	return (isset($FILTER_OPTS[$s_name]) ? $FILTER_OPTS[$s_name] : NULL);
}

function GetFilterAttrib($s_filter,$s_attrib)
{
	global $FILTER_ATTRIBS_LOOKUP;

	$a_attribs = Settings::get('FILTER_ATTRIBS');
	if (!isset($a_attribs[$s_filter]))

	{
		return (false);
	}
	if (!isset($FILTER_ATTRIBS_LOOKUP[$s_filter])) {

		$a_list                           = TrimArray(explode(",",$a_attribs[$s_filter]));
		$FILTER_ATTRIBS_LOOKUP[$s_filter] = array();
		$a_errors                         = array();

		ProcessAttributeList('FILTER_ATTRIBS',array(),$a_list,$FILTER_ATTRIBS_LOOKUP[$s_filter],$a_errors);
	}

	if (!isset($FILTER_ATTRIBS_LOOKUP[$s_filter][$s_attrib])) {
		return (false);
	}
	return ($FILTER_ATTRIBS_LOOKUP[$s_filter][$s_attrib]);
}

function IsFilterAttribSet($s_filter,$s_attrib)
{
	return (GetFilterAttrib($s_filter,$s_attrib));
}

function ProcessFormIniFile($s_file)
{
	global $EMAIL_ADDRS,$ValidEmails;

	$a_sections = parse_ini_file($s_file,TRUE);

	if ($a_sections === false) {
		Error("bad_ini",GetMessage(MSG_INI_PARSE_ERROR,array("FILE" => $s_file)));
	} elseif (empty($a_sections)) {
		SendAlert(GetMessage(MSG_INI_PARSE_WARN,array("FILE" => $s_file)),false,true);
	}
	if (Settings::get('DB_SEE_INI')) {

		$s_text = "<p><b>The following settings were found in the file '$s_file':</b></p>";
		foreach ($a_sections as $s_sect => $a_settings) {
			$s_text .= "<p>[$s_sect]\n";
			foreach ($a_settings as $s_name => $s_value) {
				$s_text .= "$s_name = \"$s_value\"\n";
			}
			$s_text .= "</p>";
		}
		CreatePage($s_text,"Debug Output - INI File Display");
		exit;
	}

	if (isset($a_sections["email_addresses"])) {
		$EMAIL_ADDRS = $a_sections["email_addresses"];

		foreach ($EMAIL_ADDRS as $s_list) {
			$ValidEmails->AddAddresses($s_list);
		}
	}

	if (isset($a_sections["special_fields"])) {
		foreach ($a_sections["special_fields"] as $s_name => $m_value) {
			if (IsSpecialField($s_name)) {
				SetSpecialField($s_name,$m_value);

				if ($s_name === "recipients" || $s_name === "cc" || $s_name === "bcc")

				{
					if (is_string($m_value)) {
						$ValidEmails->AddAddresses($m_value);
					}
				}
			}

			if (($a_multi_fld = IsSpecialMultiField($s_name)) !== false) {
				SetSpecialMultiField($a_multi_fld[0],$a_multi_fld[1],$m_value);
			}
		}
	}
}

function UnMangle($email)
{
	global $EMAIL_ADDRS;


	if (isset($EMAIL_ADDRS[$email])) {
		$email = $EMAIL_ADDRS[$email];
	}

	if (Settings::get('AT_MANGLE') != "") {
		$email = str_replace(Settings::get('AT_MANGLE'),"@",$email);
	}
	return ($email);
}

function CheckEmailAddress($m_addr,&$s_valid,&$s_invalid,$b_check = true)
{
	global $ValidEmails;

	$s_invalid = $s_valid = "";
	if (is_array($m_addr)) {
		$a_list = array();
		foreach ($m_addr as $s_addr_list) {
			$a_list = array_merge($a_list,TrimArray(explode(",",$s_addr_list)));
		}
	} else {
		$a_list = TrimArray(explode(",",$m_addr));
	}
	$a_invalid = array();
	$n_empty   = 0;
	for ($ii = 0 ; $ii < count($a_list) ; $ii++) {
		if ($a_list[$ii] === "") {

			$n_empty++;
			continue;
		}
		$s_email = UnMangle($a_list[$ii]);

		$a_this_list = TrimArray(explode(",",$s_email));
		foreach ($a_this_list as $s_email) {
			if ($s_email === "") {

				$n_empty++;
				continue;
			}
			if ($b_check) {
				$b_is_valid = $ValidEmails->CheckAddress($s_email);
			} else {
				$b_is_valid = true;
			}
			if ($b_is_valid) {
				if (empty($s_valid)) {
					$s_valid = $s_email;
				} else {
					$s_valid .= "," . $s_email;
				}
			} else {
				$a_invalid[] = $s_email;
			}
		}
	}

	if (empty($s_valid) && $n_empty > 0) {
		$a_invalid[] = GetMessage(MSG_EMPTY_ADDRESSES,array("COUNT" => $n_empty));
	}
	if (count($a_invalid) > 0) {
		$s_invalid = implode(",",$a_invalid);
	}
	return (!empty($s_valid));
}

function Redirect($url,$title)
{
	global $ExecEnv;

	if ($ExecEnv->allowSessionURL()) {
		if (session_id() !== "") {
			$url = AddURLParams($url,session_name() . "=" . urlencode(session_id()));
		} elseif (defined("SID")) {
			$url = AddURLParams($url,SID);
		}
	}

	if (function_exists('session_write_close')) {
		session_write_close();
	}

	header("Location: $url");

	$s_text = GetMessage(MSG_PLSWAIT_REDIR) . "\n\n";
	$s_text .= "<script language=\"JavaScript\" type=\"text/javascript\">";
	$s_text .= "window.location.href = '$url';";
	$s_text .= "</script>";
	$s_text .= "\n\n" . GetMessage(MSG_IFNOT_REDIR,array("URL" => $url));
	CreatePage($s_text,$title);
	exit;
}

class   JSON
{
	function    _Format($m_val)
	{
		if (is_bool($m_val)) {
			$s_value = ($m_val) ? "true" : "false";
		} elseif (is_string($m_val)) {

			$s_value = '"' . str_replace(array("\r","\n"),array('\\r','\\n'),addslashes($m_val)) . '"';
		} elseif (is_numeric($m_val)) {
			$s_value = $m_val;
		} elseif (is_array($m_val)) {
			$s_value = $this->_FormatArray($m_val);
		} else {
			$s_value = "null";
		}
		return ($s_value);
	}

	function _FormatArray($a_array)
	{
		if ($this->_IsNumericArray($a_array)) {
			$a_values = array();
			foreach ($a_array as $m_val) {
				$a_values[] = $this->_Format($m_val);
			}
			$s_value = "[" . implode(",",$a_values) . "]";
		} else {

			$s_value = $this->MakeObject($a_array);
		}
		return ($s_value);
	}

	function _IsNumericArray($a_data)
	{
		if (empty($a_data)) {
			return (true);
		} 
		$a_keys = array_keys($a_data);
		foreach ($a_keys as $m_index) {
			if (!is_int($m_index)) {
				return (false);
			}
		}
		return (true);
	}

	function    MakeObject($a_data)
	{
		$a_members = array();
		foreach ($a_data as $s_key => $m_val) {
			$a_members[] = '"' . $s_key . '":' . $this->_Format($m_val);
		}
		return ("{" . implode(",",$a_members) . "}");
	}
}

function    CORS_Response()
{
	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Max-Age: 36000');
	header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
	header('Access-Control-Allow-Headers: X-Requested-With');
}

function    JSON_Result($s_result,$a_data = array())
{
	global $aGetVars;

	FMDebug("Sending JSON_Result: $s_result");
	$a_data["Result"] = $s_result;
	$json             = new JSON();
	$s_ret            = $json->MakeObject($a_data);
	CORS_Response();

	if (isset($aGetVars['callback']) && $aGetVars['callback'] != '') {
		header('Content-Type: text/javascript; charset=utf-8');
		$s_ret = $aGetVars['callback'] . "($s_ret);";
		FMDebug('JSONP request callback=' . $aGetVars['callback']);
	} else {
		header('Content-Encoding: utf-8');
		header('Content-Type: application/json; charset=utf-8');
	}
	FMDebug("JSON_Result output: " . $s_ret);
	echo $s_ret;
}

function JoinLines($s_sep,$a_lines)
{
	$s_str = "";
	if (($i_sep_len = strlen($s_sep)) == 0)

	{
		return (implode("",$a_lines));
	}
	$n_lines = count($a_lines);
	for ($ii = 0 ; $ii < $n_lines ; $ii++) {
		$s_line = $a_lines[$ii];
		if (substr($s_line,-$i_sep_len) == $s_sep) {
			$s_str .= $s_line;
		} else {
			$s_str .= $s_line;

			if ($ii < $n_lines - 1) {
				$s_str .= $s_sep;
			}
		}
	}
	return ($s_str);
}

function OrderHeaders($a_headers)
{

	$a_ordering        = array("From","Subject","To","Cc","Bcc","Reply-To");
	$a_ordered_headers = array();
	foreach ($a_ordering as $s_name) {
		if (isset($a_headers[$s_name])) {
			$a_ordered_headers[] = array($s_name => $a_headers[$s_name]);
			unset($a_headers[$s_name]);
		}
	}

	foreach ($a_headers as $s_name => $s_value) {
		$a_ordered_headers[] = array($s_name => $a_headers[$s_name]);
	}
	return ($a_ordered_headers);
}

function    SafeHeader($s_str)
{
	return (str_replace('"','\\"',$s_str));
}

function    SafeHeaderWords($s_str)
{

	$s_specials = '()<>@,;:\\".[]'; // special characters defined by RFC822
	$s_str      = preg_replace('/[[:cntrl:]]+/',"?",$s_str); // zap all control chars
	$s_str      = preg_replace("/[" . preg_quote($s_specials,"/") . "]/","?",$s_str); // zap all specials
	return ($s_str);
}

function    SafeHeaderQString($s_str)
{
	return (str_replace('"','\\"',
	                    str_replace("\\","\\\\",
	                                str_replace("\r"," ",
	                                            str_replace("\r\n"," ",$s_str)))));
}

function    SafeHeaderComment($s_str)
{
	return (str_replace("(","\\(",
	                    str_replace(")","\\)",
	                                str_replace("\\","\\\\",
	                                            str_replace("\r"," ",
	                                                        str_replace("\r\n"," ",$s_str))))));
}

function    SafeHeaderEmail($s_str)
{

	$s_str = preg_replace('/[[:cntrl:]]+/',"",$s_str); // zap all control chars
	return ($s_str);
}

function ExpandMailHeaders($a_headers,$b_fold = false)
{
	$s_hdrs            = "";
	$a_ordered_headers = OrderHeaders($a_headers);
	for ($ii = 0 ; $ii < count($a_ordered_headers) ; $ii++) {
		foreach ($a_ordered_headers[$ii] as $s_name => $s_value) {
			if ($s_name != "") {
				if ($s_hdrs != "") {
					$s_hdrs .= Settings::get('HEAD_CRLF');
				}
				if ($b_fold) {
					$s_hdrs .= HeaderFolding($s_name . ": " . $s_value);
				} else {
					$s_hdrs .= $s_name . ": " . $s_value;
				}
			}
		}
	}

	return ($s_hdrs);
}


function ExpandMailHeadersArray($a_headers)
{
	$a_hdrs            = array();
	$a_ordered_headers = OrderHeaders($a_headers);
	for ($ii = 0 ; $ii < count($a_ordered_headers) ; $ii++) {
		foreach ($a_ordered_headers[$ii] as $s_name => $s_value) {
			if ($s_name != "") {
				$a_hdrs[] = $s_name . ": " . $s_value . Settings::get('HEAD_CRLF');
			}
		}
	}
	return ($a_hdrs);
}

function DoMail($s_to,$s_subject,$s_mesg,$a_headers,$s_options)
{

	$s_subject = EncodeHeaderText($s_subject);
	if (!Settings::isEmpty('PEAR_SMTP_HOST')) {

		require_once("Mail.php");

		$a_params = array("host" => Settings::get('PEAR_SMTP_HOST'),
		                  "port" => Settings::get('PEAR_SMTP_PORT')
		);
		if (!Settings::isEmpty('PEAR_SMTP_USER')) {
			$a_params["auth"]     = TRUE;
			$a_params["username"] = Settings::get('PEAR_SMTP_USER');
			$a_params["password"] = Settings::get('PEAR_SMTP_PWD');
		}
		$mailer = Mail::factory("smtp",$a_params);
		if (!is_object($mailer)) {
			ShowError("pear_error",GetMessage(MSG_PEAR_OBJ),FALSE,FALSE);
			exit;
		}
		if (strtolower(get_class($mailer)) === 'pear_error') {
			ShowError("pear_error",$mailer->getMessage(),FALSE,FALSE);
			exit;
		}
		if (!isset($a_headers['To']) && !isset($a_headers['to'])) {
			$a_headers['To'] = SafeHeader($s_to);
		}
		if (!isset($a_headers['Subject']) && !isset($a_headers['subject'])) {
			$a_headers['Subject'] = SafeHeader($s_subject);
		}
		$res = $mailer->send($s_to,$a_headers,$s_mesg);
		if ($res === TRUE) {
			return (TRUE);
		}

		global $aAlertInfo;

		$aAlertInfo[] = GetMessage(MSG_PEAR_ERROR,array("MSG" => $res->getMessage()));
		return (FALSE);
	} else {

		if ($s_options !== "") {
			return (mail($s_to,$s_subject,$s_mesg,ExpandMailHeaders($a_headers),$s_options));
		} else {
			return (mail($s_to,$s_subject,$s_mesg,ExpandMailHeaders($a_headers)));
		}
	}
}

function SendCheckedMail($to,$subject,$mesg,$sender,$a_headers = array())
{

	$b_f_option    = false;
	$b_form_option = IsMailOptionSet("SendMailFOption"); // this is superseded, but still supported
	if (Settings::get('SENDMAIL_F_OPTION') || $b_form_option) {
		if (empty($sender)) {

			if ($b_form_option) {

				static $b_in_here = false;
				global $SERVER;

				if (!$b_in_here) // prevent infinite recursion
				{
					$b_in_here = true;
					SendAlert(GetMessage(MSG_NO_FOPT_ADDR));
					$b_in_here = false;
				}

				$sender            = "dummy@" . (isset($SERVER) ? $SERVER : "UnknownServer");
				$a_headers['From'] = $sender;
				$b_f_option        = true;
			}
		} else {
			$b_f_option = true;
		}
	}
	if (Settings::get('INI_SET_FROM') && !empty($sender)) {
		ini_set('sendmail_from',$sender);
	}

	return (DoMail($to,$subject,$mesg,$a_headers,($b_f_option ? "-f$sender" : "")));
}

function SendAlert($s_error,$b_filter = true,$b_non_error = false)
{
	global $SPECIAL_VALUES,$FORMATTED_INPUT,$aServerVars,$aStrippedFormVars;
	global $aAlertInfo,$aCleanedValues,$aFieldOrder,$sHTMLCharSet;

	$s_error      = str_replace("\n",Settings::get('BODY_LF'),$s_error);
	$b_got_filter = GetFilterSpec($s_filter_name,$a_filter_list);

	$b_show_data = true;
	if ($b_got_filter && !$b_filter) {
		$b_show_data = false;
	}

	$s_form_subject = $s_alert_to = "";
	$b_check        = true;

	if (isset($SPECIAL_VALUES["alert_to"])) {
		$s_alert_to = trim($SPECIAL_VALUES["alert_to"]);
	}
	if (empty($s_alert_to) && isset($aStrippedFormVars["alert_to"])) {
		$s_alert_to = trim($aStrippedFormVars["alert_to"]);
	}

	if (isset($SPECIAL_VALUES["subject"])) {
		$s_form_subject = trim($SPECIAL_VALUES["subject"]);
	}
	if (empty($s_form_subject) && isset($aStrippedFormVars["subject"])) {
		$s_form_subject = trim($aStrippedFormVars["subject"]);
	}

	if (empty($s_alert_to)) {
		$s_alert_to = Settings::get('DEF_ALERT');
		$b_check    = false;
	}
	if (!empty($s_alert_to)) {
		$s_from_addr = $s_from = "";
		$a_headers   = array();
		if (!Settings::isEmpty('FROM_USER')) {
			if (Settings::get('FROM_USER') != "NONE") {
				$a_headers['From'] = Settings::get('FROM_USER');
				$s_from_addr       = Settings::get('FROM_USER');
				$s_from            = "From: $s_from_addr";
			}
		} else {
			global $SERVER;

			$s_from_addr       = "FormMail@" . $SERVER;
			$a_headers['From'] = $s_from_addr;
			$s_from            = "From: $s_from_addr";
		}
		$s_mesg = "To: " . UnMangle($s_alert_to) . Settings::get('BODY_LF');

		$s_charset = "";
		if (isset($sHTMLCharSet) && $sHTMLCharSet !== "") {
			$s_charset = $sHTMLCharSet;
		} else {
			if (IsMailOptionSet("CharSet")) {
				$s_charset = GetMailOption("CharSet");
			}
		}

		if (function_exists("html_entity_decode")) {
			$s_error = @html_entity_decode($s_error,ENT_COMPAT,$s_charset);
		}

		if ($s_charset !== "") {
			$a_headers['Content-Type'] = SafeHeader("text/plain; charset=$s_charset");
		}

		if (!empty($s_from)) {
			$s_mesg .= $s_from . Settings::get('BODY_LF');
		}
		$s_mesg .= Settings::get('BODY_LF');
		if (count($aAlertInfo) > 0) {
			if ($b_show_data) {
				$s_error .= Settings::get('BODY_LF') . GetMessage(MSG_MORE_INFO) . Settings::get('BODY_LF');
				$s_error .= implode(Settings::get('BODY_LF'),$aAlertInfo);
			} else {
				$s_error .= Settings::get('BODY_LF') . GetMessage(MSG_INFO_STOPPED) . Settings::get('BODY_LF');
			}
		}

		$a_safe_fields = array(
			"email: " . $SPECIAL_VALUES["email"],
			"realname: " . $SPECIAL_VALUES["realname"],
		);
		$s_safe_data   = implode(Settings::get('BODY_LF'),$a_safe_fields);

		if ($b_non_error) {
			$s_preamble = $s_error . Settings::get('BODY_LF') . Settings::get('BODY_LF');
			$s_mesg .= $s_preamble;
			$s_subj = GetMessage(MSG_FM_ALERT);
			if (!empty($s_form_subject)) {
				$s_subj .= " ($s_form_subject)";
			}
		} else {
			$s_preamble = GetMessage(MSG_FM_ERROR_LINE) . Settings::get('BODY_LF') .
			              $s_error . Settings::get('BODY_LF') . Settings::get('BODY_LF');
			$s_mesg .= $s_preamble;
			$s_subj = GetMessage(MSG_FM_ERROR);
			if (!empty($s_form_subject)) {
				$s_subj .= " ($s_form_subject)";
			}
			$s_mesg .= $s_safe_data;
			$s_mesg .= Settings::get('BODY_LF') . Settings::get('BODY_LF');
			if ($b_show_data) {
				$s_mesg .= implode(Settings::get('BODY_LF'),$FORMATTED_INPUT);
			} else {
				$s_mesg .= GetMessage(MSG_USERDATA_STOPPED);
			}
		}

		if ($b_filter && $b_got_filter &&
		    IsFilterAttribSet($SPECIAL_VALUES["filter"],"Encrypts")
		) {
			$s_new_mesg = $s_preamble . $s_safe_data;
			$s_new_mesg .= Settings::get('BODY_LF') . Settings::get('BODY_LF');

			if ($a_filter_list !== false) {

				list($s_unfiltered,$s_filtered_results) =
					GetFilteredOutput($aFieldOrder,$aCleanedValues,
					                  $s_filter_name,$a_filter_list);
				$s_new_mesg .= $s_unfiltered;
			} else {

				$s_filtered_results = Filter($s_filter_name,$s_mesg);
			}
			$s_new_mesg .= GetMessage(MSG_FILTERED,array("FILTER" => $s_filter_name)) .
			               Settings::get('BODY_LF') . Settings::get('BODY_LF') .
			               $s_filtered_results;
			$s_mesg = $s_new_mesg;
		}
		$s_mesg .= Settings::get('BODY_LF');

		if (isset($aServerVars['HTTP_REFERER'])) {
			$s_mesg .= "Referring page was " . $aServerVars['HTTP_REFERER'];
		} elseif (isset($SPECIAL_VALUES['this_form']) && $SPECIAL_VALUES['this_form'] !== "") {
			$s_mesg .= "Referring form was " . $SPECIAL_VALUES['this_form'];
		}

		$s_mesg .= Settings::get('BODY_LF');

		if (isset($aServerVars['SERVER_NAME'])) {
			$s_mesg .= "SERVER_NAME was " . $aServerVars['SERVER_NAME'] . Settings::get('BODY_LF');
		}
		if (isset($aServerVars['REQUEST_URI'])) {
			$s_mesg .= "REQUEST_URI was " . $aServerVars['REQUEST_URI'] . Settings::get('BODY_LF');
		}

		$s_mesg .= Settings::get('BODY_LF');

		if (isset($aServerVars['REMOTE_ADDR'])) {
			$s_mesg .= "User IP address was " . $aServerVars['REMOTE_ADDR'] . Settings::get('BODY_LF');
		}
		if (isset($aServerVars['HTTP_USER_AGENT'])) {
			$s_mesg .= "User agent was " . $aServerVars['HTTP_USER_AGENT'] . Settings::get('BODY_LF');
		}

		if ($b_check) {
			if (CheckEmailAddress($s_alert_to,$s_valid,$s_invalid)) {
				return (SendCheckedMail($s_valid,$s_subj,$s_mesg,$s_from_addr,$a_headers));
			}
		} else {
			return (SendCheckedMail($s_alert_to,$s_subj,$s_mesg,$s_from_addr,$a_headers));
		}
	}
	return (false);
}

function ReadLines($fp)
{
	$a_lines = array();
	while (!feof($fp)) {
		$s_line = fgets($fp,4096);

		$s_line    = str_replace("\r","",$s_line);
		$s_line    = str_replace("\n","",$s_line);
		$a_lines[] = $s_line;
	}
	return ($a_lines);
}

function GetURL($s_url,&$s_error,$b_ret_lines = false,$n_depth = 0)
{
	global $php_errormsg,$aServerVars,$sUserAgent,$ExecEnv;

	if ($ExecEnv->allowSessionURL()) {
		if (session_id() !== "") {
			$s_url = AddURLParams($s_url,session_name() . "=" . urlencode(session_id()));
		}
		if (defined("SID")) {
			$s_url = AddURLParams($s_url,SID);
		}
	}

	$http_get = new HTTPGet($s_url);

	if (Settings::get('AUTHENTICATE') !== "" || Settings::get('AUTH_USER') !== "" || Settings::get('AUTH_PW') !== "") {
		if (Settings::get('AUTHENTICATE') === "") {
			$http_get->SetAuthentication("Basic",Settings::get('AUTH_USER'),Settings::get('AUTH_PW'));
		} else {
			$http_get->SetAuthenticationLine(Settings::get('AUTHENTICATE'));
		}
	} else {
		$a_parts = $http_get->GetURLSplit();
		if (isset($a_parts["user"]) || isset($a_parts["pass"])) {
			$s_auth_user = isset($a_parts["user"]) ? $a_parts["user"] : "";
			$s_auth_pass = isset($a_parts["pass"]) ? $a_parts["pass"] : "";
		} else {
			$s_auth_type = isset($aServerVars["PHP_AUTH_TYPE"]) ? $aServerVars["PHP_AUTH_TYPE"] : "";
			$s_auth_user = isset($aServerVars["PHP_AUTH_USER"]) ? $aServerVars["PHP_AUTH_USER"] : "";
			$s_auth_pass = isset($aServerVars["PHP_AUTH_PW"]) ? $aServerVars["PHP_AUTH_PW"] : "";
		}
		if (!isset($s_auth_type) || $s_auth_type === "") {
			$s_auth_type = "Basic";
		}
		if ($s_auth_user !== "" || $s_auth_pass !== "") {
			$http_get->SetAuthentication($s_auth_type,$s_auth_user,$s_auth_pass);
		}
	}

	$http_get->SetAgent($sUserAgent);

	$http_get->Resolve();

	$b_closed = false;
	if (function_exists('session_write_close')) {
		session_write_close();
		$b_closed = true;

	}

	$m_buf = FALSE;

	if (($a_lines = $http_get->Read()) === FALSE) {
		$http_get->Close();

		list($i_error,$i_sys_err,$s_sys_msg) = $http_get->GetError();
		switch ($i_error) {
			case $http_get->nErrParse:
				$s_error = GetMessage(MSG_URL_PARSE);
				break;
			case $http_get->nErrScheme:
				$a_parts = $http_get->GetURLSplit();
				$s_error = GetMessage(MSG_URL_SCHEME,array("SCHEME" => $a_parts["scheme"]));
				break;
			default:
				$s_error = GetMessage(MSG_SOCKET,
				                      array("ERRNO"  => $i_sys_err,
				                            "ERRSTR" => $s_sys_msg,
				                            "PHPERR" => isset($php_errormsg) ? $php_errormsg : ""
				                      ));
				break;
		}
	} else {
		$http_get->Close();

		list($i_http_code,$s_http_status) = $http_get->GetHTTPStatus();

		if ($i_http_code < 200 || $i_http_code > 299) {
			switch ($i_http_code) {
				case 300: // multiple choices (we'll take the first)
				case 301: // moved permanently
				case 302: // found
				case 303: // see other
				case 307: // temporary redirect

					if ($n_depth < 10) {
						if (($s_location = $http_get->FindHeader("location")) !== false) {
							FMDebug("Redirect from '$s_url' to '$s_location'");
							$m_buf    = GetURL($s_location,$s_error,$b_ret_lines,$n_depth + 1);
							$b_closed = false;
							break;
						}
						FMDebug("Redirect FAILED - no location header");
					} else {
						FMDebug("Redirect FAILED depth=$n_depth");
					}

				default:
					$s_error = GetMessage(MSG_GETURL_OPEN,array("STATUS" => $s_http_status,"URL" => $s_url));
					break;
			}
		} elseif ($b_ret_lines) {
			$m_buf = $a_lines;
		} else

		{
			$m_buf = implode("",$a_lines);
		}
	}

	if ($b_closed) {
		session_start();
	}

	return ($m_buf);
}

function    FMDebug($s_mesg)
{
	static $fDebug = NULL;

	if (!isset($fDebug)) {
		$fDebug    = false; // only initialize once
		$s_db_file = "fmdebug.log"; // look for debug file in current directory

		if (file_exists($s_db_file)) {
			if (($fDebug = fopen($s_db_file,"a")) === false) {
				return;
			}
		}
	}
	if ($fDebug !== false) {
		fwrite($fDebug,date('r') . ": " . $s_mesg . "\n");
		fflush($fDebug);
	}
}

class   NetIO
{
	var $_sHost;
	var $_iPort;
	var $_sPrefix;

	var $_iConnTimeout;
	var $_fSock;

	var $_aIPs;

	var $_iError = 0;
	var $_iSysErr;
	var $_sSysMesg;

	var $nErrInit = -1; // not initialized
	var $nErrRead = -2; // read error
	var $nErrWrite = -3; // write error
	var $nErrWriteShort = -4; // failed to write all bytes

	var $nErrSocket = -100; // error in socket open

	function    NetIO($s_host = NULL,$i_port = NULL,$s_prefix = "")
	{
		if (isset($s_host)) {
			$this->_sHost = $s_host;
		}
		if (isset($i_port)) {
			$this->_iPort = $i_port;
		}
		$this->_sPrefix      = $s_prefix;
		$this->_iConnTimeout = 30;
		$this->_iSysErr      = 0;
		$this->_sSysMesg     = "";
	}

	function    _SetError($i_error,$i_sys_err = 0,$s_sys_mesg = "")
	{
		$this->_iError   = $i_error;
		$this->_iSysErr  = $i_sys_err;
		$this->_sSysMesg = $s_sys_mesg;
		return (FALSE);
	}

	function    IsError()
	{
		return ($this->_iError != 0 ? TRUE : FALSE);
	}

	function    ClearError()
	{
		$this->_SetError(0);
	}

	function    GetError()
	{
		return (array($this->_iError,$this->_iSysErr,$this->_sSysMesg));
	}

	function    SetHost($s_host)
	{
		$this->_sHost = $s_host;
	}

	function    SetPort($i_port)
	{
		$this->_iPort = $i_port;
	}

	function    SetConnectionTimeout($i_secs)
	{
		$this->_iConnTimeout = $i_secs;
	}

	function    SetPrefix($s_prefix)
	{
		$this->_sPrefix = $s_prefix;
	}

	function    GetHost()
	{
		return (isset($this->_sHost) ? $this->_sHost : "");
	}

	function    GetPort()
	{
		return (isset($this->_iPort) ? $this->_iPort : 0);
	}

	function    GetPrefix()
	{
		return ($this->_sPrefix);
	}

	function    GetConnectionTimeout()
	{
		return ($this->_iConnTimeout);
	}

	function    _CacheIt()
	{
		FMDebug("Caching " . implode(",",$this->_aIPs));
		if (IsSetSession("FormNetIODNSCache")) {
			$a_cache = GetSession("FormNetIODNSCache");
		} else {
			$a_cache = array();
		}
		$a_cache[$this->_sHost] = $this->_aIPs;
		SetSession("FormNetIODNSCache",$a_cache);
	}

	function    _CheckCache()
	{
		if (!IsSetSession("FormNetIODNSCache")) {
			return (FALSE);
		}
		$a_cache = GetSession("FormNetIODNSCache");
		if (!is_array($a_cache) || !isset($a_cache[$this->_sHost]) || !is_array($a_cache[$this->_sHost])) {
			return (FALSE);
		}
		$this->_aIPs = $a_cache[$this->_sHost];
		return (TRUE);
	}

	function    Resolve()
	{
		$this->ClearError();
		if (!isset($this->_sHost)) {
			return ($this->_SetError($this->nErrInit));
		}
		if ($this->_CheckCache()) {
			return (TRUE);
		}
		FMDebug("Start resolve of " . $this->_sHost);

		if (($a_ip_list = gethostbynamel($this->_sHost)) === FALSE) {
			FMDebug("Resolve failed");
			return ($this->_SetError($this->nErrInit,0,
			                         GetMessage(MSG_RESOLVE,array("NAME" => $this->_sHost))));
		}
		FMDebug("Done resolve: " . implode(",",$a_ip_list));
		$this->_aIPs = $a_ip_list;
		$this->_CacheIt();
		return (TRUE);
	}

	function    _SSLOpen($s_ip,&$errno,&$errstr,$i_timeout)
	{
		FMDebug("Using _SSLOpen (stream_socket_client), SNI, host=" . $this->GetHost());
		$context = stream_context_create();
		$result  = stream_context_set_option($context,'ssl','verify_host',true);
		$result  = stream_context_set_option($context,'ssl','verify_peer',false);
		$result  = stream_context_set_option($context,'ssl','allow_self_signed',true);
		$result  = stream_context_set_option($context,'ssl','SNI_enabled',true);
		$result  = stream_context_set_option($context,'ssl','SNI_server_name',$this->GetHost());

		return (stream_socket_client($this->GetPrefix() . $s_ip . ":" . $this->GetPort(),
		                             $errno,$errstr,$i_timeout,STREAM_CLIENT_CONNECT,$context));
	}

	function    Open()
	{
		$this->ClearError();
		if (!isset($this->_sHost) || !isset($this->_iPort)) {
			return ($this->_SetError($this->nErrInit));
		}
		if (!$this->Resolve()) {
			return (FALSE);
		}
		FMDebug("Starting socket open");
		$f_sock = FALSE;

		if (count($this->_aIPs) == 1) {
			FMDebug("Trying host " . $this->_sHost . ", timeout " . $this->GetConnectionTimeout());
			$f_sock = @fsockopen($this->GetPrefix() . $this->_sHost,$this->GetPort(),
			                     $errno,$errstr,$this->GetConnectionTimeout());
		} else {
			foreach ($this->_aIPs as $s_ip) {
				global $ExecEnv;

				FMDebug("Trying IP $s_ip, timeout " . $this->GetConnectionTimeout());
				if ($ExecEnv->IsPHPAtLeast("5.3.2") && substr($this->GetPrefix(),0,3) == "ssl") {
					if (($f_sock = $this->_SSLOpen($s_ip,$errno,$errstr,
					                               $this->GetConnectionTimeout())) !== FALSE
					) {
						break;
					}
				} elseif (($f_sock = @fsockopen($this->GetPrefix() . $s_ip,$this->GetPort(),
				                                $errno,$errstr,$this->GetConnectionTimeout())) !== FALSE
				) {
					break;
				}
			}
		}
		if ($f_sock === FALSE) {
			FMDebug("open failed: $errno $errstr");
			return ($this->_SetError($this->nErrSocket,$errno,$errstr));
		}
		$this->_fSock = $f_sock;
		FMDebug("Done socket open");
		return (TRUE);
	}

	function    Read()
	{
		$this->ClearError();
		$a_lines = array();
		while (($s_line = fgets($this->_fSock)) !== FALSE) {
			$a_lines[] = $s_line;
		}
		FMDebug("Read " . count($a_lines) . " lines");
		return ($a_lines);
	}

	function    Write($s_str,$b_flush = TRUE)
	{
		$this->ClearError();
		if (!isset($this->_fSock)) {
			return ($this->_SetError($this->nErrInit));
		}
		if (($n_write = fwrite($this->_fSock,$s_str)) === FALSE) {
			return ($this->_SetError($this->nErrWrite));
		}
		if ($n_write != strlen($s_str)) {
			return ($this->_SetError($this->nErrWriteShort));
		}
		if ($b_flush) {
			if (fflush($this->_fSock) === FALSE) {
				return ($this->_SetError($this->nErrWriteShort));
			}
		}
		return (TRUE);
	}

	function    Close()
	{
		if (isset($this->_fSock)) {
			fclose($this->_fSock);
			unset($this->_fSock);
		}
	}
}

class   HTTPGet extends NetIO
{
	var $_sURL;
	var $_aURLSplit;

	var $_sRequest;
	var $_aResponse;
	var $_aRespHeaders;

	var $_sAuthLine;
	var $_sAuthType;
	var $_sAuthUser;
	var $_sAuthPass;

	var $_sAgent;

	var $nErrParse = -1000; // failed to parse URL
	var $nErrScheme = -1001; // unsupported URL scheme

	function    HTTPGet($s_url = "")
	{
		NetIO::NetIO();
		$this->_aURLSplit = array();
		if (($this->_sURL = $s_url) !== "") {
			$this->_SplitURL();
		}
	}

	function    _SplitURL()
	{
		FMDebug("URL: " . $this->_sURL);
		if (($this->_aURLSplit = parse_url($this->_sURL)) === FALSE) {
			$this->_aURLSplit = array();
			return ($this->_SetError($this->nErrParse));
		}
		return (TRUE);
	}

	function    GetURLSplit()
	{
		return ($this->_aURLSplit);
	}

	function    SetURL($s_url)
	{
		$this->_aURLSplit = array();
		$this->_sURL      = $s_url;
		return ($this->_SplitURL());
	}

	function    _Init()
	{
		if (!isset($this->_aURLSplit["host"])) {
			return ($this->_SetError($this->nErrInit));
		}
		$this->SetHost($this->_aURLSplit["host"]);
		$i_port    = 80;
		$b_use_ssl = false;
		if (isset($this->_aURLSplit["scheme"])) {
			switch (strtolower($this->_aURLSplit["scheme"])) {
				case "http":
					break;
				case "https":
					$b_use_ssl = true;
					$i_port    = 443;
					break;
				default:
					return ($this->_SetError($this->nErrScheme));
			}
		}
		if (isset($this->_aURLSplit["port"])) {
			$i_port = $this->_aURLSplit["port"];
		}
		if ($b_use_ssl)

		{
			$this->SetPrefix("ssl://");
		}
		$this->SetPort($i_port);
		return (TRUE);
	}

	function    _SendRequest()
	{
		$this->_PrepareRequest();
		return (parent::Write($this->_sRequest));
	}

	function    _PrepareRequest($s_method = 'GET')
	{
		FMDebug("Path: " . $this->_aURLSplit["path"]);
		if (!isset($this->_aURLSplit["path"]) || $this->_aURLSplit["path"] === "") {
			$s_path = "/";
		} // default path
		else {
			$s_path = $this->_aURLSplit["path"];
		}
		if (isset($this->_aURLSplit["query"])) {

			$a_params = explode('&',$this->_aURLSplit["query"]);
			foreach ($a_params as $i_idx => $s_param) {
				if (($i_pos = strpos($s_param,"=")) === false) {
					$a_params[$i_idx] = urlencode($s_param);
				} else {
					$a_params[$i_idx] = substr($s_param,0,$i_pos) . '=' .
					                    urlencode(substr($s_param,$i_pos + 1));
				}
			}
			$s_path .= "?" . implode('&',$a_params);
		}

		if (isset($this->_aURLSplit["fragment"])) {
			$s_path .= '#' . urlencode($this->_aURLSplit["fragment"]);
		}

		$s_req = $s_method . " $s_path HTTP/1.0\r\n";

		if (isset($this->_sAuthLine)) {
			$s_req .= "Authorization: $this->_sAuthLine\r\n";
		} elseif (isset($this->_sAuthType)) {
			$s_req .= "Authorization: " . $this->_sAuthType . " " .
			          base64_encode($this->_sAuthUser . ":" . $this->_sAuthPass) . "\r\n";
		}

		$s_req .= "Host: " . $this->GetHost() . "\r\n";

		if (isset($this->_sAgent)) {
			$s_req .= "User-Agent: " . $this->_sAgent . "\r\n";
		}

		$s_req .= "Accept: */*\r\n";
		$s_req .= $this->_AdditionalHeaders();

		$s_req .= "\r\n";
		$this->_sRequest = $s_req;
	}

	function    _AdditionalHeaders()
	{
		return ('');
	}

	function    _GetResponse()
	{
		FMDebug("Reading");
		if (($a_lines = parent::Read()) === FALSE) {
			return (FALSE);
		}

		$this->_aRespHeaders = $this->_aResponse = array();
		$b_body              = FALSE;
		for ($ii = 0 ; $ii < count($a_lines) ; $ii++) {
			if ($b_body) {
				//FMDebug("Body line: ".rtrim($a_lines[$ii]));
				$this->_aResponse[] = $a_lines[$ii];
			} elseif ($a_lines[$ii] == "\r\n" || $a_lines[$ii] == "\n") {
				$b_body = TRUE;
			} else {
				//FMDebug("Header line: ".rtrim($a_lines[$ii]));
				$this->_aRespHeaders[] = $a_lines[$ii];
			}
		}
		return (TRUE);
	}

	function    GetResponseHeaders()
	{
		return ($this->_aRespHeaders);
	}

	function    FindHeader($s_name)
	{
		$s_name = strtolower($s_name);
		$i_len  = strlen($s_name);
		for ($ii = 0 ; $ii < count($this->_aRespHeaders) ; $ii++) {
			$s_line = $this->_aRespHeaders[$ii];
			if (($s_hdr = substr($s_line,0,$i_len)) !== false) {
				$s_hdr = strtolower($s_hdr);
				if ($s_hdr === $s_name && substr($s_line,$i_len,1) === ":") {
					return (trim(substr($s_line,$i_len + 1)));
				}
			}
		}
		return (false);
	}

	function    GetHTTPStatus()
	{
		$i_http_code = 0;
		$s_status    = "";
		for ($ii = 0 ; $ii < count($this->_aRespHeaders) ; $ii++) {
			$s_line = $this->_aRespHeaders[$ii];
			if (substr($s_line,0,4) == "HTTP") {
				$i_pos     = strpos($s_line," ");
				$s_status  = substr($s_line,$i_pos + 1);
				$i_end_pos = strpos($s_status," ");
				if ($i_end_pos === false) {
					$i_end_pos = strlen($s_status);
				}
				$i_http_code = (int)substr($s_status,0,$i_end_pos);
			}
		}
		return (array($i_http_code,$s_status));
	}

	function    Resolve()
	{
		if (!$this->_Init()) {
			return (FALSE);
		}
		return (parent::Resolve());
	}

	function    Read()
	{
		if (!$this->_Init()) {
			return (FALSE);
		}
		FMDebug("Init done");
		if (!$this->Open()) {
			return (FALSE);
		}
		FMDebug("Open done");
		if (!$this->_SendRequest()) {
			return (FALSE);
		}
		FMDebug("Send done");
		if (!$this->_GetResponse()) {
			return (FALSE);
		}
		FMDebug("Get done");
		$this->Close();
		return ($this->_aResponse);
	}

	function    SetAuthenticationLine($s_auth)
	{
		$this->_sAuthLine = $s_auth;
	}

	function    SetAuthentication($s_type,$s_user,$s_pass)
	{
		$this->_sAuthType = $s_type;
		$this->_sAuthUser = $s_user;
		$this->_sAuthPass = $s_pass;
	}

	function    SetAgent($s_agent)
	{
		$this->_sAgent = $s_agent;
	}
}



class   HTTPPost extends HTTPGet
{
	var $_sPostData; /* data to POST */

	function    HTTPPost($s_url = "")
	{
		$this->_sPostData = '';
		HTTPGet::HTTPGet($s_url);
	}

	function    _SendRequest()
	{
		$this->_PrepareRequest();
		return (NetIO::Write($this->_sRequest));
	}

	function    _PrepareRequest($s_method = 'POST')
	{
		parent::_PrepareRequest($s_method);
		$this->_AddData();
	}

	function    _AdditionalHeaders()
	{

		$a_hdrs = array(
			'Content-Type: application/x-www-form-urlencoded',
			'Content-Length: ' . strlen($this->_sPostData),
		);
		return (implode("\r\n",$a_hdrs));
	}

	function    _AddData()
	{
		$this->_sRequest .= "\r\n"; // blank line after headers
		$this->_sRequest .= $this->_sPostData;
	}

	function    _EncodeData($a_fields)
	{
		$s_data = '';
		foreach ($a_fields as $s_name => $s_value) {
			if ($s_data != '') {
				$s_data .= '&';
			}
			$s_data .= urlencode($s_name) . '=' . urlencode($s_value);
		}
		return ($s_data);
	}

	function    Post($a_fields)
	{

		$this->_sPostData = $this->_EncodeData($a_fields);
		return ($this->Read());
	}
}

function LoadTemplate($s_name,$s_dir,$s_url,$b_ret_lines = false)
{
	global $php_errormsg;

	$s_buf   = "";
	$a_lines = array();
	if (!empty($s_dir)) {
		$s_name = "$s_dir/" . basename($s_name);
		@       $fp = fopen($s_name,"r");
		if ($fp === false) {
			SendAlert(GetMessage(MSG_OPEN_TEMPLATE,array("NAME"  => $s_name,
			                                             "ERROR" => CheckString($php_errormsg)
			)));
			return (false);
		}
		if ($b_ret_lines) {
			$a_lines = ReadLines($fp);
		} else

		{
			$s_buf = fread($fp,filesize($s_name));
		}
		fclose($fp);
	} else {
		if (substr($s_url,-1) == '/') {
			$s_name = "$s_url" . basename($s_name);
		} else {
			$s_name = "$s_url/" . basename($s_name);
		}
		if (($m_data = GetURL($s_name,$s_error,$b_ret_lines)) === false) {
			SendAlert($s_error);
			return (false);
		}
		if ($b_ret_lines) {
			$a_lines = $m_data;

			for ($ii = count($a_lines) ; --$ii >= 0 ;) {
				$s_line       = $a_lines[$ii];
				$s_line       = str_replace("\r","",$s_line);
				$s_line       = str_replace("\n","",$s_line);
				$a_lines[$ii] = $s_line;
			}
		} else {
			$s_buf = $m_data;
		}
	}
	return ($b_ret_lines ? $a_lines : $s_buf);
}

function ShowErrorTemplate($s_name,$a_specs,$b_user_error)
{

	if (Settings::isEmpty('TEMPLATEDIR') && Settings::isEmpty('TEMPLATEURL')) {
		SendAlert(GetMessage(MSG_TEMPLATES));
		return (false);
	}
	if (($s_buf = LoadTemplate($s_name,Settings::get('TEMPLATEDIR'),Settings::get('TEMPLATEURL'))) === false) {
		return (false);
	}

	foreach ($a_specs as $s_tag => $s_value)

	{
		$s_buf = preg_replace('/[<\[]\s*' . preg_quote($s_tag,"/") . '\s*\/\s*[>\]]/ims',
		                      nl2br($s_value),$s_buf);
	}
	if ($b_user_error) {

		$s_buf = preg_replace('/[<\[]\s*\/?\s*fmusererror\s*[>\]]/ims','',$s_buf);

		$s_buf = preg_replace('/[<\[]\s*fmsyserror\s*[>\]].*[<\[]\s*\/\s*fmsyserror\s*[>\]]/ims','',$s_buf);
	} else {

		$s_buf = preg_replace('/[<\[]\s*\/?\s*fmsyserror\s*[>\]]/ims','',$s_buf);

		$s_buf = preg_replace('/[<\[]\s*fmusererror\s*[>\]].*[<\[]\s*\/\s*fmusererror\s*[>\]]/ims','',$s_buf);
	}

	echo $s_buf;
	return (true);
}

function ShowError($error_code,$error_mesg,$b_user_error,
                   $b_alerted = false,$a_item_list = array(),$s_extra_info = "")
{
	global $SPECIAL_FIELDS,$SPECIAL_MULTI,$SPECIAL_VALUES;
	global $aServerVars,$aStrippedFormVars;

	SetSession("FormError",$error_mesg);
	SetSession("FormErrorInfo",$s_extra_info);
	SetSession("FormErrorCode",$error_code);
	SetSession("FormErrorItems",$a_item_list);
	SetSession("FormIsUserError",$b_user_error);
	SetSession("FormAlerted",$b_alerted);
	SetSession("FormData",array());

	$bad_url      = $SPECIAL_VALUES["bad_url"];
	$bad_template = $SPECIAL_VALUES["bad_template"];
	$this_form    = $SPECIAL_VALUES["this_form"];
	if (IsAjax()) {
		JSON_Result("ERROR",array("ErrorCode"  => $error_code,
		                          "UserError"  => $b_user_error,
		                          "ErrorMesg"  => $error_mesg,
		                          "Alerted"    => $b_alerted,
		                          "ErrorItems" => $a_item_list
		));
		ZapSession();
	} elseif (!empty($bad_url)) {
		$a_params = array();
		if (Settings::get('PUT_DATA_IN_URL')) {
			$a_params[] = "this_form=" . urlencode("$this_form");
			$a_params[] = "bad_template=" . urlencode("$bad_template");
			$a_params[] = "error=" . urlencode("$error_mesg");
			$a_params[] = "extra=" . urlencode("$s_extra_info");
			$a_params[] = "errcode=" . urlencode("$error_code");
			$a_params[] = "isusererror=" . ($b_user_error ? "1" : "0");
			$a_params[] = "alerted=" . ($b_alerted ? "1" : "0");
			$i_count    = 1;
			foreach ($a_item_list as $s_item) {
				$a_params[] = "erroritem$i_count=" . urlencode("$s_item");
				$i_count++;
			}
		} else {
			$a_sess_data                 = GetSession("FormData");
			$a_sess_data["this_form"]    = "$this_form";
			$a_sess_data["bad_template"] = "$bad_template";
			SetSession("FormData",$a_sess_data);

			$a_params[] = "insession=1";
		}

		foreach ($aStrippedFormVars as $s_name => $m_value) {

			$b_special = false;
			if (in_array($s_name,$SPECIAL_FIELDS)) {
				$b_special = true;
			} else {
				foreach ($SPECIAL_MULTI as $s_multi_fld) {
					$i_len = strlen($s_multi_fld);
					if (substr($s_name,0,$i_len) == $s_multi_fld) {
						$i_index = (int)substr($s_name,$i_len);
						if ($i_index > 0) {
							$b_special = true;
							break;
						}
					}
				}
			}
			if (!$b_special) {
				if (Settings::get('PUT_DATA_IN_URL')) {
					if (is_array($m_value)) {
						foreach ($m_value as $s_value) {
							$a_params[] = "$s_name" . '[]=' .
							              urlencode(substr($s_value,0,Settings::get('MAXSTRING')));
						}
					} else {
						$a_params[] = "$s_name=" . urlencode(substr($m_value,0,Settings::get('MAXSTRING')));
					}
				} else {
					$a_sess_data = GetSession("FormData");
					if (is_array($m_value)) {
						$a_sess_data["$s_name"] = $m_value;
					} else {
						$a_sess_data["$s_name"] = substr($m_value,0,Settings::get('MAXSTRING'));
					}
					SetSession("FormData",$a_sess_data);
				}
			}
		}

		if ((isset($aServerVars["PHP_AUTH_USER"]) &&
		     $aServerVars["PHP_AUTH_USER"] !== "") ||
		    (isset($aServerVars["PHP_AUTH_PW"]) &&
		     $aServerVars["PHP_AUTH_PW"] !== "")
		) {
			if (Settings::get('PUT_DATA_IN_URL')) {
				if (isset($aServerVars["PHP_AUTH_USER"])) {
					$a_params[] = "PHP_AUTH_USER=" . urlencode($aServerVars["PHP_AUTH_USER"]);
				}

				if (isset($aServerVars["PHP_AUTH_PW"])) {
					$a_params[] = "PHP_AUTH_PW=" . urlencode($aServerVars["PHP_AUTH_PW"]);
				}

				if (isset($aServerVars["PHP_AUTH_TYPE"])) {
					$a_params[] = "PHP_AUTH_TYPE=" . urlencode($aServerVars["PHP_AUTH_TYPE"]);
				}
			} else {
				$a_sess_data = GetSession("FormData");
				if (isset($aServerVars["PHP_AUTH_USER"])) {
					$a_sess_data["PHP_AUTH_USER"] = $aServerVars["PHP_AUTH_USER"];
				}

				if (isset($aServerVars["PHP_AUTH_PW"])) {
					$a_sess_data["PHP_AUTH_PW"] = $aServerVars["PHP_AUTH_PW"];
				}

				if (isset($aServerVars["PHP_AUTH_TYPE"])) {
					$a_sess_data["PHP_AUTH_TYPE"] = $aServerVars["PHP_AUTH_TYPE"];
				}
				SetSession("FormData",$a_sess_data);
			}
		}
		$bad_url = AddURLParams($bad_url,$a_params,false);
		Redirect($bad_url,GetMessage(MSG_FORM_ERROR));
	} else {
		if (!empty($bad_template)) {
			$a_specs = array("fmerror"      => htmlspecialchars("$error_mesg"),
			                 "fmerrorcode"  => htmlspecialchars("$error_code"),
			                 "fmfullerror"  => htmlspecialchars("$error_mesg") . "\n" .
			                                   htmlspecialchars("$s_extra_info"),
			                 "fmerrorextra" => htmlspecialchars("$s_extra_info"),
			);
			for ($i_count = 1 ; $i_count <= 20 ; $i_count++) {
				$a_specs["fmerroritem$i_count"] = "";
			}
			$i_count = 1;
			foreach ($a_item_list as $s_item) {
				$a_specs["fmerroritem$i_count"] = htmlspecialchars($s_item);
				$i_count++;
			}
			$s_list = "";
			foreach ($a_item_list as $s_item) {
				$s_list .= "<li>" . htmlspecialchars($s_item) . "</li>";
			}
			$a_specs["fmerroritemlist"] = $s_list;
			if (ShowErrorTemplate($bad_template,$a_specs,$b_user_error)) {
				return;
			}
		}
		$s_text = GetMessage(MSG_ERROR_PROC);
		if ($b_user_error) {
			$s_text .= $error_mesg . "\n" . FixedHTMLEntities($s_extra_info);
		} else {
			global $SERVER;

			if ($b_alerted) {
				$s_text .= GetMessage(MSG_ALERT_DONE,array("SERVER" => $SERVER));
			} else {
				$s_text .= GetMessage(MSG_PLS_CONTACT,array("SERVER" => $SERVER));
			}
			$s_text .= GetMessage(MSG_APOLOGY,array("SERVER" => $SERVER));
		}
		CreatePage($s_text,GetMessage(MSG_FORM_ERROR),false);

		ZapSession();
	}
}

function ErrorWithIgnore($error_code,$error_mesg,$b_filter = true,$show = true,$int_mesg = "")
{
	if (function_exists('FMHookErrorWithIgnore')) {
		FMHookErrorWithIgnore($error_code,$error_mesg,$b_filter,$show,$int_mesg);
	}

	$b_alerted = false;
	if (!Settings::get('ATTACK_DETECTION_IGNORE_ERRORS')) {
		if (SendAlert("$error_code\n *****$int_mesg*****\nError=$error_mesg\n",$b_filter)) {
			$b_alerted = true;
		}
	}
	if ($show) {
		ShowError($error_code,$error_mesg,false,$b_alerted);
	} else

	{
		ShowError($error_code,GetMessage(MSG_SUBM_FAILED),false,$b_alerted);
	}
	exit;
}

function Error($error_code,$error_mesg,$b_filter = true,$show = true,$int_mesg = "")
{
	if (function_exists('FMHookError')) {
		FMHookError($error_code,$error_mesg,$b_filter,$show,$int_mesg);
	}

	$b_alerted = false;
	if (SendAlert("$error_code\n *****$int_mesg*****\nError=$error_mesg\n",$b_filter)) {
		$b_alerted = true;
	}
	if ($show) {
		ShowError($error_code,$error_mesg,false,$b_alerted);
	} else

	{
		ShowError($error_code,GetMessage(MSG_SUBM_FAILED),false,$b_alerted);
	}
	exit;
}

function UserError($s_error_code,$s_error_mesg,
                   $s_extra_info = "",$a_item_list = array())
{
	if (function_exists('FMHookUserError')) {
		FMHookUserError($s_error_code,$s_error_mesg,$s_extra_info,$a_item_list);
	}
	$b_alerted = false;
	if (Settings::get('ALERT_ON_USER_ERROR') &&
	    SendAlert("$s_error_code\nError=$s_error_mesg\n$s_extra_info\n")
	) {
		$b_alerted = true;
	}
	ShowError($s_error_code,$s_error_mesg,true,$b_alerted,$a_item_list,$s_extra_info);
	exit;
}

function CreatePage($text,$title = "",$b_show_about = true)
{
	global $FM_VERS,$sHTMLCharSet;

	if (IsAjax()) {

		JSON_Result("ERROR",array("ErrorCode" => $title,
		                          "ErrorMesg" => $text
		));
	} else {
		echo
			'<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">' .
			"\n";
		echo '<html xmlns="http://www.w3.org/1999/xhtml">' . "\n";
		echo "<head>\n";
		if (isset($sHTMLCharSet) && $sHTMLCharSet !== "") {
			echo "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=$sHTMLCharSet\" />\n";
		}
		if ($title != "") {
			echo "<title>" . FixedHTMLEntities($title) . "</title>\n";
		}
		echo "</head>\n";
		echo "<body>\n";
		echo nl2br($text);
		echo "<p />";
		if ($b_show_about) {
			echo "<p><small>\n";
			echo GetMessage(MSG_ABOUT_FORMMAIL,array("FM_VERS" => $FM_VERS,
			                                         "TECTITE" => "www.tectite.com"
			));
			echo "</small></p>\n";
		}
		echo "</body>\n";
		echo "</html>\n";
	}
}

function StripGPC($s_value)
{
	if (get_magic_quotes_gpc() != 0) {
		$s_value = stripslashes($s_value);
	}
	return ($s_value);
}

function StripGPCArray($a_values)
{
	if (get_magic_quotes_gpc() != 0) {
		foreach ($a_values as $m_key => $m_value) {
			if (is_array($m_value))

			{
				$a_values[$m_key] = StripGPCArray($m_value);
			} else

			{
				$a_values[$m_key] = stripslashes("$m_value");
			}
		}
	}
	return ($a_values);
}

function Strip($value)
{

	$value = preg_replace('/[ \r\n]+/'," ",$value); // zap all CRLF and multiple blanks
	return ($value);
}

function CleanValue($m_value)
{
	if (is_array($m_value)) {
		foreach ($m_value as $m_key => $m_item) {
			$m_value[$m_key] = CleanValue($m_item);
		}
	} elseif (!is_scalar($m_value)) {
		$m_value = "<" . gettype($m_value) . ">";
	} else {

		$m_value = substr("$m_value",0,Settings::get('MAXSTRING'));

		$m_value = trim(Strip($m_value));
	}
	return ($m_value);
}

function SpecialCleanValue($s_name,$m_value)
{
	global $SPECIAL_NOSTRIP;

	if (!in_array($s_name,$SPECIAL_NOSTRIP)) {
		$m_value = CleanValue($m_value);
	}
	return ($m_value);
}

function MakeFieldOutput($a_order,$a_fields,$s_line_feed = null)
{
	if ($s_line_feed === null) {
		$s_line_feed = Settings::get('BODY_LF');
	}
	$n_order  = count($a_order);
	$s_output = "";
	for ($ii = 0 ; $ii < $n_order ; $ii++) {
		$s_name = $a_order[$ii];
		if (isset($a_fields[$s_name])) {
			$s_output .= "$s_name: " . $a_fields[$s_name] . $s_line_feed;
		}
	}
	return ($s_output);
}

function IsSpecialField($s_name)
{
	global $SPECIAL_FIELDS;

	return (in_array($s_name,$SPECIAL_FIELDS));
}

function SetSpecialField($s_name,$m_value)
{
	global $SPECIAL_VALUES;

	if (is_array($m_value)) {
		global $SPECIAL_ARRAYS;

		if (!in_array($s_name,$SPECIAL_ARRAYS)) {
			return;
		}
	}
	$SPECIAL_VALUES[$s_name] = SpecialCleanValue($s_name,$m_value);
}

function IsSpecialMultiField($s_name)
{
	global $SPECIAL_MULTI;

	foreach ($SPECIAL_MULTI as $s_multi_fld) {
		$i_len = strlen($s_multi_fld);

		if (substr($s_name,0,$i_len) == $s_multi_fld) {
			$i_index = (int)substr($s_name,$i_len);
			if ($i_index > 0) {

				--$i_index;
				return (array($s_multi_fld,$i_index));
			}
		}
	}
	return (false);
}

function SetSpecialMultiField($s_name,$i_index,$m_value)
{
	global $SPECIAL_VALUES;

	if (!is_array($m_value)) {
		$SPECIAL_VALUES[$s_name][$i_index] = SpecialCleanValue($s_name,$m_value);
	}
}

function    IsReverseCaptchaField($s_name)
{
	$a_rev_captcha = Settings::get('ATTACK_DETECTION_REVERSE_CAPTCHA');
	return (isset($a_rev_captcha[$s_name]));
}

function ProcessField($s_name,$raw_value,&$a_order,&$a_fields,&$a_raw_fields)
{
	global $FORMATTED_INPUT;

	$b_ignore = $b_special = false;
	if (IsSpecialField($s_name)) {
		SetSpecialField($s_name,$raw_value);
		$b_special = true;
	}

	if (($a_multi_fld = IsSpecialMultiField($s_name)) !== false) {
		SetSpecialMultiField($a_multi_fld[0],$a_multi_fld[1],$raw_value);
		$b_special = true;
	}
	if (!$b_special) {
		if (IsReverseCaptchaField($s_name)) {
			$b_ignore = true;
		}
	}
	if (!$b_special && !$b_ignore) {

		$a_raw_fields[$s_name] = $raw_value;

		if (is_array($raw_value)) {
			if (empty($raw_value)) {
				$s_cleaned_value = "";
			} else {
				$a_cleaned_values = CleanValue($raw_value);

				$a_cleaned_values = str_replace(",","",$a_cleaned_values);
				$s_cleaned_value  = implode(",",$a_cleaned_values);
			}
		} else {

			if (IsMailOptionSet("KeepLines") && strpos($raw_value,"\n") !== false) {

				$s_truncated = substr("$raw_value",0,Settings::get('MAXSTRING'));

				$a_lines         = explode("\n",$s_truncated);
				$a_lines         = CleanValue($a_lines);
				$s_cleaned_value = implode(Settings::get('BODY_LF'),$a_lines);

				$s_cleaned_value = Settings::get('BODY_LF') . $s_cleaned_value;
			} else {
				$s_cleaned_value = CleanValue($raw_value);
			}
		}

		if (!IsMailOptionSet("NoEmpty") || !FieldManager::IsEmpty($s_cleaned_value)) {
			if (!IsMailExcluded($s_name)) {

				$a_order[]         = $s_name;
				$a_fields[$s_name] = $s_cleaned_value;
			}
		}

		array_push($FORMATTED_INPUT,"$s_name: '$s_cleaned_value'");
	}
}

function ParseInput($a_vars)
{
	$a_order      = array();
	$a_fields     = array();
	$a_raw_fields = array();

	foreach ($a_vars as $s_name => $raw_value) {
		ProcessField($s_name,$raw_value,$a_order,$a_fields,$a_raw_fields);
	}

	return (array($a_order,$a_fields,$a_raw_fields));
}

function GetCRMURL($spec,$vars,$url)
{
	$bad  = false;
	$list = TrimArray(explode(",",$spec));
	$map  = array();
	for ($ii = 0 ; $ii < count($list) ; $ii++) {
		$name = $list[$ii];
		if ($name) {

			if (($i_crm_name_pos = strpos($name,":")) > 0) {
				$s_crm_name = substr($name,$i_crm_name_pos + 1);
				$name       = substr($name,0,$i_crm_name_pos);
				if (isset($vars[$name])) {
					$map[] = $s_crm_name . "=" . urlencode($vars[$name]);
					$map[] = "Orig_" . $s_crm_name . "=" . urlencode($name);
				}
			} else {

				$a_values = explode("=",$name);
				if (count($a_values) > 1) {
					$map[] = urlencode($a_values[0]) . "=" . urlencode($a_values[1]);
				} else {
					$map[] = urlencode($a_values[0]);
				}
			}
		}
	}
	if (count($map) == 0) {
		return ("");
	}
	return (AddURLParams($url,$map,false));
}

function StripHTML($m_value,$s_line_feed = "\n")
{
	if (is_array($m_value)) {
		foreach ($m_value as $m_key => $s_str) {
			$m_value[$m_key] = StripHTML($s_str);
		}
		return ($m_value);
	}
	$s_str = $m_value;

	$s_str = preg_replace('/<!--([^-]*([^-]|-([^-]|-[^>])))*-->/s','',$s_str);

	$s_str = preg_replace('/<script[^>]*?>.*?<\/script[^>]*?>/si','',$s_str);

	$s_str = preg_replace('/<p[^>]*?>/i',$s_line_feed,$s_str);

	$s_str = preg_replace('/<br[[:space:]]*\/?[[:space:]]*>/i',$s_line_feed,$s_str);

	$s_str = preg_replace('/<![^>]*>/s','',$s_str);

	$s_str = strip_tags($s_str);
	return ($s_str);
}

function CheckValidURL($s_url)
{

	foreach (Settings::get('TARGET_URLS') as $s_prefix) {
		if (!empty($s_prefix) &&
		    strtolower(substr($s_url,0,strlen($s_prefix))) ==
		    strtolower($s_prefix)
		) {
			return (true);
		}
	}
	return (false);
}

function FindCRMFields($s_data)
{
	$a_ret = array();
	if (preg_match_all('/^__([A-Za-z][A-Za-z0-9_]*)__=(.*)$/m',$s_data,$a_matches) === false) {
		SendAlert(GetMessage(MSG_PREG_FAILED));
	} else {
		$n_matches = count($a_matches[0]);

		for ($ii = 0 ; $ii < $n_matches ; $ii++) {
			if (isset($a_matches[1][$ii]) && isset($a_matches[2][$ii])) {
				$a_ret[$a_matches[1][$ii]] = $a_matches[2][$ii];
			}
		}
	}
	return ($a_ret);
}

function SendToCRM($s_url,&$a_data)
{
	global $php_errormsg;

	if (!CheckValidURL($s_url)) {
		SendAlert(GetMessage(MSG_URL_INVALID,array("URL" => $s_url)));
		return (false);
	}
	@   $fp = fopen($s_url,"r"); // RJR: TO DO: re-implement using NetIO
	if ($fp === false) {
		SendAlert(GetMessage(MSG_URL_OPEN,array("URL"   => $s_url,
		                                        "ERROR" => CheckString($php_errormsg)
		)));
		return (false);
	}
	$s_mesg = "";
	while (!feof($fp)) {
		$s_line = fgets($fp,4096);
		$s_mesg .= $s_line;
	}
	fclose($fp);
	$s_mesg   = StripHTML($s_mesg);
	$s_result = preg_match('/__OK__=(.*)/',$s_mesg,$a_matches);
	if (count($a_matches) < 2 || $a_matches[1] === "") {

		SendAlert(GetMessage(MSG_CRM_FAILED,array("URL" => $s_url,
		                                          "MSG" => $s_mesg
		)));
		return (false);
	}

	$a_data = FindCRMFields($s_mesg);

	switch (strtolower($a_matches[1])) {
		case "true":
			break;
		case "false":

			if (isset($a_data["USERERRORCODE"])) {
				$s_error_code = "crm_error";
				$s_error_mesg = GetMessage(MSG_CRM_FORM_ERROR);
				$s_error_code .= $a_data["USERERRORCODE"];
				if (isset($a_data["USERERRORMESG"])) {
					$s_error_mesg = $a_data["USERERRORMESG"];
				}
				UserError($s_error_code,$s_error_mesg);
				// no return
			}
			return (false);
	}
	return (true);
}

function GetFriendlyName($s_name)
{
	if (($i_pos = strpos($s_name,':')) === false) {
		return (array(trim($s_name),trim($s_name)));
	}
	return (array(trim(substr($s_name,0,$i_pos)),trim(substr($s_name,$i_pos + 1))));
}

define("REQUIREDOPS","|^!="); // operand characters for advanced required processing

function FieldTest($s_oper,$s_fld1,$s_fld2,$a_vars,&$s_error_mesg,
                   $s_friendly1 = "",$s_friendly2 = "")
{
	$b_ok     = true;
	$s_empty1 = $s_empty2 = "";

	switch ($s_oper) {
		case '&': // both fields must be present
			if (!TestFieldEmpty($s_fld1,$a_vars,$s_empty1) &&
			    !TestFieldEmpty($s_fld2,$a_vars,$s_empty2)
			) {
				;
			} // OK
			else {

				$s_error_mesg = GetMessage(MSG_AND,array("ITEM1" => $s_friendly1,
				                                         "ITEM2" => $s_friendly2
				));
				$b_ok         = false;
			}
			break;
		case '|': // either field or both must be present
			if (!TestFieldEmpty($s_fld1,$a_vars,$s_empty1) ||
			    !TestFieldEmpty($s_fld2,$a_vars,$s_empty2)
			) {
				;
			} // OK
			else {

				$s_error_mesg = GetMessage(MSG_OR,array("ITEM1" => $s_friendly1,
				                                        "ITEM2" => $s_friendly2
				));
				$b_ok         = false;
			}
			break;
		case '^': // either field but not both must be present
			$b_got1 = !TestFieldEmpty($s_fld1,$a_vars,$s_empty1);
			$b_got2 = !TestFieldEmpty($s_fld2,$a_vars,$s_empty2);
			if ($b_got1 || $b_got2) {
				if ($b_got1 && $b_got2) {

					$s_error_mesg = GetMessage(MSG_NOT_BOTH,
					                           array("ITEM1" => $s_friendly1,
					                                 "ITEM2" => $s_friendly2
					                           ));
					$b_ok         = false;
				}
			} else {

				$s_error_mesg = GetMessage(MSG_XOR,
				                           array("ITEM1" => $s_friendly1,
				                                 "ITEM2" => $s_friendly2
				                           ));
				$b_ok         = false;
			}
			break;
		case '!=':
		case '=':
			$b_got1 = !TestFieldEmpty($s_fld1,$a_vars,$s_empty1);
			$b_got2 = !TestFieldEmpty($s_fld2,$a_vars,$s_empty2);
			if ($b_got1 && $b_got2) {
				$b_match = (GetFieldValue($s_fld1,$a_vars) ==
				            GetFieldValue($s_fld2,$a_vars));
			} elseif (!$b_got1 && !$b_got2)

			{
				$b_match = true;
			} else

			{
				$b_match = false;
			}
			if ($s_oper != '=') {

				$b_match = !$b_match;
				$s_desc  = GetMessage(MSG_IS_SAME_AS,
				                      array("ITEM1" => $s_friendly1,
				                            "ITEM2" => $s_friendly2
				                      ));
			} else {
				$s_desc = GetMessage(MSG_IS_NOT_SAME_AS,
				                     array("ITEM1" => $s_friendly1,
				                           "ITEM2" => $s_friendly2
				                     ));
			}
			if (!$b_match) {

				$s_error_mesg = $s_desc;
				$b_ok         = false;
			}
			break;
	}
	return ($b_ok);
}

function AdvancedRequired($s_cond,$i_span,$a_vars,&$s_missing,&$a_missing_list)
{
	$b_ok = true;

	list($s_fld1,$s_friendly1) = GetFriendlyName(substr($s_cond,0,$i_span));

	$s_rem  = substr($s_cond,$i_span);
	$i_span = strspn($s_rem,REQUIREDOPS);
	$s_oper = substr($s_rem,0,$i_span);
	switch ($s_oper) {
		case '|':
		case '^':
		case '=':
		case '!=':

			list($s_fld2,$s_friendly2) = GetFriendlyName(substr($s_rem,$i_span));
			if (!FieldTest($s_oper,$s_fld1,$s_fld2,$a_vars,$s_error_mesg,
			               $s_friendly1,$s_friendly2)
			) {

				$s_missing .= "$s_error_mesg\n";
				$a_missing_list[$s_fld1] = "$s_error_mesg";
				$b_ok                    = false;
			}
			break;
		default:
			SendAlert(GetMessage(MSG_REQD_OPER,array("OPER" => $s_oper)));
			break;
	}
	return ($b_ok);
}

function CheckRequired($s_reqd,$a_vars,&$s_missing,&$a_missing_list)
{
	global $reCaptchaProcessor;

	$b_bad          = false;
	$a_list         = TrimArray(explode(",",$s_reqd));
	$s_missing      = "";
	$a_missing_list = array();
	$s_mesg         = "";
	for ($ii = 0 ; $ii < count($a_list) ; $ii++) {
		$s_cond = $a_list[$ii];
		$i_len  = strlen($s_cond);
		if ($i_len <= 0) {
			continue;
		}
		if (($i_span = strcspn($s_cond,REQUIREDOPS)) >= $i_len) {

			list($s_fld,$s_friendly) = GetFriendlyName($s_cond);
			if (TestFieldEmpty($s_fld,$a_vars,$s_mesg)) {
				if ($s_mesg === "") {
					$s_mesg = "$s_friendly";
				} else {
					$s_mesg = "$s_friendly ($s_mesg)";
				}
				$b_bad = true;
				$s_missing .= "$s_mesg\n";
				$a_missing_list[$s_fld] = "$s_mesg";
			}
		} elseif (!AdvancedRequired($s_cond,$i_span,$a_vars,
		                            $s_missing,$a_missing_list)
		) {
			$b_bad = true;
		}
	}

	global $SPECIAL_VALUES;

	if (!Settings::isEmpty('REQUIRE_CAPTCHA')) {
		if ($SPECIAL_VALUES["imgverify"] === "") {
			$s_missing .= Settings::get('REQUIRE_CAPTCHA') . "\n";
			$a_missing_list['imgverify'] = Settings::get('REQUIRE_CAPTCHA');
			$b_bad                       = true;
		}
	}
	return (!$b_bad);
}

function RunTest($s_test,$a_vars)
{
	global $aAlertInfo;

	$s_op_chars = "&|^!=~#<>"; // these are the characters for the operators
	$i_len      = strlen($s_test);
	$b_ok       = true;
	$s_mesg     = "";
	if ($i_len <= 0)

	{
		;
	} elseif ($s_test == "!")

	{
		$b_ok = false;
	} elseif (($i_span = strcspn($s_test,$s_op_chars)) >= $i_len)

	{
		$b_ok = !TestFieldEmpty($s_test,$a_vars,$s_mesg);
	} else {

		$s_fld1 = trim(substr($s_test,0,$i_span));

		$s_rem  = substr($s_test,$i_span);
		$i_span = strspn($s_rem,$s_op_chars);
		$s_oper = substr($s_rem,0,$i_span);
		switch ($s_oper) {
			case '&':
			case '|':
			case '^':
			case '=':
			case '!=':

				$s_fld2 = trim(substr($s_rem,$i_span));
				$b_ok   = FieldTest($s_oper,$s_fld1,$s_fld2,$a_vars,$s_error_mesg);
				break;
			case '~':
			case '!~':

				$s_pat = trim(substr($s_rem,$i_span));
				if (!TestFieldEmpty($s_fld1,$a_vars,$s_mesg)) {
					$s_value = GetFieldValue($s_fld1,$a_vars);
				} else {
					$s_value = "";
				}

				if (preg_match($s_pat,$s_value) > 0) {
					$b_ok = ($s_oper == '~');
				} else {
					$b_ok = ($s_oper == '!~');
				}
				if (!$b_ok) {
					$aAlertInfo[] = GetMessage(MSG_PAT_FAILED,array("OPER"  => $s_oper,
					                                                "PAT"   => $s_pat,
					                                                "VALUE" => $s_value
					));
				}
				break;
			case '#=':
			case '#!=':
			case '#<':
			case '#>':
			case '#<=':
			case '#>=':

				$s_num = trim(substr($s_rem,$i_span));

				if (($s_value = GetFileSize($s_fld1)) === false) {
					$s_value = $a_vars[$s_fld1];
				}
				if (strpos($s_num,'.') === false) {

					$m_num = (int)$s_num;
					$m_fld = (int)$s_value;
				} else {

					$m_num = (float)$s_num;
					$m_fld = (float)$s_value;
				}
				switch ($s_oper) {
					case '#=':
						$b_ok = ($m_fld == $m_num);
						break;
					case '#!=':
						$b_ok = ($m_fld != $m_num);
						break;
					case '#<':
						$b_ok = ($m_fld < $m_num);
						break;
					case '#>':
						$b_ok = ($m_fld > $m_num);
						break;
					case '#<=':
						$b_ok = ($m_fld <= $m_num);
						break;
					case '#>=':
						$b_ok = ($m_fld >= $m_num);
						break;
				}
				break;
			default:
				SendAlert(GetMessage(MSG_COND_OPER,array("OPER" => $s_oper)));
				break;
		}
	}
	return ($b_ok);
}

function CheckConditions($m_conditions,$a_vars,&$s_missing,&$a_missing_list,$m_id = false)
{
	if (is_array($m_conditions)) {

		ksort($m_conditions,SORT_NUMERIC);
		foreach ($m_conditions as $m_key => $s_cond) {
			if (!CheckConditions($s_cond,$a_vars,$s_missing,$a_missing_list,$m_key)) {
				return (false);
			}
		}
		return (true);
	}
	$s_fld_name = "conditions" . ($m_id === false ? "" : ($m_id + 1));
	if (!is_string($m_conditions)) {
		SendAlert(GetMessage(MSG_INV_COND,array("FLD" => $s_fld_name)));
		return (true); // pass invalid conditions
	}
	if ($m_conditions == "") {
		return (true);
	} // pass empty conditions
	$s_cond = $m_conditions;

	if (strlen($s_cond) < 2) {
		SendAlert(GetMessage(MSG_COND_CHARS,
		                     array("FLD" => $s_fld_name,"COND" => $s_cond)));
		return (true); // pass invalid conditions
	}
	$s_list_sep     = $s_cond[0];
	$s_int_sep      = $s_cond[1];
	$s_full_cond    = $s_cond = substr($s_cond,2);
	$b_bad          = false;
	$a_list         = TrimArray(explode($s_list_sep,$s_cond));
	$s_missing      = "";
	$a_missing_list = array();
	for ($ii = 0 ; $ii < count($a_list) ; $ii++) {
		$s_cond = $a_list[$ii];
		$i_len  = strlen($s_cond);
		if ($i_len <= 0) {
			continue;
		}

		$a_components = TrimArray(explode($s_int_sep,$s_cond));
		if (count($a_components) < 5) {
			SendAlert(GetMessage(MSG_COND_INVALID,
			                     array("FLD" => $s_fld_name,"COND" => $s_cond,
			                           "SEP" => $s_int_sep
			                     )));

			continue;
		}

		$a_components = array_slice($a_components,1);
		switch ($a_components[0]) {
			case "TEST":
				if (count($a_components) > 5) {
					SendAlert(GetMessage(MSG_COND_TEST_LONG,
					                     array("FLD" => $s_fld_name,"COND" => $s_cond,
					                           "SEP" => $s_list_sep
					                     )));
					continue;
				}
				if (!RunTest($a_components[1],$a_vars)) {
					$s_missing .= $a_components[2] . "\n";
					$a_missing_list[] = $a_components[2];
					$b_bad            = true;
				}
				break;
			case "IF":
				if (count($a_components) < 6) {
					SendAlert(GetMessage(MSG_COND_IF_SHORT,
					                     array("FLD" => $s_fld_name,"COND" => $s_cond,
					                           "SEP" => $s_int_sep
					                     )));
					continue;
				}
				if (count($a_components) > 7) {
					SendAlert(GetMessage(MSG_COND_IF_LONG,
					                     array("FLD" => $s_fld_name,"COND" => $s_cond,
					                           "SEP" => $s_list_sep
					                     )));
					continue;
				}
				if (RunTest($a_components[1],$a_vars)) {
					$b_test = RunTest($a_components[2],$a_vars);
				} else {
					$b_test = RunTest($a_components[3],$a_vars);
				}
				if (!$b_test) {
					$s_missing .= $a_components[4] . "\n";
					$a_missing_list[] = $a_components[4];
					$b_bad            = true;
				}
				break;
			default:
				SendAlert(GetMessage(MSG_COND_UNK,
				                     array("FLD" => $s_fld_name,"COND" => $s_cond,
				                           "CMD" => $a_components[0]
				                     )));
				break;
		}
	}
	return (!$b_bad);
}

function GetEnvVars($list,$s_line_feed)
{
	global $aServerVars;

	$output = "";
	for ($ii = 0 ; $ii < count($list) ; $ii++) {
		$name = $list[$ii];
		if ($name && array_search($name,Settings::get('VALID_ENV'),true) !== false) {

			if (($s_value = getenv($name)) === "" || $s_value === false) {
				if (isset($aServerVars[$name])) {
					$s_value = $aServerVars[$name];
				} else {
					$s_value = "";
				}
			}
			$output .= $name . "=" . $s_value . $s_line_feed;
		}
	}
	return ($output);
}

function SocketFilter($filter,$a_filter_info,$m_data)
{
	static $b_in_here = false;
	global $php_errormsg;

	if ($b_in_here) {
		return ("<DATA DISCARDED>");
	}
	$b_in_here = true;

	$a_errors = array();
	if (!isset($a_filter_info["site"])) {
		$a_errors[] = GetMessage(MSG_MISSING,array("ITEM" => "site"));
	} else {
		$s_site = $a_filter_info["site"];
	}

	if (!isset($a_filter_info["port"])) {
		$a_errors[] = GetMessage(MSG_MISSING,array("ITEM" => "port"));
	} else {
		$i_port = (int)$a_filter_info["port"];
	}

	if (!isset($a_filter_info["path"])) {
		$a_errors[] = GetMessage(MSG_MISSING,array("ITEM" => "path"));
	} else {
		$s_path = $a_filter_info["path"];
	}

	if (!isset($a_filter_info["params"])) {
		$a_params = array();
	} elseif (!is_array($a_filter_info["params"])) {
		$a_errors[] = GetMessage(MSG_NEED_ARRAY,array("ITEM" => "params"));
	} else {
		$a_params = $a_filter_info["params"];
	}

	if (!empty($a_errors)) {
		Error("bad_filter",GetMessage(MSG_FILTER_WRONG,array(
			"FILTER" => $filter,
			"ERRORS" => implode(', ',$a_errors)
		)),false,false);
		exit;
	}

	set_time_limit(60);
	@   $f_sock = fsockopen($s_site,$i_port,$i_errno,$s_errstr,30);
	if ($f_sock === false) {
		Error("filter_connect",GetMessage(MSG_FILTER_CONNECT,array(
			"FILTER" => $filter,
			"SITE"   => $s_site,
			"ERRNUM" => $i_errno,
			"ERRSTR" => "$s_errstr (" . CheckString($php_errormsg) . ")"
		)),
		      false,false);
		exit;
	}

	$m_request_data = array();
	$i_count        = 0;
	foreach ($a_params as $m_var) {
		$i_count++;

		if (is_array($m_var)) {
			if (!isset($m_var["name"])) {
				Error("bad_filter",GetMessage(MSG_FILTER_PARAM,
				                              array("FILTER" => $filter,
				                                    "NUM"    => $i_count,
				                                    "NAME"   => "name"
				                              )),false,false);
				fclose($f_sock);
				exit;
			}
			$s_name = $m_var["name"];
			if (!isset($m_var["file"])) {
				Error("bad_filter",GetMessage(MSG_FILTER_PARAM,
				                              array("FILTER" => $filter,
				                                    "NUM"    => $i_count,
				                                    "NAME"   => "file"
				                              )),false,false);
				fclose($f_sock);
				exit;
			}

			@           $fp = fopen($m_var["file"],"r");
			if ($fp === false) {
				Error("filter_error",GetMessage(MSG_FILTER_OPEN_FILE,
				                                array("FILTER" => $filter,
				                                      "FILE"   => $m_var["file"],
				                                      "ERROR"  => CheckString($php_errormsg)
				                                )),false,false);
				fclose($f_sock);
				exit;
			}
			$s_data  = "";
			$n_lines = 0;
			while (!feof($fp)) {
				if (($s_line = fgets($fp,2048)) === false) {
					if (feof($fp)) {
						break;
					} else {
						Error("filter_error",GetMessage(MSG_FILTER_FILE_ERROR,
						                                array("FILTER" => $filter,
						                                      "FILE"   => $m_var["file"],
						                                      "ERROR"  => CheckString($php_errormsg),
						                                      "NLINES" => $n_lines
						                                )),false,false);
						fclose($f_sock);
						exit;
					}
				}
				$s_data .= $s_line;
				$n_lines++;
			}

			fclose($fp);
			$m_request_data[] = "$s_name=" . urlencode($s_data);
		} else {
			$m_request_data[] = (string)$m_var;
		}
	}

	if (is_array($m_data)) {
		$m_request_data[] = "data=" . urlencode(implode(Settings::get('BODY_LF'),$m_data));
	} else {
		$m_request_data[] = "data=" . urlencode($m_data);
	}
	$s_request = implode("&",$m_request_data);

	if (($i_pos = strpos($s_site,"://")) !== false) {
		$s_site_name = substr($s_site,$i_pos + 3);
	} else {
		$s_site_name = $s_site;
	}

	fputs($f_sock,"POST $s_path HTTP/1.0\r\n");
	fputs($f_sock,"Host: $s_site_name\r\n");
	fputs($f_sock,"Content-Type: application/x-www-form-urlencoded\r\n");
	fputs($f_sock,"Content-Length: " . strlen($s_request) . "\r\n");
	fputs($f_sock,"\r\n");
	fputs($f_sock,"$s_request\r\n");

	$m_hdr    = "";
	$m_data   = "";
	$b_in_hdr = true;
	$b_ok     = false;
	while (!feof($f_sock)) {
		if (($s_line = fgets($f_sock,2048)) === false) {
			if (feof($f_sock)) {
				break;
			} else {
				Error("filter_failed",GetMessage(MSG_FILTER_READ_ERROR,
				                                 array("FILTER" => $filter,
				                                       "ERROR"  => CheckString($php_errormsg)
				                                 )),false,false);
				fclose($f_sock);
				exit;
			}
		}

		if (trim($s_line) == "__OK__") {
			$b_ok = true;
		} elseif ($b_in_hdr) {

			if (trim($s_line) == "") {
				$b_in_hdr = false;
			} else {
				$m_hdr .= $s_line;
			}
		} else {
			$m_data .= $s_line;
		}
	}

	if (!$b_ok) {
		Error("filter_failed",GetMessage(MSG_FILTER_NOT_OK,
		                                 array("FILTER" => $filter,
		                                       "DATA"   => $m_data
		                                 )),false,false);
		fclose($f_sock);
		exit;
	}
	fclose($f_sock);
	$b_in_here = false;
	return ($m_data);
}

function Filter($filter,$m_data)
{

	global $php_errormsg;
	static $b_in_here = false;

	if ($b_in_here) {
		return ("<DATA DISCARDED>");
	}
	$b_in_here = true;

	$a_filters = Settings::get('FILTERS');
	if (!isset($a_filters[$filter]) || $a_filters[$filter] == "") {

		$a_filters = Settings::get('SOCKET_FILTERS');
		if (!isset($a_filters[$filter]) || $a_filters[$filter] == "") {
			ErrorWithIgnore("bad_filter",GetMessage(MSG_FILTER_UNK,
			                                        array("FILTER" => $filter)),false,false);
			exit;
		}
		$m_data = SocketFilter($filter,$a_filters[$filter],$m_data);
	} elseif ($a_filters[$filter] == "null")

	{
		;
	} elseif ($a_filters[$filter] == "csv") {
		$m_data = BuiltinFilterCSV();
	} else {
		$cmd = $a_filters[$filter];

		$a_words = preg_split('/\s+/',$cmd);
		$prog    = $a_words[0];

		$s_cwd = getcwd();

		$dirname = dirname($prog);
		if ($dirname != "" && $dirname != "." && !chdir($dirname)) {
			Error("chdir_filter",GetMessage(MSG_FILTER_CHDIR,
			                                array("DIR"   => $dirname,"FILTER" => $filter,
			                                      "ERROR" => CheckString($php_errormsg)
			                                )),false,false);
			exit;
		}

		$temp_file       = GetTempName("FMF");
		$temp_error_file = GetTempName("FME");
		$cmd             = "$cmd >$temp_file 2>$temp_error_file";

		$pipe = popen($cmd,"w");
		if ($pipe === false) {
			$s_sv_err = CheckString($php_errormsg);
			$err      = join('',file($temp_error_file));
			unlink($temp_file);
			unlink($temp_error_file);
			Error("filter_not_found",GetMessage(MSG_FILTER_NOTFOUND,
			                                    array("CMD"   => $cmd,"FILTER" => $filter,
			                                          "ERROR" => $s_sv_err
			                                    )),false,false,$err);
			exit;
		}

		if (is_array($m_data)) {
			fwrite($pipe,implode(Settings::get('BODY_LF'),$m_data));
		} else {
			fwrite($pipe,$m_data);
		}
		if (($i_st = pclose($pipe)) != 0) {
			$s_sv_err = CheckString($php_errormsg);
			$err      = join('',file($temp_error_file));
			unlink($temp_file);
			unlink($temp_error_file);
			Error("filter_failed",GetMessage(MSG_FILTER_ERROR,
			                                 array("FILTER" => $filter,
			                                       "ERROR"  => $s_sv_err,
			                                       "STATUS" => $i_st
			                                 )),false,false,$err);
			exit;
		}

		$m_data = join('',file($temp_file));
		unlink($temp_error_file);
		unlink($temp_file);

		chdir($s_cwd);
	}
	$b_in_here = false;
	return ($m_data);
}

function    FilterFiles(&$a_files)
{
	global $SPECIAL_VALUES;

	FMDebug("FilterFiles " . count($a_files));
	if (!GetFilterSpec($s_filter,$a_filter_list,true) || $a_filter_list === false)

	{
		return;
	}
	if (($s_mime = GetFilterAttrib($s_filter,"MIME")) === false) {
		$s_mime = "";
	}

	foreach ($a_files as $s_fld => $a_upload) {
		FMDebug("Checking $s_fld");
		if (!IsUploadedFile($a_upload)) {
			FMDebug("Not uploaded");

			continue;
		}
		if (!in_array($s_fld,$a_filter_list,true)) {
			FMDebug("Not to be filtered");
			continue;
		}

		$s_file_name = $a_upload["tmp_name"];

		if (isset($a_upload["saved_as"]) && !empty($a_upload["saved_as"])) {
			$s_file_name = $a_upload["saved_as"];
		}
		FMDebug("File name is $s_file_name");

		if (($s_data = ReadInFile($s_file_name,"upload")) === false) {
			Error("filter_files",GetMessage(MSG_FILE_UPLOAD_ERR_UNK,array("ERRNO" => "reading $s_fld")),false,false);
		}

		$s_data = Filter($s_filter,$s_data);
		if (!WriteOutFile($s_file_name,$s_data,"upload")) {
			Error("filter_files",GetMessage(MSG_FILE_UPLOAD_ERR_UNK,array("ERRNO" => "writing $s_fld")),false,false);
		}

		$a_upload["size"] = strlen($s_data);
		if ($s_mime !== "") {
			$a_upload["type"] = $s_mime;
		}
		$a_files[$s_fld] = $a_upload;
	}
}

function    ReadInFile($s_file_name,$s_file_error_type,$b_text = false)
{
	global $php_errormsg;

	if (($fp = @fopen($s_file_name,"r" . ($b_text ? "t" : "b"))) === false) {
		SendAlert(GetMessage(MSG_FILE_OPEN_ERROR,array("NAME"  => $s_file_name,
		                                               "TYPE"  => "read " . $s_file_error_type,
		                                               "ERROR" => CheckString($php_errormsg)
		)));
		return (false);
	}
	$s_data = "";
	while (!feof($fp)) {
		$s_data .= fread($fp,8192);
	}
	@fclose($fp);
	return ($s_data);
}

function    WriteOutFile($s_file_name,$s_data,$s_file_error_type,$b_text = false)
{
	global $php_errormsg;

	if (($fp = @fopen($s_file_name,"w" . ($b_text ? "t" : "b"))) === false) {
		SendAlert(GetMessage(MSG_FILE_OPEN_ERROR,array("NAME"  => $s_file_name,
		                                               "TYPE"  => "write " . $s_file_error_type,
		                                               "ERROR" => CheckString($php_errormsg)
		)));
		return (false);
	}
	if (fwrite($fp,$s_data) < strlen($s_data)) {
		@fclose($fp);
		return (false);
	}
	@fclose($fp);
	return (true);
}

class   CSVFormat
{
	var $_cSep; /* field separator character */
	var $_cQuote; /* field quote character */
	var $_cIntSep; /* internal separator character (for lists) */
	var $_sEscPolicy; /* escape processing policy */
	var $_sCleanFunc; /* cleaning function for fields */

	function    CSVFormat($c_sep = ',',$c_quote = '"',$c_int_sep = ';',
	                      $s_esc_policy = "backslash",$s_clean_func = NULL)
	{
		$this->SetSep($c_sep);
		$this->SetQuote($c_quote);
		$this->SetIntSep($c_int_sep);
		$this->SetEscPolicy($s_esc_policy);
		$this->SetCleanFunc($s_clean_func);
	}

	function    SetEscPolicy($s_esc_policy)
	{
		switch ($s_esc_policy) {
			default: /* should generate a warning */
			case "backslash":
				$this->_sEscPolicy = "b";
				break;
			case "double":
				$this->_sEscPolicy = "d";
				break;
			case "strip":
				$this->_sEscPolicy = "s";
				break;
			case "conv":
				$this->_sEscPolicy = "c";
				break;
		}
	}

	function    SetSep($c_sep)
	{
		$this->_cSep = $c_sep;
	}

	function    SetQuote($c_quote)
	{
		$this->_cQuote = $c_quote;
	}

	function    SetIntSep($c_int_sep)
	{
		$this->_cIntSep = $c_int_sep;
	}

	function    SetCleanFunc($s_clean_func)
	{
		$this->_sCleanFunc = $s_clean_func;
	}

	function    _Escape($m_value)
	{
		switch ($this->_sEscPolicy) {
			default: /* should generate an error */
			case "b":

				$m_value = str_replace("\\","\\\\",$m_value);
				$m_value = str_replace($this->_cQuote,"\\" . $this->_cQuote,
				                       $m_value);
				break;
			case "d":

				$m_value = str_replace($this->_cQuote,
				                       $this->_cQuote . $this->_cQuote,$m_value);
				break;
			case "s":

				$m_value = str_replace($this->_cQuote,"",$m_value);
				break;
			case "c":

				switch ($this->_cQuote) {
					case '"':

						$m_value = str_replace("\"","'",$m_value);
						break;
					case '\'':

						$m_value = str_replace("'","\"",$m_value);
						break;
					default:

						break;
				}
				break;
		}
		return ($m_value);
	}

	function    _Format($s_value,$s_format = "")
	{
		$s_value  = $this->_Escape($s_value);
		$s_prefix = "";

		$i_len = strlen($s_format);
		for ($ii = 0 ; $ii < $i_len ; $ii++) {
			switch ($s_format[$ii]) {
				case "c":

					$s_value = CleanValue($s_value);
					break;
				case "r":

					$s_value = str_replace("\r","",$s_value);
					break;
				case "s":

					if (strlen($s_value) > 0) {
						$s_prefix = "=";
					}
					break;
			}
		}

		return ($s_prefix . $this->_cQuote . $s_value . $this->_cQuote);
	}

	function    _GetColumn($s_col_spec)
	{
		$s_format = "";
		if (($i_pos = strpos($s_col_spec,":")) !== false) {
			$s_col_name = trim(substr($s_col_spec,0,$i_pos));
			$s_format   = trim(substr($s_col_spec,$i_pos + 1));
		} else {
			$s_col_name = $s_col_spec;
		}
		return (array($s_col_name,$s_format));
	}

	function    MakeCSVRecord($a_column_list,$a_vars)
	{
		$s_rec     = "";
		$n_columns = count($a_column_list);
		for ($ii = 0 ; $ii < $n_columns ; $ii++) {
			list($s_col_name,$s_format) = $this->_GetColumn($a_column_list[$ii]);

			$s_value = GetFieldValue($s_col_name,$a_vars,$this->_cIntSep);
			if (isset($this->_sCleanFunc)) {
				$s_func  = $this->_sCleanFunc;
				$s_value = $s_func($s_value);
			}

			$s_value = $this->_Format($s_value,$s_format);
			if ($ii > 0) /*
{
				$s_rec .= $this->_cSep;
			}
			$s_rec .= $s_value;
		}
		return ($s_rec);
	}

	function    MakeHeading($a_column_list)
	{
		$s_rec     = "";
		$n_columns = count($a_column_list);
		for ($ii = 0 ; $ii < $n_columns ; $ii++) {
			list($s_col_name,$s_format) = $this->_GetColumn($a_column_list[$ii]);
			$s_value = $this->_Format($s_col_name);
			if ($ii > 0) 
 {
				$s_rec .= $this->_cSep;
			}
			$s_rec .= $s_value;
		}
		return ($s_rec);
	}
}

function BuiltinFilterCSV()
{
	global $aAllRawValues,$aRawDataValues,$SPECIAL_VALUES;

	$b_heading     = false;
	$a_column_list = array();
	$s_cols        = $SPECIAL_VALUES["filter_fields"];
	if (!isset($s_cols) || empty($s_cols) || !is_string($s_cols)) {
		$s_cols = $SPECIAL_VALUES["csvcolumns"];
		if (!isset($s_cols) || empty($s_cols) || !is_string($s_cols)) {

			$s_cols = "";

			$a_column_list = array("email","realname");
	
			$a_column_list = array_merge($a_column_list,
			                             array_keys($aRawDataValues));
			$b_heading     = true;
		}
	}
	if (empty($a_column_list)) {
		$a_column_list = TrimArray(explode(",",$s_cols));
	}

	$csv_format = new CSVFormat();

	$m_temp = GetFilterOption("CSVQuote");
	if (isset($m_temp)) {
		$csv_format->SetQuote($m_temp);
	}
	$m_temp = GetFilterOption("CSVSep");
	if (isset($m_temp)) {
		$csv_format->SetSep($m_temp);
	}
	$m_temp = GetFilterOption("CSVIntSep");
	if (isset($m_temp)) {
		$csv_format->SetIntSep($m_temp);
	}
	$m_temp = GetFilterOption("CSVEscPolicy");
	if (isset($m_temp)) {
		$csv_format->SetEscPolicy($m_temp);
	}
	$m_temp = GetFilterOption("CSVHeading");
	if (isset($m_temp)) {
		$b_heading = true;
	}

	$m_temp = GetFilterOption("CSVRaw");
	if (!isset($m_temp)) {
		$csv_format->SetCleanFunc(create_function('$m_value',
		                                          'return CleanValue($m_value);'));
	}

	$s_csv = $csv_format->MakeCSVRecord($a_column_list,$aAllRawValues);

	if ($b_heading) {
		$s_head = $csv_format->MakeHeading($a_column_list);

		return ($s_head . Settings::get('CSVLINE') . $s_csv . Settings::get('CSVLINE'));
	} else  {
		return ($s_csv . Settings::get('CSVLINE'));
	}
}

$aSubstituteErrors  = array();
$SubstituteFields   = NULL;
$sSubstituteMissing = NULL;

function ArrayHTMLSpecialChars($a_list)
{
	$a_new = array();
	foreach ($a_list as $m_key => $m_value) {
		if (is_array($m_value)) {
			$a_new[$m_key] = ArrayHTMLSpecialChars($m_value);
		} else {
			$a_new[$m_key] = htmlspecialchars($m_value);
		}
	}
	return ($a_new);
}

function Truncate($s_value,$n_max_chars,$n_max_lines)
{
	if ($n_max_lines > 0) {
		$a_lines = explode("\n",$s_value);
		if (count($a_lines) > $n_max_lines) {
			$a_lines = array_slice($a_lines,0,$n_max_lines);
			$s_value = implode("\n",$a_lines);
			$s_value .= "...";
		}
	}
	if ($n_max_chars > 0) {
		$a_lines = explode("\n",$s_value);
		for ($ii = 0 ; $ii < count($a_lines) ; $ii++) {
			$n_len = strlen($a_lines[$ii]);
			$s_eol = "";
			if (substr($a_lines[$ii],-1) == "\n") {
				$n_len--;
				$s_eol = "\n";
			}
			if ($n_len > $n_max_chars) {
				$a_lines[$ii] = substr($a_lines[$ii],0,$n_max_chars) . "..." . $s_eol;
			}
		}
		$s_value = implode("\n",$a_lines);
	}
	return ($s_value);
}

function SubstituteValueWorker($a_matches,$s_repl,$b_html = true)
{

	global $aSubstituteErrors,$SubstituteFields,$SPECIAL_VALUES;

	$b_insert_br = true; // option to put "<br />" tags before newlines in HTML templates
	$n_max_chars = 0;
	$n_max_lines = 0;
	$s_list_sep  = $SPECIAL_VALUES['template_list_sep'];
	$b_text_subs = false;

	$s_name = $a_matches[0];
	assert(strlen($s_name) > 1 && $s_name[0] == '$');
	$s_name = substr($s_name,1);
	if (($i_len = strlen($s_name)) > 0 && $s_name[0] == '{') {
		assert($s_name[$i_len - 1] == '}');
		$s_name = substr($s_name,1,-1);

		$a_args = explode(":",$s_name);
		$s_name = $a_args[0];
		if (($n_args = count($a_args)) > 1) {
			for ($ii = 1 ; $ii < $n_args ; $ii++) {

				$s_param = "";
				if (($i_pos = strpos($a_args[$ii],'=')) !== false) {
					$s_param = substr($a_args[$ii],$i_pos + 1);
					$s_opt   = substr($a_args[$ii],0,$i_pos);
				} else {
					$s_opt = $a_args[$ii];
				}
				switch ($s_opt) {
					case "nobr":
						$b_insert_br = false;
						break;
					case "chars":
						if ($s_param !== "") {
							$n_max_chars = (int)$s_param;
						}
						break;
					case "lines":
						if ($s_param !== "") {
							$n_max_lines = (int)$s_param;
						}
						break;
					case "sep":
						if ($s_param !== "") {
							$s_list_sep = $s_param;
						}
						break;
					case "subs":
						$b_text_subs = true;
						break;
				}
			}
		}
	}
	$s_value = "";
	$s_mesg  = "";
	if ($SubstituteFields->IsFieldSet($s_name) &&
	    !$SubstituteFields->TestFieldEmpty($s_name,$s_mesg)
	) {
		if ($b_html)

		{
			$s_value = $SubstituteFields->GetSafeFieldValue($s_name,$b_text_subs,$s_list_sep);
		} else {
			$s_value = $SubstituteFields->GetFieldValue($s_name,$s_list_sep);
		}
		$s_value = Truncate($s_value,$n_max_chars,$n_max_lines);
		if ($b_html && $b_insert_br)

		{
			$s_value = nl2br($s_value);
		}
	} elseif (isset($SPECIAL_VALUES[$s_name])) {
		$s_value = $b_html ?
			htmlspecialchars((string)$SPECIAL_VALUES[$s_name]) :
			(string)$SPECIAL_VALUES[$s_name];
		$s_value = Truncate($s_value,$n_max_chars,$n_max_lines);
	} elseif (isset($s_repl))

	{
		$s_value = $s_repl;
	} else {
		$s_value = "";
	}
	return ($s_value);
}

function SubstituteValue($a_matches)
{
	global $sSubstituteMissing;

	return (SubstituteValueWorker($a_matches,$sSubstituteMissing));
}

function SubstituteValuePlain($a_matches)
{
	global $sSubstituteMissing;

	return (SubstituteValueWorker($a_matches,$sSubstituteMissing,false));
}

function SubstituteValueForPage($a_matches)
{
	return (SubstituteValueWorker($a_matches,""));
}

function SubstituteValueDummy($a_matches)
{
	return ($a_matches[0]);
}

function DoProcessTemplate($s_dir,$s_url,$s_template,&$a_lines,
                           $a_values,$s_missing,$s_subs_func)
{
	global $aSubstituteErrors,$SubstituteFields,$sSubstituteMissing;

	if (($a_template_lines = LoadTemplate($s_template,$s_dir,
	                                      $s_url,true)) === false
	) {
		return (false);
	}
	FMDebug("Template '$s_template' contains " . count($a_template_lines) . " lines");

	$b_ok = true;

	$aSubstituteErrors = array();

	$SubstituteFields   = new FieldManager($a_values,array());
	$sSubstituteMissing = $s_missing;

	foreach ($a_template_lines as $s_line) {

		$a_lines[] = preg_replace_callback('/\$[a-z][a-z0-9_]*|\$\{[a-z][a-z0-9_]*(:[^\}]*)*\}/i',
		                                   $s_subs_func,$s_line);
	}

	FMDebug("DoProcessTemplate error count=" . count($aSubstituteErrors));
	if (count($aSubstituteErrors) != 0) {
		SendAlert(GetMessage(MSG_TEMPLATE_ERRORS,array("NAME" => $s_template)) .
		          implode("\n",$aSubstituteErrors));
		$b_ok = false;
	}
	global $FMCTemplProc;

	if ($b_ok && Settings::get('ADVANCED_TEMPLATES') && isset($FMCTemplProc)) {
		$s_buf = implode("\n",$a_lines);

		if (strpos($s_buf,"FormMail-Basic-Template") === FALSE) {
			$a_mesgs = array();

			set_time_limit(60);
			if (($m_result = $FMCTemplProc->Process($s_buf,$a_mesgs)) === false) {
				$s_msgs = "\n";
				foreach ($a_mesgs as $a_msg) {
					$s_msgs .= "Line " . $a_msg["LINE"];
					$s_msgs .= ", position " . $a_msg["CHAR"] . ": ";
					$s_msgs .= $a_msg["MSG"] . "\n";
				}
				Error("fmadvtemplates",GetMessage(MSG_TEMPL_PROC,
				                                  array("ERRORS" => $s_msgs)),false,false);
				$b_ok = false;
			} else {

				$a_lines = explode("\n",implode("",$m_result));
			}
			$a_alerts = $FMCTemplProc->GetAlerts();
			if (count($a_alerts) > 0) {
				SendAlert(GetMessage(MSG_TEMPL_ALERT,
				                     array("ALERTS" => implode("\n",$a_alerts))));
			}
			$a_debug = $FMCTemplProc->GetDebug();
			if (count($a_debug) > 0) {
				SendAlert(GetMessage(MSG_TEMPL_DEBUG,
				                     array("DEBUG" => implode("\n",$a_debug))));
			}
		}
	}

	return ($b_ok);
}

function ProcessTemplate($s_template,&$a_lines,$a_values,$s_missing = NULL,
                         $s_subs_func = 'SubstituteValue')
{

	if (Settings::isEmpty('TEMPLATEDIR') && Settings::isEmpty('TEMPLATEURL')) {
		SendAlert(GetMessage(MSG_TEMPLATES));
		return (false);
	}
	return (DoProcessTemplate(Settings::get('TEMPLATEDIR'),Settings::get('TEMPLATEURL'),$s_template,$a_lines,
	                          $a_values,$s_missing,$s_subs_func));
}

function OutputTemplate($s_template,$a_values)
{
	$a_lines = array();
	if (!ProcessTemplate($s_template,$a_lines,$a_values,"",'SubstituteValueForPage')) {
		Error("template_failed",GetMessage(MSG_TEMPLATE_FAILED,
		                                   array("NAME" => $s_template)),false,false);
	} else {
		for ($ii = 0 ; $ii < count($a_lines) ; $ii++) {
			echo $a_lines[$ii] . "\n";
		}
	}
}

function RemoveFieldValue($s_name,$s_buf)
{

	$s_pat = '/<(\s*input[^>]*name="';
	$s_pat .= preg_quote($s_name,"/");
	$s_pat .= '"[^>]*)>';
	$s_pat .= '/ims';
	$s_buf = preg_replace($s_pat,'<!-- disabled by FormMail: $1 -->',$s_buf);

	return ($s_buf);
}

function RegReplaceQuote($s_value)
{
	return (str_replace('$','\\$',str_replace('\\','\\\\',$s_value)));
}

function FixInputText($s_name,$s_value,$s_buf)
{

	$s_pat = '/(<\s*input[^>]*type="(?:text|password)"[^>]*name="';
	$s_pat .= preg_quote($s_name,"/");
	$s_pat .= '"[^>]*)(value="[^"]*")([^>]*?)(\s*\/\s*)?>';
	$s_pat .= '/ims';
	$s_buf = preg_replace($s_pat,'$1$3$4>',$s_buf);

	$s_pat = '/(<\s*input[^>]*name="';
	$s_pat .= preg_quote($s_name,"/");
	$s_pat .= '"[^>]*type="(?:text|password)"[^>]*)(value="[^"]*")([^>]*?)(\s*\/\s*)?>';
	$s_pat .= '/ims';
	$s_buf = preg_replace($s_pat,'$1$3$4>',$s_buf);

	$s_repl = '$1 value="' . htmlspecialchars(RegReplaceQuote($s_value)) . '" $2>';

	$s_pat = '/(<\s*input[^>]*type="(?:text|password)"[^>]*name="';
	$s_pat .= preg_quote($s_name,"/");
	$s_pat .= '"[^>]*?)(\s*\/\s*)?>';
	$s_pat .= '/ims';
	$s_buf = preg_replace($s_pat,$s_repl,$s_buf);

	$s_pat = '/(<\s*input[^>]*name="';
	$s_pat .= preg_quote($s_name,"/");
	$s_pat .= '"[^>]*type="(?:text|password)"[^>]*?)(\s*\/\s*)?>';
	$s_pat .= '/ims';
	$s_buf = preg_replace($s_pat,$s_repl,$s_buf);

	return ($s_buf);
}

function FixTextArea($s_name,$s_value,$s_buf)
{

	$s_pat = '/(<\s*textarea[^>]*name="';
	$s_pat .= preg_quote($s_name,"/");
	$s_pat .= '"[^>]*)>.*?<\s*\/\s*textarea\s*>';
	$s_pat .= '/ims';

	$s_repl = '$1>' . htmlspecialchars(RegReplaceQuote($s_value)) . '</textarea>';
	$s_buf  = preg_replace($s_pat,$s_repl,$s_buf);

	return ($s_buf);
}

function FixButton($s_name,$s_value,$s_buf)
{

	$s_pat = '/(<\s*input[^>]*type="(?:radio|checkbox)"[^>]*name="';
	$s_pat .= preg_quote($s_name,"/");
	$s_pat .= '"[^>]*?[^"\w])checked(="checked"|(?=[^"\w]))?([^>]*?)(\s*\/\s*)?>';
	$s_pat .= '/ims';
	$s_buf = preg_replace($s_pat,'$1$3$4>',$s_buf);

	$s_pat = '/(<\s*input[^>]*name="';
	$s_pat .= preg_quote($s_name,"/");
	$s_pat .= '"[^>]*type="(?:radio|checkbox)"[^>]*?[^"\w])checked(="checked"|(?=[^"\w]))?([^>]*?)(\s*\/\s*)?>';
	$s_pat .= '/ims';
	$s_buf = preg_replace($s_pat,'$1$3$4>',$s_buf);

	$s_pat = '/(<\s*input[^>]*type="(?:radio|checkbox)"[^>]*name="';
	$s_pat .= preg_quote($s_name,"/");
	$s_pat .= '"[^>]*value="';
	$s_pat .= preg_quote($s_value,"/");
	$s_pat .= '")([^>]*?)(\s*\/\s*)?>';
	$s_pat .= '/ims';
	$s_buf = preg_replace($s_pat,'$1$2 checked="checked" $3>',$s_buf);

	$s_pat = '/(<\s*input[^>]*name="';
	$s_pat .= preg_quote($s_name,"/");
	$s_pat .= '"[^>]*type="(?:radio|checkbox)"[^>]*value="';
	$s_pat .= preg_quote($s_value,"/");
	$s_pat .= '")([^>]*?)(\s*\/\s*)?>';
	$s_pat .= '/ims';
	$s_buf = preg_replace($s_pat,'$1$2 checked="checked" $3>',$s_buf);

	return ($s_buf);
}

function FixCheckboxes($s_name,$a_values,$s_buf)
{


	$s_pat = '/(<\s*input[^>]*type="checkbox"[^>]*name="';
	$s_pat .= preg_quote($s_name,"/");
	$s_pat .= '\[]"[^>]*?[^"\w])checked(="checked"|(?=[^"\w]))?([^>]*?)(\s*\/\s*)?>';
	$s_pat .= '/ims';
	$s_buf = preg_replace($s_pat,'$1$3$4>',$s_buf);

	$s_pat = '/(<\s*input[^>]*name="';
	$s_pat .= preg_quote($s_name,"/");
	$s_pat .= '\[]"[^>]*type="checkbox"[^>]*?[^"\w])checked(="checked"|(?=[^"\w]))?([^>]*?)(\s*\/\s*)?>';
	$s_pat .= '/ims';
	$s_buf = preg_replace($s_pat,'$1$3$4>',$s_buf);

	foreach ($a_values as $s_value) {

		$s_pat = '/(<\s*input[^>]*type="checkbox"[^>]*name="';
		$s_pat .= preg_quote($s_name,"/");
		$s_pat .= '\[\]"[^>]*value="';
		$s_pat .= preg_quote($s_value,"/");
		$s_pat .= '")([^>]*?)(\s*\/\s*)?>';
		$s_pat .= '/ims';
		$s_buf = preg_replace($s_pat,'$1$2 checked="checked"$3>',$s_buf);

		$s_pat = '/(<\s*input[^>]*name="';
		$s_pat .= preg_quote($s_name,"/");
		$s_pat .= '\[\]"[^>]*type="checkbox"[^>]*value="';
		$s_pat .= preg_quote($s_value,"/");
		$s_pat .= '")([^>]*?)(\s*\/\s*)?>';
		$s_pat .= '/ims';
		$s_buf = preg_replace($s_pat,'$1$2 checked="checked">',$s_buf);
	}
	return ($s_buf);
}

function FixSelect($s_name,$s_value,$s_buf)
{

	$s_pat = '/(<\s*select[^>]*name="';
	$s_pat .= preg_quote($s_name,"/");
	$s_pat .= '".*?<\s*option[^>]*value="';
	$s_pat .= preg_quote($s_value,"/");
	$s_pat .= '"[^>]*)>';
	$s_pat .= '/ims';
	$s_repl = '$1 selected="selected">';

	$s_buf = preg_replace($s_pat,$s_repl,$s_buf);

	return ($s_buf);
}

function FixMultiSelect($s_name,$a_values,$s_buf)
{

	foreach ($a_values as $s_value) {
		$s_pat = '/(<\s*select[^>]*name="';
		$s_pat .= preg_quote($s_name,"/");
		$s_pat .= '\[\]".*?<\s*option[^>]*value="';
		$s_pat .= preg_quote($s_value,"/");
		$s_pat .= '"[^>]*)>';
		$s_pat .= '/ims';
		$s_repl = '$1 selected="selected">';

		$s_buf = preg_replace($s_pat,$s_repl,$s_buf);
	}
	return ($s_buf);
}

function UnCheckStuff($s_buf)
{
	global $php_errormsg;

	$s_pat = '/(<\s*input[^>]*type="checkbox"[^>]*?[^"\w])checked(="checked"|(?=[^"\w]))?([^>]*?)(\s*\/\s*)?>';
	$s_pat .= '/ims';
	$s_buf = preg_replace($s_pat,'$1$3$4>',$s_buf);

	$s_pat = '/(<\s*option[^>]*?[^"\w])selected(="selected"|(?=[^"\w]))?([^>]*)>';
	$s_pat .= '/ims';
	$s_buf = preg_replace($s_pat,'$1$3>',$s_buf);

	return ($s_buf);
}

function AddUserAgent($s_url)
{
	global $aServerVars,$aGetVars;

	$s_agent = "";
	if (isset($aGetVars['USER_AGENT']) && $aGetVars['USER_AGENT'] !== "") {
		$s_agent = $aGetVars['USER_AGENT'];

		if (!IsURLEncoded($s_agent)) {
			$s_agent = urlencode($s_agent);
		}
	} elseif (isset($aServerVars['HTTP_USER_AGENT'])) {
		$s_agent = urlencode($aServerVars['HTTP_USER_AGENT']);
	}

	if ($s_agent !== "") {
		return (AddURLParams($s_url,"USER_AGENT=$s_agent",false));
	} else {
		return ($s_url);
	}
}

function    IsURLEncoded($s_str)
{

	if (preg_match('/[^a-z0-9$_.+!*\'(),%-]/i',$s_str,$a_matches)) {
		FMDebug("IsURLEncoded: '$s_str' matched '" . $a_matches[0] . "' and is therefore not URL-encoded");
		return (false);
	}
	return (true);
}

function SetPreviousValues($s_form_buf,$a_values,$a_strip = array())
{

	$s_form_buf = UnCheckStuff($s_form_buf);
	foreach ($a_values as $s_name => $m_value) {
		if (is_array($m_value)) {

			$s_form_buf = FixCheckboxes($s_name,$m_value,$s_form_buf);
			$s_form_buf = FixMultiSelect($s_name,$m_value,$s_form_buf);
		} else {

			$s_form_buf = FixInputText($s_name,$m_value,$s_form_buf);

			$s_form_buf = FixButton($s_name,$m_value,$s_form_buf);

			$s_form_buf = FixTextArea($s_name,$m_value,$s_form_buf);

			$s_form_buf = FixSelect($s_name,$m_value,$s_form_buf);
		}
	}

	foreach ($a_strip as $s_name) {
		$s_form_buf = RemoveFieldValue($s_name,$s_form_buf);
	}
	return ($s_form_buf);
}

function ProcessReturnToForm($s_url,$a_values,$a_strip = array())
{
	global $php_errormsg;

	if (!CheckValidURL($s_url)) {
		Error("invalid_url",GetMessage(MSG_RETURN_URL_INVALID,
		                               array("URL" => $s_url)),false,false);
	}

	$s_form_url = AddUserAgent($s_url);
	$s_error    = "";
	$s_form_buf = GetURL($s_form_url,$s_error);
	if ($s_form_buf === false) {
		Error("invalid_url",GetMessage(MSG_OPEN_URL,
		                               array("URL"   => $s_form_url,
		                                     "ERROR" => $s_error . ": " . (isset($php_errormsg) ?
				                                     $php_errormsg : "")
		                               )),false,false);
	}

	echo SetPreviousValues($s_form_buf,$a_values,$a_strip);
}

function GetReturnLink($s_this_script,$i_form_index)
{
	if (!CheckValidURL($s_this_script)) {
		Error("not_valid_url",GetMessage(MSG_RETURN_URL_INVALID,
		                                 array("URL" => $s_this_script)),false,false);
	}

	$a_params   = array();
	$a_params[] = "return=$i_form_index";
	if (isset($aServerVars["QUERY_STRING"])) {
		$a_params[] = $aServerVars["QUERY_STRING"];
	}
	$a_params[] = session_name() . "=" . session_id();
	return (AddURLParams($s_this_script,$a_params));
}

function ProcessMultiFormTemplate($s_template,$a_values,&$a_lines)
{
	global $SPECIAL_VALUES;

	if (Settings::isEmpty('MULTIFORMDIR') && Settings::isEmpty('MULTIFORMURL')) {
		SendAlert(GetMessage(MSG_MULTIFORM));
		return (false);
	}

	$i_index                   = GetSession("FormIndex");
	$a_list                    = GetSession("FormList");
	$a_values["this_form_url"] = $a_list[$i_index]["URL"];

	$a_values = GetSavedFileNames($a_values);

	return (DoProcessTemplate(Settings::get('MULTIFORMDIR'),Settings::get('MULTIFORMURL'),$s_template,$a_lines,
	                          $a_values,"",'SubstituteValueForPage'));
}

function OutputMultiFormTemplate($s_template,$a_values)
{
	$a_lines = array();
	if (!ProcessMultiFormTemplate($s_template,$a_values,$a_lines)) {
		Error("multi_form_failed",GetMessage(MSG_MULTIFORM_FAILED,
		                                     array("NAME" => $s_template)),false,false);
	} else {
		$n_lines = count($a_lines);
		$s_buf   = "";
		for ($ii = 0 ; $ii < $n_lines ; $ii++) {
			$s_buf .= $a_lines[$ii] . "\n";
			unset($a_lines[$ii]); // free memory (hopefully)
		}
		unset($a_lines); // free memory (hopefully)

		if (IsSetSession("FormKeep"))

		{
			echo SetPreviousValues($s_buf,GetSession("FormKeep"));
		} else {
			echo $s_buf;
		}
	}
}

function MimePreamble(&$a_lines,$a_mesg = array())
{
	$a_preamble = explode("\n",GetMessage(MSG_MIME_PREAMBLE));
	foreach ($a_preamble as $s_line) {
		$a_lines[] = $s_line . Settings::get('HEAD_CRLF');
	}

	$a_lines[]    = Settings::get('HEAD_CRLF'); // blank line
	$b_need_blank = false;
	foreach ($a_mesg as $s_line) {
		$a_lines[] = $s_line . Settings::get('HEAD_CRLF');
		if (!empty($s_line)) {
			$b_need_blank = true;
		}
	}
	if ($b_need_blank) {
		$a_lines[] = Settings::get('HEAD_CRLF');
	} // blank line
}

function HTMLMail(&$a_lines,&$a_headers,$s_body,$s_template,$s_missing,$s_filter,
                  $s_boundary,$a_raw_fields,$b_no_plain,$b_process_template)
{
	$s_charset = GetMailOption("CharSet");
	if (!isset($s_charset)) {
		$s_charset = "ISO-8859-1";
	}
	if ($b_no_plain) {
		$b_multi = false;

		$a_headers['Content-Type'] = SafeHeader("text/html; charset=$s_charset");
	} else {
		$b_multi                   = true;
		$a_headers['Content-Type'] = "multipart/alternative; boundary=\"$s_boundary\"";

		$a_pre_lines = explode("\n",GetMessage(MSG_MIME_HTML,
		                                       array("NAME" => $s_template)));

		MimePreamble($a_lines,$a_pre_lines);

		$a_lines[] = "--$s_boundary" . Settings::get('HEAD_CRLF');
		$a_lines[] = "Content-Type: text/plain; charset=$s_charset" . Settings::get('HEAD_CRLF');
		$a_lines[] = Settings::get('HEAD_CRLF'); // blank line

		$a_lines[] = $s_body;
		$a_lines[] = Settings::get('HEAD_CRLF'); // blank line

		$a_lines[] = "--$s_boundary" . Settings::get('HEAD_CRLF');
		$a_lines[] = "Content-Type: text/html; charset=$s_charset" . Settings::get('HEAD_CRLF');
		$a_lines[] = Settings::get('HEAD_CRLF'); // blank line
	}

	$a_html_lines = array();
	if (!$b_process_template) {
		if (!ProcessTemplate($s_template,$a_html_lines,$a_raw_fields,NULL,'SubstituteValueDummy')) {
			return (false);
		}
	} elseif (!ProcessTemplate($s_template,$a_html_lines,$a_raw_fields,$s_missing)) {
		return (false);
	}

	if (!empty($s_filter))

	{
		$a_lines[] = Filter($s_filter,$a_html_lines);
	} else {
		foreach ($a_html_lines as $s_line) {
			$a_lines[] = $s_line;
		}
	}

	if ($b_multi) {

		$a_lines[] = "--$s_boundary--" . Settings::get('HEAD_CRLF');
		$a_lines[] = Settings::get('HEAD_CRLF'); // blank line
	}
	return (true);
}

function AddFile(&$a_lines,$s_file_name,$i_file_size,$b_remove = true)
{
	global $php_errormsg;

	@   $fp = fopen($s_file_name,"rb");
	if ($fp === false) {
		SendAlert(GetMessage(MSG_FILE_OPEN_ERROR,array("NAME"  => $s_file_name,
		                                               "TYPE"  => "attachment",
		                                               "ERROR" => CheckString($php_errormsg)
		)));
		return (false);
	}

	$s_contents = fread($fp,$i_file_size);

	$a_lines[] = chunk_split(base64_encode($s_contents));
	fclose($fp);
	if ($b_remove) {
		@unlink($s_file_name);
	}
	return (true);
}

function AddData(&$a_lines,$s_data)
{

	$a_lines[] = chunk_split(base64_encode($s_data));
	return (true);
}

function IsUploadedFile($a_file_spec)
{

	if (isset($a_file_spec["moved"]) && $a_file_spec["moved"]) {
		return (true);
	}
	return (is_uploaded_file($a_file_spec["tmp_name"]));
}

function SaveFileInRepository(&$a_file_spec)
{
	global $php_errormsg;

	if (isset($a_file_spec["new_name"])) {
		$s_file_name = basename($a_file_spec["new_name"]);
	} else {
		$s_file_name = basename($a_file_spec["name"]);
	}
	$s_dest = Settings::get('FILE_REPOSITORY') . "/" . $s_file_name;

	$b_ok    = true;
	$s_error = "";

	if (isset($a_file_spec["saved_as"]) && !empty($a_file_spec["saved_as"])) {
		FMDebug("SaveFileInRepository: saved_as");
		$s_srce = $a_file_spec["saved_as"];
	} else {
		$s_srce = $a_file_spec["tmp_name"];
	}

	FMDebug("SaveFileInRepository: $s_srce");
	if (!Settings::get('FILE_OVERWRITE')) {
		clearstatcache();
		if (@file_exists($s_dest)) {
			$b_ok    = false;
			$s_error = GetMessage(MSG_SAVE_FILE_EXISTS,array("FILE" => $s_dest));
		}
	}
	if (Settings::get('MAX_FILE_UPLOAD_SIZE') != 0 &&
	    $a_file_spec["size"] > Settings::get('MAX_FILE_UPLOAD_SIZE') * 1024
	)

	{
		UserError("upload_size",GetMessage(MSG_FILE_UPLOAD_SIZE,
		                                   array("NAME" => $a_file_spec["name"],
		                                         "SIZE" => $a_file_spec["size"],
		                                         "MAX"  => Settings::get('MAX_FILE_UPLOAD_SIZE')
		                                   )));
	}
	if ($b_ok) {
		if (isset($a_file_spec["saved_as"]) && !empty($a_file_spec["saved_as"])) {
			if (!copy($s_srce,$s_dest) || !@unlink($s_srce)) {
				$b_ok = false;
			}
		} else {
			if (!move_uploaded_file($s_srce,$s_dest)) {
				$b_ok = false;
			}
		}
		if ($b_ok) {

			$a_file_spec["in_repository"] = true;

			$a_file_spec["saved_as"] = $s_dest;

			$a_file_spec["moved"] = true;
		} else {
			$s_error = $php_errormsg;
		}
	}
	if (!$b_ok) {
		SendAlert(GetMessage(MSG_SAVE_FILE,array(
			"FILE" => $s_srce,
			"DEST" => $s_dest,
			"ERR"  => $s_error
		)));
		return (false);
	}

	if (Settings::get('FILE_MODE') != 0 && !chmod($s_dest,Settings::get('FILE_MODE'))) {
		SendAlert(GetMessage(MSG_CHMOD,array(
			"FILE" => $s_dest,
			"MODE" => Settings::get('FILE_MODE'),
			"ERR"  => $s_error
		)));
	}
	return (true);
}

function SaveAllFilesToRepository()
{
	global $aFileVars;

	if (!Settings::get('FILEUPLOADS') || Settings::get('FILE_REPOSITORY') === "")

	{
		return (true);
	}

	foreach ($aFileVars as $m_file_key => $a_upload) {

		if (!isset($a_upload["tmp_name"]) || empty($a_upload["tmp_name"]) ||
		    !isset($a_upload["name"]) || empty($a_upload["name"])
		) {
			continue;
		}
		if (isset($a_upload["in_repository"]) && $a_upload["in_repository"])

		{
			continue;
		}
		if (!IsUploadedFile($a_upload)) {
			SendAlert(GetMessage(MSG_FILE_UPLOAD_ATTACK,
			                     array("NAME" => $a_upload["name"],
			                           "TEMP" => $a_upload["tmp_name"],
			                           "FLD"  => $m_file_key
			                     )));
			continue;
		}
		if (!SaveFileInRepository($aFileVars[$m_file_key])) {
			return (false);
		}

		if (IsSetSession("FormSavedFiles")) {
			$a_saved_files = GetSession("FormSavedFiles");
		} else {
			$a_saved_files = array();
		}
		$a_saved_files["repository_" . $m_file_key] = $aFileVars[$m_file_key];
		SetSession("FormSavedFiles",$a_saved_files);
	}
	return (true);
}

function DeleteFileFromRepository($s_fld)
{
	global $aFileVars;

	if (!Settings::get('FILEUPLOADS') || Settings::get('FILE_REPOSITORY') === "")

	{
		return (false);
	}

	if (($a_upload = GetFileInfo($s_fld)) === false) {
		return (false);
	}

	if (isset($a_upload["in_repository"]) && $a_upload["in_repository"]) {
		if (isset($a_upload["saved_as"]) && !empty($a_upload["saved_as"])) {
			@unlink($a_upload["saved_as"]);
		}
	}
	DeleteFileInfo($s_fld);
	return (true);
}

function SaveUploadedFile(&$a_file_spec,$s_prefix)
{
	global $php_errormsg;

	FMDebug("SaveUploadedFile");
	$s_dest = GetScratchPadFile($s_prefix);
	if (!move_uploaded_file($a_file_spec["tmp_name"],$s_dest)) {
		SendAlert(GetMessage(MSG_SAVE_FILE,array(
			"FILE" => $a_file_spec["tmp_name"],
			"DEST" => $s_dest,
			"ERR"  => $php_errormsg
		)));
		return (false);
	}
	$a_file_spec["saved_as"] = $s_dest;
	$a_file_spec["moved"]    = true;
	return (true);
}

function CleanScratchPad($s_prefix = "")
{
	global $lNow;
	global $php_errormsg;

	if (Settings::isEmpty('SCRATCH_PAD'))

	{
		return;
	}
	if (Settings::get('CLEANUP_TIME') <= 0)

	{
		return;
	}

	if (Settings::get('CLEANUP_CHANCE') < 100) {
		$i_rand = mt_rand(1,100);
		if ($i_rand > Settings::get('CLEANUP_CHANCE')) {
			return;
		}
	}
	if (($f_dir = @opendir(Settings::get('SCRATCH_PAD'))) === false) {
		Error("open_scratch_pad",GetMessage(MSG_OPEN_SCRATCH_PAD,array(
			"DIR" => Settings::get('SCRATCH_PAD'),
			"ERR" => $php_errormsg
		)),false,false);
		return;
	}
	$i_len = strlen($s_prefix);
	while (($s_file = readdir($f_dir)) !== false) {
		$s_path = Settings::get('SCRATCH_PAD') . "/" . $s_file;
		if (is_file($s_path) && ($i_len == 0 || substr($s_file,0,$i_len) == $s_prefix)) {
			if (($a_stat = @stat($s_path)) !== false) {
				if (isset($a_stat['mtime'])) {
					$l_time = $a_stat['mtime'];
				} else {
					$l_time = $a_stat[9];
				}
				if (($lNow - $l_time) / 60 >= Settings::get('CLEANUP_TIME')) {
					@unlink($s_path);
				}
			}
		}
	}
	closedir($f_dir);
}

function SaveAllUploadedFiles(&$a_file_vars)
{
	global $php_errormsg;

	$s_prefix = "UPLD";
	if (Settings::isEmpty('SCRATCH_PAD')) {
		Error("need_scratch_pad",GetMessage(MSG_NEED_SCRATCH_PAD),false,false);
		return (false);
	}

	CleanScratchPad($s_prefix);

	foreach (array_keys($a_file_vars) as $m_file_key) {
		$a_upload = &$a_file_vars[$m_file_key];

		if (!isset($a_upload["tmp_name"]) || empty($a_upload["tmp_name"]) ||
		    !isset($a_upload["name"]) || empty($a_upload["name"])
		) {
			continue;
		}

		if (!isset($a_upload["saved_as"]) || empty($a_upload["saved_as"])) {
			if (!IsUploadedFile($a_upload)) {
				SendAlert(GetMessage(MSG_FILE_UPLOAD_ATTACK,
				                     array("NAME" => $a_upload["name"],
				                           "TEMP" => $a_upload["tmp_name"],
				                           "FLD"  => $m_file_key
				                     )));
			} elseif (!SaveUploadedFile($a_upload,$s_prefix)) {
				return (false);
			}
		}
	}
	return (true);
}

function AttachFile(&$a_lines,$s_att_boundary,$a_file_spec,$s_charset,$b_remove = true)
{
	$a_lines[] = "--$s_att_boundary" . Settings::get('HEAD_CRLF');

	if (isset($a_file_spec["new_name"])) {
		$s_file_name = $a_file_spec["new_name"];
	} else {
		$s_file_name = $a_file_spec["name"];
	}
	$s_file_name = str_replace('"','',$s_file_name);
	$s_mime_type = $a_file_spec["type"];

	$a_lines[] = "Content-Type: $s_mime_type; name=\"$s_file_name\"; charset=$s_charset" . Settings::get('HEAD_CRLF');
	$a_lines[] = "Content-Transfer-Encoding: base64" . Settings::get('HEAD_CRLF');
	$a_lines[] = "Content-Disposition: attachment; filename=\"$s_file_name\"" . Settings::get('HEAD_CRLF');
	$a_lines[] = Settings::get('HEAD_CRLF'); // blank line
	if (isset($a_file_spec["tmp_name"]) && isset($a_file_spec["size"])) {
		$s_srce = $a_file_spec["tmp_name"];

		if (isset($a_file_spec["saved_as"]) && !empty($a_file_spec["saved_as"])) {
			$s_srce = $a_file_spec["saved_as"];
		}
		FMDebug("AttachFile: $s_srce");
		return (AddFile($a_lines,$s_srce,$a_file_spec["size"],$b_remove));
	}
	if (!isset($a_file_spec["data"])) {
		SendAlert(GetMessage(MSG_ATTACH_DATA));
		return (false);
	}
	return (AddData($a_lines,$a_file_spec["data"]));
}

function MakeMimeMail(&$s_body,&$a_headers,$a_raw_fields,$s_template = "",
                      $s_missing = NULL,$b_no_plain = false,
                      $s_filter = "",$a_file_vars = array(),
                      $a_attach_spec = array(),$b_process_template = true)
{
	global $FM_VERS;
	global $SPECIAL_VALUES;

	$s_charset = GetMailOption("CharSet");
	if (!isset($s_charset)) {
		$s_charset = "ISO-8859-1";
	}
	$b_att        = $b_html = false;
	$b_got_filter = (isset($s_filter) && !empty($s_filter));
	if (isset($s_template) && !empty($s_template)) {
		$b_html = true;
	}
	if (count($a_file_vars) > 0) {
		if (!Settings::get('FILEUPLOADS')) {
			SendAlert(GetMessage(MSG_FILE_UPLOAD));

		} elseif (Settings::get('FILE_REPOSITORY') === "" || IsMailOptionSet("AlwaysEmailFiles")) {
			foreach ($a_file_vars as $a_upload) {

				if (isset($a_upload["tmp_name"]) && !empty($a_upload["tmp_name"]) &&
				    isset($a_upload["name"]) && !empty($a_upload["name"])
				) {
					$b_att = true;
					break;
				}
			}
		}
	}

	if (isset($a_attach_spec["Data"])) {
		$b_att = true;
	}

	$s_uniq                    = md5($s_body);
	$s_body_boundary           = "BODY$s_uniq";
	$s_att_boundary            = "PART$s_uniq";
	$a_headers['MIME-Version'] = "1.0 (produced by FormMail $FM_VERS from www.tectite.com)";

	if ($b_got_filter && IsFilterAttribSet($s_filter,"Strips"))

	{
		$b_html = false;
	}
	$a_new = array();
	if ($b_att) {
		$a_headers['Content-Type'] = "multipart/mixed; boundary=\"$s_att_boundary\"";

		MimePreamble($a_new);

		$a_new[] = "--$s_att_boundary" . Settings::get('HEAD_CRLF');
		if ($b_html) {
			$a_lines = $a_local_headers = array();
			if (!HTMLMail($a_lines,$a_local_headers,$s_body,$s_template,
			              $s_missing,($b_got_filter) ? $s_filter : "",
			              $s_body_boundary,$a_raw_fields,$b_no_plain,
			              $b_process_template)
			) {
				return (false);
			}
			$a_new   = array_merge($a_new,ExpandMailHeadersArray($a_local_headers));
			$a_new[] = Settings::get('HEAD_CRLF'); // blank line after header
			$a_new   = array_merge($a_new,$a_lines);
		} else {
			$a_new[] = "Content-Type: text/plain; charset=$s_charset" . Settings::get('HEAD_CRLF');
			$a_new[] = Settings::get('HEAD_CRLF'); // blank line

			$a_new[] = $s_body;
		}

		if (Settings::get('FILEUPLOADS') &&
		    (Settings::get('FILE_REPOSITORY') === "" || IsMailOptionSet("AlwaysEmailFiles"))
		) {
			foreach ($a_file_vars as $m_file_key => $a_upload) {

				if (!isset($a_upload["tmp_name"]) || empty($a_upload["tmp_name"]) ||
				    !isset($a_upload["name"]) || empty($a_upload["name"])
				) {
					continue;
				}
				if (!IsUploadedFile($a_upload)) {
					SendAlert(GetMessage(MSG_FILE_UPLOAD_ATTACK,
					                     array("NAME" => $a_upload["name"],
					                           "TEMP" => $a_upload["tmp_name"],
					                           "FLD"  => $m_file_key
					                     )));
					continue;
				}
				if (Settings::get('MAX_FILE_UPLOAD_SIZE') != 0 &&
				    $a_upload["size"] > Settings::get('MAX_FILE_UPLOAD_SIZE') * 1024
				) {
					UserError("upload_size",GetMessage(MSG_FILE_UPLOAD_SIZE,
					                                   array("NAME" => $a_upload["name"],
					                                         "SIZE" => $a_upload["size"],
					                                         "MAX"  => Settings::get('MAX_FILE_UPLOAD_SIZE')
					                                   )));
				}
				if (!AttachFile($a_new,$s_att_boundary,$a_upload,$s_charset,
				                (Settings::get('FILE_REPOSITORY') === "") ? true : false)
				) {
					return (false);
				}
			}
		}
		if (isset($a_attach_spec["Data"])) {

			$a_file_spec["name"] = isset($a_attach_spec["Name"]) ?
				$a_attach_spec["Name"] :
				"attachment.dat";
			$a_file_spec["type"] = isset($a_attach_spec["MIME"]) ?
				$a_attach_spec["MIME"] :
				"text/plain";
			$a_file_spec["data"] = $a_attach_spec["Data"];
			if (!AttachFile($a_new,$s_att_boundary,$a_file_spec,
			                isset($a_attach_spec["CharSet"]) ?
				                $a_attach_spec["CharSet"] :
				                $s_charset)
			) {
				return (false);
			}
		}
		$a_new[] = "--$s_att_boundary--" . Settings::get('HEAD_CRLF'); // the end
		$a_new[] = Settings::get('HEAD_CRLF'); // blank line
	} elseif ($b_html) {
		if (!HTMLMail($a_new,$a_headers,$s_body,$s_template,
		              $s_missing,($b_got_filter) ? $s_filter : "",
		              $s_body_boundary,$a_raw_fields,$b_no_plain,
		              $b_process_template)
		) {
			return (false);
		}
	} else {
		$a_headers['Content-Type'] = SafeHeader("text/plain; charset=$s_charset");

		$a_new[] = $s_body;
	}

	$s_body = JoinLines(Settings::get('BODY_LF'),$a_new);
	return (true);
}

function MakeFromLine($s_email,$s_name)
{
	$s_style = GetMailOption("FromLineStyle");
	$s_line  = "";
	if (!isset($s_style)) {
		$s_style = "";
	}

	switch ($s_style) {
		default:
		case "":
		case "default":
		case "AddrSpecName":

			if (!empty($s_email)) {
				$s_line .= SafeHeaderEmail($s_email) . " ";
			}
			if (!empty($s_name)) {
				$s_line .= "(" . SafeHeaderComment(EncodeHeaderText($s_name)) . ")";
			}
			break;
		case "NameAddrSpec":

			if (!empty($s_name)) {
				$s_line .= "(" . SafeHeaderComment(EncodeHeaderText($s_name)) . ") ";
			}
			if (!empty($s_email)) {
				$s_line .= SafeHeaderEmail($s_email);
			}
			break;
		case "RouteAddr":

			if (!empty($s_email)) {
				$s_line .= "<" . SafeHeaderEmail($s_email) . ">";
			}
			break;
		case "QuotedNameRouteAddr":

			if (!empty($s_name)) {
				$s_line .= '"' . SafeHeaderQString(EncodeHeaderText($s_name)) . '" ';
			}
			if (!empty($s_email)) {
				$s_line .= "<" . SafeHeaderEmail($s_email) . ">";
			}
			break;
		case "NameRouteAddr":

			if (!empty($s_name)) {
				$s_line .= SafeHeaderWords(EncodeHeaderText($s_name)) . ' ';
			}
			if (!empty($s_email)) {
				$s_line .= "<" . SafeHeaderEmail($s_email) . ">";
			}
			break;
	}
	return ($s_line);
}


function GetFilteredOutput($a_fld_order,$a_clean_fields,$s_filter,$a_filter_list)
{

	$a_unfiltered_list = array();
	$n_flds            = count($a_fld_order);
	for ($ii = 0 ; $ii < $n_flds ; $ii++) {
		if (!in_array($a_fld_order[$ii],$a_filter_list)) {
			$a_unfiltered_list[] = $a_fld_order[$ii];
		}
	}
	$s_unfiltered_results = MakeFieldOutput($a_unfiltered_list,$a_clean_fields);

	$s_filtered_results = MakeFieldOutput($a_filter_list,$a_clean_fields);
	$s_filtered_results = Filter($s_filter,$s_filtered_results);
	return (array($s_unfiltered_results,$s_filtered_results));
}

function MakePlainEmail($a_fld_order,$a_clean_fields,
                        $s_to,$s_cc,$s_bcc,$a_raw_fields,$s_filter,$a_filter_list)
{
	global $SPECIAL_VALUES;

	$s_unfiltered_results = $s_filtered_results = "";
	$b_got_filter         = (isset($s_filter) && !empty($s_filter));
	if ($b_got_filter) {
		if (isset($a_filter_list) && count($a_filter_list) > 0) {
			$b_limited_filter = true;
		} else {
			$b_limited_filter = false;
		}
	}
	$b_used_template = false;
	if (IsMailOptionSet("PlainTemplate")) {
		$s_template = GetMailOption("PlainTemplate");
		if (ProcessTemplate($s_template,$a_lines,$a_raw_fields,GetMailOption('TemplateMissing'),
		                    'SubstituteValuePlain')
		) {
			$b_used_template      = true;
			$s_unfiltered_results = implode(Settings::get('BODY_LF'),$a_lines);
			if ($b_got_filter) {

				if ($b_limited_filter) {
					list ($s_discard,$s_filtered_results) = GetFilteredOutput($a_fld_order,
					                                                          $a_clean_fields,
					                                                          $s_filter,$a_filter_list);
				} else {
					$s_filtered_results   = Filter($s_filter,$s_unfiltered_results);
					$s_unfiltered_results = "";
				}
			}
		}
	}
	if (!$b_used_template) {
		$res_hdr = "";

		if (IsMailOptionSet("DupHeader")) {

			$res_hdr = "To: $s_to" . Settings::get('BODY_LF');
			if (!empty($s_cc)) {
				$res_hdr .= "Cc: $s_cc" . Settings::get('BODY_LF');
			}
			if (!empty($SPECIAL_VALUES["email"])) {
				$res_hdr .= "From: " . MakeFromLine($SPECIAL_VALUES["email"],
				                                    $SPECIAL_VALUES["realname"]) . Settings::get('BODY_LF');
			}
			$res_hdr .= Settings::get('BODY_LF');
			if (IsMailOptionSet("StartLine")) {
				$res_hdr .= "--START--" . Settings::get('BODY_LF');
			} // signals the beginning of the text to filter
		}

		if (!IsMailExcluded("realname")) {
			array_unshift($a_fld_order,"realname");
			$a_clean_fields["realname"] = $SPECIAL_VALUES["realname"];
		}
		if (!IsMailExcluded("email")) {
			array_unshift($a_fld_order,"email");
			$a_clean_fields["email"] = $SPECIAL_VALUES["email"];
		}
		if ($b_got_filter) {
			if ($b_limited_filter) {
				list($s_unfiltered_results,$s_filtered_results) =
					GetFilteredOutput($a_fld_order,$a_clean_fields,
					                  $s_filter,$a_filter_list);
			} else {

				$s_filtered_results = MakeFieldOutput($a_fld_order,$a_clean_fields);
				$s_filtered_results = Filter($s_filter,$s_filtered_results);
			}
		} else {

			$s_unfiltered_results = MakeFieldOutput($a_fld_order,$a_clean_fields);
		}
		$s_unfiltered_results = $res_hdr . $s_unfiltered_results;
	}
	$s_results = $s_unfiltered_results;
	if ($b_got_filter && !empty($s_filtered_results)) {
		if (!empty($s_results)) {
			$s_results .= Settings::get('BODY_LF');
		}
		$s_results .= $s_filtered_results;
	}

	if (isset($SPECIAL_VALUES["env_report"]) && !empty($SPECIAL_VALUES["env_report"])) {
		$s_results .= Settings::get('BODY_LF') . "==================================" . Settings::get('BODY_LF');
		$s_results .= Settings::get('BODY_LF') . GetEnvVars(TrimArray(explode(",",$SPECIAL_VALUES["env_report"])),
		                                                    Settings::get('BODY_LF'));
	}
	return (array($s_results,$s_unfiltered_results,$s_filtered_results));
}

function GetFilterList($b_file_fields)
{
	global $SPECIAL_VALUES;

	if (!empty($SPECIAL_VALUES["filter"])) {
		if ($b_file_fields) {
			if (isset($SPECIAL_VALUES["filter_files"]) && !empty($SPECIAL_VALUES["filter_files"])) {
				return (TrimArray(explode(",",$SPECIAL_VALUES["filter_files"])));
			}
		} else {
			if (isset($SPECIAL_VALUES["filter_fields"]) && !empty($SPECIAL_VALUES["filter_fields"])) {
				return (TrimArray(explode(",",$SPECIAL_VALUES["filter_fields"])));
			}
		}
	}
	return (false);
}

function    GetFilterSpec(&$s_filter,&$m_filter_list,$b_file_fields = false)
{
	global $SPECIAL_VALUES;

	if (isset($SPECIAL_VALUES["filter"]) && !empty($SPECIAL_VALUES["filter"])) {
		$s_filter      = $SPECIAL_VALUES["filter"];
		$m_filter_list = GetFilterList($b_file_fields);
		return (true);
	}
	return (false);
}

function SendResults($a_fld_order,$a_clean_fields,$s_to,$s_cc,$s_bcc,$a_raw_fields)
{
	global $SPECIAL_VALUES,$aFileVars;

	$b_filter_attach = false;
	$a_attach_spec   = array();
	$s_filter        = "";
	$a_filter_list   = array();
	if ($b_got_filter = GetFilterSpec($s_filter,$a_filter_list)) {
		if ($a_filter_list === false) {

			$b_limited_filter = false;
			$a_filter_list    = array();
		} else {
			$b_limited_filter = true;
		}
		FMDebug("SendResults: got filter '$s_filter', limited=$b_limited_filter");
		$s_filter_attach_name = GetFilterOption("Attach");
		if (isset($s_filter_attach_name)) {
			if (!is_string($s_filter_attach_name) || empty($s_filter_attach_name)) {
				SendAlert(GetMessage(MSG_ATTACH_NAME));
			} else {
				$b_filter_attach = true;
				$a_attach_spec   = array("Name" => $s_filter_attach_name);
				if (($s_mime = GetFilterAttrib($s_filter,"MIME")) !== false) {
					$a_attach_spec["MIME"] = $s_mime;
				}

				if (($s_cset = GetFilterAttrib($s_filter,"CharSet")) !== false) {
					$a_attach_spec["CharSet"] = $s_cset;
				}
			}
		}
	}

	$b_mime_mail = (IsMailOptionSet("HTMLTemplate") || count($aFileVars) > 0 ||
	                $b_filter_attach);

	$a_headers = array();
	if (!empty($s_cc)) {
		$a_headers['Cc'] = SafeHeader($s_cc);
	}
	if (!empty($SPECIAL_VALUES["replyto"])) {

		CheckEmailAddress($SPECIAL_VALUES["replyto"],$s_list,$s_invalid,false);
		if (!empty($s_list)) {
			$a_headers['Reply-To'] = SafeHeader($s_list);
		}
	}
	if (!empty($s_bcc)) {
		$a_headers['Bcc'] = SafeHeader($s_bcc);
	}

	$s_sender = GetMailOption("FromAddr");
	if (!isset($s_sender)) {
		$s_sender = "";
		if (!empty($SPECIAL_VALUES["email"])) {
			$a_headers['From'] = MakeFromLine($SPECIAL_VALUES["email"],
			                                  $SPECIAL_VALUES["realname"]);
		}
	} elseif ($s_sender !== "") {
		$s_sender = $a_headers['From'] = SafeHeader(UnMangle($s_sender));
	}

	if (Settings::get('FIXED_SENDER') !== "") {
		$s_sender = Settings::get('FIXED_SENDER');
	}

	if ($s_sender === "") {
		if (Settings::get('SET_SENDER_FROM_EMAIL')) {
			$s_sender = $SPECIAL_VALUES["email"];
		}
	}

	$a_keys = array_keys($a_raw_fields);
	if (count($a_keys) == 1 && is_string($a_raw_fields[$a_keys[0]]) &&
	    !IsMailOptionSet("AlwaysList") && !IsMailOptionSet("DupHeader")
	) {
		if (IsMailExcluded($a_keys[0])) {
			SendAlert("Exclusion of single field '" . $a_keys[0] . "' ignored");
		}
		$s_value = $a_raw_fields[$a_keys[0]];

		$s_value = str_replace("\r\n",'<br />',$s_value);

		$s_value = str_replace("\n",'<br />',$s_value);

		$s_value = str_replace("\r","",$s_value);

		$s_value = preg_replace('/[[:cntrl:]]+/','<br />',$s_value);

		$s_value = StripHTML($s_value,Settings::get('BODY_LF'));

		if ($b_mime_mail) {
			if ($b_got_filter) {

				$s_results = Filter($s_filter,$s_value);
				if ($b_filter_attach) {
					$a_attach_spec["Data"] = $s_results;

					if (!IsFilterOptionSet("KeepInLine")) {
						$s_results = "";
					}
					$s_filter = ""; // no more filtering
				}
			} else {
				$s_results = $s_value;
			}

			if (!MakeMimeMail($s_results,$a_headers,$a_raw_fields,
			                  GetMailOption('HTMLTemplate'),
			                  GetMailOption('TemplateMissing'),
			                  IsMailOptionSet("NoPlain"),
			                  $s_filter,$aFileVars,$a_attach_spec)
			) {
				return (false);
			}
		} elseif ($b_got_filter)

		{
			$s_results = Filter($s_filter,$s_value);
		} else {
			$s_results = $s_value;
			if (IsMailOptionSet("CharSet"))

			{
				$a_headers['Content-Type'] = "text/plain; charset=" . SafeHeader(GetMailOption("CharSet"));
			}
		}
	} else {
		if ($b_mime_mail) {

			list($s_results,$s_unfiltered_results,$s_filtered_results) =
				MakePlainEmail($a_fld_order,$a_clean_fields,
				               $s_to,$s_cc,$s_bcc,$a_raw_fields,$s_filter,
				               $a_filter_list);
			if ($b_filter_attach) {

				$a_attach_spec["Data"] = $s_filtered_results;

				if (!IsFilterOptionSet("KeepInLine"))
					//
					// put the unfiltered results in the body of the message
					//
				{
					$s_results = $s_unfiltered_results;
				}
				$s_filter = ""; // no more filtering
			}
			if (!MakeMimeMail($s_results,$a_headers,$a_raw_fields,
			                  GetMailOption('HTMLTemplate'),
			                  GetMailOption('TemplateMissing'),
			                  IsMailOptionSet("NoPlain"),
			                  $s_filter,$aFileVars,$a_attach_spec)
			) {
				return (false);
			}
		} else {
			list($s_results,$s_unfiltered_results,$s_filtered_results) =
				MakePlainEmail($a_fld_order,$a_clean_fields,
				               $s_to,$s_cc,$s_bcc,$a_raw_fields,$s_filter,
				               $a_filter_list);
			if (!$b_got_filter && IsMailOptionSet("CharSet"))

			{
				$a_headers['Content-Type'] = "text/plain; charset=" . SafeHeader(GetMailOption("CharSet"));
			}
		}
	}

	if (Settings::get('FILEUPLOADS') && Settings::get('FILE_REPOSITORY') !== "") {
		if (!SaveAllFilesToRepository()) {
			return (false);
		}
	}

	return (SendCheckedMail($s_to,$SPECIAL_VALUES["subject"],$s_results,
	                        $s_sender,$a_headers));
}

function WriteLog($log_file)
{
	global $SPECIAL_VALUES,$php_errormsg;

	@   $log_fp = fopen($log_file,"a");
	if ($log_fp === false) {
		SendAlert(GetMessage(MSG_FILE_OPEN_ERROR,array("NAME"  => $log_file,
		                                               "TYPE"  => "log",
		                                               "ERROR" => CheckString($php_errormsg)
		)));
		return;
	}
	$date  = gmdate("H:i:s d-M-y T");
	$entry = $date . ":" . $SPECIAL_VALUES["email"] . "," .
	         $SPECIAL_VALUES["realname"] . "," . $SPECIAL_VALUES["subject"] . "\n";
	fwrite($log_fp,$entry);
	fclose($log_fp);
}

function WriteCSVFile($s_csv_file,$a_vars)
{
	global $SPECIAL_VALUES;

	$a_column_list = $SPECIAL_VALUES["csvcolumns"];
	if (!isset($a_column_list) || empty($a_column_list) || !is_string($a_column_list)) {
		SendAlert(GetMessage(MSG_CSVCOLUMNS,array("VALUE" => $a_column_list)));
		return;
	}
	if (!isset($s_csv_file) || empty($s_csv_file) || !is_string($s_csv_file)) {
		SendAlert(GetMessage(MSG_CSVFILE,array("VALUE" => $s_csv_file)));
		return;
	}

	@   $fp = fopen($s_csv_file,"a" . Settings::get('CSVOPEN'));
	if ($fp === false) {
		SendAlert(GetMessage(MSG_FILE_OPEN_ERROR,array("NAME"  => $s_csv_file,
		                                               "TYPE"  => "CSV",
		                                               "ERROR" => CheckString($php_errormsg)
		)));
		return;
	}

	$a_column_list = TrimArray(explode(",",$a_column_list));
	$n_columns     = count($a_column_list);

	$b_heading = false;
	if (filesize($s_csv_file) == 0) {
		$b_heading = true;
	}

	$csv_format = new CSVFormat();

	$csv_format->SetQuote(Settings::get('CSVQUOTE'));
	$csv_format->SetEscPolicy("conv");
	$csv_format->SetSep(Settings::get('CSVSEP'));
	$csv_format->SetIntSep(Settings::get('CSVINTSEP'));
	if (Settings::get('LIMITED_IMPORT')) {
		$csv_format->SetCleanFunc(create_function('$m_value',
		                                          'return CleanValue($m_value);'));
	}

	$s_csv = $csv_format->MakeCSVRecord($a_column_list,$a_vars);

	if ($b_heading) {
		fwrite($fp,$csv_format->MakeHeading($a_column_list) . Settings::get('CSVLINE'));
	}

	fwrite($fp,$s_csv . Settings::get('CSVLINE'));
	fclose($fp);

}

function CheckConfig()
{

	$a_mesgs = array();
	if (in_array("TARGET_EMAIL",Settings::get('CONFIG_CHECK'))) {

		$a_target_email = Settings::get('TARGET_EMAIL');
		for ($ii = 0 ; $ii < count($a_target_email) ; $ii++) {
			$s_pattern = $a_target_email[$ii];
			if (substr($s_pattern,0,1) != '^') {
				$a_mesgs[] = GetMessage(MSG_TARG_EMAIL_PAT_START,
				                        array("PAT" => $s_pattern));
			}
			if (substr($s_pattern,-1) != '$') {
				$a_mesgs[] = GetMessage(MSG_TARG_EMAIL_PAT_END,
				                        array("PAT" => $s_pattern));
			}
		}
	}
	if (count($a_mesgs) > 0) {
		SendAlert(GetMessage(MSG_CONFIG_WARN,
		                     array("MESGS" => implode("\n",$a_mesgs))),false,true);
	}
}

function WriteARLog($s_to,$s_subj,$s_info)
{
	global $aServerVars,$php_errormsg;

	if (Settings::isEmpty('LOGDIR') || Settings::isEmpty('AUTORESPONDLOG')) {
		return;
	}

	$log_file = Settings::get('LOGDIR') . "/" . Settings::get('AUTORESPONDLOG');
	@   $log_fp = fopen($log_file,"a");
	if ($log_fp === false) {
		SendAlert(GetMessage(MSG_FILE_OPEN_ERROR,array("NAME"  => $log_file,
		                                               "TYPE"  => "log",
		                                               "ERROR" => CheckString($php_errormsg)
		)));
		return;
	}
	$a_entry   = array();
	$a_entry[] = gmdate("H:i:s d-M-y T"); // date/time in GMT
	$a_entry[] = $aServerVars['REMOTE_ADDR']; // remote IP address
	$a_entry[] = $s_to; // target email address
	$a_entry[] = $s_subj; // subject line
	$a_entry[] = $s_info; // information

	$s_log_entry = implode(",",$a_entry) . "\n";
	fwrite($log_fp,$s_log_entry);
	fclose($log_fp);
}

if (isset($aGetVars["testalert"]) && $aGetVars["testalert"] == 1) {
	function ShowServerVar($s_name)
	{
		global $aServerVars;

		return (isset($aServerVars[$s_name]) ? $aServerVars[$s_name] : "-not set-");
	}

	$sAlert = GetMessage(MSG_ALERT,
	                     array("LANG"               => $sLangID,
	                           "PHPVERS"            => $ExecEnv->getPHPVersionString(),
	                           "FM_VERS"            => $FM_VERS,
	                           "SERVER"             => (IsServerWindows() ? "Windows" : "non-Windows"),
	                           "DOCUMENT_ROOT"      => ShowServerVar('DOCUMENT_ROOT'),
	                           "SCRIPT_FILENAME"    => ShowServerVar('SCRIPT_FILENAME'),
	                           "PATH_TRANSLATED"    => ShowServerVar('PATH_TRANSLATED'),
	                           "REAL_DOCUMENT_ROOT" => CheckString($REAL_DOCUMENT_ROOT),
	                     ));

	if (Settings::get('DEF_ALERT') == "") {
		echo "<p>" . GetMessage(MSG_NO_DEF_ALERT) . "</p>";
	} elseif (SendAlert($sAlert,false,true)) {
		echo "<p>" . GetMessage(MSG_TEST_SENT) . "</p>";
	} else {
		echo "<p>" . GetMessage(MSG_TEST_FAILED) . "</p>";
	}
	exit;
}

if (isset($aGetVars["testlang"]) && $aGetVars["testlang"] == 1) {

	function ShowMessages()
	{
		global $aMessages,$sLangID,$aGetVars,$sHTMLCharSet;

		if (isset($aGetVars["mnums"]) && $aGetVars["mnums"] == "no") {
			Settings::set('bShowMesgNumbers',false);
		} else {
			Settings::set('bShowMesgNumbers',true);
		}
		LoadBuiltinLanguage();

		$s_def_lang  = $sLangID;
		$a_def_mesgs = $aMessages;

		LoadLanguageFile();

		$s_active_lang  = $sLangID;
		$a_active_mesgs = $aMessages;

		$a_list = get_defined_constants();

		echo "<html>\n";
		echo "<head>\n";
		if (isset($sHTMLCharSet) && $sHTMLCharSet !== "") {
			echo "<META HTTP-EQUIV=\"Content-Type\" CONTENT=\"text/html; charset=$sHTMLCharSet\">\n";
		}
		echo "</head>\n";
		echo "<body>\n";

		echo "<table border=\"1\" cellpadding=\"10\" width=\"95%\">\n";
		echo "<tr>\n";
		echo "<th>\n";
		echo "Message Number";
		echo "</th>\n";
		echo "<th>\n";
		echo "$s_def_lang";
		echo "</th>\n";
		echo "<th>\n";
		echo "$s_active_lang";
		echo "</th>\n";
		echo "</tr>\n";
		foreach ($a_list as $s_name => $i_value) {
			if (substr($s_name,0,4) == "MSG_") {

				switch ($s_name) {
					case "MSG_IPC_NOWAIT":
					case "MSG_EAGAIN":
					case "MSG_ENOMSG":
					case "MSG_NOERROR":
					case "MSG_EXCEPT":
					case "MSG_OOB":
					case "MSG_PEEK":
					case "MSG_DONTROUTE":
					case "MSG_EOR":
						continue 2;
				}
				if ($i_value >= 256) {
					continue;
				}
				echo "<tr>\n";
				echo "<td valign=\"top\">\n";
				echo "$s_name ($i_value)";
				echo "</td>\n";
				echo "<td valign=\"top\">\n";
				$aMessages = $a_def_mesgs;
				$s_def_msg = GetMessage((int)$i_value,array(),true,true);
				echo nl2br(htmlentities($s_def_msg)); // English - don't need
				// FixedHTMLEntities
				echo "</td>\n";
				echo "<td valign=\"top\">\n";
				$aMessages = $a_active_mesgs;
				$s_act_msg = GetMessage((int)$i_value,array(),true,true);
				if ($s_def_msg == $s_act_msg) {
					echo "<i>identical</i>\n";
				} else {
					echo nl2br(FixedHTMLEntities($s_act_msg));
				}
				echo "</td>\n";
				echo "</tr>\n";
			}
		}
		echo "</table>\n";

		echo "</body>\n";
		echo "</html>\n";
	}

	ShowMessages();
	exit();
}

function    GetSavedFileNames($a_values)
{
	if (IsSetSession("FormSavedFiles")) {
		$a_saved_files = GetSession("FormSavedFiles");
		foreach ($a_saved_files as $s_fld => $a_upload) {
			if (isset($a_upload["name"])) {
				$a_values[$s_fld] = $a_upload["name"];
			}
			if (isset($a_upload["new_name"])) {
				$a_values["name_of_$s_fld"] = $a_upload["new_name"];
			}
		}
	}
	return ($a_values);
}

function GetMultiValues($a_form_list,$i_form_index,$a_order = array(),
                        $a_clean = array(),
                        $a_raw_data = array(),
                        $a_all_data = array(),
                        $a_file_data = array())
{
	$a_ret_clean = $a_ret_raw = $a_ret_all = $a_ret_files = array();
	for ($ii = 0 ; $ii < $i_form_index ; $ii++) {

		$a_form_order = $a_form_list[$ii]["ORDER"];
		$n_order      = count($a_form_order);
		for ($jj = 0 ; $jj < $n_order ; $jj++) {
			if (array_search($a_form_order[$jj],$a_order) === false) {
				$a_order[] = $a_form_order[$jj];
			}
		}
		$a_ret_clean = array_merge($a_ret_clean,$a_form_list[$ii]["CLEAN"]);
		$a_ret_raw   = array_merge($a_ret_raw,$a_form_list[$ii]["RAWDATA"]);
		$a_ret_all   = array_merge($a_ret_all,$a_form_list[$ii]["ALLDATA"]);
		$a_ret_files = array_merge($a_ret_files,$a_form_list[$ii]["FILES"]);
	}

	$a_ret_clean = array_merge($a_ret_clean,$a_clean);
	$a_ret_raw   = array_merge($a_ret_raw,$a_raw_data);
	$a_ret_all   = array_merge($a_ret_all,$a_all_data);
	$a_ret_files = array_merge($a_ret_files,$a_file_data);
	return (array($a_order,$a_ret_clean,$a_ret_raw,$a_ret_all,$a_ret_files));
}

$bMultiForm = false;

function MultiFormReturn($i_return_to)
{
	global $iFormIndex;
	global $SessionAccessor;

	if (!IsSetSession("FormList") ||
	    !IsSetSession("FormIndex") ||
	    $i_return_to < 0 ||
	    $i_return_to > GetSession("FormIndex")
	) {
		Error("cannot_return",GetMessage(MSG_CANNOT_RETURN,
		                                 array("TO"       => $i_return_to,
		                                       "TOPINDEX" => (
		                                       IsSetSession("FormIndex") ?
			                                       GetSession("FormIndex") :
			                                       "<undefined>")
		                                 )),
		      false,false);
	}
	$a_list = GetSession("FormList");
	assert($i_return_to < count($a_list));
	$a_form_def = $a_list[$i_return_to];
	SetSession("FormList",$a_list = array_slice($a_list,0,$i_return_to + 1));
	SetSession("FormIndex",$iFormIndex = $i_return_to);
	if (isset($a_form_def["FORM"])) {

		list(,,$a_values,,) = GetMultiValues($a_list,$i_return_to);

		$SessionAccessor->CopyIn($a_values,true);
		$a_lines = array();

		if (ProcessMultiFormTemplate($a_form_def["FORM"],$a_values,$a_lines)) {
			$n_lines = count($a_lines);
			$s_buf   = "";
			for ($ii = 0 ; $ii < $n_lines ; $ii++) {
				$s_buf .= $a_lines[$ii] . "\n";
				unset($a_lines[$ii]); // free memory (hopefully)
			}
			unset($a_lines); // free memory (hopefully)

			echo SetPreviousValues($s_buf,$a_form_def["RAWDATA"]);
		} else {
			Error("multi_form_failed",GetMessage(MSG_MULTIFORM_FAILED,
			                                     array("NAME" => $a_form_def["FORM"])),false,false);
		}
	} else

	{
		ProcessReturnToForm($a_form_def["URL"],$a_form_def["RAWDATA"],array("multi_start"));
	}

}

function MultiKeep()
{
	global $SPECIAL_VALUES,$aRawDataValues;

	if (isset($SPECIAL_VALUES["multi_keep"]) &&
	    !empty($SPECIAL_VALUES["multi_keep"])
	) {
		$a_list = TrimArray(explode(",",$SPECIAL_VALUES["multi_keep"]));
		if (IsSetSession("FormKeep")) {
			$a_keep = GetSession("FormKeep");
		} else {
			$a_keep = array();
		}

		foreach ($a_list as $s_fld_name) {
			if (!empty($s_fld_name)) {
				if (isset($aRawDataValues[$s_fld_name])) {
					$a_keep[$s_fld_name] = $aRawDataValues[$s_fld_name];
				} else {
					unset($a_keep[$s_fld_name]);
				}
			}
		}
		SetSession("FormKeep",$a_keep);
	}
}

function MultiFormLogic()
{
	global $bMultiForm,$SPECIAL_VALUES,$aServerVars,$aFileVars,$ExecEnv;
	global $sFormMailScript,$bGotGoBack,$bGotNextForm,$iFormIndex;
	global $aFieldOrder,$aCleanedValues,$aRawDataValues,$aAllRawValues;

	if ($SPECIAL_VALUES["multi_start"] == 1) {
		if (empty($SPECIAL_VALUES["this_form"])) {
			ErrorWithIgnore("need_this_form",GetMessage(MSG_NEED_THIS_FORM),false,false);
		}

		$bMultiForm = true;

		$a_list     = array();
		$a_list[0]  = array("URL"     => $SPECIAL_VALUES["this_form"],
		                    "ORDER"   => $aFieldOrder,
		                    "CLEAN"   => $aCleanedValues,
		                    "RAWDATA" => $aRawDataValues,
		                    "ALLDATA" => $aAllRawValues,
		                    "FILES"   => $aFileVars
		);
		$iFormIndex = 0; // zero is the first form, which was just submitted
		SetSession("FormList",$a_list);
		SetSession("FormIndex",$iFormIndex);

		UnsetSession("FormSavedFiles");
		UnsetSession("FormKeep");
	} elseif (IsSetSession("FormList")) {
		$bMultiForm = true;
	}

	if ($bMultiForm) {
		$sFormMailScript = $ExecEnv->GetScript();
		$iFormIndex      = GetSession("FormIndex");
	}

	if ($bMultiForm && !$bGotGoBack) {

		$iFormIndex                     = GetSession("FormIndex");
		$a_list                         = GetSession("FormList");
		$a_list[$iFormIndex]["ORDER"]   = $aFieldOrder;
		$a_list[$iFormIndex]["CLEAN"]   = $aCleanedValues;
		$a_list[$iFormIndex]["RAWDATA"] = $aRawDataValues;
		$a_list[$iFormIndex]["ALLDATA"] = $aAllRawValues;
		if (count($aFileVars) > 0 && !Settings::get('FILEUPLOADS')) {
			SendAlert(GetMessage(MSG_FILE_UPLOAD));
		} elseif (count($aFileVars) > 0 && !SaveAllUploadedFiles($aFileVars)) {
			Error("upload_save_failed",GetMessage(MSG_MULTI_UPLOAD),false,false);
		}
		$a_list[$iFormIndex]["FILES"] = $aFileVars;
		$iFormIndex++;
		$s_url               = GetReturnLink($sFormMailScript,$iFormIndex);
		$a_list[$iFormIndex] = array("URL"     => $s_url,
		                             "FORM"    => $SPECIAL_VALUES["next_form"],
		                             "ORDER"   => $aFieldOrder,
		                             "CLEAN"   => $aCleanedValues,
		                             "RAWDATA" => $aRawDataValues,
		                             "ALLDATA" => $aAllRawValues,
		                             "FILES"   => $aFileVars
		);
		SetSession("FormList",$a_list);
		SetSession("FormIndex",$iFormIndex);
		MultiKeep();
	}
}

function DetectMimeAttack($a_fields,&$s_attack,&$s_info,&$s_user_info)
{

	$a_rec_flds = array("recipients","cc","bcc","replyto","subject");
	foreach ($a_rec_flds as $s_fld) {
		if (isset($a_fields[$s_fld])) {

			if (is_array($a_fields[$s_fld])) {
				$s_data = implode(",",$a_fields[$s_fld]);
			} else {
				$s_data = $a_fields[$s_fld];
			}
			$s_data = strtolower($s_data);
			if (($i_mime = strpos($s_data,"mime-version")) !== false ||
			    ($i_cont = strpos($s_data,"content-type")) !== false
			) {
				$s_attack = "MIME";
				$s_info   = GetMessage(MSG_ATTACK_MIME_INFO,
				                       array("FLD"     => $s_fld,
				                             "CONTENT" => ($i_mime !== false) ?
					                             "mime-version" :
					                             "content-type"
				                       ),false);
				return (true);
			}
		}
	}
	return (false);
}

function    AttackDetectionStripLang($s_data)
{

	foreach (Settings::get('ATTACK_DETECTION_JUNK_LANG_STRIP') as $s_seq) {
		$s_data = str_replace($s_seq," ",$s_data);
	}
	return ($s_data);
}

function    AttackDetectionFindJunk($s_data,$s_alpha,$n_consec,&$a_matches)
{
	$s_pat = "/[" . preg_quote($s_alpha,"/") . "]{" . "$n_consec,$n_consec" . "}/";
	if (($n_count = preg_match_all($s_pat,$s_data,$a_matches)) === false) {
		$n_count   = 0;
		$a_matches = array();
	} else {
		$a_matches = $a_matches[0];
	}
	return ($n_count);
}

function DetectJunkAttack($a_fields,&$s_attack,&$s_info,&$s_user_info)
{

	$n_count        = 0;
	$a_matches      = array();
	$a_user_matches = array();
	foreach ($a_fields as $s_fld => $m_value) {
		if (IsSpecialField($s_fld) || IsSpecialMultiField($s_fld)) {

			$b_skip = true;
			switch ($s_fld) {
				case "realname":
				case "subject":
					$b_skip = false;
					break;
			}
			if ($b_skip) {
				continue;
			}
		}

		if (in_array($s_fld,Settings::get('ATTACK_DETECTION_JUNK_IGNORE_FIELDS'),true)) {
			continue;
		}
		if (isset($m_value) && !FieldManager::IsEmpty($m_value)) {
			if (!is_array($m_value)) {
				$m_value = array($m_value);
			}
			foreach ($m_value as $s_data) {
				$s_orig_data = $s_data = strtolower($s_data);

				$s_data = AttackDetectionStripLang($s_data);

				$n_match = AttackDetectionFindJunk($s_data,Settings::get('ATTACK_DETECTION_JUNK_CONSONANTS'),
				                                   Settings::get('ATTACK_DETECTION_JUNK_CONSEC_CONSONANTS'),
				                                   $a_match_cons);
				if ($n_match > 0) {
					$a_user_matches[] = $s_orig_data;
				}
				$n_count += $n_match;
				$n_match = AttackDetectionFindJunk($s_data,Settings::get('ATTACK_DETECTION_JUNK_VOWELS'),
				                                   Settings::get('ATTACK_DETECTION_JUNK_CONSEC_VOWELS'),
				                                   $a_match_vow);
				if ($n_match > 0) {
					$a_user_matches[] = $s_orig_data;
				}
				$n_count += $n_match;
				if ($n_count >= Settings::get('ATTACK_DETECTION_JUNK_TRIGGER')) {
					$a_matches   = array_merge($a_matches,$a_match_cons,$a_match_vow);
					$s_user_info = GetMessage(MSG_USER_ATTACK_JUNK,
					                          array("INPUT" => implode(", ",$a_user_matches)),false);
					$s_attack    = "JUNK";
					$s_info      = GetMessage(MSG_ATTACK_JUNK_INFO,
					                          array("FLD"  => $s_fld,
					                                "JUNK" => implode(" ",$a_matches)
					                          ),false);
					return (true);
				}
			}
		}
	}
	return (false);
}

function DetectDupAttack($a_fields,&$s_attack,&$s_info,&$s_user_info)
{

	$a_data_map = array();
	foreach (Settings::get('ATTACK_DETECTION_DUPS') as $s_fld) {
		if (isset($a_fields[$s_fld]) &&
		    is_scalar($a_fields[$s_fld]) && // can only work with string data
		    !empty($a_fields[$s_fld])
		) {
			$s_data = (string)$a_fields[$s_fld];
			if (isset($a_data_map[$s_data])) {

				$s_attack    = "Duplicate Fields";
				$s_info      = GetMessage(MSG_ATTACK_DUP_INFO,
				                          array("FLD1" => $a_data_map[$s_data],
				                                "FLD2" => $s_fld
				                          ),false);
				$s_user_info = GetMessage(MSG_USER_ATTACK_DUP,array(),false);
				return (true);
			}
			$a_data_map[$s_data] = $s_fld;
		}
	}
	return (false);
}

function DetectSpecialsAttack($a_fields,&$s_attack,&$s_info,&$s_user_info)
{

	foreach (Settings::get('ATTACK_DETECTION_SPECIALS_ONLY_EMAIL') as $s_fld) {
		if (isset($a_fields[$s_fld]) &&
		    is_scalar($a_fields[$s_fld]) && // can only work with string data
		    !empty($a_fields[$s_fld])
		) {
			$s_data = $a_fields[$s_fld];
			if (preg_match("/^\b[-a-z0-9._%]+@[-.%_a-z0-9]+\.[a-z]{2,9}\b$/i",
			               $s_data) === 1
			) {

				$s_attack = "Special Fields Only";
				$s_info   = GetMessage(MSG_ATTACK_SPEC_INFO,
				                       array("FLD" => $s_fld),false);
				return (true);
			}
		}
	}

	foreach (Settings::get('ATTACK_DETECTION_SPECIALS_ANY_EMAIL') as $s_fld) {
		if (isset($a_fields[$s_fld]) &&
		    is_scalar($a_fields[$s_fld]) && // can only work with string data
		    !empty($a_fields[$s_fld])
		) {
			$s_data = $a_fields[$s_fld];
			if (preg_match("/\b[-a-z0-9._%]+@[-.%_a-z0-9]+\.[a-z]{2,9}\b/i",
			               $s_data) > 0
			) {

				$s_attack = "Special Fields Any";
				$s_info   = GetMessage(MSG_ATTACK_SPEC_INFO,
				                       array("FLD" => $s_fld),false);
				return (true);
			}
		}
	}
	return (false);
}

function DetectManyURLsAttack($a_fields,&$s_attack,&$s_info,&$s_user_info)
{

	$a_fld_names = array();

	$s_srch = '((\bhttps{0,1}:\/\/|<\s*a\s+href=["' . "'" . ']{0,1})[-a-z0-9.]+\b)';

	if (!Settings::isEmpty('ATTACK_DETECTION_URL_PATTERNS') &&
	    is_array(Settings::get('ATTACK_DETECTION_URL_PATTERNS'))
	) {
		foreach (Settings::get('ATTACK_DETECTION_URL_PATTERNS') as $s_pat) {
			if ($s_pat == "") {
				continue;
			}
			$s_srch .= "|" . str_replace('/','\/',$s_pat);
		}
	}

	foreach ($a_fields as $s_fld => $s_data) {
		if (IsSpecialField($s_fld) || IsSpecialMultiField($s_fld))

		{
			continue;
		}
		if (isset($s_data) &&
		    is_scalar($s_data) && // can only work with string data
		    !empty($s_data)
		) {
			$n_match = preg_match_all("/$s_srch/msi",$s_data,$a_matches);
			if (!is_int($n_match)) {
				$n_match = 0;
			}

			if (Settings::get('ATTACK_DETECTION_MANY_URLS') > 0) {
				if ($n_match >= Settings::get('ATTACK_DETECTION_MANY_URLS')) {
					$s_attack    = "Many URLS in a field";
					$s_info      = GetMessage(MSG_ATTACK_MANYURL_INFO,
					                          array("FLD" => $s_fld,"NUM" => ($n_match)),false);
					$s_user_info = GetMessage(MSG_USER_ATTACK_MANY_URLS,array(),false);
					return (true);
				}
			}
			if ($n_match > 0) {
				$a_fld_names[] = $s_fld;
			}
		}
	}
	if (Settings::get('ATTACK_DETECTION_MANY_URL_FIELDS') > 0) {
		if (count($a_fld_names) >= Settings::get('ATTACK_DETECTION_MANY_URL_FIELDS')) {
			$s_attack    = "Many fields with URLs";
			$s_info      = GetMessage(MSG_ATTACK_MANYFIELDS_INFO,
			                          array("FLDS" => implode(",",$a_fld_names),
			                                "NUM"  => (count($a_fld_names))
			                          ),false);
			$s_user_info = GetMessage(MSG_USER_ATTACK_MANY_URL_FIELDS,array(),false);
			return (true);
		}
	}
	return (false);
}

function    IsAjax()
{
	global $SPECIAL_VALUES,$aFormVars,$aGetVars;
	if ($SPECIAL_VALUES["fmmode"] == "ajax") {
		return (true);
	}
	if (isset($aFormVars["fmmode"])) {
		return ($aFormVars["fmmode"] == "ajax");
	}
	if (isset($aGetVars["fmmode"])) {
		return ($aGetVars["fmmode"] == "ajax");
	}
	return (false);
}
function DetectAttacks($a_fields)
{

	$s_info      = $s_attack = "";
	$b_attacked  = false;
	$s_user_info = "";
	if (Settings::get('ATTACK_DETECTION_MIME')) {
		if (DetectMimeAttack($a_fields,$s_attack,$s_info,$s_user_info)) {
			$b_attacked = true;
		}
	}
	if (!$b_attacked && !Settings::isEmpty('ATTACK_DETECTION_DUPS')) {
		if (DetectDupAttack($a_fields,$s_attack,$s_info,$s_user_info)) {
			$b_attacked = true;
		}
	}
	if (!$b_attacked && Settings::get('ATTACK_DETECTION_SPECIALS')) {
		if (DetectSpecialsAttack($a_fields,$s_attack,$s_info,$s_user_info)) {
			$b_attacked = true;
		}
	}
	if (!$b_attacked && (Settings::get('ATTACK_DETECTION_MANY_URLS') ||
	                     Settings::get('ATTACK_DETECTION_MANY_URL_FIELDS'))
	) {
		if (DetectManyURLsAttack($a_fields,$s_attack,$s_info,$s_user_info)) {
			$b_attacked = true;
		}
	}
	if (Settings::get('ATTACK_DETECTION_JUNK')) {
		if (DetectJunkAttack($a_fields,$s_attack,$s_info,$s_user_info)) {
			$b_attacked = true;
		}
	}

	if (!$b_attacked && !Settings::isEmpty('ATTACK_DETECTION_REVERSE_CAPTCHA')) {
		if (DetectRevCaptchaAttack(Settings::get('ATTACK_DETECTION_REVERSE_CAPTCHA'),$a_fields,$s_attack,$s_info,
		                           $s_user_info)
		) {
			$b_attacked = true;
		}
	}

	if ($b_attacked) {
		if (function_exists('FMHookAttacked')) {
			FMHookAttacked('');
		}
		if (Settings::get('ALERT_ON_ATTACK_DETECTION')) {
			SendAlert(GetMessage(MSG_ATTACK_DETECTED,
			                     array("ATTACK" => $s_attack,
			                           "INFO"   => $s_info,
			                     )),
			          false);
		}
		if (!IsAjax() && Settings::get('ATTACK_DETECTION_URL') !== "") {
			Redirect(Settings::get('ATTACK_DETECTION_URL'),GetMessage(MSG_FORM_ERROR));
		} else {
			global $SERVER;

			CreatePage(GetMessage(MSG_ATTACK_PAGE,array("SERVER" => $SERVER,"USERINFO" => $s_user_info)),
			           GetMessage(MSG_FORM_ERROR));
		}
		exit;
	}
}
function    DetectRevCaptchaAttack($a_revcap_spec,$a_form_data,&$s_attack,&$s_info,&$s_user_info)
{
	global $bReverseCaptchaCompleted;

	if (count($a_revcap_spec) < 2) {
		SendAlert(GetMessage(MSG_REV_CAP));
		return (false);
	}
	$n_empty    = $n_non_empty = 0;
	$b_attacked = false;
	$s_info     = "";
	foreach ($a_revcap_spec as $s_fld_name => $s_value) {
		if ($s_value === "") {
			$n_empty++;
			if (isset($a_form_data[$s_fld_name]) &&
			    $a_form_data[$s_fld_name] !== ""
			) {
				$b_attacked = true;
				$s_info .= "\n" . GetMessage(MSG_ATTACK_REV_CAP_INFO,
				                             array("FLD"     => $s_fld_name,
				                                   "CONTENT" => $a_form_data[$s_fld_name]
				                             ),false);
			}
		} else {
			$n_non_empty++;
			if (!isset($a_form_data[$s_fld_name]) ||
			    $a_form_data[$s_fld_name] !== $s_value
			) {
				$b_attacked = true;
				$s_info .= "\n" . GetMessage(MSG_ATTACK_REV_CAP_INFO,
				                             array("FLD"     => $s_fld_name,
				                                   "CONTENT" =>
					                                   isset($a_form_data[$s_fld_name]) ?
						                                   $a_form_data[$s_fld_name] :
						                                   ""
				                             ),false);
			}
		}
	}
	if ($n_empty + $n_non_empty < 2 ||
	    $n_empty == 0 || $n_non_empty == 0
	) {
		SendAlert(GetMessage(MSG_REV_CAP));
		return (false);
	}
	if ($b_attacked) {
		$s_attack    = "Reverse Captcha";
		$s_user_info = GetMessage(MSG_USER_ATTACK_REV_CAP,array(),false);
	}
	$bReverseCaptchaCompleted = !$b_attacked;
	return ($b_attacked);
}

function    CheckCaptchaSubmit()
{
	global $SPECIAL_VALUES,$reCaptchaProcessor;

	if ($SPECIAL_VALUES["imgverify"] !== "") {

		if (isset($reCaptchaProcessor)) {
			$s_error = '';
			if (!$reCaptchaProcessor->Check($SPECIAL_VALUES["imgverify"],$SPECIAL_VALUES,$s_error)) {
				$s_error_mesg = GetMessage(MSG_RECAPTCHA_MATCH,array("ERR" => $s_error));
				UserError("recaptcha",$s_error_mesg,array(),array('imgverify' => $s_error_mesg));
			}
		}

		else {

			if (!IsSetSession("VerifyImgString") &&
			    !IsSetSession("turing_string")
			) {
				ErrorWithIgnore("verify_failed",GetMessage(MSG_VERIFY_MISSING),false);
			}

			if (IsSetSession("VerifyImgString")) {
				if (strtoupper(str_replace(" ","",$SPECIAL_VALUES["imgverify"])) !==
				    strtoupper(GetSession("VerifyImgString"))
				) {
					$s_error_mesg = GetMessage(MSG_VERIFY_MATCH);
					UserError("img_verify",$s_error_mesg,array(),array('imgverify' => $s_error_mesg));
				}
			} else {
				if (strtoupper(str_replace(" ","",$SPECIAL_VALUES["imgverify"])) !==
				    strtoupper(GetSession("turing_string"))
				) {
					$s_error_mesg = GetMessage(MSG_VERIFY_MATCH);
					UserError("img_verify",$s_error_mesg,array(),array('imgverify' => $s_error_mesg));
				}
			}
		}
	}
}

class   AutoResponder
{
	var $_bRequested; // true if requested by the form
	var $_sTo; // to-address for auto response
	var $_sSubject; // subject for auto response
	var $_iNone = 0; // must be zero - initializes iType and iCaptchaType
	var $_iCaptchaType; // type of CAPTCHA that's been successfully processed
	var $_bCaptchaOK; // true if CAPTCHA processing is OK, otherwise false
	var $_iFull = 1; // full captcha
	var $_iRev = 2; // reverse captcha
	var $_iType; // type of autoresponse (template or plain)
	var $_iSendTemplate = 1; // send a template
	var $_iSendPlain = 2; // send a plain file

	function    AutoResponder()
	{
		global $SPECIAL_VALUES;

		$this->_bCaptchaOK   = $this->_bRequested = false;
		$this->_sTo          = "";
		$this->_sSubject     = "";
		$this->_iType        = $this->_iNone;
		$this->_iCaptchaType = $this->_iNone;

		if (IsAROptionSet('HTMLTemplate') ||
		    IsAROptionSet('PlainTemplate')
		) {
			$this->_iType = $this->_iSendTemplate;
		}
		if (IsAROptionSet('PlainFile') ||
		    IsAROptionSet('HTMLFile')
		) {
			$this->_iType = $this->_iSendPlain;
		}
		if ($this->_iType) {

			if (!isset($SPECIAL_VALUES["email"]) || empty($SPECIAL_VALUES["email"])) {
				SendAlert(GetMessage(MSG_ARESP_EMAIL));
			} else {
				$this->_bRequested = true;
				$this->_sTo        = $SPECIAL_VALUES["email"];
				if (IsAROptionSet('Subject')) {
					$this->_sSubject = GetAROption('Subject');
				} else {
					$this->_sSubject = GetMessage(MSG_ARESP_SUBJ,array(),false);
				}
			}
		}
	}

	function    Process($b_check_only = false)
	{
		global $SPECIAL_VALUES;

		FMDebug("AutoResponder::Process: check=" . ($b_check_only ? "Y" : "N"));
		if ($this->IsRequested()) {
			FMDebug("AutoResponder::Process: requested");

			$this->_CheckCaptcha();
			if (!$b_check_only && $this->_bCaptchaOK) {
				FMDebug("AutoResponder::Process: proceeding, type=" . $this->_iType);

				if ($this->_iType == $this->_iSendTemplate) {
					if ($this->_iCaptchaType == $this->_iFull) {
						$this->_Send(true);
					}
				}

				elseif ($this->_iType == $this->_iSendPlain) {
					if ($this->_iCaptchaType) {
						$this->_Send(false);
					}
				}
			}
		}
	}

	function    _CheckCaptcha()
	{
		global $SPECIAL_VALUES,$bReverseCaptchaCompleted;
		global $reCaptchaProcessor;

		if (!$this->_iCaptchaType) {

			if (isset($reCaptchaProcessor) && $SPECIAL_VALUES["arverify"] !== "") {
				$this->_iCaptchaType = $this->_iFull;
				$s_error             = '';
				if ($reCaptchaProcessor->Check($SPECIAL_VALUES["arverify"],$SPECIAL_VALUES,$s_error)) {
					$this->_bCaptchaOK = true;
				} else {
					$this->_bCaptchaOK = false;

					WriteARLog($this->_sTo,$this->_sSubject,
					           GetMessage(MSG_LOG_RECAPTCHA,array("ERR" => $s_error),false));
					UserError("recaptcha",GetMessage(MSG_RECAPTCHA_MATCH,array("ERR" => $s_error)));
				}
			}

			elseif ($SPECIAL_VALUES["arverify"] !== "") {

				$s_arverify          = str_replace(" ","",$SPECIAL_VALUES["arverify"]);
				$this->_iCaptchaType = $this->_iFull;

				if (IsSetSession("VerifyImgString") || IsSetSession("turing_string")) {
					$b_match = false;

					if (IsSetSession("VerifyImgString")) {
						if (strtoupper($s_arverify) === strtoupper(GetSession("VerifyImgString"))) {
							$b_match = true;
						}
					} else {
						if (strtoupper($s_arverify) === strtoupper(GetSession("turing_string"))) {
							$b_match = true;
						}
					}
					if ($b_match) {
						$this->_bCaptchaOK = true;
					} else {
						WriteARLog($this->_sTo,$this->_sSubject,
						           GetMessage(MSG_LOG_NO_MATCH,array(),false));
						UserError("ar_verify",GetMessage(MSG_ARESP_NO_MATCH));
					}
				} else {

					WriteARLog($this->_sTo,$this->_sSubject,
					           GetMessage(MSG_LOG_NO_VERIMG,array(),false));
					ErrorWithIgnore("verify_failed",GetMessage(MSG_ARESP_NO_AUTH),true);
				}
			} elseif (Settings::get('ENABLE_ATTACK_DETECTION') &&
			          !Settings::isEmpty('ATTACK_DETECTION_REVERSE_CAPTCHA')
			) {

				$this->_iCaptchaType = $this->_iRev;
				$this->_bCaptchaOK   = $bReverseCaptchaCompleted;
			}
		}
	}

	function    _Send($b_use_template)
	{
		global $SPECIAL_VALUES;

		global $aFieldOrder,$aCleanedValues,$aRawDataValues,$aAllRawValues,$aFileVars;

		FMDebug("Sending auto response: " . ($b_use_template ? "template" : "plain"));

		if (!Settings::isEmpty('HOOK_DIR')) {
			if (!@include(Settings::get('HOOK_DIR') . "/fmhookprearesp.inc.php")) {
				@include(Settings::get('HOOK_DIR') . "/fmhookprearesp.inc");
			}
		}
		if (!$this->_SendEmail($this->_sTo,$this->_sSubject,$aRawDataValues,$b_use_template)) {
			WriteARLog($this->_sTo,$this->_sSubject,
			           GetMessage(MSG_LOG_FAILED,array(),false));
			SendAlert(GetMessage(MSG_ARESP_FAILED));
		} else {
			WriteARLog($this->_sTo,$this->_sSubject,
			           GetMessage(MSG_LOG_OK,array(),false));

			if (!Settings::isEmpty('HOOK_DIR')) {
				if (!@include(Settings::get('HOOK_DIR') . "/fmhookpostaresp.inc.php")) {
					@include(Settings::get('HOOK_DIR') . "/fmhookpostaresp.inc");
				}
			}
		}
	}

	function _SendEmail($s_to,$s_subj,$a_values,$b_use_template)
	{
		global $SPECIAL_VALUES;

		$a_headers   = array();
		$s_mail_text = "";
		$s_from_addr = GetAROption("FromAddr");

		if (!isset($s_from_addr)) {
			$s_from_addr = "";
			if (!Settings::isEmpty('FROM_USER')) {
				if (Settings::get('FROM_USER') != "NONE") {
					$s_from_addr = Settings::get('FROM_USER');
				}
			} else {
				global $SERVER;

				$s_from_addr = "FormMail@" . $SERVER;
			}
		} else {
			$s_from_addr = UnMangle($s_from_addr);
		}

		if (!empty($s_from_addr)) {
			$a_headers['From'] = SafeHeader($s_from_addr);
		}

		$s_type = "";
		if ($b_use_template) {
			if (IsAROptionSet('PlainTemplate')) {
				$s_type .= "PlainTemplate ";
				$s_template = GetAROption("PlainTemplate");
				if (!ProcessTemplate($s_template,$a_lines,$a_values,
				                     GetAROption('TemplateMissing'),
				                     'SubstituteValuePlain')
				) {
					return (false);
				}
				FMDebug("AutoRespond: PlainTemplate " . count($a_lines) . " lines");
				$s_mail_text = implode(Settings::get('BODY_LF'),$a_lines);
			}
			if (IsAROptionSet("HTMLTemplate")) {
				$s_type .= "HTMLTemplate ";
				if (!MakeMimeMail($s_mail_text,$a_headers,$a_values,
				                  GetAROption("HTMLTemplate"),
				                  GetAROption('TemplateMissing'))
				) {
					return (false);
				}
				FMDebug("AutoRespond: HTMLTemplate " . strlen($s_mail_text) . " bytes");
			}
		} else {

			if (IsAROptionSet('PlainFile')) {
				$s_type .= "PlainFile ";

				if (Settings::isEmpty('TEMPLATEDIR') && Settings::isEmpty('TEMPLATEURL')) {
					SendAlert(GetMessage(MSG_TEMPLATES));
					return (false);
				}
				$s_file = GetAROption("PlainFile");
				if (($a_lines = LoadTemplate($s_file,Settings::get('TEMPLATEDIR'),Settings::get('TEMPLATEURL'),
				                             true)) === false
				) {
					return (false);
				}
				$s_mail_text = implode(Settings::get('BODY_LF'),$a_lines);
				FMDebug("AutoRespond: PlainFile " . count($a_lines) . " lines");
			}
			if (IsAROptionSet("HTMLFile")) {
				$s_type .= "HTMLFile ";
				if (!MakeMimeMail($s_mail_text,$a_headers,$a_values,
				                  GetAROption("HTMLFile"),"",
				                  false,"",array(),array(),false)
				) {
					return (false);
				}
				FMDebug("AutoRespond: HTMLTemplate " . strlen($s_mail_text) . " bytes");
			}
		}
		if (strlen($s_mail_text) == 0) {
			SendAlert(GetMessage(MSG_ARESP_EMPTY),array("TYPE" => $s_type));
		}
		FMDebug("AutoRespond: message is " . strlen($s_mail_text) . " bytes");
		return (SendCheckedMail($s_to,$s_subj,$s_mail_text,$s_from_addr,$a_headers));
	}
}

class   SessionAccess
{
	var $_aAccessList;

	function    SessionAccess($a_access_list)
	{
		$this->_aAccessList = $a_access_list;
	}

	function    CopyIn(&$a_vars,$b_overwrite_empty)
	{
		$n_copied = 0;
		foreach ($this->_aAccessList as $s_var_name) {
			if (IsSetSession($s_var_name)) {
				if (!isset($a_vars[$s_var_name]) ||
				    ($b_overwrite_empty &&
				     FieldManager::IsEmpty($a_vars[$s_var_name]))
				) {
					$a_vars[$s_var_name] = GetSession($s_var_name);
					$n_copied++;
				}
			}
		};

		return ($n_copied);
	}

	function    CopyOut(&$a_vars,$a_fields = array())
	{

		$n_copied = 0;
		foreach ($this->_aAccessList as $s_var_name) {
			if (isset($a_vars[$s_var_name])) {
				if (empty($a_fields) || in_array($s_var_name,$a_fields)) {
					SetSession($s_var_name,$a_vars[$s_var_name]);

					$n_copied++;
				}
			}
		};

		return ($n_copied);
	}
}

$SessionAccessor = new SessionAccess(Settings::get('SESSION_ACCESS'));

$bAdvTemplates = false;
if (Settings::get('ADVANCED_TEMPLATES') &&
    (!Settings::isEmpty('TEMPLATEDIR') || !Settings::isEmpty('TEMPLATEURL') ||
     !Settings::isEmpty('MULTIFORMDIR') || !Settings::isEmpty('MULTIFORMURL'))
) {
	$bAdvTemplates = true;
}

if (isset($aGetVars["return"]) && is_numeric($aGetVars["return"])) {

	if ($bAdvTemplates) {
		$FMCTEMPLATE_PROC = true;
		if (!include_once("$MODULEDIR/$FMCOMPUTE")) {
			Error("load_fmcompute",GetMessage(MSG_LOAD_FMCOMPUTE,
			                                  array("FILE"  => "$MODULEDIR/$FMCOMPUTE",
			                                        "ERROR" => $php_errormsg
			                                  )),false,false);
		}
	}
	MultiFormReturn($aGetVars["return"]);

	if (!Settings::isEmpty('HOOK_DIR')) {
		if (!@include(Settings::get('HOOK_DIR') . "/fmhookpostreturnform.inc.php")) {
			@include(Settings::get('HOOK_DIR') . "/fmhookpostreturnform.inc");
		}
	}
	exit;
}

if (!Settings::isEmpty('HOOK_DIR')) {
	if (!@include(Settings::get('HOOK_DIR') . "/fmhookpostinit.inc.php")) {
		@include(Settings::get('HOOK_DIR') . "/fmhookpostinit.inc");
	}
}

CheckConfig();

$aStrippedFormVars = $aAllRawValues = StripGPCArray($aFormVars);

if (Settings::get('ENABLE_ATTACK_DETECTION')) {
	DetectAttacks($aAllRawValues);
}

$SessionAccessor->CopyIn($aAllRawValues,true);
$SessionAccessor->CopyIn($aStrippedFormVars,true);

ProcessMailOptions($aAllRawValues);
ProcessCRMOptions($aAllRawValues);
ProcessAROptions($aAllRawValues);
ProcessFilterOptions($aAllRawValues);

$aAllRawValues = CreateDerived($aAllRawValues);

list($aFieldOrder,$aCleanedValues,$aRawDataValues) = ParseInput($aAllRawValues);

FilterFiles($aFileVars);

if (IsSetSession("FormList") && $SPECIAL_VALUES["multi_start"] != 1) {
	list($aFieldOrder,$aCleanedValues,
		$aRawDataValues,$aAllRawValues,$aFileVars) = GetMultiValues(
		GetSession("FormList"),
		GetSession("FormIndex"),
		$aFieldOrder,$aCleanedValues,
		$aRawDataValues,$aAllRawValues,
		$aFileVars);
}

if ($SPECIAL_VALUES["file_names"] !== "") {
	list($aFieldOrder,$aCleanedValues,$aRawDataValues,$aAllRawValues,$aFileVars) =
		SetFileNames($SPECIAL_VALUES["file_names"],$aFieldOrder,
		             $aCleanedValues,$aRawDataValues,$aAllRawValues,$aFileVars);
}

if (!Settings::isEmpty('HOOK_DIR')) {
	if (!@include(Settings::get('HOOK_DIR') . "/fmhookload.inc.php")) {
		@include(Settings::get('HOOK_DIR') . "/fmhookload.inc");
	}
}

if (Settings::get('FORM_INI_FILE') !== "") {
	ProcessFormIniFile(Settings::get('FORM_INI_FILE'));

	if (!Settings::isEmpty('HOOK_DIR')) {
		if (!@include(Settings::get('HOOK_DIR') . "/fmhookinifile.inc.php")) {
			@include(Settings::get('HOOK_DIR') . "/fmhookinifile.inc");
		}
	}
}

$bDoneSomething = false;
if (Settings::get('DB_SEE_INPUT')) {

	CreatePage(implode("\n",$FORMATTED_INPUT),"Debug Output - Fields Submitted");
	ZapSession();
	exit;
}

if (!empty($SPECIAL_VALUES["fmcompute"]) || $bAdvTemplates) {
	$FM_UserErrors = array();

	function FM_CallFunction($s_func,$a_params,&$m_return,
	                         &$s_mesg,&$a_debug,&$a_alerts)
	{
		switch ($s_func) {
			case "FMFatalError":
				SendComputeAlerts();
				if (count($a_params) < 3) {
					Error("fmcompute_call",GetMessage(MSG_CALL_PARAM_COUNT,
					                                  array("FUNC"  => $s_func,
					                                        "COUNT" => count($a_params)
					                                  )),
					      false,false);
				} else {
					Error("fmcompute_error",$a_params[0],$a_params[1],$a_params[2]);
				}
				break;

			case "FMFatalUserError":
				SendComputeAlerts();
				if (count($a_params) < 1) {
					Error("fmcompute_call",GetMessage(MSG_CALL_PARAM_COUNT,
					                                  array("FUNC"  => $s_func,
					                                        "COUNT" => count($a_params)
					                                  )),
					      false,false);
				} else {
					UserError("fmcompute_usererror",$a_params[0]);
				}
				break;

			case "FMUserError":
				if (count($a_params) < 1) {
					SendComputeAlerts();
					Error("fmcompute_call",GetMessage(MSG_CALL_PARAM_COUNT,
					                                  array("FUNC"  => $s_func,
					                                        "COUNT" => count($a_params)
					                                  )),
					      false,false);
				} else {
					global $FM_UserErrors;

					$FM_UserErrors[] = $a_params[0];
				}
				break;

			case "FMSaveAllFilesToRepository":
				if (count($a_params) != 0) {
					SendComputeAlerts();
					Error("fmcompute_call",GetMessage(MSG_CALL_PARAM_COUNT,
					                                  array("FUNC"  => $s_func,
					                                        "COUNT" => count($a_params)
					                                  )),
					      false,false);
				} else {
					$m_return = SaveAllFilesToRepository();
				}
				break;

			case "FMDeleteFileFromRepository":
				if (count($a_params) != 1) {
					SendComputeAlerts();
					Error("fmcompute_call",GetMessage(MSG_CALL_PARAM_COUNT,
					                                  array("FUNC"  => $s_func,
					                                        "COUNT" => count($a_params)
					                                  )),
					      false,false);
				} else {
					$m_return = DeleteFileFromRepository($a_params[0]);
				}
				break;

			case "FMNextNum":
				if (count($a_params) != 2) {
					SendComputeAlerts();
					Error("fmcompute_call",GetMessage(MSG_CALL_PARAM_COUNT,
					                                  array("FUNC"  => $s_func,
					                                        "COUNT" => count($a_params)
					                                  )),
					      false,false);
				} else {
					$i_pad  = $a_params[0];
					$i_base = $a_params[1];
					if ($i_base < 2 || $i_base > 36) {
						Error("fmcompute_call",GetMessage(MSG_CALL_INVALID_PARAM,
						                                  array("FUNC"    => $s_func,
						                                        "PARAM"   => 2,
						                                        "CORRECT" => 2. . .36
						                                  )),
						      false,false);
					}
					$m_return = GetNextNum($i_pad,$i_base);
				}
				break;

			default:
				$s_mesg = GetMessage(MSG_CALL_UNK_FUNC,array("FUNC" => $s_func));
				return (false);
		}
		return (true);
	}

	function RegisterFormMailFunctions(&$fmc)
	{

		if (($s_msg = $fmc->RegisterExternalFunction("PHP","void",
		                                             "FMFatalError",
		                                             array("string","bool","bool"),
		                                             "FM_CallFunction")) !== true
		) {
			Error("fmcompute_reg",GetMessage(MSG_REG_FMCOMPUTE,
			                                 array("FUNC"  => "FMFatalError",
			                                       "ERROR" => $s_msg
			                                 )),false,false);
		}

		if (($s_msg = $fmc->RegisterExternalFunction("PHP","void",
		                                             "FMFatalUserError",
		                                             array("string"),
		                                             "FM_CallFunction")) !== true
		) {
			Error("fmcompute_reg",GetMessage(MSG_REG_FMCOMPUTE,
			                                 array("FUNC"  => "FMFatalUserError",
			                                       "ERROR" => $s_msg
			                                 )),false,false);
		}

		if (($s_msg = $fmc->RegisterExternalFunction("PHP","void",
		                                             "FMUserError",
		                                             array("string"),
		                                             "FM_CallFunction")) !== true
		) {
			Error("fmcompute_reg",GetMessage(MSG_REG_FMCOMPUTE,
			                                 array("FUNC"  => "FMUserError",
			                                       "ERROR" => $s_msg
			                                 )),false,false);
		}

		if (($s_msg = $fmc->RegisterExternalFunction("PHP","bool",
		                                             "FMSaveAllFilesToRepository",
		                                             array(),
		                                             "FM_CallFunction")) !== true
		) {
			Error("fmcompute_reg",GetMessage(MSG_REG_FMCOMPUTE,
			                                 array("FUNC"  => "FMSaveAllFilesToRepository",
			                                       "ERROR" => $s_msg
			                                 )),false,false);
		}

		if (($s_msg = $fmc->RegisterExternalFunction("PHP","bool",
		                                             "FMDeleteFileFromRepository",
		                                             array("string"),
		                                             "FM_CallFunction")) !== true
		) {
			Error("fmcompute_reg",GetMessage(MSG_REG_FMCOMPUTE,
			                                 array("FUNC"  => "FMDeleteFileFromRepository",
			                                       "ERROR" => $s_msg
			                                 )),false,false);
		}

		if (($s_msg = $fmc->RegisterExternalFunction("PHP","string",
		                                             "FMNextNum",
		                                             array("int","int"),
		                                             "FM_CallFunction")) !== true
		) {
			Error("fmcompute_reg",GetMessage(MSG_REG_FMCOMPUTE,
			                                 array("FUNC"  => "FMNextNum",
			                                       "ERROR" => $s_msg
			                                 )),false,false);
		}
	}

	if (!empty($SPECIAL_VALUES["fmcompute"])) {
		$FMCOMPUTE_CLASS   = true;
		$FMCOMPUTE_NODEBUG = true;
	}
	if ($bAdvTemplates) {
		$FMCTEMPLATE_PROC = true;
	}
	if (!include_once("$MODULEDIR/$FMCOMPUTE")) {
		Error("load_fmcompute",GetMessage(MSG_LOAD_FMCOMPUTE,
		                                  array("FILE"  => "$MODULEDIR/$FMCOMPUTE",
		                                        "ERROR" => $php_errormsg
		                                  )),false,false);
	}
	if (!empty($SPECIAL_VALUES["fmcompute"])) {
		RegisterFormMailFunctions($FMCalc);

		if (!Settings::isEmpty('GEOIP_LIC')) {
			$FMMODULE_LOAD = true; 
			if (!include_once("$MODULEDIR/$FMGEOIP")) {
				Error("load_module",GetMessage(MSG_LOAD_MODULE,
				                               array("FILE"  => "$MODULEDIR/$FMGEOIP",
				                                     "ERROR" => $php_errormsg
				                               )),false,false);
			}

			$GeoIP = new FMGeoIP(Settings::get('GEOIP_LIC'));
			if (!$GeoIP->RegisterModule($FMCalc)) {
				Error("reg_module",GetMessage(MSG_REGISTER_MODULE,
				                              array("NAME"  => "FMGeoIP",
				                                    "ERROR" => $GeoIP->GetError()
				                              )),false,false);
			}
		}
	}
}

if (isset($SPECIAL_VALUES["multi_go_back"]) && !empty($SPECIAL_VALUES["multi_go_back"])) {
	if (!IsSetSession("FormList") || GetSession("FormIndex") == 0) {
		ErrorWithIgnore("go_back",GetMessage(MSG_GO_BACK),false,false);
	}
	MultiKeep(); 
	if (isset($SPECIAL_VALUES["multi_keep"]) && !empty($SPECIAL_VALUES["multi_keep"])) {
		$SessionAccessor->CopyOut($aAllRawValues,$SPECIAL_VALUES["multi_keep"]);
	}
	MultiFormReturn(GetSession("FormIndex") - 1);

	if (!Settings::isEmpty('HOOK_DIR')) {
		if (!@include(Settings::get('HOOK_DIR') . "/fmhookpostreturnform.inc.php")) {
			@include(Settings::get('HOOK_DIR') . "/fmhookpostreturnform.inc");
		}
	}
	exit;
}

if ($bIsGetMethod && count($aFormVars) == 0) {
	if (!Settings::get('ALLOW_GET_METHOD') && $bHasGetData) {
		CreatePage(GetMessage(MSG_GET_DISALLOWED),GetMessage(MSG_FORM_ERROR));
	} else {
		CreatePage(GetMessage(MSG_NO_DATA_PAGE),GetMessage(MSG_FORM_ERROR));
	}
	ZapSession();
	exit;
}

if (!Settings::isEmpty('HOOK_DIR')) {
	if (!@include(Settings::get('HOOK_DIR') . "/fmhookprechecks.inc.php")) {
		@include(Settings::get('HOOK_DIR') . "/fmhookprechecks.inc");
	}
}

if (!CheckRequired($SPECIAL_VALUES["required"],$aAllRawValues,$sMissing,$aMissingList)) {
	UserError("missing_fields",GetMessage(MSG_REQD_ERROR),$sMissing,$aMissingList);
}

if (!CheckConditions($SPECIAL_VALUES["conditions"],$aAllRawValues,$sMissing,$aMissingList)) {
	UserError("failed_conditions",GetMessage(MSG_COND_ERROR),$sMissing,$aMissingList);
}

CheckCaptchaSubmit();

if (!Settings::isEmpty('HOOK_DIR')) {
	if (!@include(Settings::get('HOOK_DIR') . "/fmhookchecks.inc.php")) {
		@include(Settings::get('HOOK_DIR') . "/fmhookchecks.inc");
	}
}

if (!empty($SPECIAL_VALUES["fmmodules"])) {
	$aModuleList   = TrimArray(explode(",",$SPECIAL_VALUES["fmmodules"]));
	$FMMODULE_LOAD = true;
	foreach ($aModuleList as $sModule) {
		if (!include_once("$MODULEDIR/$sModule")) {
			Error("load_module",GetMessage(MSG_LOAD_MODULE,
			                               array("FILE"  => "$MODULEDIR/$sModule",
			                                     "ERROR" => $php_errormsg
			                               )),false,false);
		}
	}
}

if (!empty($SPECIAL_VALUES["fmcompute"])) {

	function AddLineNumbersCallback($a_matches)
	{
		global $iAddLineNumbersCounter;

		return (sprintf("%d:",++$iAddLineNumbersCounter) . $a_matches[0]);
	}

	function AddLineNumbers($s_code)
	{
		global $iAddLineNumbersCounter;

		$iAddLineNumbersCounter = 0;
		return (preg_replace_callback('/^/m','AddLineNumbersCallback',$s_code));
	}

	function Load($s_code)
	{
		global $FMCalc;

		$a_mesgs = array();

		if ($FMCalc->Parse($s_code,$a_mesgs) === false) {
			$s_msgs = "";
			foreach ($a_mesgs as $a_msg) {
				$s_msgs .= "Line " . $a_msg["LINE"];
				$s_msgs .= ", position " . $a_msg["CHAR"] . ": ";
				$s_msgs .= $a_msg["MSG"] . "\n";
			}
			Error("fmcompute_parse",GetMessage(MSG_COMP_PARSE,
			                                   array("CODE"   => AddLineNumbers($s_code),
			                                         "ERRORS" => $s_msgs
			                                   )),false,false);
		}
	}

	function SendComputeAlerts()
	{
		global $FMCalc;

		$a_alerts = $FMCalc->GetAlerts();
		if (count($a_alerts) > 0) {
			SendAlert(GetMessage(MSG_COMP_ALERT,
			                     array("ALERTS" => implode("\n",StripHTML($a_alerts)))));
		}
		$a_debug = $FMCalc->GetDebug();
		if (count($a_debug) > 0) {
			SendAlert(GetMessage(MSG_COMP_DEBUG,
			                     array("DEBUG" => implode("\n",StripHTML($a_debug)))));
		}
	}

	function Compute(&$a_field_order,&$a_cleaned_values,&$a_raw_data_values,
	                 &$a_values)
	{
		global $FMCalc,$FM_UserErrors;

		$a_mesgs       = array();
		$FM_UserErrors = array();
		if (($a_flds = $FMCalc->Execute($a_mesgs)) !== false) {
			SendComputeAlerts();
			foreach ($a_flds as $s_name => $s_value) {
				$a_values[$s_name] = $s_value;
				ProcessField($s_name,$s_value,$a_field_order,
				             $a_cleaned_values,$a_raw_data_values);
			}
			if (count($FM_UserErrors) > 0) {
				UserError("fmcompute_usererrors",GetMessage(MSG_USER_ERRORS),
				          "",$FM_UserErrors);
			}
		} else {
			SendComputeAlerts();
			Error("fmcompute_exec",GetMessage(MSG_COMP_EXEC,
			                                  array("ERRORS" => implode("\n",$a_mesgs))),false,false);
		}
	}

	function RegisterData($a_form_data,$a_file_vars)
	{
		global $FMCalc;

		foreach ($a_form_data as $s_name => $s_value) {
			if (isset($s_name) && isset($s_value)) {
				if (($s_msg = $FMCalc->RegisterExternalData("PHP","string",
				                                            $s_name,"c",$s_value)) !== true
				) {
					Error("fmcompute_regdata",GetMessage(MSG_COMP_REG_DATA,
					                                     array("NAME" => $s_name,"ERROR" => $s_msg)),false,false);
				}
			}
		}

		foreach ($a_file_vars as $s_fld_name => $a_file_spec) {
			if (IsUploadedFile($a_file_spec)) {
				if (isset($a_file_spec["new_name"])) {

					$FMCalc->RegisterExternalData("PHP","string",
					                              "name_of_" . $s_fld_name,"c",$a_file_spec["new_name"]);
				}
				$s_value = $a_file_spec["name"];
			} else {
				$s_value = "";
			}
			if (($s_msg = $FMCalc->RegisterExternalData("PHP","string",
			                                            $s_fld_name,"c",$s_value)) !== true
			) {
				Error("fmcompute_regdata",GetMessage(MSG_COMP_REG_DATA,
				                                     array("NAME" => $s_fld_name,"ERROR" => $s_msg)),false,false);
			}
		}
	}


	function    MergeFileArrays($a_new_files,$a_saved_files)
	{
		if (isset($a_saved_files)) {
			foreach ($a_saved_files as $s_key => $a_def) {
				if (isset($a_new_files[$s_key])) {
					if (!IsUploadedFile($a_new_files[$s_key])) {
						$a_new_files[$s_key] = $a_def;
					}
				} else {
					$a_new_files[$s_key] = $a_def;
				}
			}
		}
		return ($a_new_files);
	}

	RegisterData($aAllRawValues,MergeFileArrays($aFileVars,IsSetSession("FormSavedFiles") ?
		GetSession("FormSavedFiles") : array()));

	if (is_array($SPECIAL_VALUES["fmcompute"])) {
		$nCompute = count($SPECIAL_VALUES["fmcompute"]);
		for ($iCompute = 0 ; $iCompute < $nCompute ; $iCompute++) {
			Load($SPECIAL_VALUES["fmcompute"][$iCompute]);
		}
	} else {
		Load($SPECIAL_VALUES["fmcompute"]);
	}

	Compute($aFieldOrder,$aCleanedValues,$aRawDataValues,$aAllRawValues);

	if (!Settings::isEmpty('HOOK_DIR')) {
		if (!@include(Settings::get('HOOK_DIR') . "/fmhookcompute.inc.php")) {
			@include(Settings::get('HOOK_DIR') . "/fmhookcompute.inc");
		}
	}
}

$bGotGoBack = $bGotNextForm = $bGotGoodTemplate = $bGotGoodUrl = false;
if (isset($SPECIAL_VALUES["good_url"]) &&
    !empty($SPECIAL_VALUES["good_url"])
) {
	$bGotGoodUrl = true;
}

if (isset($SPECIAL_VALUES["good_template"]) &&
    !empty($SPECIAL_VALUES["good_template"])
) {
	$bGotGoodTemplate = true;
}

if (isset($SPECIAL_VALUES["next_form"]) &&
    !empty($SPECIAL_VALUES["next_form"])
) {
	$bGotNextForm = true;
}

if (isset($SPECIAL_VALUES["multi_go_back"]) &&
    !empty($SPECIAL_VALUES["multi_go_back"])
) {
	$bGotGoBack = true;
}

if ($bGotNextForm && ($bGotGoodTemplate || $bGotGoodUrl)) {
	ErrorWithIgnore("next_plus_good",GetMessage(MSG_NEXT_PLUS_GOOD,array("WHICH" =>
		                                                                     ($bGotGoodUrl ? "good_url" :
			                                                                     "good_template")
	)),false,false);
}

MultiFormLogic();

if (!Settings::isEmpty('HOOK_DIR')) {
	if (!@include(Settings::get('HOOK_DIR') . "/fmhookmulti.inc.php")) {
		@include(Settings::get('HOOK_DIR') . "/fmhookmulti.inc");
	}
}


if (!Settings::isEmpty('CSVDIR') && isset($SPECIAL_VALUES["csvfile"]) &&
    !empty($SPECIAL_VALUES["csvfile"])
) {

	if (!Settings::isEmpty('HOOK_DIR')) {
		if (!@include(Settings::get('HOOK_DIR') . "/fmhookprecsv.inc.php")) {
			@include(Settings::get('HOOK_DIR') . "/fmhookprecsv.inc");
		}
	}
	WriteCSVFile(Settings::get('CSVDIR') . "/" . basename($SPECIAL_VALUES["csvfile"]),$aAllRawValues);

	if (!Settings::isEmpty('HOOK_DIR')) {
		if (!@include(Settings::get('HOOK_DIR') . "/fmhookpostcsv.inc.php")) {
			@include(Settings::get('HOOK_DIR') . "/fmhookpostcsv.inc");
		}
	}
	$bDoneSomething = true;
}

if (!Settings::isEmpty('LOGDIR') && isset($SPECIAL_VALUES["logfile"]) && !empty($SPECIAL_VALUES["logfile"])) {

	if (!Settings::isEmpty('HOOK_DIR')) {
		if (!@include(Settings::get('HOOK_DIR') . "/fmhookprelog.inc.php")) {
			@include(Settings::get('HOOK_DIR') . "/fmhookprelog.inc");
		}
	}
	WriteLog(Settings::get('LOGDIR') . "/" . basename($SPECIAL_VALUES["logfile"]));

	if (!Settings::isEmpty('HOOK_DIR')) {
		if (!@include(Settings::get('HOOK_DIR') . "/fmhookpostlog.inc.php")) {
			@include(Settings::get('HOOK_DIR') . "/fmhookpostlog.inc");
		}
	}
	$bDoneSomething = true;
}

if (isset($SPECIAL_VALUES["crm_url"]) && isset($SPECIAL_VALUES["crm_spec"]) &&
    !empty($SPECIAL_VALUES["crm_url"]) && !empty($SPECIAL_VALUES["crm_spec"])
) {
	$sCRM = GetCRMURL($SPECIAL_VALUES["crm_spec"],$aAllRawValues,$SPECIAL_VALUES["crm_url"]);
	if (!empty($sCRM)) {
		$aCRMReturnData = array();

		if (!Settings::isEmpty('HOOK_DIR')) {
			if (!@include(Settings::get('HOOK_DIR') . "/fmhookprecrm.inc.php")) {
				@include(Settings::get('HOOK_DIR') . "/fmhookprecrm.inc");
			}
		}
		if (!SendToCRM($sCRM,$aCRMReturnData)) {

			if (IsCRMOptionSet("ErrorOnFail")) {
				Error("crm_failed",GetMessage(MSG_CRM_FAILURE,
				                              array("URL"  => $sCRM,
				                                    "DATA" => GetObjectAsString($aCRMReturnData)
				                              )),
				      false,false);
			}
		} else

		{
			$aRawDataValues = array_merge($aRawDataValues,$aCRMReturnData);
		}

		if (!Settings::isEmpty('HOOK_DIR')) {
			if (!@include(Settings::get('HOOK_DIR') . "/fmhookpostcrm.inc.php")) {
				@include(Settings::get('HOOK_DIR') . "/fmhookpostcrm.inc");
			}
		}
		$bDoneSomething = true;
	}
}

if (IsMailOptionSet("SendMailFOption")) {
	SendAlert(GetMessage(MSG_FOPTION_WARN,array("LINE" => Settings::get('SENDMAIL_F_OPTION_LINE'))),
	          false,true);
}

$AutoResp = new AutoResponder();

$AutoResp->Process(true);

if (!Settings::isEmpty('HOOK_DIR')) {
	if (!@include(Settings::get('HOOK_DIR') . "/fmhookprecomplete.inc.php")) {
		@include(Settings::get('HOOK_DIR') . "/fmhookprecomplete.inc");
	}
}

if (!isset($SPECIAL_VALUES["recipients"]) || empty($SPECIAL_VALUES["recipients"])) {

	if (!$bDoneSomething) {
		if (!$bGotGoBack && !$bGotNextForm) {
			ErrorWithIgnore("no_recipients",GetMessage(MSG_NO_ACTIONS));
		}
	}
} else {

	if (!Settings::isEmpty('HOOK_DIR')) {
		if (!@include(Settings::get('HOOK_DIR') . "/fmhookpreemail.inc.php")) {
			@include(Settings::get('HOOK_DIR') . "/fmhookpreemail.inc");
		}
	}
	$s_invalid = $s_invalid_cc = $s_invalid_bcc = "";
	if (!CheckEmailAddress($SPECIAL_VALUES["recipients"],$s_valid_recipients,$s_invalid)) {
		ErrorWithIgnore("no_valid_recipients",GetMessage(MSG_NO_RECIP));
	} else {
		$s_valid_cc = $s_valid_bcc = "";

		if (isset($SPECIAL_VALUES["cc"]) && !empty($SPECIAL_VALUES["cc"])) {
			CheckEmailAddress($SPECIAL_VALUES["cc"],$s_valid_cc,$s_invalid_cc);
		}
		if (isset($SPECIAL_VALUES["bcc"]) && !empty($SPECIAL_VALUES["bcc"])) {
			CheckEmailAddress($SPECIAL_VALUES["bcc"],$s_valid_bcc,$s_invalid_bcc);
		}

		$s_error = "";
		if (!empty($s_invalid)) {
			$s_error .= "recipients: $s_invalid\r\n";
		}
		if (!empty($s_invalid_cc)) {
			$s_error .= "cc: $s_invalid_cc\r\n";
		}
		if (!empty($s_invalid_bcc)) {
			$s_error .= "bcc: $s_invalid_bcc\r\n";
		}
		if (!empty($s_error)) {
			SendAlert(GetMessage(MSG_INV_EMAIL,array("ERRORS" => $s_error)));
		}

		if (!SendResults($aFieldOrder,$aCleanedValues,$s_valid_recipients,$s_valid_cc,
		                 $s_valid_bcc,$aRawDataValues)
		) {
			Error("mail_failed",GetMessage(MSG_FAILED_SEND));
		}

		if (!Settings::isEmpty('HOOK_DIR')) {
			if (!@include(Settings::get('HOOK_DIR') . "/fmhookpostemail.inc.php")) {
				@include(Settings::get('HOOK_DIR') . "/fmhookpostemail.inc");
			}
		}
	}
}

$AutoResp->Process();

$SessionAccessor->CopyOut($aAllRawValues);

if ($bGotNextForm) {
	OutputMultiFormTemplate($SPECIAL_VALUES["next_form"],$aRawDataValues);

	if (!Settings::isEmpty('HOOK_DIR')) {
		if (!@include(Settings::get('HOOK_DIR') . "/fmhookpostnextform.inc.php")) {
			@include(Settings::get('HOOK_DIR') . "/fmhookpostnextform.inc");
		}
	}
} else {

	if (!Settings::isEmpty('HOOK_DIR')) {
		if (!@include(Settings::get('HOOK_DIR') . "/fmhookprefinish.inc.php")) {
			@include(Settings::get('HOOK_DIR') . "/fmhookprefinish.inc");
		}
	}

	UnsetSession("FormList");
	UnsetSession("FormIndex");
	UnsetSession("FormKeep");
	if (!$bGotGoodUrl) {
		if (IsAjax()) {
			JSON_Result("OK");
		} else {
			if ($bGotGoodTemplate) {
				OutputTemplate($SPECIAL_VALUES["good_template"],$aRawDataValues);
			} else {
				CreatePage(GetMessage(MSG_THANKS_PAGE),GetMessage(MSG_FORM_OK));
			}
		}

		if (!Settings::isEmpty('HOOK_DIR')) {
			if (!@include(Settings::get('HOOK_DIR') . "/fmhookpostfinish.inc.php")) {
				@include(Settings::get('HOOK_DIR') . "/fmhookpostfinish.inc");
			}
		}

		ZapSession();
	} elseif (IsAjax()) {
		JSON_Result("OK");
	} else

	{
		Redirect($SPECIAL_VALUES["good_url"],GetMessage(MSG_FORM_OK));
	}
}
