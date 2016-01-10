<?php

require_once('../../config.php');
require_once('lib.php');
require_once('skill_bars_newprofile.php');

global $DB, $OUTPUT, $PAGE, $USER;

// Check for all required variables.
$courseid = required_param('courseid', PARAM_INT);

if (!$course = get_course($courseid)) { // $DB->get_record('course', array('id' => $courseid))) {
    print_error('invalidcourse', 'block_skill_bars', $courseid);
}

require_login($course);

$userid = $USER->id;
$context = context_course::instance($courseid);

$PAGE->set_url('/blocks/skill_bars/view.php', array('id' => $courseid));
$PAGE->set_pagelayout('standard');
$PAGE->set_heading(get_string('profile', 'block_skill_bars'));

$settingsnode = $PAGE->settingsnav->add(get_string('pluginsettings', 'block_skill_bars'));

if( has_capability('block/skill_bars:viewpages', $context) ) {

    // Handle Administration menu items
    $profurl = new moodle_url('/blocks/skill_bars/view.php',
        array('courseid' => $courseid,));
    $profilenode = $settingsnode->add(get_string('profile', 'block_skill_bars'), $profurl);
    $profilenode->make_active();

    if( has_capability('block/skill_bars:managepages', $context) ) {

        $editurl = new moodle_url('/blocks/skill_bars/edit.php', array('courseid' => $courseid,));
        $editnode = $settingsnode->add(get_string('editpage', 'block_skill_bars'), $editurl);
        $editnode->make_active();
        $updateurl = new moodle_url('/blocks/skill_bars/update.php', array('courseid' => $courseid,));
        $updatenode = $settingsnode->add(get_string('updatepage', 'block_skill_bars'), $updateurl);
        $updatenode->make_active();

    }

    // Generate page content
    $skills = get_user_skills($userid, $courseid);
    if( count($skills) ) {

        $skill_bars = print_skill_bars_profile($userid, $courseid, true);

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
        $toform['courseid'] = $courseid;

        if( $profile->is_cancelled() ) {
            $courseurl = new moodle_url('/course/view.php', array('id' => $courseid));
            redirect($courseurl);
        } elseif( $postdata = (array)($profile->get_data()) ) {
            $returnurl = new moodle_url('/blocks/skill_bars/view.php', array('courseid' => $courseid));
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
                    $skillpoints[$skill->id] = 3;
                } elseif( $skill->id == $weakid && $skill->id != $strongid ) {
                    $skillpoints[$skill->id] = 1;
                } else {
                    $skillpoints[$skill->id] = 2;
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
}