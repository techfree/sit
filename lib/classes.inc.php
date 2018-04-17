<?php
// classes.inc.php - The classes used by SiT
//
// SiT (Support Incident Tracker) - Support call tracking system
// Copyright (C) 2010-2012 The Support Incident Tracker Project
// Copyright (C) 2000-2009 Salford Software Ltd. and Contributors
//
// This software may be used and distributed according to the terms
// of the GNU General Public License, incorporated herein by reference.
//

// Author:  Ivan Lucas <ivanlucas[at]users.sourceforge.net>
//             Paul Heaney <paul[at]sitracker.org>


// Prevent script from being run directly (ie. it must always be included
if (realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME']))
{
    exit;
}


class Holiday {
    var $starttime;
    var $endtime;
}


/**
 * Highest level within SiT! all entities within SiT! should extend from this class
 * this provides a common interface exposing values and functiosn which are common across all entities
 * @author Paul Heaney
*/
abstract class SitEntity {
    var $id;

    /**
     * Adds the entity to SiT
     */
    abstract function add();

    /**
     * Edits an existing entity in sit
     */
    abstract function edit();
}


/**
 * Base class for all types of people, this contains the core attributes common for all people
 * @author Paul Heaney
 */
abstract class Person extends SitEntity {
    var $username;
    var $password;
    var $jobtitle;
    var $email;
    var $phone;
    var $mobile;
    var $fax;
    var $source; ///< default: sit, ldap etc
}


/**
 * Represents a user adding the additional details possible for a user
 * @author Paul Heaney
 */
class User extends Person{
    var $realname;
    var $roleid;
    var $groupid;
    var $signature;
    var $status;
    var $message;
    var $accepting;
    var $holiday_entitlement;
    var $holiday_resetdate;
    var $qualifications;
    var $incident_refresh;
    var $update_order;
    var $num_updates_view;
    var $style;
    var $hide_auto_updates;
    var $hideheader;
    var $monitor;
    var $i18n;
    var $utc_offset;
    var $emoticons;
    var $startdate;

    /**
     * Adds a user to SiT! this performs a number of checks to ensure uniqueness and mandertory details are present
     *
     * @return mixed int for user ID if sucessful else FALSE
     * @author Paul Heaney
     */
    function add()
    {
        global $CONFIG, $now;

        $this->style = $CONFIG['default_interface_style'];
        $this->startdate = $now;
        if (empty($this->source)) $this->source = 'sit';

        if (empty($this->password)) $this->password = generate_password(16);

        $toReturn = FALSE;

        $sql = "SELECT * FROM `{$GLOBALS['dbUsers']}` WHERE username = '".clean_dbstring($this->username)."'";
        $result = mysql_query($sql);
        if (mysql_error()) trigger_error(mysql_error(), E_USER_WARNING);

        if (mysql_num_rows($result) != 0)
        {
            // Already exists
            trigger_error($GLOBALS['strUsernameNotUnique'] . " Username: '{$this->username}'", E_USER_ERROR);
            $toReturn = FALSE;
        }
        else
        {
            // Insert
            $sql = "INSERT INTO `{$GLOBALS['dbUsers']}` (username, password, realname, roleid, ";
            $sql .= "groupid, title, email, phone, mobile, fax, status, var_style, ";
            $sql .= "holiday_entitlement, user_startdate, lastseen, user_source) ";
            $sql .= "VALUES ('".clean_dbstring($this->username)."', MD5('".clean_dbstring($this->password)."'), '".clean_dbstring($this->realname)."', '".clean_int($this->roleid)."', ";
            $sql .= "'".clean_int($this->groupid)."', '".clean_dbstring($this->jobtitle)."', '".clean_dbstring($this->email)."', '".clean_dbstring($this->phone)."', '".clean_dbstring($this->mobile)."', '".clean_dbstring($this->fax)."', ";
            $sql .= "".clean_int($this->status).", '".clean_dbstring($this->style)."', ";
            $sql .= "'".clean_int($this->holiday_entitlement)."', '".clean_dbstring($this->startdate)."', 0, '".clean_dbstring($this->source)."')";
            $result = mysql_query($sql);
            if (mysql_error())
            {
                trigger_error("MySQL Query Error ".mysql_error(), E_USER_ERROR);
                $toReturn = false;
            }
            $toReturn = mysql_insert_id();

            if ($toReturn != FALSE)
            {
                // Create permissions (set to none)
                $sql = "SELECT * FROM `{$GLOBALS['dbPermissions']}`";
                $result = mysql_query($sql);
                if (mysql_error()) trigger_error("MySQL Query Error ".mysql_error(), E_USER_WARNING);
                while ($perm = mysql_fetch_object($result))
                {
                    $psql = "INSERT INTO `{$GLOBALS['dbUserPermissions']}` (userid, permissionid, granted) ";
                    $psql .= "VALUES ('{$toReturn}', '{$perm->id}', 'false')";
                    mysql_query($psql);
                    if (mysql_error()) trigger_error("MySQL Query Error ".mysql_error(), E_USER_ERROR);
                }

                setup_user_triggers($toReturn);
                trigger('TRIGGER_NEW_USER', array('userid' => $toReturn));
            }
        }

        return $toReturn;
    }


    /**
     * Updates the details of a user within SiT!
     * @author Paul Heaney
     * @return bool True if updated sucessfully FALSE otherwise
     */
    function edit()
    {
        global $now;
        $toReturn = false;

        if (!empty($this->id) AND is_number($this>id))
        {
            $sql = "SELECT username FROM `{$GLOBALS['dbUsers']}` WHERE id = {$this->id}";
            $result = mysql_query($sql);
            if (mysql_error()) trigger_error(mysql_error(),E_USER_WARNING);

            if (mysql_num_rows($result) != 0)
            {
                // Exists
                $s = array();

                if (!empty($this->password)) $s[] = "password = MD5('".clean_dbstring($this->password)."')";
                if (!empty($this->realname)) $s[] = "realname = '".clean_dbstring($this->realname)."'";
                if (!empty($this->roleid)) $s[] = "roleid = ".clean_int($this->roleid)."";
                if (!empty($this->groupid)) $s[] = "groupid = ".clean_int($this->groupid)."";
                if (!empty($this->jobtitle)) $s[] = "title = '".clean_dbstring($this->jobtitle)."'";
                if (!empty($this->signature)) $s[] = "signature = '".clean_dbstring($this->signature)."'";
                if (!empty($this->email)) $s[] = "email = '".clean_dbstring($this->email)."'";
                if (!empty($this->phone)) $s[] = "phone = '".clean_dbstring($this->phone)."'";
                if (!empty($this->mobile)) $s[] = "mobile = '".clean_dbstring($this->mobile)."'";
                if (!empty($this->fax)) $s[] = "fax = '".clean_dbstring($this->fax)."'";
                if (!empty($this->status)) $s[] = "status = ".clean_int($this->status)."";
                if (!empty($this->message)) $s[] = "message = '".clean_dbstring($this->message)."'";
                if (!empty($this->accepting))
                {
                    if ($this->accepting) $s[] = "accepting = 'Yes'";
                    else $s[] = "accepting = 'No'";
                }
                if (!empty($this->holiday_entitlement)) $s[] = "holiday_entitlement = ".clean_int($this->holiday_entitlement)."";
                if (!empty($this->holiday_resetdate)) $s[] = "holiday_restdate = '".cleanvar($this->holiday_resetdate)."'";
                if (!empty($this->qualifications)) $s[] = "qualifications = '".clean_dbstring($this->qualifications)."'";

                if (!empty($this->incident_refresh)) $s[] = "var_incident_refresh = ".clean_int($this->incident_refresh)."";
                if (!empty($this->update_order)) $s[] = "var_update_order = '".cleanvar($this->update_order)."'";
                if (!empty($this->num_updates_view)) $s[] = "var_num_updates_view = ".clean_int($this->num_updates_view)."";
                if (!empty($this->style)) $s[] = "var_style = ".clean_dbstring($this->style)."";
                if (!empty($this->hide_auto_updates)) $s[] = "var_hideautoupdates = '".cleanvar($this->hide_auto_updates)."'";
                if (!empty($this->hideheader)) $s[] = "var_hideheader = '".cleanvar($this->hideheader)."'";
                if (!empty($this->monitor)) $s[] = "var_monitor = '".cleanvar($this->monitor)."'";
                if (!empty($this->i18n)) $s[] = "var_i18n = '".cleanvar($this->i18n)."'";
                if (!empty($this->utc_offset)) $s[] = "var_utc_offset = ".cleanvar($this->utc_offset)."";
                if (!empty($this->emoticons)) $s[] = "var_emoticons = '".cleanvar($this->emoticons)."'";
                if (!empty($this->startdate)) $s[] = "user_startdate = '".cleanvar($this->startdate)."'";

                $sql = "UPDATE `{$GLOBALS['dbUsers']}` SET ".implode(", ", $s)." WHERE id = {$this->id}";
                $result = mysql_query($sql);
                if (mysql_error())
                {
                    trigger_error(mysql_error(), E_USER_WARNING);
                    $toReturn = FALSE;
                }
                else
                {
                    $toReturn = TRUE;
                }
            }
            else
            {
                $toReturn = FALSE;
            }
        }

        return $toReturn;
    }


    /**
     * Disabled this user in SiT!
     * @author Paul Heaney
     * @return bool
     * @retval TRUE user disabled
     * @retval FALSE user not disabled
     */
    function disable()
    {
        $toReturn = true;
        if (!empty($this->id) AND $this->status != 0)
        {
            $sql = "UPDATE `{$GLOBALS['dbUsers']}` SET status = 0 WHERE id = {$this->id}";

            $result = mysql_query($sql);
            if (mysql_error()) trigger_error(mysql_error(),E_USER_WARNING);
            if (mysql_affected_rows() != 1)
            {
                $sql = "SELECT status FROM `{$GLOBALS['dbUsers']}` WHERE id = {$this->id} AND status = 0 ";
                $result = mysql_query($result);
                if (mysql_num_rows($result) == 0)
                {
                    trigger_error("Failed to disable user {$this->username}", E_USER_WARNING);
                    $toReturn = FALSE;
                }
                else
                {
                    // Already disabled
                    $toReturn = TRUE;
                }
            }
            else
            {
                $toReturn = TRUE;
            }
        }

        return $toReturn;
    }
}


/**
 * Represents a contact within SiT! adding the necessary details unique to contacts
 * @author Paul Heaney
 */
class Contact extends Person {
    var $notify_contact;
    var $forenames;
    var $surname;
    var $courtesytitle;
    var $siteid;
    var $department;
    var $address1;
    var $address2;
    var $city;
    var $county;
    var $country;
    var $postcode;
    var $dataprotection_email; ///< boolean
    var $dataprotection_phone; ///< boolean
    var $dataprotection_address; ///< boolean
    var $notes;
    var $active;

    var $emailonadd; // Boolean - default sto false

    function __construct()
    {
        $this->emailonadd = false;
    }

    /**
     * Checks to see if the required fields are present and optionally that the user is unique
     * @author Paul Heaney
     * @param bool $duplicate Whether to check if this contact is a duplicate, defaults to true
     * @return bool true indicates valid contact, false otherwise
     */
    function check_valid($duplicate=true)
    {
        $errors = 0;
        if (empty($this->siteid))
        {
            $errors++;
            trigger_error($GLOBALS['strMustSelectCustomerSite'], E_USER_ERROR);
        }

        if (empty($this->surname))
        {
            $errors++;
            trigger_error($GLOBALS['strMustEnterSurname'], E_USER_ERROR);
        }

        if ($duplicate AND $this->is_duplicate())
        {
            $errors++;
            trigger_error($GLOBALS['strContactRecordExists'], E_USER_ERROR);
        }

        if ($errors > 0) return false;
        else return true;
    }


    /**
     * Generates an array of insertable values for the contacts data protection settings
     * @author Paul Heaney
     * @return array an array with keys email, phone, address with either Yes or No as values
     */
    function get_dataprotection()
    {
        $dp['email'] = 'Yes';
        $dp['phone'] = 'Yes';
        $dp['address'] = 'Yes';

        if (!$this->dataprotection_email) $dp['email'] = 'No';
        if (!$this->dataprotection_phone) $dp['phone'] = 'No';
        if (!$this->dataprotection_address) $dp['address'] = 'No';

        return $dp;
    }


    /**
     * Performs the addition of the contact to SiT! this performs validity checks before adding the contact
     * @author Paul Heaney
     * @return mixed int for contactID if sucsesful, false otherwise
     */
    function add()
    {
        global $now, $sit;
        $toReturn = false;
        $generate_username = false;

        if ($this->check_valid())
        {
            $dp = $this->get_dataprotection();

            if (empty($this->source)) $this->source = 'sit';

            if (empty($this->username))
            {
                $generate_username = true;
                $this->username = strtolower($this->surname).$now;
            }

            if (empty($this->password)) $this->password = generate_password(16);

            $sql  = "INSERT INTO `".cleanvar($GLOBALS['dbContacts'])."` (username, password, courtesytitle, forenames, surname, jobtitle, ";
            $sql .= "siteid, address1, address2, city, county, country, postcode, email, phone, mobile, fax, ";
            $sql .= "department, notes, dataprotection_email, dataprotection_phone, dataprotection_address, ";
            $sql .= "timestamp_added, timestamp_modified, created, createdby, modified, modifiedby, contact_source) ";
            $sql .= "VALUES ('".clean_dbstring($this->username)."', MD5('".clean_dbstring($this->password)."'), '".clean_dbstring($this->courtesytitle)."', '".clean_dbstring($this->forenames)."', '".clean_dbstring($this->surname)."', '".clean_dbstring($this->jobtitle)."', ";
            $sql .= "'".clean_int($this->siteid)."', '".clean_dbstring($this->address1)."', '".clean_dbstring($this->address2)."', '".clean_dbstring($this->city)."', '".clean_dbstring($this->county)."', '".clean_dbstring($this->country)."', '".clean_dbstring($this->postcode)."', '".clean_dbstring($this->email)."', ";
            $sql .= "'".clean_dbstring($this->phone)."', '".clean_dbstring($this->mobile)."', '".clean_dbstring($this->fax)."', '".clean_dbstring($this->department)."', '".clean_dbstring($this->notes)."', '".clean_dbstring($dp['email'])."', ";
            $sql .= "'".clean_dbstring($dp['phone'])."', '".clean_dbstring($dp['address'])."', '".clean_dbstring($now)."', '".clean_dbstring($now)."', NOW(), '".clean_int($_SESSION['userid'])."', NOW(), '".clean_int($_SESSION['userid'])."', '".clean_dbstring($this->source)."')";
            $result = mysql_query($sql);
            if (mysql_error()) trigger_error("MySQL Query Error ".mysql_error(), E_USER_ERROR);

            $newid = mysql_insert_id();

            $toReturn = $newid;

            if ($generate_username)
            {
                // concatenate username with insert id to make unique
                $username = $username . $newid;
                $sql = "UPDATE `{$GLOBALS['dbContacts']}` SET username='{$username}' WHERE id='{$newid}'";
                $result = mysql_query($sql);
                if (mysql_error()) trigger_error("MySQL Query Error ".mysql_error(), E_USER_ERROR);
            }

            if ($this->emailonadd) $emaildetails = 1;
            else $emaildetails = 0;

            trigger('TRIGGER_NEW_CONTACT', array('contactid' => $newid,
                                     'prepassword' => $this->password,
                                     'userid' => $sit[2],
                                     'emaildetails' => $emaildetails
                                     ));
        }

        return $toReturn;
    }


    /**
     * Updates the details of an existing contact within SiT!
     * @author Paul Heaney
     * @return bool. true on sucess, false otherwise
     */
    function edit()
    {
        global $now;

        $toReturn = false;

        if (!empty($this->id) AND is_numeric($this->id))
        {
            $dp = $this->get_dataprotection();

            if (!empty($this->username)) $s[] = "username = '".clean_dbstring($this->username)."'";
            if (!empty($this->password)) $s[] = "password = MD5('".clean_dbstring($this->password)."')";
            if (!empty($this->jobtitle)) $s[] = "jobtitle = '".clean_dbstring($this->jobtitle)."'";
            if (!empty($this->email)) $s[] = "email = '".clean_dbstring($this->email)."'";
            if (!empty($this->phone)) $s[] = "phone = '".clean_dbstring($this->phone)."'";
            if (!empty($this->mobile)) $s[] = "mobile = '".clean_dbstring($this->mobile)."'";
            if (!empty($this->fax)) $s[] = "fax = '".clean_dbstring($this->fax)."'";
            if (!empty($this->notify_contact)) $s[] = "notify_contactid = ".clean_int($this->motify_contact)."'";
            if (!empty($this->forenames)) $s[] = "forenames = '".clean_dbstring($this->forenames)."'";
            if (!empty($this->surname)) $s[] = "surname = '".clean_dbstring($this->surname)."'";
            if (!empty($this->courtesytitle)) $s[] = "courtesytitle = '".clean_dbstring($this->courtesytitle)."'";
            if (!empty($this->siteid)) $s[] = "siteid = ".clean_int($this->siteid)."";
            if (!empty($this->department)) $s[] = "department = '".clean_dbstring($this->department)."'";
            if (!empty($this->address1)) $s[] = "address1 = '".clean_dbstring($this->address1)."'";
            if (!empty($this->address2)) $s[] = "address2 = '".clean_dbstring($this->address2)."'";
            if (!empty($this->city)) $s[] = "city = '".clean_dbstring($this->city)."'";
            if (!empty($this->county)) $s[] = "county = '".clean_dbstring($this->county)."'";
            if (!empty($this->country)) $s[] = "country = '".clean_dbstring($this->country)."'";
            if (!empty($this->postcode)) $s[] = "postcode = '".clean_dbstring($this->postcode)."'";
            if (!empty($this->dataprotection_email)) $s[] = "dataprotection_email = '".clean_dbstring($db['email'])."'";
            if (!empty($this->dataprotection_phone)) $s[] = "dataprotection_phone = '".clean_dbstring($db['phone'])."'";
            if (!empty($this->dataprotection_address)) $s[] = "dataprotection_address = '".clean_dbstring($db['address'])."'";
            if (!empty($this->notes)) $s[] = "notes = '".clean_dbstring($this->notes)."'";
            if (!empty($this->source)) $s[] = "contact_source = '{$this->source}'";
            if (!empty($this->active))
            {
                if ($this->active) $s[] = "active = 'true'";
                else $s[] = "active = 'false'";
            }
            $s[] = "modified = NOW()";
            $s[] = "timestamp_modified = {$now}";
            if (!empty($_SESSION['userid']))
            {
                // If LDAP is doing this then we dont have the details
                $s[] = "modifiedby = {$_SESSION['userid']}";
            }

            $sql = "UPDATE `{$GLOBALS['dbContacts']}` SET ".implode(", ", $s)." WHERE id = {$this->id}";
            $result = mysql_query($sql);
            if (mysql_error())
            {
                trigger_error(mysql_error(), E_USER_WARNING);
                $toReturn = false;
            }
            {
                $toReturn = true;
            }
        }
        else
        {
            $toReturn = false;
        }

        return $toReturn;
    }

    /**
     * Disabled this contact in SiT!
     * @author Paul Heaney
     * @return bool True if disabled, false otherwise
     */
    function disable()
    {
        $toReturn = true;
        if (!empty($this->id))
        {
            $sql = "SELECT 1 FROM `{$GLOBALS['dbContacts']}` WHERE id = {$this->id} AND active = 'false'";
            $result = mysql_query($sql);
            if (mysql_error()) trigger_error(mysql_error(), E_USER_WARNING);
            if (mysql_num_rows($result) == 0)
            {
                debug_log("Failed to disable contact {$this->username}, was already disabled");
                $toReturn = true;
            }
            else 
            {
                $sql = "UPDATE `{$GLOBALS['dbContacts']}` SET active = 'false' WHERE id = {$this->id}";
    
                $result = mysql_query($sql);
                if (mysql_error()) trigger_error(mysql_error(), E_USER_WARNING);
                if (mysql_affected_rows() != 1)
                {
                    $sql = "SELECT active FROM `{$GLOBALS['dbContacts']}` WHERE id = {$this->id} AND active = 'false'";
                    $result = mysql_query($sql);
                    if (mysql_num_rows($result) == 0)
                    {
                        trigger_error("Failed to disable contact {$this->username}", E_USER_WARNING);
                        $toReturn = false;
                    }
                    else
                    {
                        // The contact was already disabled
                        $toReturn = true;
                    }
                }
                else
                {
                    $toReturn = true;
                }
            }
        }

        return $toReturn;
    }

    /**
     * Checks to see if the contact is a duplicate within SiT!
     * @author Paul Heaney
     * @return bool. true for duplicate, false otherwise
     */
    function is_duplicate()
    {
        // Check this is not a duplicate
        $sql = "SELECT id FROM `{$GLOBALS['dbContacts']}` WHERE email='{$this->email}' AND LCASE(surname)=LCASE('{$this->surname}') LIMIT 1";
        $result = mysql_query($sql);
        if (mysql_num_rows($result) >= 1) return true;
        else return false;
    }
}
?>