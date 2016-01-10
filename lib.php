<?php

function get_user_skills($userid, $courseid) {
    global $DB;

    // TODO: MUST USE MOODLE DATA MANIPULATION API
    $sql = "SELECT sk.id,
                   sk.name,
                   skpts.id as pointsid,
                   skpts.points
            FROM {skill_points} skpts
            INNER JOIN {skill} sk ON sk.id = skpts.skillid
            INNER JOIN {course_skill} crsk ON crsk.skillid = skpts.skillid
            WHERE skpts.userid = :userid
              AND skpts.courseid = :courseid";
    $sqlparams['userid'] = $userid;
    $sqlparams['courseid'] = $courseid;

    $skills = array_values($DB->get_records_sql($sql, $sqlparams));

    return $skills;
}

function get_user_subskills_by_skill($skillid, $userid) {
    global $DB;

    // TODO: MUST USE MOODLE DATA MANIPULATION API
    $sql = "SELECT sbsk.id,
                   sbsk.name,
                   sbskflg.id AS flagid,
                   sbskflg.mark,
                   sbskflg.warning
            FROM {subskill_flags} sbskflg
            INNER JOIN {subskill} sbsk ON sbsk.id = sbskflg.subskillid
            WHERE sbsk.skillid = :skillid
              AND sbskflg.userid = :userid";
    $sqlparams['skillid'] = $skillid;
    $sqlparams['userid'] = $userid;

    $subskills = array_values($DB->get_records_sql($sql, $sqlparams));

    return $subskills;
}

function get_course_skills($courseid) {
    global $DB;

    // TODO: MUST USE MOODLE DATA MANIPULATION API
    $sql = "SELECT sk.id,
                   sk.name
            FROM {skill} sk
            INNER JOIN {course_skill} crsk ON crsk.skillid = sk.id
            WHERE crsk.courseid = :courseid";
    $sqlparams['courseid'] = $courseid;

    $skills = array_values($DB->get_records_sql($sql, $sqlparams));

    return $skills;
}

function skillbars_get_course_subskills($courseid) {
    global $DB;

    $subskills = array_values($DB->get_records('subskill', array('courseid' => $courseid)));

    return $subskills;
}

function print_skill_bars_profile($userid, $courseid, $return = false) {
    $profile = '';
    $skills = get_user_skills($userid, $courseid);

    $profile .= '<h1>'. get_string('skillsection', 'block_skill_bars') .'</h1>';
    $images = get_skillbar_images();

    foreach( $skills as $skill ) {
        $pts = $skill->points;
        $subskills = get_user_subskills_by_skill($skill->id,$userid);

        $profile .= '<br /><p><strong>'. $skill->name .'</strong></p>';
        $profile .= $images[$pts];
        $profile .= '<br /><br />';

        $list = '<ul class="unlist">';
        $listItem = '<li>%s%s%s</li>';
        for( $k = 0; $k < count($subskills); $k++ ) {
            $warning ='';
            $check = '&#9744 ';
            if( $subskills[$k]->warning > 0 ) {
                $warning = ' <span class="warning">(!)</span>';
            }
            if( $subskills[$k]->mark > 0 ) {
                $check = '&#9745 ';
            }

            $list .= sprintf($listItem, $check, $subskills[$k]->name, $warning);
        }
        $list .= '</ul>';

        $profile .= $list;
    }

    if( $return ) {
        return $profile;
    } else {
        echo $profile;
    }
}

function block_print_userlist_page($courseid, $return = false) {

    $context = context_course::instance($courseid);
    $userfields = 'u.id, u.firstname, u.lastname';
    $userlist = get_enrolled_users($context, 'block/skill_bars:viewpages', 0, $userfields);
    $skillslist = get_course_skills($courseid);
    $skillbars = get_skillbar_icons();

    $headers = array('User');
    foreach( $skillslist as $skill ) {
        $headers[] = $skill->name;
    }

    $showlist = new html_table();
    $showlist->head = $headers;
    $showlistdata = array();


    foreach( $userlist as $user ) {

        $fullname = $user->firstname ." ". $user->lastname;
        $skills = get_user_skills($user->id, $courseid);
        $record = array();

        if( count($skills) ) {
            $url = new moodle_url('/blocks/skill_bars/update.php', array('courseid' => $courseid, 'userid' => $user->id));
            $link = html_writer::link($url, $fullname);
            $record[] = $link;

            foreach( $skills as $skill ) {
                $pts = $skill->points;
                $record[] = $skillbars[$pts];
            }
        } else {
            $record[] = $fullname;
            foreach( $skillslist as $skill ) {
                $record[] = '';
            }
        }
        $showlistdata[] = $record;
    }

    $showlist->data = $showlistdata;

    if( $return ) {
        return html_writer::table($showlist);
    } else {
        echo html_writer::table($showlist);
    }
}
/**
 * @return array of small-size skill bars associated with the point values of each array index
 */
function get_skillbar_icons() {
    global $CFG;
    $icons[] = array();

    for( $i = 0; $i < 9; $i++ ) {   // must abstract out the total skillbar point value to config
        $source = $CFG->wwwroot ."/blocks/skill_bars/pix/SmallSkillBar". $i .".png";
        $icons[$i] = html_writer::tag('img', '', array('src' => $source));
    }

    return $icons;
}

/**
 * @return array of full-size skill bars associated with the point values of each array index
 */
function get_skillbar_images() {
    global $CFG;
    $icons[] = array();

    for( $i = 0; $i < 9; $i++ ) {
        $source = $CFG->wwwroot ."/blocks/skill_bars/pix/SkillBar". $i .".png";
        $icons[$i] = html_writer::tag('img', '', array('src' => $source));
    }

    return $icons;
}

function skillbars_update_skill_names($skills) {
    global $DB;

    $data = new stdClass();
    while( list($skillid, $name) = each($skills) ) {
        $data->id = $skillid;
        $data->name = $name;

        $DB->update_record('skill', $data, true);
    }

    return true;
}

function skillbars_update_subskill_names($subskills) {
    global $DB;

    $data = new stdClass();
    while( list($subskillid, $name) = each($subskills) ) {
        $data->id = $subskillid;
        $data->name = $name;

        $DB->update_record('subskill', $data, true);
    }

    return true;
}

function skillbars_update_skill_points($skills) {
    global $DB;

    $data = new stdClass();
    while( list($skillid, $pts) = each($skills) ) {
        $data->id = $skillid;
        $data->points = $pts;

        $DB->update_record('skill_points', $data, true);
    }

    return true;
}

function skillbars_update_subskill_flags($checks, $warnings) {
    global $DB;

    $data = new stdClass();
    foreach( array_keys($checks) as $subskillid ) {
        $data->id = $subskillid;
        $data->mark = $checks[$subskillid];
        $data->warning = $warnings[$subskillid];

        $DB->update_record('subskill_flags', $data, true);
    }

    return true;
}

function skillbars_add_new_course_skill($courseid, $skillname) {
    global $DB;

    $data_skill = new stdClass();
    $data_skill->name = $skillname;

    $skillid = $DB->insert_record('skill', $data_skill);

    $data_courseskill = new stdClass();
    $data_courseskill->courseid = $courseid;
    $data_courseskill->skillid = $skillid;

    $DB->insert_record('course_skill', $data_courseskill, false, true);

    // give default values for this skill to students who already initiated profiles
    $users = array_values($DB->get_records('skill_points', array('courseid' => $courseid), '', 'DISTINCT userid'));
    if( count( $users ) ) {
        foreach( $users as $user ) {
            $data = new stdClass();
            $data->courseid = $courseid;
            $data->userid = $user->userid;
            $data->skillid = $skillid;
            $data->points = 2; // TODO: extract this default value to a config setting

            $DB->insert_record('skill_points', $data, false, true);
        }
    }

    return true;
}

function skillbars_add_new_subskill_to_skill($courseid, $skillid, $subskill) {
    global $DB;

    $data_subskill = new stdClass();
    $data_subskill->courseid = $courseid;
    $data_subskill->skillid = $skillid;
    $data_subskill->name = $subskill;

    $subskillid = $DB->insert_record('subskill', $data_subskill);

    // add records to subskill_flags for students who already initiated profiles
    $sql = "SELECT DISTINCT sbskflg.userid
            FROM {subskill_flags} sbskflg
            INNER JOIN {subskill} sbsk ON sbsk.id = sbskflg.subskillid
            WHERE sbsk.courseid = :courseid";
    $params['courseid'] = $courseid;

    $users = array_values($DB->get_records_sql($sql, $params));
    if( count($users) ) {
        foreach( $users as $user ) {
            $data = new stdClass();
            $data->subskillid = $subskillid;
            $data->userid = $user->userid;

            $DB->insert_record('subskill_flags', $data, false, true);
        }
    }
}

function skillbars_add_new_skills_for_user($userid, $courseid, $skillpoints) {
    global $DB;

    foreach( array_keys($skillpoints) as $skillid) {
        $data_skillpoints = new stdClass();
        $data_skillpoints->userid = &$userid;
        $data_skillpoints->courseid = $courseid;
        $data_skillpoints->skillid = $skillid;
        $data_skillpoints->points = $skillpoints[$skillid];

        $DB->insert_record('skill_points', $data_skillpoints, false, true);

        $subskills = $DB->get_records('subskill', array('skillid' => $skillid, 'courseid' => $courseid));

        foreach( $subskills as $subskill ) {
            $data_subskillflags = new stdClass();
            $data_subskillflags->subskillid = $subskill->id;
            $data_subskillflags->userid = $userid;

            $DB->insert_record('subskill_flags', $data_subskillflags, false, true);
        }
    }

    return true;
}

function skillbars_delete_course_skill($courseid, $skillid) {
    global $DB;


    skillbars_delete_skill_subskills($courseid, $skillid);
    $DB->delete_records('skill_points', array('skillid' => $skillid, 'courseid' => $courseid));
    $DB->delete_records('course_skill', array('skillid' => $skillid, 'courseid' => $courseid));

    if( !$DB->count_records('course_skill', array('skillid' => $skillid)) ) {
        // no other courses use the deleted skill, so we can scrap all traces of it
        $DB->delete_records('skill', array('id' => $skillid));
    }

    return true;
}

function skillbars_delete_skill_subskills($courseid, $skillid) {
    global $DB;

    $subskills = array_keys($DB->get_records('subskill', array('skillid' => $skillid, 'courseid' => $courseid)));

    foreach( $subskills as $subskillid ) {
        skillbars_delete_subskill($subskillid);
    }

    return true;
}

function skillbars_delete_subskill($subskillid) {
    global $DB;

    $DB->delete_records('subskill_flags', array('subskillid' => $subskillid));
    $DB->delete_records('subskill', array('id' => $subskillid));

    return true;
}