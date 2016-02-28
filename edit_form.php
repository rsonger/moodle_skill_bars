<?php
/**
 * Skill Bars Edit Form class definition
 *
 * @copyright  2015 Robert W. Songer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class block_skill_bars_edit_form extends block_edit_form {

    protected function specific_definition($mform) {
        // Set the section header from the language file
        $mform->addElement('header','configheader', get_string('blocksettings','block'));

        // Add a configuration for the block title
        $mform->addElement('text', 'config_title', get_string('blocktitle', 'block_skill_bars'));
        $mform->setDefault('config_title', '');//get_string('skill_bars', 'block_skill_bars'));
        $mform->setType('config_title', PARAM_TEXT);

        // Add configurations for bar length and value
        // TODO: use get_string() on each field below
        $attributes = 'size="5"';
        $mform->addElement('text', 'config_bar_length', 'Small bar length (in pixels)', $attributes);
        $mform->setDefault('config_bar_length', 100);
        $mform->setType('config_bar_length', PARAM_INT);

        $mform->addElement('text', 'config_bar_height', 'Small bar height (in pixels)', $attributes);
        $mform->setDefault('config_bar_height', 12);
        $mform->setType('config_bar_height', PARAM_INT);

        $mform->addElement('text', 'config_bar_value', 'Max skill value', $attributes);
        $mform->setDefault('config_bar_value', 100);
        $mform->setType('config_bar_value', PARAM_INT);

        $mform->addElement('text', 'config_init_value', 'Initial skill value', $attributes);
        $mform->setDefault('config_init_value', 20);
        $mform->setType('config_init_value', PARAM_INT);

        $mform->addElement('text', 'config_strong_value', 'Initial strong skill value', $attributes);
        $mform->setDefault('config_strong_value', 30);
        $mform->setType('config_strong_value', PARAM_INT);

        $mform->addElement('text', 'config_weak_value', 'Initial weak skill value', $attributes);
        $mform->setDefault('config_weak_value', 10);
        $mform->setType('config_weak_value', PARAM_INT);
    }
} 