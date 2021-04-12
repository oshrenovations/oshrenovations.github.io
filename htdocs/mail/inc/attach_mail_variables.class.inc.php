<?php
/**
 * Form Mail Script 
 * 
 * Copyright: Ralf Stadtaus - http://www.gentlesource.com/
 * 
 */


//require_once 'File/IMC.php';
require_once 'Contact_Vcard_Build.php';


define('G10E_ATTACH_TYPE_VCARD', 	1);
define('G10E_ATTACH_TYPE_CSV', 		2);

// Include form names as head line
define('G10E_ATTACH_TYPE_CSV_HEAD',			true);
define('G10E_ATTACH_TYPE_CSV_DELIMITER',	';');


class attach_mail_variables
{
	
	/**
	 * Select attachment file type and return result
	 * 
	 * @param array $mailvars
	 * @param int $type
	 * @return mixed attachment content
	 */
	function get_content($config)
	{
        // Remove control fields
        $mailvars = attach_mail_variables::remove_control_fields($config['mailvars'], $config['control']);
        
        // Create VCard
		if ($config['type'] == G10E_ATTACH_TYPE_VCARD) {
			if ($res = attach_mail_variables::vcard($mailvars)) {
			    return $res;
			}
		}
        
        // Create CSV
		if ($config['type'] == G10E_ATTACH_TYPE_CSV) {
			if ($res = attach_mail_variables::csv($mailvars)) {
			    return $res;
			}
		}
	}
	
	/**
	 * Select attachment file type and return result
	 * 
	 * @param array $mailvars
	 * @param int $type
	 * @return mixed attachment content
	 */
	function remove_control_fields($mailvars, $control)
	{
        foreach ($control as $value)
        {
            if (array_key_exists(trim($value), $mailvars)) {
                unset($mailvars[trim($value)]);
            }
        }
        return $mailvars;
	}

	/**
	 * Create vCard
	 * 
	 * Mailvars array must consist of following elements
	 * 
	 * firstname
	 * lastname
	 * title
	 * email 
	 * pobox 
	 * extendedaddress 
	 * street 
	 * zipcode 
	 * city 
	 * state 
	 * country
	 * 
	 * @param array $mailvars
	 * @return string attachment content
	 */
	function vcard($mailvars)
	{
		if (!is_array($mailvars)) {
		    return false;
		}
		$default = array(
                    'firstname'         => '',
                    'middlename'        => '',
                    'lastname'          => '',
                    'nickname'          => '',
                    'title'             => '',
                    'work_title'        => '',
					'email'             => '',
					'pobox'             => '',
					'extendedaddress'   => '',
					'street'            => '',
					'zipcode'           => '',
					'city'              => '',
					'state'             => '',
                    'country'           => '',
                    'work_pobox'        => '',
                    'work_office'       => '',
                    'work_street'       => '',
                    'work_zipcode'      => '',
                    'work_city'         => '',
                    'work_state'        => '',
                    'work_country'      => '',
                    'birthday'          => '',
                    'work_email'        => '',
                    'work_telephone'    => '',
                    'website'           => '',
                    'telephone'         => '',
                    'work_role'         => '',
                    'note'              => '',
                    'work_organization' => '',
					);
		$vars = array_merge($default, $mailvars);
	    $vcard = new Contact_Vcard_Build();
        
        // General informations
        $vcard->setFormattedName($vars['firstname'] . ' ' . $vars['lastname']);
        $vcard->setName($vars['lastname'], $vars['firstname'], $vars['middlename'], $vars['title'], '');
        $vcard->setBirthday($vars['birthday']);
        $vcard->addNickname($vars['nickname']);
        $vcard->setNote($vars['note']);
        
        // Home informations
        $vcard->addEmail($vars['email']);
    	$vcard->addParam('TYPE', 'HOME');
        
        $vcard->addTelephone($vars['telephone']);
    	$vcard->addParam('TYPE', 'HOME');
    	
        $vcard->addAddress(	
    				$vars['pobox'], 
					$vars['street'],
                    $vars['extendedaddress'], 
        			$vars['city'], 
					$vars['state'], 
					$vars['zipcode'], 
					$vars['country']
					);
    	$vcard->addParam('TYPE', 'HOME');
        
        $vcard->setURL($vars['website']);
        $vcard->addParam('TYPE', 'HOME');
        
        // Business informations
        $vcard->addEmail($vars['work_email']);
        $vcard->addParam('TYPE', 'WORK');
        
        $vcard->addTelephone($vars['work_telephone']);
        $vcard->addParam('TYPE', 'WORK');
        
        $vcard->addAddress( 
                    $vars['work_pobox'], 
                    $vars['work_office'], 
                    $vars['work_street'],
                    $vars['work_city'], 
                    $vars['work_state'], 
                    $vars['work_zipcode'], 
                    $vars['work_country']
                    );
        $vcard->addParam('TYPE', 'WORK');
        
        $vcard->addOrganization($vars['work_organization']);
        $vcard->setRole($vars['work_role']);
        $vcard->setTitle($vars['work_title']);
        
        
    	return $vcard->fetch();
	}

	/**
	 * Create CSV (comma separated) file
	 * 
	 * 
	 * @param array $mailvars
	 * @return string attachment content
	 */
	function csv($mailvars)
	{
		if (!is_array($mailvars)) {
		    return false;
		}
		
		$lines = '';
		if (G10E_ATTACH_TYPE_CSV_HEAD == true) {
		    $lines .= join(G10E_ATTACH_TYPE_CSV_DELIMITER, array_flip($mailvars)) . "\n";
		}
		$lines .= join(G10E_ATTACH_TYPE_CSV_DELIMITER, $mailvars);
		
		return $lines;
	}

// -----------------------------------------------------------------------------





} // End of class



?>
