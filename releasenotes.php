<?php
// releasenotes.php - Release notes summary
//
// SiT (Support Incident Tracker) - Support call tracking system
// Copyright (C) 2010-2012 The Support Incident Tracker Project
// Copyright (C) 2000-2009 Salford Software Ltd. and Contributors
//
// This software may be used and distributed according to the terms
// of the GNU General Public License, incorporated herein by reference.


$permission = 0;
require ('core.php');
require (APPLICATION_LIBPATH . 'functions.inc.php');
$version = cleanvar($_GET['v']);
//as passed by triggers
$version = str_replace("v", "", $version);
if (!empty($version))
{
    header("Location: {$_SERVER['PHP_SELF']}#{$version}");
}

// This page requires authentication
require (APPLICATION_LIBPATH . 'auth.inc.php');
include_once (APPLICATION_INCPATH . 'htmlheader.inc.php');
echo "<h2>Release Notes</h2>";

echo "<div id='help'>";
echo "<h4>This is a summary of the full release notes showing only the most important changes, for more detailed notes and the latest information on this release please <a href='http://sitracker.org/wiki/ReleaseNotes'>see the SiT website</a>:</h4>";

echo "<h3><a name='3.6x'>v3.6x LTS</a></h3>";
echo "<div>";
echo "<p>This is a Long Term Support edition, which means that we will be providing Technical Support and bug fixes for this ";
echo "release (as v3.61, v3.62... etc.) until around the time that v4.1 is released. Security fixes will be made available ";
echo "for longer than that - at least until v4.2 is released! We've decided to do this so that we can concentrate our main development ";
echo "efforts on exciting new features for 4.x without ignoring existing users who are currently using the 3.x versions of SiT! ";
echo "and to provide a stable upgrade path.</p>";
echo "</div>";

echo "<h4><a name='3.67'>v3.67 LTS</a></h4>";
echo "<div>";
echo "<ul>";
echo "<li>Security Fix: Robert Foggia of Trustwave discovered a cross-site scripting (XSS) vulnerability in the setup script.</li>";
echo "</ul>";
echo "</div>";

echo "<h4><a name='3.66'>v3.66 LTS</a></h4>";
echo "<div>";
echo "<ul>";
echo "<li>Security Fixes: Ilya Verbitskiy discovered multiple high risk vulnerabilities in version 3.65 and prior. http://www.kb.cert.org/vuls/id/576355</li>";
echo "<li>Minor bug fixes</li>";
echo "<li>A few triggers added</li>";
echo "<li>Updated and improved (da-DK) translation by Carsten Jensen</li>";
echo "</ul>";
echo "</div>";


echo "<h4><a name='3.65'>v3.65 LTS</a></h4>";
echo "<div>";
echo "<ul>";
echo "<li>Security Fixes: High-Tech Bridge discovered multiple high risk vulnerabilities in version 3.64 and prior. (HTB23043)</li>";
echo "<li>Minor bug fixes</li>";
echo "<li>New Afrikaans (af) translation (19%) thanks to Nico du Toit</li>";
echo "<li>Updated German (de-DE) translation and help files thanks to Gabriele Pohl</li>";
echo "</ul>";
echo "</div>";

echo "<h4><a name='3.64'>v3.64 LTS</a></h4>";
echo "<div>";
echo "<ul>";
echo "<li>Many minor and not so minor bug fixes</li>";
echo "<li>Fixed some security vulnerabilities (PT-2011-25) discovered by Yuri Goltsev, Positive Research Lab (Positive Technologies Company)</li>";
echo "<li>Updated Portuguese (pt-PT) translation (100%) thanks to José Tomás & Luis Manuel Rodrigues</li>";
echo "<li>Help files are now translated to German (de-DE) thanks to Gabriele Pohl </li>";
echo "</ul>";
echo "</div>";

echo "<h4><a name='3.63'>v3.63 LTS</a></h4>";
echo "<div>";
echo "<ul>";
echo "<li>Many minor and not so minor bug fixes</li>";
echo "<li>Fixed some security vulnerabilities (SA43612) discovered by Autosec Tools</li>";
echo "<li>New Persian/Farsi (fa-IR) translation (90%) by Mahdi Heidari</li>";
echo "<li>New Polish (pl-PL) translation (90%) by Tom Kapelko & Urszula Gola</li>";
echo "<li>Updated German (de-DE) translation (100%) by Gabriele Pohl &amp; Raffael Luthiger</li>";
echo "<li>Updated Russian (ru-RU) translation (100%) by Алексей Назаров</li>";
echo "<li>Support for right-to-left languages (currently Arabic and Persian)</li>";
echo "</ul>";
echo "</div>";

echo "<h4><a name='3.62'>v3.62 LTS</a></h4>";
echo "<div>";
echo "<ul>";
echo "<li>Many minor and not so minor bug fixes</li>";
echo "<li>Updated Mexican Spanish (es-MX) translation (100%) by Josías Galván Reyes</li>";
echo "<li>Romanian (ro-RO) translation (76%) by Adrian Cristinici</li>";
echo "</ul>";
echo "</div>";


echo "<h4><a name='3.61'>v3.61 LTS</a></h4>";
echo "<div>";
echo "<ul>";
echo "<li>Many minor and not-so-minor enhancements and bug fixes </li>";
echo "<li>Mexican Spanish (es-MX) translation (99%) by Josías Galván Reyes </li>";
echo "<li>Updated Welsh (cy-GB) (100%), Norsk Bokmål (nb-NO) (99%), and Catalan (ca-ES) (38%) translations by Jeff Stone </li>";
echo "<li>Updated Brazilian Portuguese (pt-BR) translation (71%) by Fernando Suzarte Schiavon </li>";
echo "<li>Updated Russian (ru-RU) translation (99%) by Anton Gultyaev </li>";
echo "</ul>";
echo "</div>";

echo "<h4><a name='3.60'>v3.60 LTS</a></h4>";
echo "<div>";
echo "<ul>";
echo "<li>Many minor and not-so-minor enhancements and bug fixes </li>";
echo "<li>Updated German (de-DE) translation (65%) by Raffael Luthiger</li>";
echo "<li>Updated Slovenian sl-SL translation (52%) by Alen Grižonič </li>";
echo "<li>Updated Danish (da-DK) translation (100%) by Carsten Jensen </li>";
echo "<li>Updated Russian (ru-RU) translation (99%) by sancho78rus</li>";
echo "<li>Support for daylight savings time (DST)</li>";
echo "<li>Added more plugin contexts, including support for a plugins tab on the configuration page </li>";
echo "</ul>";
echo "</div>";

echo "</div>";

include_once (APPLICATION_INCPATH . 'htmlfooter.inc.php');

?>