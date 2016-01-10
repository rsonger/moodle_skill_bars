<?php

require_once $CFG->dirroot.'/blocks/skill_bars/lib.php';

class block_skill_bars extends block_list {
    public function init(){
        $this->title = get_string('skill_bars', 'block_skill_bars');
    }

    public function get_content() {
        global $COURSE, $USER;

        $courseid = $COURSE->id;
        $context = context_course::instance($courseid);
        $blockid = $this->instance->id;

        if($this->content !== null) {
            return $this->content;
        }

        $this->content = new stdClass;
        $this->content->footer = "";

        $skills = get_user_skills($USER->id, $courseid);
        $icons = get_skillbar_icons();

        if( count($skills) > 0 && has_capability("block/skill_bars:viewpages", $context) ) {
            $this->content->items[] = array();
            $this->content->icons[] = array();

            for( $i = 0; $i < count($skills); $i++ ) {
                $pts = $skills[$i]->points;
                $this->content->items[$i] = '&nbsp;'. $skills[$i]->name;
                $this->content->icons[$i] = $icons[$pts];
            }
            $profurl = new moodle_url('/blocks/skill_bars/view.php',
                array('blockid' => $blockid, 'courseid' => $courseid));
            $this->content->footer .= html_writer::link($profurl, get_string('gotoprofile', 'block_skill_bars'));
            $this->content->footer .= html_writer::empty_tag('br');

        } else {

            $profurl = new moodle_url('/blocks/skill_bars/view.php',
                array('blockid' => $blockid, 'courseid' => $courseid));
            $this->content->footer .= html_writer::link($profurl, get_string('makeprofile', 'block_skill_bars'));
            $this->content->footer .= html_writer::empty_tag('br');

        }
        if( has_capability("block/skill_bars:managepages", $context) ) {

            $editurl = new moodle_url('/blocks/skill_bars/edit.php',
                array('blockid' => $blockid, 'courseid' => $courseid));
            $updateurl = new moodle_url('/blocks/skill_bars/update.php',
                array('blockid' => $blockid, 'courseid' => $courseid));
            $this->content->footer .= html_writer::link($editurl, get_string('editskills', 'block_skill_bars'));
            $this->content->footer .= html_writer::empty_tag('br');
            $this->content->footer .= html_writer::link($updateurl, get_string('updateskills', 'block_skill_bars'));
            $this->content->footer .= html_writer::empty_tag('br');
        }

        return $this->content;
    }

    public function applicable_formats() {
        return array(
            'course-view'    => true,
            'site'           => false,
            'mod'            => false,
            'my'             => false
        );
    }

    public function instance_allow_multiple() {
        return false;
    }

    /*public function specialization(){
        if(! empty($this->config->text)){
            $this->content->text = $this->config->text;
        }
        if(!empty($this->config->title)) {
            $this->title = $this->config->title;
        } else {
            $this->config->title = 'Default title...';
        }

        if(empty($this->config->text)){
            $this->config->text = 'Default text...';
        }
    }*/
}
