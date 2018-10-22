<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Initially developped for :
 * Université de Cergy-Pontoise
 * 33, boulevard du Port
 * 95011 Cergy-Pontoise cedex
 * FRANCE
 *
 * Book usernames for creation
 *
 * @package   tool_loginreservation
 * @copyright 2018 Laurent Guillet <laurent.guillet@u-cergy.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * File : index.php
 * Main file
 */

require_once('../../../config.php');
require_once($CFG->dirroot.'/lib/coursecatlib.php');
require_once('index_form.php');

$previous = optional_param('previous', 0, PARAM_INT);

require_login();
$systemcontext = context_system::instance();
$PAGE->set_context($systemcontext);
$title = get_string('title', 'tool_loginreservation');
$PAGE->set_title($title);
$PAGE->set_heading($title);


if (!coursecat::has_capability_on_any(array('moodle/category:manage'))) {
    // The user isn't able to manage any categories. Lets redirect them to a relevant page.
    
    redirect($CFG->wwwroot);
}

echo $OUTPUT->header();
echo $OUTPUT->heading($title);

if ($previous) {
	
	echo get_string('returnlink', 'tool_loginreservation');
	
	$tableoldlogin = new html_table();
	$tableoldlogin->head = array(get_string('logincreated', 'tool_loginreservation'), get_string('type', 'tool_loginreservation'), 
		get_string('generatedby', 'tool_loginreservation'), get_string('generatedon', 'tool_loginreservation'), 
		get_string('usercreated', 'tool_loginreservation'));
		
	$tableoldlogin->colclasses = array('leftalign logincreated', 'leftalign type', 'leftalign generatedby',
		'leftalign generatedon', 'leftalign usercreated');
    $tableoldlogin->id = 'loginreservation';
    $tableoldlogin->attributes['class'] = 'admintable generaltable';
		
	$previouslogins = $DB->get_recordset('tool_loginreservation');
	
	$data = array();
	
	foreach ($previouslogins as $previouslogin) {
		
		$line = array();
		$line[] = $previouslogin->login;
		
		if (substr($previouslogin->login, 0, 1) == 'e') {
			
			$line[] = get_string('student', 'tool_loginreservation');
		} else {
			
			$line[] = get_string('teacher', 'tool_loginreservation');
		}
		
		$creator = $DB->get_record('user', array('id' => $previouslogin->creatorid));
		
		$line[] = "<a href='$CFG->wwwroot/user/profile.php?id=$creator->id'>$creator->firstname $creator->lastname</a>";
		$line[] = date('d/m/Y', $previouslogin->timecreated);
		$createduser = $DB->get_record('user', array('username' => $previouslogin->login));
		
		if ($createduser) {
			
			$line[] = "<a href='$CFG->wwwroot/user/profile.php?id=$createduser->id'>$createduser->firstname $createduser->lastname</a>";
		} else {
			
			$line[] = get_string('notused', 'tool_loginreservation');
		}
		
		$data[] = $row = new html_table_row($line);
	}
	
	$tableoldlogin->data  = $data;
    echo html_writer::table($tableoldlogin);
	
	echo get_string('returnlink', 'tool_loginreservation');
	
} else {

	$mform = new index_form();

	// Form processing and displaying is done here.

	if ($fromform = $mform->get_data()) {
	  // In this case you process validated data. $mform->get_data() returns data posted in form.
	  
		$teacherorstudent = $fromform->teacherorstudent;
		$numusers = $fromform->numusers;
		
		if ($teacherorstudent == 0) {
			
			$type = 'e';
		} else {
			
			$type = 'p';
		}
		
		$sql = "SELECT MAX(id) AS maxid FROM {tool_loginreservation}";
		
		$lastusedrecord = $DB->get_record_sql($sql);
		
		if ($lastusedrecord->maxid) {
			
			$lastusedid = $lastusedrecord->maxid;
		} else {
			
			$lastusedid = 0;
		}
		
		// Affichage des logins créés.
		
		$tablenewlogin = new html_table();
		$tablenewlogin->head = array(get_string('logincreated', 'tool_loginreservation'));
			
		$tablenewlogin->colclasses = array('leftalign logincreated');
		$tablenewlogin->id = 'loginscreated';
		$tablenewlogin->attributes['class'] = 'admintable generaltable';
		
		$datanewlogin = array();
		
		for ($i = 0; $i < $numusers; $i++) {
			
			$newlogin = ucpcreateexternallogin($lastusedid + $i, $type);
			
			$line = array();
			$line[] = $newlogin;
			$datanewlogin[] = $row = new html_table_row($line);
		}
		
		$tablenewlogin->data  = $datanewlogin;
		echo html_writer::table($tablenewlogin);
		
		echo get_string('returnlink', 'tool_loginreservation');
	  
	} else {
		
	  // This branch is executed if the form is submitted but the data doesn't validate and the form should be redisplayed
	  // or on the first display of the form.
	  
	  echo get_string('formintro', 'tool_loginreservation');
	 
	  // Set default data (if any).
	  $mform->set_data(null);
	  
	  // Displays the form.
	  $mform->display();
	  
	  echo get_string('previouslogins', 'tool_loginreservation');
	}
}

echo $OUTPUT->footer();

function ucpcreateexternallogin($newid, $type) {

	global $DB, $USER, $CFG;
	
	$year = $CFG->thisyear;
	$newlogin = $type.$year."id".$newid;
	
	while ($DB->record_exists('user', array('username' => $newlogin))) {
		
		$newid++;
		$newlogin = $type.$year."id".$newid;
	}
	
	$newrecord = new stdClass();
	$newrecord->login = $newlogin;
	$newrecord->creatorid = $USER->id;
	$newrecord->timecreated = time();	
	$newrecord->id = $DB->insert_record('tool_loginreservation', $newrecord);
	
	return $newlogin;
}

