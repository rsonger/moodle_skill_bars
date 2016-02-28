<?php

/**
 * Skill bars block settings
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {

    $settings->add(new admin_setting_configcolourpicker('block_skill_bars/progress_color',
            get_string('progress_color', 'block_skill_bars'),
            get_string('progress_color_desc', 'block_skill_bars'),
            get_string('progress_default_color', 'block_skill_bars'),
            null)
    );
}