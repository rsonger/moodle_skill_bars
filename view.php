<?php

require_once('../../config.php');
require_once('lib.php');
require_once('skill_bars_newprofile.php');

global $DB, $OUTPUT, $PAGE, $USER;

// Check for all required variables.
$courseid = required_param('courseid', PARAM_INT);
$blockid = required_param('blockid', PARAM_INT);

if (!$course = get_course($courseid)) { // $DB->get_record('course', array('id' => $courseid))) {
    print_error('invalidcourse', 'block_skill_bars', $courseid);
}

require_login($course);

$context = context_course::instance($courseid);

require_capability('block/skill_bars:viewpages', $context);

$userid = $USER->id;

$PAGE->set_url('/blocks/skill_bars/view.php', array('id' => $courseid, 'blockid' => $blockid));
$PAGE->set_pagelayout('standard');
$PAGE->set_heading(get_string('profile', 'block_skill_bars'));

$settingsnode = $PAGE->settingsnav->add(get_string('pluginsettings', 'block_skill_bars'));

// Handle Administration menu items
$profurl = new moodle_url('/blocks/skill_bars/view.php', array('blockid' => $blockid, 'courseid' => $courseid));
$profilenode = $settingsnode->add(get_string('profile', 'block_skill_bars'), $profurl);
$profilenode->make_active();

if( has_capability('block/skill_bars:managepages', $context) ) {

    $editurl = new moodle_url('/blocks/skill_bars/edit.php', array('blockid' => $blockid, 'courseid' => $courseid,));
    $editnode = $settingsnode->add(get_string('editpage', 'block_skill_bars'), $editurl);
    $editnode->make_active();
    $updateurl = new moodle_url('/blocks/skill_bars/update.php', array('blockid' => $blockid, 'courseid' => $courseid,));
    $updatenode = $settingsnode->add(get_string('updatepage', 'block_skill_bars'), $updateurl);
    $updatenode->make_active();

}

// Generate page content
$skills = get_user_skills($userid, $courseid);
$block = $DB->get_record('block_instances', array('id'=>$blockid), '*', MUST_EXIST);
$block = block_instance('skill_bars', $block);
$max_value = 8;
$init_value = 2;
$strong_value = 3;
$weak_value = 1;
if(isset($block->config)) {
    $max_value = $block->config->bar_value;
    $init_value = $block->config->init_value;
    $strong_value = $block->config->strong_value;
    $weak_value = $block->config->weak_value;
}

if( count($skills) ) {

    $skill_bars = print_skill_bars_profile($userid, $max_value, $courseid, true);

    // TODO: Use own renderer here
    echo $OUTPUT->header();
    echo $skill_bars;
    echo $OUTPUT->footer();

} else {

    // TODO: Generate new profile form
    $course_skills = get_course_skills($courseid);
    $user_fullname = $DB->get_field('user', 'firstname', array('id' => $userid)) ." ".
                     $DB->get_field('user', 'lastname', array('id' => $userid));

    $profile = new skill_bars_newprofile(null, array('user' => $user_fullname, 'skills' => $course_skills));

    $toform['userid'] = $userid;
    $toform['blockid'] = $blockid;
    $toform['courseid'] = $courseid;

    if( $profile->is_cancelled() ) {
        $courseurl = new moodle_url('/course/view.php', array('id' => $courseid));
        redirect($courseurl);
    } elseif( $postdata = (array)($profile->get_data()) ) {
        $returnurl = new moodle_url('/blocks/skill_bars/view.php', array('blockid' => $blockid, 'courseid' => $courseid));
        $skillpoints = array();
        $strongid = 0;
        $weakid = 0;

        if (isset($postdata['strongrad'])) {
            $strongid = $postdata['strongrad']['strong'];
        }
        if (isset($postdata['weakrad'])) {
            $weakid = $postdata['weakrad']['weak'];
        }

        foreach( $course_skills as $skill ) {
            if( $skill->id == $strongid && $skill->id != $weakid ) {
                $skillpoints[$skill->id] = $strong_value;
            } elseif( $skill->id == $weakid && $skill->id != $strongid ) {
                $skillpoints[$skill->id] = $weak_value;
            } else {
                $skillpoints[$skill->id] = $init_value;
            }
        }

        if( !skillbars_add_new_skills_for_user($userid, $courseid, $skillpoints) ) {
            print_error('Yikes!'); //TODO: use get_string() here
        }

        redirect($returnurl);
    } else {
        $profile->set_data($toform);

        // TODO: Use own renderer here
        echo $OUTPUT->header();
        $profile->display();
        echo $OUTPUT->footer();

    }
}