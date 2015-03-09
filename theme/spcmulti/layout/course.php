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
global $DB;
global $COURSE;


// This is an attempt to strip the Course title from the Page title.
// E.g., LEAD1004.1: Foobar goes to Foobar.
//error_log("PAGE title: " . $PAGE->title);
if (stripos($PAGE->title, ":") > 0) {
    // if there's a colon, get everything after it.
    $thispagetitle = substr($PAGE->title, stripos($PAGE->title, ":") + 1);
} else {
    // otherwise start with the title
    $thispagetitle = $PAGE->title;
}

// trim the string.
$thispagetitle = trim($thispagetitle);
//error_log("thispagetitle: " . $thispagetitle);

$spcmenu="<div class=\"content\" id=\"spcmenu_container\">\n<ul id=\"spcmenu\">\n";
$extrasmenu="<div class=\"content\" id=\"extrasmenu_container\">\n<ul id=\"extrasmenu\">\n";
$certmenu = "";

global $ME; //THIS PAGE's URL

$sectionno = 0;

if(stripos($ME, '/mod/')) {
    $cm = $PAGE->cm;
    $sectionno = $cm->sectionnum;
}

$coursepassed = false;
$currpageflag = false;
$currpageclass = "";
$spcmenulines ='';
$checkdb ='';
$writeline='';        


foreach ($mymodinfo as $key => $currentarry) {
    $href= "href='$CFG->wwwroot/mod/$currentarry->mod/view.php?id=$currentarry->cm'";
    $pagename = trim($currentarry->name);
    $currpage = "";

    if ($thispagetitle == $pagename) {
        //error_log("Page in Array: " . $pagename);
        //error_log("=== Page Title found in Array! ===");
        $currpage = "class='current'";
        $currpageflag = true;
        $href="";
    }

    $progress = 'not_viewed';
    $doctype  = $currentarry->mod;
    //Set doctype for certificate page
    if (strcasecmp($pagename, 'certificate') == 0) {
        $doctype  = 'cert';
    }
    if (strcasecmp($pagename, 'certificate-earned') == 0) {
        $doctype  = 'cert_earned'; 
        $certtype  = 'earned'; 
        $pagename = 'Certificate Earned';
    }

    $topic  = $currentarry->section; //this refers to Moodle "topics" within a course (called sections here)
    $tooltip = "";
    $showthis = "";
    /* ========================================== */        
    ////     GET GRADE FROM QUIZ
    if ($doctype == 'quiz') {
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
    $writeline = "<li $currpage><span class=\"$doctype\"><a $href class=\"$progress\" $tooltip>$pagename</a></span></li>\n";
        

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
    if ($doctype == 'cert' && $coursepassed == true) {
        continue;
    }
    if ($doctype == 'cert_earned' && $coursepassed == false) {
        continue;
    }
    if ($doctype == 'cert' OR $doctype == 'cert_earned') {
        $certmenu .= $writeline;
        continue;
    }

    $ignored_modules = array("label", "resource");
    
    if ($topic == $sectionno && in_array($doctype, $ignored_modules)<1) {
        if ($topic =='0') {
            $extrasmenu .= $writeline;
        } else {
            $spcmenulines .= $writeline;
        }
    } elseif ($topic =='0' && in_array($doctype, $ignored_modules)<1) {
        $extrasmenu .= $writeline;
    }
}

//nothing else set the current page flag so this must be it.
if ($currpageflag == false) {
    $currpageclass = "class='current'";
}

$homeline="<li $currpageclass><span class=\"home\"><a href=\"$CFG->wwwroot/course/view.php?id=$COURSE->id\" class=\"not_viewed\">Overview</a></span></li>\n";
$menuclose = "\n</ul></div>";


echo "    $spcmenu 
          $homeline 
          $spcmenulines 
          $certmenu 
          $menuclose 
          $extrasmenu 
          $menuclose";

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
