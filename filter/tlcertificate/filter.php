<?php
/**
 * Provides a way for content creators to hardcode certificate links from 
 * within a WYSIWYG editor.
 *
 * E.g., {{ CERTIFICATELINK:... }}
 */
class filter_tlcertificate extends moodle_text_filter {
    public function filter($text, array $options = array()) {
        // Looks likes the text is the text about to be displayed.
        // We build up a Regex and the new html, then do the regex replacement.

        // Moodle Globals.
        global $CFG, $COURSE, $USER;

        // TeacherLine Global.
        global $TL_BASE_URL;


        // Define the filter pattern to match against.
        // Appears that the usage would be: {CERTIFICATELINK: Foo bar}.
        $filter_pattern = '{{CERTIFICATELINK:([^\}]*)}}';

        # Course Certificate Link will send user back to TL with enough 
        # information to update the TL Enrollment with the completion date and 
        # the grade "P".
        $course_shortname = $COURSE->shortname; // e.g. TECH101.5
        $grade = "P"; // [P]ass

        // The completion date is stored in the mod/spcdata module, but the 
        // current date is good enough.  Common case will be the user clicking 
        // on the link just after completion.
        $completion_date = date("c"); // ISO8601

        # Hash the MOODLE23_USERNAME, MOODLE23_PASSWORD as a security check on 
        # the client side.  Add the course and username (the User.pk in TL) to 
        # make the hash unique to this user/section combo.
        $md5_string = $CFG->tl_user . ':' . $CFG->tl_pass . ':' . $course_shortname . ':' . $USER->username;
        $md5_hash = md5($md5_string);
        //error_log("MD5 String: $md5_string");
        //error_log("MD5 Hash: $md5_hash");


        $query_data = array(
            "grade" => $grade, // [P]ass
            "completion_date" => $completion_date, // ISO-8601
            "md5" => $md5_hash, // MD5 Hash as security check.
        );
        $query_string = http_build_query($query_data);
        
        // The TL endpoint will pull in the parameters, check the hash against 
        // the user.pk, update the enrollment, and forward the user to the PDF.
        $link = $TL_BASE_URL.'/mycourses/sections/'.$course_shortname.'/spc-course-completed/';
        $html = '<a href="' . $link . '?' . $query_string . '">$1</a>';
        //<!-- ' . $USER->username . ' and ' . $_COOKIE['MoodleSession'] . ' and ' . $completion_date . ' -->';

        return preg_replace($filter_pattern, $html, $text);
    }
}
?>
