<?php
// link_add.php - Add a link between two tables
//
// SiT (Support Incident Tracker) - Support call tracking system
// Copyright (C) 2010-2012 The Support Incident Tracker Project
// Copyright (C) 2000-2009 Salford Software Ltd. and Contributors
//
// This software may be used and distributed according to the terms
// of the GNU General Public License, incorporated herein by reference.
//

// Author: Ivan Lucas <ivanlucas[at]users.sourceforge.net>


$permission = 0; // Allow all auth users

require ('core.php');
require (APPLICATION_LIBPATH . 'functions.inc.php');

// This page requires authentication
require (APPLICATION_LIBPATH . 'auth.inc.php');

$title = $strAddLink;

// External variables
$action = clean_fixed_list($_REQUEST['action'], array('','addlink'));
$origtab = cleanvar($_REQUEST['origtab']);
$origref = clean_int($_REQUEST['origref']);
$linkref = clean_int($_REQUEST['linkref']);
$linktypeid = clean_int($_REQUEST['linktype']);
$direction = clean_fixed_list($_REQUEST['dir'], array('left','right','bi'));
if ($direction == '') $direction = 'left';

switch ($action)
{
    case 'addlink':
        $sql = "INSERT INTO `{$dbLinks}` ";
        $sql .= "(linktype, origcolref, linkcolref, direction, userid) ";
        $sql .= "VALUES ('{$linktypeid}', '{$origref}', '{$linkref}', {$direction}, '{$sit[2]}')";
        mysql_query($sql);
        if (mysql_error()) trigger_error(mysql_error(),E_USER_ERROR);

        html_redirect('main.php');
    break;

    case '':
    default:
        include (APPLICATION_INCPATH . 'htmlheader.inc.php');

        // Find out what kind of link we are to make
        $sql = "SELECT * FROM `{$dbLinkTypes}` WHERE id='{$linktypeid}'";
        $result = mysql_query($sql);
        while ($linktype = mysql_fetch_object($result))
        {
            if ($direction == 'left')
            {
                echo "<h2>Link {$linktype->lrname}</h2>";
            }
            elseif ($direction == 'right')
            {
                echo "<h2>Link {$linktype->rlname}</h2>";
            }

            echo "<p align='center'>Make a {$linktype} link for origtab {$origtab}, origref {$origref}</p>"; // FIMXE i18n
            $recsql = "SELECT {$linktype->linkcol} AS recordref, {$linktype->selectionsql} AS recordname FROM `{$CONFIG['db_tableprefix']}{$linktype->linktab}` ";
            $recsql .= "WHERE {$linktype->linkcol} != '{$origref}'";

            $recresult = mysql_query($recsql);
            if (mysql_error()) trigger_error(mysql_error(),E_USER_WARNING);
            if (mysql_num_rows($recresult) >= 1)
            {
                echo "<form action='{$_SERVER['PHP_SELF']}' method='post'>";
                echo "<p>";
                echo "<select name='linkref'>";
                while ($record = mysql_fetch_object($recresult))
                {
                    echo "<option value='{$record->recordref}'>{$record->recordname}</option>\n";
                }
                echo "</select>";
                echo "</p>";
                echo "<p><input name='submit' type='submit' value='{$strAdd}' /></p>";
                echo "<input type='hidden' name='action' value='addlink' />";
                echo "<input type='hidden' name='origtab' value='{$origtab}' />";
                echo "<input type='hidden' name='origref' value='{$origref}' />";
                echo "<input type='hidden' name='linktype' value='{$linktypeid}' />";
                echo "<input type='hidden' name='dir' value='{$direction}' />";
                echo "<input type='hidden' name='redirect' value='" . htmlspecialchars($_SERVER['HTTP_REFERER'], ENT_QUOTES, $i18ncharset) ."' />";
                echo "</form>";
            }
            else echo "<p class='error'>{$strNothingToLink}</p>";
        }
        include (APPLICATION_INCPATH . 'htmlfooter.inc.php');
}

?>