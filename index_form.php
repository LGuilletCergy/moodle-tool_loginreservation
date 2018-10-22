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
 * File : index_form.php
 * Form file
 */

require_once('../../../config.php');
require_once("$CFG->libdir/formslib.php");

class index_form extends moodleform {

    // Add elements to form.
    public function definition() {

        global $CFG;
 
        $mform = $this->_form; // Don't forget the underscore! 		
		
		$radioarray = array();
		$radioarray[] = $mform->createElement('radio', 'teacherorstudent', '', get_string('student', 'tool_loginreservation'), 0);
		$radioarray[] = $mform->createElement('radio', 'teacherorstudent', '', get_string('teacher', 'tool_loginreservation'), 1);
		$mform->setDefault('teacherorstudent', 0);
		$mform->addGroup($radioarray, 'radioar', get_string('usertype', 'tool_loginreservation'), array(' '), false);
 
        $mform->addElement('text', 'numusers', get_string('numusers', 'tool_loginreservation'));
        $mform->setType('numusers', PARAM_INT);
        $mform->setDefault('numusers', '0');
		
		$this->add_action_buttons($cancel = false);
    }
	
    function validation($data, $files) {
        return array();
    }
}
