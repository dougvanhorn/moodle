<?php // $Id: lib.php,v 1.5.2.1 1213/05/30 10:53:40 jacolina from journal.php by davmon Exp $


// STANDARD MODULE FUNCTIONS /////////////////////////////////////////////////////////

function spcnotebook_add_instance($spcnotebook) {
// Given an object containing all the necessary data,
// (defined by the form in mod.html) this function
// will create a new instance and return the id number
// of the new instance.

    global $DB;

    $spcnotebook->timemodified = time();
    $spcnotebook->id = $DB->insert_record("spcnotebook", $spcnotebook);

    spcnotebook_grade_item_update($spcnotebook);

    return $spcnotebook->id;
}


function spcnotebook_update_instance($spcnotebook) {
// Given an object containing all the necessary data,
// (defined by the form in mod.html) this function
// will update an existing instance with new data.

    global $DB;

    $spcnotebook->timemodified = time();
    $spcnotebook->id = $spcnotebook->instance;

    $result = $DB->update_record("spcnotebook", $spcnotebook);

    spcnotebook_grade_item_update($spcnotebook);

    spcnotebook_update_grades($spcnotebook, 0, false);

    return $result;
}


function spcnotebook_delete_instance($id) {
// Given an ID of an instance of this module,
// this function will permanently delete the instance
// and any data that depends on it.

    global $DB;

    $result = true;

    if (! $spcnotebook = $DB->get_record("spcnotebook", array("id" => $id))) {
        return false;
    }

    if (! $DB->delete_records("spcnotebook_entries", array("spcnotebook" => $spcnotebook->id))) {
        $result = false;
    }

    if (! $DB->delete_records("spcnotebook", array("id" => $spcnotebook->id))) {
        $result = false;
    }

    return $result;
}


function spcnotebook_supports($feature) {
    switch($feature) {
        case FEATURE_MOD_INTRO:               return true;
        case FEATURE_GRADE_HAS_GRADE:         return true;
        case FEATURE_GRADE_OUTCOMES:          return false;
        case FEATURE_RATE:                    return false;
        case FEATURE_GROUPS:                  return true;
        case FEATURE_GROUPINGS:               return true;
        case FEATURE_GROUPMEMBERSONLY:        return true;
        case FEATURE_COMPLETION_TRACKS_VIEWS: return false;
        case FEATURE_BACKUP_MOODLE2:          return true;

        default: return null;
    }
}


function spcnotebook_get_view_actions() {
    return array('view','view all','view responses');
}


function spcnotebook_get_post_actions() {
    return array('add entry','update entry','update feedback');
}


function spcnotebook_user_outline($course, $user, $mod, $spcnotebook) {

    global $DB;

    if ($entry = $DB->get_record("spcnotebook_entries", array("userid" => $user->id, "spcnotebook" => $spcnotebook->id))) {

        $numwords = count(preg_split("/\w\b/", $entry->text)) - 1;

        $result->info = get_string("numwords", "", $numwords);
        $result->time = $entry->modified;
        return $result;
    }
    return NULL;
}


function spcnotebook_user_complete($course, $user, $mod, $spcnotebook) {

    global $DB, $OUTPUT;

    if ($entry = $DB->get_record("spcnotebook_entries", array("userid" => $user->id, "spcnotebook" => $spcnotebook->id))) {

        echo $OUTPUT->box_start();

        if ($entry->modified) {
            echo "<p><font size=\"1\">".get_string("lastedited").": ".userdate($entry->modified)."</font></p>";
        }
        if ($entry->text) {
            echo format_text($entry->text, $entry->format);
        }
        if ($entry->teacher) {
            $grades = make_grades_menu($spcnotebook->grade);
            spcnotebook_print_feedback($course, $entry, $grades);
        }

        echo $OUTPUT->box_end();

    } else {
        print_string("noentry", "spcnotebook");
    }
}


function spcnotebook_cron () {
// Function to be run periodically according to the moodle cron
// Finds all spcnotebook notifications that have yet to be mailed out, and mails them

    global $CFG, $USER, $DB;

    $cutofftime = time() - $CFG->maxeditingtime;
/*
    if ($entries = spcnotebook_get_unmailed_graded($cutofftime)) {
        $timenow = time();

        foreach ($entries as $entry) {

            echo "Processing spcnotebook entry $entry->id\n";

            if (! $user = $DB->get_record("user", array("id" => $entry->userid))) {
                echo "Could not find user $entry->userid\n";
                continue;
            }

            $USER->lang = $user->lang;

            if (! $course = $DB->get_record("course", array("id" => $entry->course))) {
                echo "Could not find course $entry->course\n";
                continue;
            }

            if (! $teacher = $DB->get_record("user", array("id" => $entry->teacher))) {
                echo "Could not find teacher $entry->teacher\n";
                continue;
            }


            if (! $mod = get_coursemodule_from_instance("spcnotebook", $entry->spcnotebook, $course->id)) {
                echo "Could not find course module for spcnotebook id $entry->spcnotebook\n";
                continue;
            }

            $context = get_context_instance(CONTEXT_MODULE, $mod->id);
            $canadd = has_capability('mod/spcnotebook:addentries', $context, $user);
            $entriesmanager = has_capability('mod/spcnotebook:manageentries', $context, $user);

            if (!$canadd and $entriesmanager) {
                continue;  // Not an active participant
            }

            unset($spcnotebookinfo);
            $spcnotebookinfo->teacher = fullname($teacher);
            $spcnotebookinfo->spcnotebook = format_string($entry->name,true);
            $spcnotebookinfo->url = "$CFG->wwwroot/mod/spcnotebook/view.php?id=$mod->id";
            $modnamepl = get_string( 'modulenameplural','spcnotebook' );
            $msubject = get_string( 'mailsubject','spcnotebook' );

            $postsubject = "$course->shortname: $msubject: ".format_string($entry->name,true);
            $posttext  = "$course->shortname -> $modnamepl -> ".format_string($entry->name,true)."\n";
            $posttext .= "---------------------------------------------------------------------\n";
            $posttext .= get_string("spcnotebookmail", "spcnotebook", $spcnotebookinfo)."\n";
            $posttext .= "---------------------------------------------------------------------\n";
            if ($user->mailformat == 1) {  // HTML
                $posthtml = "<p><font face=\"sans-serif\">".
                "<a href=\"$CFG->wwwroot/course/view.php?id=$course->id\">$course->shortname</a> ->".
                "<a href=\"$CFG->wwwroot/mod/spcnotebook/index.php?id=$course->id\">spcnotebooks</a> ->".
                "<a href=\"$CFG->wwwroot/mod/spcnotebook/view.php?id=$mod->id\">".format_string($entry->name,true)."</a></font></p>";
                $posthtml .= "<hr /><font face=\"sans-serif\">";
                $posthtml .= "<p>".get_string("spcnotebookmailhtml", "spcnotebook", $spcnotebookinfo)."</p>";
                $posthtml .= "</font><hr />";
            } else {
              $posthtml = "";
            }

            if (! email_to_user($user, $teacher, $postsubject, $posttext, $posthtml)) {
                echo "Error: SPC Notebook cron: Could not send out mail for id $entry->id to user $user->id ($user->email)\n";
            }
            if (!$DB->set_field("spcnotebook_entries", "mailed", "1", array("id" => $entry->id))) {
                echo "Could not update the mailed field for id $entry->id\n";
            }
        }
    }*/

    return true;
}

function spcnotebook_print_recent_activity($course, $isteacher, $timestart) {
    global $CFG, $DB, $OUTPUT;

    if (!get_config('spcnotebook', 'showrecentactivity')) {
        return false;
    }

    $content = false;
    $spcnotebooks = NULL;

    // log table should not be used here

    $select = "time > ? AND
               course = ? AND
               module = 'spcnotebook' AND
               (action = 'add entry' OR action = 'update entry')";
    if (!$logs = $DB->get_records_select('log', $select, array($timestart, $course->id), 'time ASC')){
        return false;
    }

    $modinfo = & get_fast_modinfo($course);
    foreach ($logs as $log) {
        ///Get spcnotebook info.  I'll need it later
        $j_log_info = spcnotebook_log_info($log);

        $cm = $modinfo->instances['spcnotebook'][$j_log_info->id];
        if (!$cm->uservisible) {
            continue;
        }

        if (!isset($spcnotebooks[$log->info])) {
            $spcnotebooks[$log->info] = $j_log_info;
            $spcnotebooks[$log->info]->time = $log->time;
            $spcnotebooks[$log->info]->url = str_replace('&', '&amp;', $log->url);
        }
    }

    if ($spcnotebooks) {
        $content = true;
        echo $OUTPUT->heading(get_string('newspcnotebookentries', 'spcnotebook').':', 3);
        foreach ($spcnotebooks as $spcnotebook) {
            print_recent_activity_note($spcnotebook->time, $spcnotebook, $spcnotebook->name,
                                       $CFG->wwwroot.'/mod/spcnotebook/'.$spcnotebook->url);
        }
    }

    return $content;
}

function spcnotebook_get_participants($spcnotebookid) {
//Returns the users with data in one spcnotebook
//(users with records in spcnotebook_entries, students and teachers)

    global $DB;

    //Get students
    $students = $DB->get_records_sql("SELECT DISTINCT u.id
                                      FROM {user} u,
                                      {spcnotebook_entries} j
                                      WHERE j.spcnotebook = '$spcnotebookid' and
                                      u.id = j.userid");
    //Get teachers
    $teachers = $DB->get_records_sql("SELECT DISTINCT u.id
                                      FROM {user} u,
                                      {spcnotebook_entries} j
                                      WHERE j.spcnotebook = '$spcnotebookid' and
                                      u.id = j.teacher");

    //Add teachers to students
    if ($teachers) {
        foreach ($teachers as $teacher) {
            $students[$teacher->id] = $teacher;
        }
    }
    //Return students array (it contains an array of unique users)
    return ($students);
}

function spcnotebook_scale_used ($spcnotebookid,$scaleid) {
//This function returns if a scale is being used by one spcnotebook
    global $DB;
    $return = false;

    $rec = $DB->get_record("spcnotebook", array("id" => $spcnotebookid, "grade" => -$scaleid));

    if (!empty($rec) && !empty($scaleid)) {
        $return = true;
    }

    return $return;
}

/**
 * Checks if scale is being used by any instance of spcnotebook
 *
 * This is used to find out if scale used anywhere
 * @param $scaleid int
 * @return boolean True if the scale is used by any spcnotebook
 */
function spcnotebook_scale_used_anywhere($scaleid) {
    global $DB;

    if ($scaleid and $DB->get_records('spcnotebook', array('grade' => -$scaleid))) {
        return true;
    } else {
        return false;
    }
}

/**
 * Implementation of the function for printing the form elements that control
 * whether the course reset functionality affects the spcnotebook.
 *
 * @param object $mform form passed by reference
 */
function spcnotebook_reset_course_form_definition(&$mform) {
    $mform->addElement('header', 'spcnotebookheader', get_string('modulenameplural', 'spcnotebook'));
    $mform->addElement('advcheckbox', 'reset_spcnotebook', get_string('removemessages','spcnotebook'));
}

/**
 * Course reset form defaults.
 *
 * @param object $course
 * @return array
 */
function spcnotebook_reset_course_form_defaults($course) {
    return array('reset_spcnotebook'=>1);
}

/**
 * Removes all entries
 *
 * @param object $data
 */
function spcnotebook_reset_userdata($data) {

    global $CFG, $DB;

    $status = array();
    if (!empty($data->reset_spcnotebook)) {

        $sql = "SELECT j.id
                FROM {spcnotebook} j
                WHERE j.course = ?";
        $params = array($data->courseid);

        $DB->delete_records_select('spcnotebook_entries', "spcnotebook IN ($sql)", $params);

        $status[] = array('component' => get_string('modulenameplural', 'spcnotebook'),
                          'item' => get_string('removeentries', 'spcnotebook'),
                          'error' => false);
    }

    return $status;
}

function spcnotebook_print_overview($courses, &$htmlarray) {

    global $USER, $CFG, $DB;

    if (!get_config('spcnotebook', 'overview')) {
        return array();
    }

    if (empty($courses) || !is_array($courses) || count($courses) == 0) {
        return array();
    }

    if (!$spcnotebooks = get_all_instances_in_courses('spcnotebook', $courses)) {
        return array();
    }

    $strspcnotebook = get_string('modulename', 'spcnotebook');

    $timenow = time();
    foreach ($spcnotebooks as $spcnotebook) {

        $courses[$spcnotebook->course]->format = $DB->get_field('course', 'format', array('id' => $spcnotebook->course));

        if ($courses[$spcnotebook->course]->format == 'weeks' AND $spcnotebook->days) {

            $coursestartdate = $courses[$spcnotebook->course]->startdate;

            $spcnotebook->timestart  = $coursestartdate + (($spcnotebook->section - 1) * 608400);
            if (!empty($spcnotebook->days)) {
                $spcnotebook->timefinish = $spcnotebook->timestart + (3600 * 24 * $spcnotebook->days);
            } else {
                $spcnotebook->timefinish = 9999999999;
            }
            $spcnotebookopen = ($spcnotebook->timestart < $timenow && $timenow < $spcnotebook->timefinish);

        } else {
            $spcnotebookopen = true;
        }

        if ($spcnotebookopen) {
            $str = '<div class="spcnotebook overview"><div class="name">'.
                   $strspcnotebook.': <a '.($spcnotebook->visible?'':' class="dimmed"').
                   ' href="'.$CFG->wwwroot.'/mod/spcnotebook/view.php?id='.$spcnotebook->coursemodule.'">'.
                   $spcnotebook->name.'</a></div></div>';

            if (empty($htmlarray[$spcnotebook->course]['spcnotebook'])) {
                $htmlarray[$spcnotebook->course]['spcnotebook'] = $str;
            } else {
                $htmlarray[$spcnotebook->course]['spcnotebook'] .= $str;
            }
        }
    }
}

function spcnotebook_get_user_grades($spcnotebook, $userid=0) {

    global $DB;

    if ($userid) {
        $userstr = 'AND userid = '.$userid;
    } else {
        $userstr = '';
    }

    if (!$spcnotebook) {
        return false;

    } else {

        $sql = "SELECT userid, modified as datesubmitted, format as feedbackformat,
                rating as rawgrade, entrycomment as feedback, teacher as usermodifier, timemarked as dategraded
                FROM {spcnotebook_entries}
                WHERE spcnotebook = '$spcnotebook->id' ".$userstr;

        $grades = $DB->get_records_sql($sql);

        if ($grades) {
            foreach ($grades as $key=>$grade) {
                $grades[$key]->id = $grade->userid;
            }
        } else {
            return false;
        }

        return $grades;
    }

}


/**
 * Update spcnotebook grades in 1.9 gradebook
 *
 * @param object   $spcnotebook      if is null, all spcnotebooks
 * @param int      $userid       if is false al users
 * @param boolean  $nullifnone   return null if grade does not exist
 */
function spcnotebook_update_grades($spcnotebook=null, $userid=0, $nullifnone=true) {

    global $CFG, $DB;

    if (!function_exists('grade_update')) { //workaround for buggy PHP versions
        require_once($CFG->libdir.'/gradelib.php');
    }

    if ($spcnotebook != null) {
        if ($grades = spcnotebook_get_user_grades($spcnotebook, $userid)) {
            spcnotebook_grade_item_update($spcnotebook, $grades);
        } else if ($userid && $nullifnone) {
            $grade = new object();
            $grade->userid   = $userid;
            $grade->rawgrade = NULL;
            spcnotebook_grade_item_update($spcnotebook, $grade);
        } else {
            spcnotebook_grade_item_update($spcnotebook);
        }
    } else {
        $sql = "SELECT j.*, cm.idnumber as cmidnumber
                FROM {course_modules} cm
                JOIN {modules} m ON m.id = cm.module
                JOIN {spcnotebook} j ON cm.instance = j.id
                WHERE m.name = 'spcnotebook'";
        if ($recordset = $DB->get_records_sql($sql)) {
           foreach ($recordset as $spcnotebook) {
                if ($spcnotebook->grade != false) {
                    spcnotebook_update_grades($spcnotebook);
                } else {
                    spcnotebook_grade_item_update($spcnotebook);
                }
            }
        }
    }
}


/**
 * Create grade item for given spcnotebook
 *
 * @param object $spcnotebook object with extra cmidnumber
 * @param mixed optional array/object of grade(s); 'reset' means reset grades in gradebook
 * @return int 0 if ok, error code otherwise
 */
function spcnotebook_grade_item_update($spcnotebook, $grades=NULL) {
    global $CFG;
    if (!function_exists('grade_update')) { //workaround for buggy PHP versions
        require_once($CFG->libdir.'/gradelib.php');
    }

    if (array_key_exists('cmidnumber', $spcnotebook)) {
        $params = array('itemname'=>$spcnotebook->name, 'idnumber'=>$spcnotebook->cmidnumber);
    } else {
        $params = array('itemname'=>$spcnotebook->name);
    }

    if ($spcnotebook->grade > 0) {
        $params['gradetype']  = GRADE_TYPE_VALUE;
        $params['grademax']   = $spcnotebook->grade;
        $params['grademin']   = 0;
        $params['multfactor'] = 1.0;

    } else if($spcnotebook->grade < 0) {
        $params['gradetype'] = GRADE_TYPE_SCALE;
        $params['scaleid']   = -$spcnotebook->grade;

    } else {
        $params['gradetype']  = GRADE_TYPE_NONE;
        $params['multfactor'] = 1.0;
    }

    if ($grades  === 'reset') {
        $params['reset'] = true;
        $grades = NULL;
    }

    return grade_update('mod/spcnotebook', $spcnotebook->course, 'mod', 'spcnotebook', $spcnotebook->id, 0, $grades, $params);
}


/**
 * Delete grade item for given spcnotebook
 *
 * @param   object   $spcnotebook
 * @return  object   grade_item
 */
function spcnotebook_grade_item_delete($spcnotebook) {
    global $CFG;

    require_once($CFG->libdir.'/gradelib.php');

    return grade_update('mod/spcnotebook', $spcnotebook->course, 'mod', 'spcnotebook', $spcnotebook->id, 0, NULL, array('deleted'=>1));
}


// SQL FUNCTIONS ///////////////////////////////////////////////////////////////////

function spcnotebook_get_users_done($spcnotebook, $currentgroup) {
    global $DB;


    $sql = "SELECT u.* FROM {spcnotebook_entries} j
            JOIN {user} u ON j.userid = u.id ";

    // Group users
    if ($currentgroup != 0) {
        $sql.= "JOIN {groups_members} gm ON gm.userid = u.id AND gm.groupid = '$currentgroup'";
    }

    $sql.= " WHERE j.spcnotebook = '$spcnotebook->id' ORDER BY j.modified DESC";
    $spcnotebooks = $DB->get_records_sql($sql);

    $cm = spcnotebook_get_coursemodule($spcnotebook->id);
    if (!$spcnotebooks || !$cm) {
        return NULL;
    }

    // remove unenrolled participants
    foreach ($spcnotebooks as $key => $user) {

        $context = get_context_instance(CONTEXT_MODULE, $cm->id);

        $canadd = has_capability('mod/spcnotebook:addentries', $context, $user);
        $entriesmanager = has_capability('mod/spcnotebook:manageentries', $context, $user);

        if (!$entriesmanager and !$canadd) {
            unset($spcnotebooks[$key]);
        }
    }

    return $spcnotebooks;
}

function spcnotebook_count_entries($spcnotebook, $groupid = 0) {
/// Counts all the spcnotebook entries (optionally in a given group)

    global $DB;

    $cm = spcnotebook_get_coursemodule($spcnotebook->id);
    $context = get_context_instance(CONTEXT_MODULE, $cm->id);

    if ($groupid) {     /// How many in a particular group?

        $sql = "SELECT DISTINCT u.id FROM {spcnotebook_entries} j
                JOIN {groups_members} g ON g.userid = j.userid
                JOIN {user} u ON u.id = g.userid
                WHERE j.spcnotebook = $spcnotebook->id AND g.groupid = '$groupid'";
        $spcnotebooks = $DB->get_records_sql($sql);

    } else { /// Count all the entries from the whole course

        $sql = "SELECT DISTINCT u.id FROM {spcnotebook_entries} j
                JOIN {user} u ON u.id = j.userid
                WHERE j.spcnotebook = '$spcnotebook->id'";
        $spcnotebooks = $DB->get_records_sql($sql);
    }


    if (!$spcnotebooks) {
        return 0;
    }

    // remove unenrolled participants
    foreach ($spcnotebooks as $key => $user) {

        $canadd = has_capability('mod/spcnotebook:addentries', $context, $user);
        $entriesmanager = has_capability('mod/spcnotebook:manageentries', $context, $user);

        if (!$entriesmanager && !$canadd) {
            unset($spcnotebooks[$key]);
        }
    }

    return count($spcnotebooks);
}

function spcnotebook_get_unmailed_graded($cutofftime) {
    global $DB;

    $sql = "SELECT je.*, j.course, j.name FROM {spcnotebook_entries} je
            JOIN {spcnotebook} j ON je.spcnotebook = j.id
            WHERE je.mailed = '0' AND je.timemarked < '$cutofftime' AND je.timemarked > 0";
    return $DB->get_records_sql($sql);
}

function spcnotebook_log_info($log) {
    global $DB;

    $sql = "SELECT j.*, u.firstname, u.lastname
            FROM {spcnotebook} j
            JOIN {spcnotebook_entries} je ON je.spcnotebook = j.id
            JOIN {user} u ON u.id = je.userid
            WHERE je.id = '$log->info'";
    return $DB->get_record_sql($sql);
}

/**
 * Returns the spcnotebook instance course_module id
 *
 * @param integer $spcnotebook
 * @return object
 */
function spcnotebook_get_coursemodule($spcnotebookid) {

    global $DB;

    return $DB->get_record_sql("SELECT cm.id FROM {course_modules} cm
                                JOIN {modules} m ON m.id = cm.module
                                WHERE cm.instance = '$spcnotebookid' AND m.name = 'spcnotebook'");
}


// OTHER SPCNOTEBOOK FUNCTIONS ///////////////////////////////////////////////////////////////////

function spcnotebook_print_user_entry($course, $user, $entry, $teachers, $grades) {

    global $USER, $OUTPUT, $DB, $CFG;

    require_once($CFG->dirroot.'/lib/gradelib.php');

    echo "\n<table class=\"spcnotebookuserentry\">";

    echo "\n<tr>";
    echo "\n<td class=\"userpix\" rowspan=\"2\">";
    echo $OUTPUT->user_picture($user, array('courseid' => $course->id));
    echo "</td>";
    echo "<td class=\"userfullname\">".fullname($user);
    if ($entry) {
        echo " <span class=\"lastedit\">".get_string("lastedited").": ".userdate($entry->modified)."</span>";
    }
    echo "</td>";
    echo "</tr>";

    echo "\n<tr><td>";
    if ($entry) {
        echo format_text($entry->text, $entry->format);
    } else {
        print_string("noentry", "spcnotebook");
    }
    echo "</td></tr>";

    if ($entry) {
        echo "\n<tr>";
        echo "<td class=\"userpix\">";
        if (!$entry->teacher) {
            $entry->teacher = $USER->id;
        }
        if (empty($teachers[$entry->teacher])) {
            $teachers[$entry->teacher] = $DB->get_record('user', array('id' => $entry->teacher));
        }
        echo $OUTPUT->user_picture($teachers[$entry->teacher], array('courseid' => $course->id));
        echo "</td>";
        echo "<td>".get_string("feedback").":";


        $attrs = array();
        $hiddengradestr = '';
        $gradebookgradestr = '';
        $feedbackdisabledstr = '';
        $feedbacktext = $entry->entrycomment;

        // If the grade was modified from the gradebook disable edition
        $grading_info = grade_get_grades($course->id, 'mod', 'spcnotebook', $entry->spcnotebook, array($user->id));
        if ($gradingdisabled = $grading_info->items[0]->grades[$user->id]->locked || $grading_info->items[0]->grades[$user->id]->overridden) {
            $attrs['disabled'] = 'disabled';
            $hiddengradestr = '<input type="hidden" name="r'.$entry->id.'" value="'.$entry->rating.'"/>';
            $gradebooklink = '<a href="'.$CFG->wwwroot.'/grade/report/grader/index.php?id='.$course->id.'">';
            $gradebooklink.= $grading_info->items[0]->grades[$user->id]->str_long_grade.'</a>';
            $gradebookgradestr = '<br/>'.get_string("gradeingradebook", "spcnotebook").':&nbsp;'.$gradebooklink;

            $feedbackdisabledstr = 'disabled="disabled"';
            $feedbacktext = $grading_info->items[0]->grades[$user->id]->str_feedback;
        }

        // Grade selector
        echo html_writer::select($grades, 'r'.$entry->id, $entry->rating, get_string("nograde").'...', $attrs);
        echo $hiddengradestr;
        if ($entry->timemarked) {
            echo " <span class=\"lastedit\">".userdate($entry->timemarked)."</span>";
        }
        echo $gradebookgradestr;

        // Feedback text
        echo "<p><textarea name=\"c$entry->id\" rows=\"12\" cols=\"60\" $feedbackdisabledstr>";
        p($feedbacktext);
        echo "</textarea></p>";

        if ($feedbackdisabledstr != '') {
            echo '<input type="hidden" name="c'.$entry->id.'" value="'.$feedbacktext.'"/>';
        }
        echo "</td></tr>";
    }
    echo "</table>\n";

}

function spcnotebook_print_feedback($course, $entry, $grades) {

    global $CFG, $DB, $OUTPUT;

    require_once($CFG->dirroot.'/lib/gradelib.php');

    if (! $teacher = $DB->get_record('user', array('id' => $entry->teacher))) {
        print_error('Weird spcnotebook error');
    }

    echo '<table class="feedbackbox">';

    echo '<tr>';
    echo '<td class="left picture">';
    echo $OUTPUT->user_picture($teacher, array('courseid' => $course->id));
    echo '</td>';
    echo '<td class="entryheader">';
    echo '<span class="author">'.fullname($teacher).'</span>';
    echo '&nbsp;&nbsp;<span class="time">'.userdate($entry->timemarked).'</span>';
    echo '</td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td class="left side">&nbsp;</td>';
    echo '<td class="entrycontent">';

    echo '<div class="grade">';

    // Gradebook preference
    if ($grading_info = grade_get_grades($course->id, 'mod', 'spcnotebook', $entry->spcnotebook, array($entry->userid))) {
        echo get_string('grade').': ';
        echo $grading_info->items[0]->grades[$entry->userid]->str_long_grade;
    } else {
        print_string('nograde');
    }
    echo '</div>';

    // Feedback text
    echo format_text($entry->entrycomment);
    echo '</td></tr></table>';
}

