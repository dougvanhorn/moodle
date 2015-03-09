<?php include 'common_header.php';?>
<!--?php $longcoursename="An Introduction to Underlying Principles and Research for Effective
 Literacy Instruction";?
 
//var bdg_cn = encodeURIComponent("< ?php echo $longcoursename; ?>")
//var bdg_fn = encodeURIComponent("< ?php echo 'Jose Alejandro';  ?>")
//var bdg_ln = encodeURIComponent("< ?php echo 'Rodriguez Martinez';   ?>")
//var bdg_fn = encodeURIComponent("< ?php echo 'Max';  ?>")
//var bdg_ln = encodeURIComponent("< ?php echo 'Doe';   ?>")
 
-->
<script type="text/javascript">
//<![CDATA[
var bdg_fn = encodeURIComponent("<?php echo $USER->firstname;  ?>")
var bdg_ln = encodeURIComponent("<?php echo $USER->lastname;   ?>")
var bdg_cn = encodeURIComponent("<?php echo $COURSE->fullname; ?>")
var bdg_cs = encodeURIComponent("<?php echo $COURSE->shortname;?>")

buildUrl = "/moodle-assets/badge.php?\
    f="+bdg_fn+"&\
    l="+bdg_ln+"&\
    c="+bdg_cn+"&\
    s="+bdg_cs+" "
var bdg_link = document.createElement("a");
    bdg_link.href= buildUrl;
    bdg_link.setAttribute("class", "popup");
    bdg_link.setAttribute("id", "badgelink");
    bdg_link.appendChild(document.createTextNode("print your badge"));


<?php
if (!isset($_GET["course_rt"]) || empty($_GET["course_rt"])) {
$course_rt = "http://pbs.org/teacherline";
} else {
$course_rt = $_GET["course_rt"]; ?>
    return_link = document.createElement("a");
    return_link.href= buildUrl;
    return_link.setAttribute("class", "return");
    return_link.setAttribute("id", "return");
    return_link.appendChild(document.createTextNode("Back to course"));
<?php } 

?>
//]]>
</script>

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

$thispagetitle = $PAGE->title;
$thispagetitle = substr($thispagetitle,stripos($thispagetitle,":"));
$thispagetitle = str_replace(":","",$thispagetitle);
$thispagetitle = trim($thispagetitle);

$spcmenu="<div class=\"content\" id=\"spcmenu_container\">\n<ul id=\"spcmenu\">\n";
$extrasmenu="<div class=\"content\" id=\"extrasmenu_container\">\n<ul id=\"extrasmenu\">\n";
$spcmenulines ='' ;
$coursepass = 1;
$currflag = 0;
foreach ($mymodinfo as $key => $currentarry) {
    //$rprp =$currentarry->showavailability;
    $href= "href='$CFG->wwwroot/mod/$currentarry->mod/view.php?id=$currentarry->cm'";
    $currpage = "";
    $pagename = $currentarry->name;
    
    $pagename = $currentarry->name;
    if ($thispagetitle == $pagename) {
        $currpage = "class='current'";$currflag = 1;
        $href="";
    }
    $progress = 'not_viewed';
    $doctype  = $currentarry->mod;
    //if (strcasecmp($pagename, 'overview') == 0) { $doctype  = 'home'; }
    if (strcasecmp($pagename, 'certificate') == 0) { $doctype  = 'cert'; }
    if (strcasecmp($pagename, 'certificate-earned') == 0) { $doctype  = 'cert_earned'; $pagename = 'Certificate Earned';$progress = 'progress_pass'; }
    $currsec  = $currentarry->section; //this refers to Moodle "topics" within a course (called sections here)
    $tooltip = "";
    $showthis = "";
    if($currsec =='0' && $doctype =='quiz') {
        $maxm_grade = trim($currentarry->gradeinfo->grademax);
        $quiz_grade = trim($currentarry->gradeinfo->grades[$USER->id]->grade);
        if ($quiz_grade == "") {
            $progress = 'progress_none';
            $coursepass = 0;
            $tooltip = 'title ="QUIZ - required to obtain a certificate"';
        } else {
            if ($quiz_grade<$maxm_grade) {
                $progress = 'progress_alert';
                $coursepass = 0;
                $tooltip = 'title ="QUIZ - attempted but not passed"';
            } else {
                $progress = 'progress_pass';
                $coursepass = ($coursepass==0)?0:1;
                $tooltip = 'title ="QUIZ - Passed!"';
            }
        }
    }
    $writeline="<li $currpage><span class=\"$doctype\"><a $href class=\"$progress\" $tooltip>$pagename</a></span></li>\n";//| $coursepass |
    if ($doctype =='cert' && $coursepass == 1) { $writeline=""; }
    if ($doctype =='cert_earned' && $coursepass == 0) { $writeline=""; }

    if ($currsec =='0') {
        $spcmenulines.= $writeline;
    }
    elseif ($currsec =='1') {
        $extrasmenu.= $writeline;
    }
}    


$currclass = "";
if ($currflag==0) { $currclass = "class='current'"; }//nothing else was the current page so this must be it

$homeline="<li $currclass><span class=\"home\"><a href=\"$CFG->wwwroot/course/view.php?id=$COURSE->id\" class=\"not_viewed\">Overview</a></span></li>\n";

echo "$spcmenu $homeline $spcmenulines\n</ul></div>";
echo "$extrasmenu\n</ul></div>";

// Record Certificate Earned Date.
if ($coursepass == 1) {
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
?>

<?php if ($hassidepre) {echo $OUTPUT->blocks_for_region('side-pre');}?>

</div>

            </div>
            <div id="region-main-wrap">
                <div id="region-main">
                    <h1 class="headermain"><?php echo $PAGE->heading ?></h1>
                    <div class="region-content"><?php //php $varz = $PAGE->title;echo $varz;?>
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
