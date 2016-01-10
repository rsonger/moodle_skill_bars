<?php

require_once($CFG->libdir ."/formslib.php");

class skill_bars_editform extends moodleform {

    function definition() {
        $mform =& $this->_form;

        $coursename = $this->_customdata['course'];
        $skills = $this->_customdata['skills'];
        $subskills = $this->_customdata['subskills'];

        // TODO: use get_string() here
        $mform->addElement('static', 'editheader', 'Editing skills for:', $coursename);

        foreach($skills as $skill) {
            $htmlname = 'skillname_'. $skill->id;
            $mform->addElement('header', $htmlname .'_header', 'Skill: '. $skill->name); // TODO: use get_string() here
            $mform->setExpanded($htmlname .'_header');
            $mform->addElement('text', $htmlname, 'Skill name:'); // TODO: use get_string() here
            $mform->setType($htmlname, PARAM_TEXT);

            $showrepeats = 1;
            if( count($subskills[$skill->id]) ) {
                $subskillboxes = array();
                $grouphtmlname = 'subskills_'. $skill->id;

                foreach( $subskills[$skill->id] as $subskill ) {
                    $sbhtmlname = $subskill->id;
                    $subskillboxes[] =& $mform->createElement('text', $sbhtmlname, '', 'size="60"');
                    $mform->setType($grouphtmlname .'['. $sbhtmlname .']', PARAM_TEXT);
                }
                $mform->addGroup($subskillboxes, $grouphtmlname, 'Subskills:', '<br />');
                $showrepeats = 0;
            }

            $newsubskillsname = 'new_subskills_'. $skill->id;
            $repeatelems = array();
            $repeatelems[] = $mform->createElement('text', $newsubskillsname, 'New subskill:', 'size="60"'); //TODO: use get_string() here

            $repeatoptions[$newsubskillsname]['type'] = PARAM_TEXT;

            $this->repeat_elements($repeatelems,
                $showrepeats,
                $repeatoptions,
                'subskill_repeat_'. $skill->id,
                'add_subskill'. $skill->id,
                1,
                'Add a new subskill',
                true);
        }

        $newrepeatelems = array();
        $newrepeatelems[] = $mform->createElement('header', 'new_header', 'New Skill'); // TODO: use get_string() here
        $newrepeatelems[] = $mform->createElement('text', 'new_skill', 'Skill name:'); // TODO: use get_string() here

        $newrepeatoptions['new_header']['expanded'] = true;
        $newrepeatoptions['new_skill']['type'] = PARAM_TEXT;

        $this->repeat_elements($newrepeatelems,
                               0,
                               $newrepeatoptions,
                               'skill_repeat',
                               'add_skill',
                               1,
                               'Create a new skill');

        $mform->addElement('hidden', 'userid');
        $mform->setType('userid', PARAM_INT);
        $mform->addElement('hidden', 'courseid');
        $mform->setType('courseid', PARAM_INT);

        $this->add_action_buttons();
    }
} 