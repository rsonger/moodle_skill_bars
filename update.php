<?php

require_once('../../config.php');
require_once('lib.php');
require_once('skill_bars_updateform.php');

global $DB, $OUTPUT, $PAGE;

//Check for all the required variables
$courseid = required_param('courseid', PARAM_INT);
$blockid = required_param('blockid', PARAM_INT);

if (!$course = get_course($courseid)) {
    print_error('invalidcourse', 'block_skill_bars', $courseid);
}

require_login($course);

$context = context_course::instance($courseid);

require_capability('block/skill_bars:viewpages', $context);
require_capability('block/skill_bars:managepages', $context);

// Next look for optional variables.
$userid = optional_param('userid', 0, PARAM_INT);

$PAGE->set_url('/blocks/skill_bars/update.php', array('blockid' => $blockid, 'courseid' => $courseid));
$PAGE->set_pagelayout('standard');
$PAGE->set_heading(get_string('updatepage', 'block_skill_bars'));

$settingsnode = $PAGE->settingsnav->add(get_string('pluginsettings', 'block_skill_bars'));

// Handle Administration menu items
$profurl = new moodle_url('/blocks/skill_bars/view.php', array('blockid' => $blockid, 'courseid' => $courseid,));
$profilenode = $settingsnode->add(get_string('profile', 'block_skill_bars'), $profurl);
$profilenode->make_active();
$editurl = new moodle_url('/blocks/skill_bars/edit.php', array('blockid' => $blockid, 'courseid' => $courseid,));
$editnode = $settingsnode->add(get_string('editpage', 'block_skill_bars'), $editurl);
$editnode->make_active();
$updateurl = new moodle_url('/blocks/skill_bars/update.php', array('blockid' => $blockid, 'courseid' => $courseid,));
$updatenode = $settingsnode->add(get_string('updatepage', 'block_skill_bars'), $updateurl);
$updatenode->make_active();

// Pull necessary data from the block configuration
$block = $DB->get_record('block_instances', array('id'=>$blockid), '*', MUST_EXIST);
$block = block_instance('skill_bars', $block);
$max_value = 100;
if(isset($block->config)) {
    $max_value = $block->config->bar_value;
}

if( $userid ) {
    // Generate form content for updating individual user details
    $user_subskills = array();
    $user_fullname = $DB->get_field('user', 'firstname', array('id' => $userid)) ." ".
                     $DB->get_field('user', 'lastname', array('id' => $userid));

    // Build array of existing data from the database
    $toform = array();
    $toform['userid'] = $userid;
    $toform['blockid'] = $blockid;
    $toform['courseid'] = $courseid;
    $skills = get_user_skills($userid, $courseid);

    foreach( $skills as $skill ) {
        $id_skill = 'skill_'. $skill->pointsid;
        $toform[$id_skill] = $skill->points;

        $subskills = get_user_subskills_by_skill($skill->id, $userid);
        $user_subskills[$skill->id] = $subskills;

        foreach( $subskills as $subskill ) {
            if( $subskill->mark ) {
                $id_subskill_check = 'subskill_'. $subskill->flagid .'[check]';
                $toform[$id_subskill_check] = '1';
            }
            if( $subskill->warning ) {
                $id_subskill_warn = 'subskill_'. $subskill->flagid .'[warning]';
                $toform[$id_subskill_warn] = '1';
            }
        }
    }

    $user_skills = new skill_bars_updateform(null, array('user' => $user_fullname,
                                                         'skills' => $skills,
                                                         'subskills' => $user_subskills,
                                                         'skillmax' => $max_value));

    // Process display options
    if($user_skills->is_cancelled()) {
        $returnurl = new moodle_url('/blocks/skill_bars/update.php', array('blockid' => $blockid, 'courseid' => $courseid));
        redirect($returnurl);
    } else if ($postdata = (array)($user_skills->get_data())) {
        $returnurl = new moodle_url('/blocks/skill_bars/update.php', array('blockid' => $blockid, 'courseid' => $courseid));

        $skillsdata = array();
        $subskillscheckdata = array();
        $subskillswarndata = array();

        while( list($key, $value) = each($postdata) ) {
            $token = strtok($key, '_');
            if( $token == 'skill' ) {
                $skillid = strtok('_');
                $skillsdata[$skillid] = $value;
            } elseif( $token == 'subskill' ) {
                $subskillid = strtok('_');
                $subskillscheckdata[$subskillid] = $value['check'];
                $subskillswarndata[$subskillid] = $value['warning'];
            }
        }

        if( !skillbars_update_skill_points($skillsdata) ) {
            print_error('Yikes!'); //TODO: use get_string() here
        }
        if( !skillbars_update_subskill_flags($subskillscheckdata, $subskillswarndata) ) {
            print_error('Double Yikes!');  //TODO: use get_string() here
        }
        redirect($returnurl);
    } else {
        // form didn't validate or this is the first display
        $user_skills->set_data($toform);

        //TODO: Use own renderer here
        echo $OUTPUT->header();
        $user_skills->display();
        echo $OUTPUT->footer();
    }
} else {
    // Generate overview table of all users
    $table = block_print_user_table($blockid, $courseid, $max_value, true);

    //TODO: Use own renderer here
    echo $OUTPUT->header();
    echo $table;
    echo $OUTPUT->footer();
}