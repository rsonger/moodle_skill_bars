<?php

/**
 * Skill Bars block definition
 *
 * @copyright  2015 Robert W. Songer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once $CFG->dirroot.'/blocks/skill_bars/lib.php';

/**
 * Class block_skill_bars
 *
 * @copyright   2015 Robert W. Songer
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class block_skill_bars extends block_list {

    /**
     * Sets the block title
     *
     * @return void
     */

    public function init(){
        $this->title = get_string('skill_bars', 'block_skill_bars');
    }

    /**
     * Creates the block content
     *
     * @return stdClass|stdObject
     */

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

        if( count($skills) > 0 && has_capability("block/skill_bars:viewpages", $context) ) {
            $this->content->items[] = array();
            $this->content->icons[] = array();

            // Set the bar length and max values
            $bar_length = 100;
            $bar_height = 12;
            $bar_max = 100;
            if(isset($this->config)) {
                $bar_length = $this->config->bar_length;
                $bar_height = $this->config->bar_height;
                $bar_max = $this->config->bar_value;
            }

            for( $i = 0; $i < count($skills); $i++ ) {
                $this->content->items[$i] = '&nbsp;'. $skills[$i]->name;
                $this->content->icons[$i] = get_skillbar_icon($skills[$i]->points, $bar_max, $bar_length, $bar_height);
//                $pts = $skills[$i]->points / $bar_max * 100;
//                // TODO: find a better way to apply CSS
//                $bar_attributes = array('style' => 'width: '. $bar_length .'px; height: '. $bar_height .'px; position: relative; border: 1px solid #ccc;');
//                $progress_attributes = array('style' => 'width: '. $pts .'%; height:100%; position: absolute; background-color: orange;');
//                $this->content->items[$i] = '&nbsp;'. $skills[$i]->name;
//                $this->content->icons[$i] = html_writer::start_div('bar_icon', $bar_attributes);
//                $this->content->icons[$i] .= html_writer::start_div('bar_icon_progress', $progress_attributes);
//                $this->content->icons[$i] .= html_writer::end_div() . html_writer::end_div();
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

    /**
     * Defines where the block is allowed
     *
     * @return array
     */

    public function applicable_formats() {
        return array(
            'course-view'    => true,
            'site'           => false,
            'mod'            => false,
            'my'             => false
        );
    }

    /**
     * Prevents multiple instances of the block on a page
     *
     * @return bool
     */

    public function instance_allow_multiple() {
        return false;
    }

    public function specialization(){
        if(isset($this->config->title) && trim($this->config->title) != '') {
            $this->title = format_string($this->config->title);
        }
    }

    public function has_config() {
        return true;
    }
}
