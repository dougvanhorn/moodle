<?php // $Id: edit.php,v 1.2 1213/05/30 11:36:57 jacolina from journal.php by davmon Exp $

require_once("../../config.php");
require_once('./edit_form.php');

$id = required_param('id', PARAM_INT);    // Course Module ID

if (!$cm = get_coursemodule_from_id('spcnotebook', $id)) {
    print_error("Course Module ID was incorrect");
}

if (!$course = $DB->get_record("course", array("id" => $cm->course))) {
    print_error("Course is misconfigured");
}

$context = get_context_instance(CONTEXT_MODULE, $cm->id);

require_login($course->id, false, $cm);

require_capability('mod/spcnotebook:addentries', $context);






if (!$spcnotebook = $DB->get_record("spcnotebook", array("id" => $cm->instance))) {
    print_error("Course module is incorrect");
}

if (!$cw = $DB->get_record("course_sections", array("id" => $cm->section))) {
    print_error("Course module is incorrect");
}

// Header
$PAGE->set_url('/mod/spcnotebook/edit.php', array('id' => $id));
$PAGE->navbar->add(get_string('edit'));
$PAGE->set_title(format_string($spcnotebook->name));
$PAGE->set_heading($course->fullname);
$data = new StdClass();

$entry = $DB->get_record("spcnotebook_entries", array("userid" => $USER->id, "spcnotebook" => $spcnotebook->id));
if ($entry) {

    $data->text["text"] = $entry->text;
    
}
$data->text["format"] = FORMAT_PLAIN;
$data->id = $cm->id;
$form = new mod_spcnotebook_entry_form(null, array('current' => $data));

/// If data submitted, then process and store.
if ($fromform = $form->get_data()) {

    $timenow = time();

    // Common
    $newentry = new StdClass();
    $newentry->text = $fromform->text["text"];
    $newentry->format = $fromform->text["format"];
    $newentry->modified = $timenow;

    if ($entry) {
        $newentry->id = $entry->id;
        if (!$DB->update_record("spcnotebook_entries", $newentry)) {
            print_error("Could not update your spcnotebook");
        }
        $logaction = "update entry";

    } else {
        $newentry->userid = $USER->id;
        $newentry->spcnotebook = $spcnotebook->id;
        if (!$newentry->id = $DB->insert_record("spcnotebook_entries", $newentry)) {
            print_error("Could not insert a new spcnotebook entry");
        }
        $logaction = "add entry";
    }

    add_to_log($course->id, "spcnotebook", $logaction, 'view.php?id='.$cm->id, $newentry->id, $cm->id);

    redirect('view.php?id='.$cm->id);
    die;
}
$link = new moodle_url('/mod/spcnotebook/print.php', array('id' => $id));



echo $OUTPUT->header();
echo $OUTPUT->heading(format_string($spcnotebook->name));

$intro = format_module_intro('spcnotebook', $spcnotebook, $cm->id);


echo $OUTPUT->box($intro);

$question = format_text($spcnotebook->question);
echo $OUTPUT->box($question);
?><div class="prntbutton"><?php echo $OUTPUT->action_link($link, 'PRINT YOUR NOTES', new popup_action ('click', $link));?></div><?php
/// Otherwise fill and print the form.
$form->display();

echo $OUTPUT->footer();
