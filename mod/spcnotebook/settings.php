<?php

$settings->add(new admin_setting_configselect('spcnotebook/showrecentactivity', get_string('showrecentactivity', 'spcnotebook'),
                                              get_string('showrecentactivity', 'spcnotebook'), 0,
                                              array('0' => get_string('no'), '1' => get_string('yes'))));

$settings->add(new admin_setting_configselect('spcnotebook/overview', get_string('showoverview', 'spcnotebook'),
                                              get_string('showoverview', 'spcnotebook'), 1,
                                              array('0' => get_string('no'), '1' => get_string('yes'))));
