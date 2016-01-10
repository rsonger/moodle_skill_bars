<?php

require_once('../../config.php');
require_once('lib.php');
require_once('skill_bars_editform.php');

global $DB, $OUTPUT, $PAGE;

//Check for all the required variables
$courseid = required_param('courseid', PARAM_INT);

if (!$course = get_course($courseid)) {//$DB->get_record('course', array('id' => $courseid))) {
    print_error('invalidcourse', 'block_skill_bars', $courseid);
}

require_login($course);

$context = context_course::instance($courseid);

// Next look for optional variables.
$id = optional_param('id', 0, PARAM_INT);
$userid = optional_param('userid', 0, PARAM_INT);

$PAGE->set_url('/blocks/skill_bars/edit.php', array('courseid' => $courseid));
$PAGE->set_pagelayout('standard');
$PAGE->set_heading(get_string('editskills', 'block_skill_bars'));

$settingsnode = $PAGE->settingsnav->add(get_string('pluginsettings', 'block_skill_bars'));

if( has_capability('block/skill_bars:viewpages', $context) && has_capability('block/skill_bars:managepages', $context) ) {

    // Handle Administration menu items
    $profurl = new moodle_url('/blocks/skill_bars/view.php', array('id' => $id, 'courseid' => $courseid,));
    $profilenode = $settingsnode->add(get_string('profile', 'block_skill_bars'), $profurl);
    $profilenode->make_active();
    $editurl = new moodle_url('/blocks/skill_bars/edit.php', array('id' => $id, 'courseid' => $courseid,));
    $editnode = $settingsnode->add(get_string('editpage', 'block_skill_bars'), $editurl);
    $editnode->make_active();
    $updateurl = new moodle_url('/blocks/skill_bars/update.php', array('id' => $id, 'courseid' => $courseid,));
    $updatenode = $settingsnode->add(get_string('updatepage', 'block_skill_bars'), $updateurl);
    $updatenode->make_active();

    $course_skills = get_course_skills($courseid);
    $course_subskills = skillbars_get_course_subskills($courseid);
    $coursename = $DB->get_field('course', 'fullname', array('id' => $courseid));

    $toform = array();
    $toform['userid'] = $userid;
    $toform['courseid'] = $courseid;

    $subskills = array();
    if( count($course_skills) ) {
        // Build array of existing data from the database
        foreach( $course_skills as $skill ) {
            $toform['skillname_'. $skill->id] = $skill->name;
            $subskills[$skill->id] = array();
        }
        foreach( $course_subskills as $subskill ) {
            $toform['subskills_'. $subskill->skillid .'['. $subskill->id .']'] = $subskill->name;
            $subskills[$subskill->skillid][] = $subskill;
        }
    }

    $skills_editform = new skill_bars_editform(null, array('course' => $coursename,
                                                           'skills' => $course_skills,
                                                           'subskills' => $subskills));

    // Process display options
    if($skills_editform->is_cancelled()) {
        $courseurl = new moodle_url('/course/view.php', array('id' => $courseid));
        redirect($courseurl);
    } else if ($postdata = (array)($skills_editform->get_data())) {
        $courseurl = new moodle_url('/blocks/skill_bars/edit.php', array('courseid' => $courseid));

        // check for skills to delete first so their subskills don't update
        $emptyskills = array_keys($postdata, '');
        foreach( $emptyskills as $emptyskill ) {
            $token = strtok($emptyskill, '_');

            if( $token == 'skillname' ) {
                $skillid = strtok('_');
                skillbars_delete_course_skill($courseid, $skillid);
                $subskilltoken = 'subskills_'. $skillid;
                if( array_key_exists($subskilltoken, $postdata) ) {
                    unset($postdata[$subskilltoken]);
                }
                unset($postdata[$emptyskill]);
            }

        }

        // now handle skills and subskills to add or update, and subskills to delete
        while( list($key, $value) = each($postdata) ) {
            $token = strtok($key, '_');

            if( $token == 'new' ) {
                $token = strtok('_');
                if( $token == 'subskills' ) {
                    $skillid = strtok('_');
                    $subskills = array();

                    foreach( $value as $subskill ) {
                        if( !empty($subskill) ) {
                            skillbars_add_new_subskill_to_skill($courseid, $skillid, $subskill);
                        }
                    }
                } elseif( $token == 'skill' ) {
                    foreach( $value as $skillname ) {
                        if( !empty($skillname) ) {
                            skillbars_add_new_course_skill($courseid, $skillname);
                        }
                    }
                }
            } elseif( $token == 'skillname' ) {
                $skillid = strtok('_');
                if( !empty($value) ) {
                    skillbars_update_skill_names(array($skillid => $value));
                }
            } elseif( $token == 'subskills') {
                if( !empty($value) ) {
                    while( list($subskillid, $desc) = each($value) ) {
                        if( !empty($desc) ) {
                            skillbars_update_subskill_names(array($subskillid => $desc));
                        } else {
                            skillbars_delete_subskill($subskillid);
                        }
                    }
                }
            }
        }

        redirect($courseurl);
    } else {
        // form didn't validate or this is the first display
        $skills_editform->set_data($toform);

        //TODO: Use own renderer here
        echo $OUTPUT->header();
        $skills_editform->display();
        echo $OUTPUT->footer();
    }


    }