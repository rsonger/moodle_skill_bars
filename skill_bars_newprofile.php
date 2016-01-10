<?php

require_once($CFG->libdir ."/formslib.php");

class skill_bars_newprofile extends moodleform {
    
    function definition() {
        $mform =& $this->_form;

        // TODO: use get_string() here.
        $mform->addElement('static', 'user', 'New profile for:', $this->_customdata['user']);

        $skills = $this->_customdata['skills'];

        if( count($skills) ) {
            // TODO: use get_string() here.
            $mform->addElement('static', 'description', null,
                               'Below are the skills for this course. Please choose one for your strong skill and one for your weak skill.');

            $strongskills = array();
            foreach( $skills as $skill ) {
                $strongskills[] =& $mform->createElement('static', 'skillname', '', $skill->name);
                $strongskills[] =& $mform->createElement('radio', 'strong', '', '', $skill->id, null);
            }
            $mform->addGroup($strongskills, 'strongrad', 'Strong skill:'); // TODO: use get_string() here

            $weakskills = array();
            foreach( $skills as $skill ) {
                $weakskills[] =& $mform->createElement('static', 'skillname', '', $skill->name);
                $weakskills[] =& $mform->createElement('radio', 'weak', '', '', $skill->id, null);
            }
            $mform->addGroup($weakskills, 'weakrad', 'Weak skill:');  // TODO: use get_string() here

            $mform->addElement('hidden', 'userid');
            $mform->setType('userid', PARAM_INT);
            $mform->addElement('hidden', 'courseid');
            $mform->setType('courseid', PARAM_INT);

            $this->add_action_buttons();
        } else {
            //TODO: use get_string here
            $mform->addElement('static', 'description', null, 'No skills have been set for this course yet.');
        }
    }

}
