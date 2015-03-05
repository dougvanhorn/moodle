<?php  // $Id: view.php,v 1.3 1213/05/30 15:20:30 jacolina from journal.php by davmon Exp $

require_once("../../config.php");
require_once("lib.php");


$id = required_param('id', PARAM_INT);    // Course Module ID

if (! $cm = get_coursemodule_from_id('spcnotebook', $id)) {
    print_error("Course Module ID was incorrect");
}

if (! $course = $DB->get_record("course", array('id' => $cm->course))) {
    print_error("Course is misconfigured");
}

$context = get_context_instance(CONTEXT_MODULE, $cm->id);

require_login($course->id, true, $cm);

$entriesmanager = has_capability('mod/spcnotebook:manageentries', $context);
$canadd = has_capability('mod/spcnotebook:addentries', $context);

if (!$entriesmanager && !$canadd) {
    print_error('accessdenied', 'spcnotebook');
}

if (! $spcnotebook = $DB->get_record("spcnotebook", array("id" => $cm->instance))) {
    print_error("Course module is incorrect");
}

if (! $cw = $DB->get_record("course_sections", array("id" => $cm->section))) {
    print_error("Course module is incorrect");
}


// Header
$PAGE->set_url('/mod/spcnotebook/view.php', array('id'=>$id));
$PAGE->navbar->add(format_string($spcnotebook->name));
$PAGE->set_title(format_string($spcnotebook->name));
$PAGE->set_heading($course->fullname);

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Untitled Document</title>
<style type="text/css">
<!--
body {
	font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: 12px;
}
#intro.box.generalbox {
	font-family: Verdana, Arial, Helvetica, sans-serif;
	padding: 0 3em 0 3em;
	border: none;
	margin:0
}
.box.generalbox {
	font-family: "Courier New", Courier, monospace;
	padding: 3em;
	border: thin solid #66CCFF;
}
-->
</style>
</head>

<body>
<form><input type="button" value=" Print this page "
onclick="window.print();return false;" /></form>
<?php
echo $OUTPUT->heading(format_string($spcnotebook->name));





$spcnotebook->intro = trim($spcnotebook->intro);

$spcnotebook->question = trim($spcnotebook->question);
echo $spcnotebook->question;
if (empty($spcnotebook->question)) {
  if (!empty($spcnotebook->intro)) {

    $intro = format_module_intro('spcnotebook', $spcnotebook, $cm->id);
    echo $OUTPUT->box($intro, 'generalbox', 'intro');
	}
  }else{echo $OUTPUT->box($question, 'generalbox', 'question');
}

echo '<br />';





    echo $OUTPUT->box_start();



    // Display entry
    if ($entry = $DB->get_record('spcnotebook_entries', array('userid' => $USER->id, 'spcnotebook' => $spcnotebook->id))) {
        if (empty($entry->text)) {
            echo '<p align="center"><b>'.get_string('blankentry','spcnotebook').'</b></p>';
        } else {
            echo format_text($entry->text, $entry->format);
        }
    } else {
        echo '<span class="warning">'.get_string('notstarted','spcnotebook').'</span>';
    }

    echo $OUTPUT->box_end();

    // Info
    




