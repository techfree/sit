<?php
// dashboard_random_tip.php - A random tip
//
// SiT (Support Incident Tracker) - Support call tracking system
// Copyright (C) 2010-2012 The Support Incident Tracker Project
// Copyright (C) 2000-2009 Salford Software Ltd. and Contributors
//
// This software may be used and distributed according to the terms
// of the GNU General Public License, incorporated herein by reference.
//

$PLUGININFO['dashboard_random_tip']['version'] = 2;
$PLUGININFO['dashboard_random_tip']['description'] = 'Displays random help tips';
$PLUGININFO['dashboard_random_tip']['author'] = 'SiT! Developers';
$PLUGININFO['dashboard_random_tip']['legal'] = 'GPL';
$PLUGININFO['dashboard_random_tip']['sitminversion'] = 3.45;
$PLUGININFO['dashboard_random_tip']['sitmaxversion'] = 3.69;


$dashboard_random_tip_version = $PLUGININFO['dashboard_random_tip']['version'];

function dashboard_random_tip($dashletid)
{
    global $iconset, $CONFIG;

    echo dashlet('random_tip', $dashletid, icon('tip', 16), $GLOBALS['strRandomTip'], '', $content);
}


function dashboard_random_tip_display($dashletid)
{
    global $CONFIG;

    $delim="\n";
    $tipsfile = dirname( __FILE__ ).DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR."help/{$_SESSION['lang']}/tips.txt";
    if (!file_exists($tipsfile)) $tipsfile = dirname( __FILE__ ).DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR."help/en-GB/tips.txt";
    if (!file_exists($tipsfile))
    {
        trigger_error("Tips file '{$tipsfile}' was not found!",E_USER_WARNING);
    }
    else
    {
        $fp = fopen($tipsfile, "r");
        if (!$fp) trigger_error("{$tipsfile} was not found!", E_USER_WARNING);
    }
    $contents = fread($fp, filesize($tipsfile));
    $tips = explode($delim,$contents);
    array_shift($tips);
    srand((double)microtime()*1000000);
    $atip = (rand(1, sizeof($tips))-1);
    $content = "#".($atip+1).": ".$tips[$atip];

    echo $content;
}

function dashboard_random_tip_upgrade()
{
 $upgrade_schema[2] ="";
 return $upgrade_schema;
}


function dashboard_random_tip_get_version()
{
    global $dashboard_random_tip_version;
    return $dashboard_random_tip_version;
}


?>