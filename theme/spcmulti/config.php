<?php
/**
 * @package   spcmulti
  */
$THEME->name = 'spcmulti';
$THEME->parents = array('base');
// Stylesheets
$THEME->sheets = array(
    'pagelayout',   /** Must come first: Page layout **/
    'core',         /** Must come second: General styles **/
    'admin',
    'blocks',
    'calendar',
    'course',
    'dock',
    'grade',
    'message',
    'question',
    'user',
    'filemanager',
    'spc',
    'styles_layout',
    'styles_fonts',
    'styles_color'
);
$THEME->layouts = array(
    'base' => array(
        'file' => 'standard.php',
        'regions' => array(),
    ),
    'standard' => array(
        'file' => 'standard.php',
        'regions' => array('side-pre', 'side-post'),
        'defaultregion' => 'side-post',
    ),
    'course' => array(
        'file' => 'course.php',
        'regions' => array('side-pre', 'side-post'),
        'defaultregion' => 'side-pre'
    ),
    'incourse' => array(
        'file' => 'course.php',
        'regions' => array('side-pre'),
        'defaultregion' => 'side-pre',
    ),
    'coursecategory' => array(
        'file' => 'standard.php',
        'regions' => array('side-pre', 'side-post'),
        'defaultregion' => 'side-pre',
    ),
    'question' => array(
        // DVH Nov 19 2013: Why exactly?
        //'file' => 'question.php',
        'file' => 'course.php',
        'regions' => array('side-pre', 'side-post'),
        'defaultregion' => 'side-pre',
    ),
    'frontpage' => array(
        'file' => 'standard.php',
        'regions' => array('side-pre', 'side-post'),
        'defaultregion' => 'side-post',
    ),
    'admin' => array(
        'file' => 'standard.php',
        'regions' => array('side-pre'),
        'defaultregion' => 'side-pre',
    ),
    'mydashboard' => array(
        'file' => 'standard.php',
        'regions' => array('side-pre', 'side-post'),
        'defaultregion' => 'side-post',
        'options' => array('langmenu'=>true),
    ),
    'mypublic' => array(
        'file' => 'standard.php',
        'regions' => array('side-pre', 'side-post'),
        'defaultregion' => 'side-post',
    ),
    'login' => array(
        'file' => 'standard.php',
        'regions' => array(),
        'options' => array('langmenu'=>true),
    ),
    'popup' => array(
        'file' => 'standard.php',
        'regions' => array(),
        'options' => array('nofooter'=>true),
    ),
    'frametop' => array(
        'file' => 'standard.php',
        'regions' => array(),
        'options' => array('nofooter'=>true),
    ),
    'maintenance' => array(
        'file' => 'standard.php',
        'regions' => array(),
        'options' => array('nofooter'=>true, 'nonavbar'=>true),
    ),
    'print' => array(
        'file' => 'standard.php',
        'regions' => array(),
        'options' => array('nofooter'=>true, 'nonavbar'=>false),
    ),
);
 
/** List of javascript files that need to be included on each page */
$THEME->javascripts = array('jquery-1.9.1','jquery.simplemodal.1.4.4.min', 'spc.functions');
$THEME->javascripts_footer = array();

// Tell Moodle to look for overridden renderers in the renderer.php file.
$THEME->rendererfactory = 'theme_overridden_renderer_factory';
