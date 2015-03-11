<?php include 'common_header.php';?>

<!-- END OF HEADER -->
<div id="maincontainer" class="shdw-dn rnd main">
    <div id="page-wrap" >
        <div id="page-content">
            <div class="block-region region-content" id="region-pre">
                <div class="side-pre-content">

<?php
require_once("$CFG->libdir/completionlib.php");
require_once("$CFG->libdir/accesslib.php");

// To help understand what's available.
//r_error_log("\nGLOBALS:");
//r_error_log(array_keys($GLOBALS));
//r_error_log("\n");
/*
 * _GET
 * _POST
 * _COOKIE
 * _FILES
 * CFG
 * _SERVER
 * DB
 * SESSION
 * USER
 * SITE
 * PAGE
 * COURSE
 * OUTPUT
 * FULLME
 * ME
 * FULLSCRIPT
 * SCRIPT
 * _REQUEST
 * PERF
 * ACCESSLIB_PRIVATE
 * _ENV
 * GLOBALS
 * _SESSION
 * relativepath
 * forcedownload
 * preview
 * COMPLETION_CRITERIA_TYPES
 * BADGE_CRITERIA_TYPES
 * FILTERLIB_PRIVATE
 * TL_BASE_URL
 */

global $DB;
global $COURSE;


// This is an attempt to strip the Course title from the Page title.
// E.g., LEAD1004.1: Foobar goes to Foobar.
//error_log("PAGE title: " . $PAGE->title);
$colon_i = stripos($PAGE_TITLE, ":");
if ($colon_i > 0) {
    // if there's a colon, get everything after it.
    $munged_title = substr($PAGE_TITLE, $colon_i + 1);
} else {
    // otherwise start with the title
    $munged_title = $PAGE_TITLE;
}
$munged_title = trim($munged_title);
//r_error_log("Munged: " . $munged_title);

$extrasmenu = "";
$certmenu = "";

global $ME; //THIS PAGE's URL

$current_section_number = 0;

if(stripos($ME, '/mod/')) {
    $cm = $PAGE->cm;
    $current_section_number = $cm->sectionnum;
}

$coursepassed = false;
// this is a little fucked, not sure what's going on here, it OR'd over the 
// loop and used after the loop.
$currpageflag = false;
$currpageclass = "";
$spcmenulines ='';
$checkdb ='';
$writeline='';        


// Loop over the Course ModInfo objects.
foreach ($MODINFO->cms as $course_modinfo) {
    //r_error_log($course_modinfo);

    $href= "href=" . $course_modinfo->url->out();
    $pagename = trim($course_modinfo->name);
    $currpage = "";

    if ($munged_title == $pagename) {
        $currpage = "class='current'";
        $currpageflag = true;
        $href = "";
    }

    $progress = 'not_viewed';
    $page_type  = $course_modinfo->modname;

    //Set page_type for certificate page
    if (strcasecmp($pagename, 'certificate') == 0) {
        $page_type  = 'cert';
    }
    if (strcasecmp($pagename, 'certificate-earned') == 0) {
        $page_type  = 'cert_earned'; 
        $certtype  = 'earned'; 
        $pagename = 'Certificate Earned';
    }

    $tooltip = "";
    $showthis = "";
    /* ========================================== */        
    ////     GET GRADE FROM QUIZ
    if ($page_type == 'quiz' && false) {
        // gradeinfo holds: itemnumber, scaleid, name, grademin, grademax, 
        // gradepass, locked, hidden, grades (Array).
        //error_log(print_r($current_arry, true));
        //ob_start();
        //var_dump($currentarry->gradeinfo);
        //$debug_output = ob_get_clean();
        //error_log($debug_output);
        // Grade to pass is set within a Gradebook. In a Course:
        //   1. Go to Grades.
        //   2. Turn on Editing.
        //   3. Edit the Controls for a particular quiz.
        //   4. Set the grade to pass at the desired level.
        $grade_to_pass = trim($currentarry->gradeinfo->gradepass);
        $grade_to_pass_value = floatval($grade_to_pass);
        $quiz_grade = trim($currentarry->gradeinfo->grades[$USER->id]->grade);
        $quiz_grade_value = floatval($quiz_grade);

        if ($quiz_grade == "") {
            $progress = 'progress_none';
            $coursepassed = false;
            $tooltip = 'title ="QUIZ - required to obtain a certificate"';
        } else if ($quiz_grade_value < $grade_to_pass_value) {
                $progress = 'progress_alert';
                $coursepassed = false;
                $tooltip = 'title ="QUIZ - attempted but not passed"';
        } else {
            $progress = 'progress_pass';
            $coursepassed = true;
            $tooltip = 'title ="QUIZ - Passed!"';
        }
    }
    /* ========================================== */
    $writeline = <<<EOD
<li $currpage>
    <span class="$page_type"><a $href class="$progress" $tooltip>$pagename</a></span>
</li>
EOD;
        

    // Record Certificate Earned Date.
    if ($coursepassed == true) {
        $completed_record = $DB->get_record('spcdata', array('userid'=>$USER->id, 'course'=>$COURSE->id));        
        if (!$completed_record) {
            $current_time = time();
            $record = new stdClass();
            $record->name = $COURSE->shortname;
            $record->timecreated = $current_time;
            $record->timemodified = $current_time;
            $record->course = $COURSE->id;
            $record->userid = $USER->id;
            $record->certificatedate = $current_time;
            $DB->insert_record('spcdata', $record, $returnid=true);
        }
    }

    // CERTIFICATE EARNED TOGGLE    
    if ($page_type == 'cert' && $coursepassed == true) {
        continue;
    }
    if ($page_type == 'cert_earned' && $coursepassed == false) {
        continue;
    }
    if ($page_type == 'cert' OR $page_type == 'cert_earned') {
        $certmenu .= $writeline;
        continue;
    }

    $ignored_modules = array("label", "resource");
    
    //r_error_log("Current Section: " . $current_section_number);
    //r_error_log("Topic: " . $course_modinfo->sectionnum);
    //r_error_log("Page Type: " . $page_type);
    if ($course_modinfo->sectionnum == $current_section_number && in_array($page_type, $ignored_modules)<1) {
        if ($course_modinfo->sectionnum == '0') {
            //r_error_log("section match Extra menu writing $pagename");
            $extrasmenu .= $writeline;
        } else {
            //r_error_log("section match SPC menu writing $pagename");
            $spcmenulines .= $writeline;
        }
    } elseif ($course_modinfo->sectionnum == '0' && in_array($page_type, $ignored_modules)<1) {
        //r_error_log("First section Extra menu writing $pagename");
        $extrasmenu .= $writeline;
    }
}

//nothing else set the current page flag so this must be it.
if ($currpageflag == false) {
    $currpageclass = " class='current'";
}

echo <<<EOD
<!-- Side Menu -->
<div class="content" id="spcmenu_container">
    <ul id="spcmenu">
        <li$currpageclass>
            <span class="home"><a href="$CFG->wwwroot/course/view.php?id=$COURSE->id" class="not_viewed">Overview</a></span>
        </li>
        $spcmenulines 
        $certmenu 
    </ul>
</div>
<div class="content" id="extrasmenu_container">
    <ul id="extrasmenu">
        $extrasmenu 
    </ul>
</div>
EOD;
?>

<?php if ($hassidepre) {echo $OUTPUT->blocks_for_region('side-pre');}?>

</div>

            </div>
            <div id="region-main-wrap">
                <div id="region-main">
                    <h1 class="headermain"><?php echo $PAGE->heading ?></h1>
                    <div class="region-content">
                        <?php echo $OUTPUT->main_content() ?>
                    </div>
                    
                <div class="side-post-content">
                
                <?php if ($hassidepost) {echo $OUTPUT->blocks_for_region('side-post');}  ?>
                </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- START OF FOOTER -->

<?php $tmpn=__FILE__; include 'common_footer.php';
?>
