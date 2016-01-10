<?php

require_once($CFG->libdir ."/formslib.php");

class skill_bars_updateform extends moodleform {

    function definition() {
        $mform =& $this->_form;

        // TODO: use get_string() here.
        $mform->addElement('static', 'user', 'Updating skills for:', $this->_customdata['user']);

        $skills = $this->_customdata['skills'];

        foreach( $skills as $skill ) {
            $id_skillbox = 'skill_'. $skill->pointsid;

            $mform->addElement('header', 'skillheader', $skill->name);

            //TODO: abstract out point values
            $pointlevels = array(0,1,2,3,4,5,6,7,8);
            $mform->addElement('select', $id_skillbox, 'Points: ', $pointlevels);
            $mform->setType($id_skillbox, PARAM_INT);

            $subskills = $this->_customdata['subskills'][$skill->id];
            foreach( $subskills as $subskill ) {
                $subskillid = $subskill->flagid;
                $id_subskill = 'subskill_'. $subskillid;

                $checkboxes = array();
                $checkboxes[] =& $mform->createElement('static', 'mark_desc', '', '[Mark]');
                $checkboxes[] =& $mform->createElement('advcheckbox', 'check', '', '', null, array(0,1));
                $checkboxes[] =& $mform->createElement('static', 'warn_desc', '', '[Warn]');
                $checkboxes[] =& $mform->createElement('advcheckbox', 'warning', '', '', null, array(0,1));
                $checkboxes[] =& $mform->createElement('static', 'subskill_desc', '', $subskill->name);
                $mform->addGroup($checkboxes, $id_subskill, '');
            }
        }

        $mform->addElement('hidden', 'userid');
        $mform->setType('userid', PARAM_INT);
        $mform->addElement('hidden', 'courseid');
        $mform->setType('courseid', PARAM_INT);

        $this->add_action_buttons();

    }
}