<?php
/**
 * Prints a particular instance of spcdata
 *
 * You can have a rather longer description of the file as well,
 * if you like, and it can span multiple lines.
 *
 * @package    mod
 * @subpackage spcdata
 * @copyright  PBS
 */

/// (Replace spcdata with the name of your module and remove this line)

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');

$id = optional_param('id', 0, PARAM_INT); // course_module ID, or
$n  = optional_param('n', 0, PARAM_INT);  // spcdata instance ID - it should be named as the first character of the module

if ($id) {
    // 2.8 Support
    list ($course, $cm) = get_course_and_cm_from_cmid($id, 'spcdata');
    $spcdata = $DB->get_record('spcdata', array('id'=> $cm->instance), '*', MUST_EXIST);
    //$cm         = get_coursemodule_from_id('spcdata', $id, 0, false, MUST_EXIST);
    //$course     = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    //$spcdata  = $DB->get_record('spcdata', array('id' => $cm->instance), '*', MUST_EXIST);
} elseif ($n) {
    // 2.8 Support
    list ($course, $cm) = get_course_and_cm_from_cmid($id, 'spcdata');
    $spcdata = $DB->get_record('spcdata', array('id'=> $n), '*', MUST_EXIST);
    //$spcdata  = $DB->get_record('spcdata', array('id' => $n), '*', MUST_EXIST);
    //$course     = $DB->get_record('course', array('id' => $spcdata->course), '*', MUST_EXIST);
    //$cm         = get_coursemodule_from_instance('spcdata', $spcdata->id, $course->id, false, MUST_EXIST);
} else {
    error('You must specify a course_module ID or an instance ID');
}

require_login($course, true, $cm);
$context = get_context_instance(CONTEXT_MODULE, $cm->id);

add_to_log($course->id, 'spcdata', 'view', "view.php?id={$cm->id}", $spcdata->name, $cm->id);

/// Print the page header

$PAGE->set_url('/mod/spcdata/view.php', array('id' => $cm->id));
$PAGE->set_title(format_string($spcdata->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

// other things you may want to set - remove if not needed
//$PAGE->set_cacheable(false);
//$PAGE->set_focuscontrol('some-html-id');
//$PAGE->add_body_class('spcdata-'.$somevar);

// Output starts here
echo $OUTPUT->header();

if ($spcdata->intro) { // Conditions to show the intro can change to look for own settings or whatever
    echo $OUTPUT->box(format_module_intro('spcdata', $spcdata, $cm->id), 'generalbox mod_introbox', 'spcdataintro');
}

// Replace the following lines with you own code
echo $OUTPUT->heading('Yay! It works!');

// Finish the page
echo $OUTPUT->footer();
