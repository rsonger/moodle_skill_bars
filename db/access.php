<?php

    defined('MOODLE_INTERNAL') || die();

    $capabilities = array(

        'block/skill_bars:viewpages' => array(
            'captype' => 'read',
            'contextlevel' => CONTEXT_COURSE,
            'legacy' => array(
                'guest' => CAP_PREVENT,
                'student' => CAP_ALLOW,
                'teacher' => CAP_ALLOW,
                'editingteacher' => CAP_ALLOW,
                'coursecreator' => CAP_ALLOW,
                'manager' => CAP_ALLOW
            )
        ),

        'block/skill_bars:makeprofile' => array(
            'captype' => 'read',
            'contextlevel' => CONTEXT_COURSE,
            'legacy' => array(
                'guest' => CAP_PREVENT,
                'student' => CAP_ALLOW,
                'teacher' => CAP_PREVENT,
                'editingteacher' => CAP_PREVENT,
                'coursecreator' => CAP_PREVENT,
                'manager' => CAP_PREVENT,
            )
        ),

        'block/skill_bars:managepages' => array(
            'captype' => 'read',
            'contextlevel' => CONTEXT_COURSE,
            'legacy' => array (
                'guest' => CAP_PREVENT,
                'student' => CAP_PREVENT,
                'teacher' => CAP_PREVENT,
                'editingteacher' => CAP_ALLOW,
                'coursecreator' => CAP_ALLOW,
                'manager' => CAP_ALLOW
            )
        ),

        'block/skill_bars:myaddinstance' => array(
            'captype' => 'write',
            'contextlevel' => CONTEXT_SYSTEM,
            'archetypes' => array(
                'user' => CAP_PREVENT
            ),

            'clonepermissionsfrom' => 'moodle/my:manageblocks'
        ),

        'block/skill_bars:addinstance' => array(
            'riskbitmask' => RISK_SPAM | RISK_XSS,

            'captype' => 'write',
            'contextlevel' => CONTEXT_BLOCK,
            'archetypes' => array(
                'editingteacher' => CAP_ALLOW,
                'manager' => CAP_ALLOW
            ),

            'clonepermissionsfrom' => 'moodle/site:manageblocks'
        )
    );