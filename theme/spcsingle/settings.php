<?php

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {

    // Logo file setting
    $name = 'theme_spcsingle/logo';
    $title = get_string('logo','theme_spcsingle');
    $description = get_string('logodesc', 'theme_spcsingle');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default, PARAM_URL);
    $settings->add($setting);

    // related PD
    $name = 'theme_spcsingle/related_pd';
    $title = get_string('related_pd','theme_spcsingle');

    $default = '';
    $setting = new admin_setting_configtext($name, $title, $default, PARAM_URL);
    $settings->add($setting);
    
    // Forum i.e "dicuss"
    $name = 'theme_spcsingle/discuss';
    $title = get_string('discuss','theme_spcsingle');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $default, PARAM_URL);
    $settings->add($setting);
    // admin_setting_confightmleditor
    // Custom CSS file
    $name = 'theme_spcsingle/customcss';
    $title = get_string('customcss','theme_spcsingle');
    $description = get_string('customcssdesc', 'theme_spcsingle');
    $default = '';
    $setting = new admin_setting_configtextarea($name, $title, $description, $default);
    $settings->add($setting);

}
