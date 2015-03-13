<?php
// Provides a lot of "global-ish" variables to layout files that include this 
// one.  If you're reading a layout file and don't know where a variable came 
// from, it's probably from in here.

$hassidepre = $PAGE->blocks->region_has_content('side-pre', $OUTPUT);
$hassidepost = $PAGE->blocks->region_has_content('side-post', $OUTPUT);
$custommenu = $OUTPUT->custom_menu();
$hascustommenu = (empty($PAGE->layout_options['nocustommenu']) && !empty($custommenu));
$bodyclasses = array();

if ($hascustommenu) {
    $bodyclasses[] = 'has_custom_menu';
}

$fullbaseurl = "$CFG->wwwroot";

// Set this globally.  If there's an error, the page title is modified.
$PAGE_TITLE = $PAGE->title;

echo $OUTPUT->doctype() ?>
<html <?php echo $OUTPUT->htmlattributes() ?>>
<head>
    <title><?php echo $PAGE_TITLE ?></title>
    <link rel="shortcut icon" href="<?php echo $OUTPUT->pix_url('favicon', 'theme')?>" />
    <meta name="description" content="<?php p(strip_tags(format_text($SITE->summary, FORMAT_HTML))) ?>" />
    <link href='http://fonts.googleapis.com/css?family=Open+Sans:400italic,700italic,400,700' rel='stylesheet' type='text/css'>
    <?php echo $OUTPUT->standard_head_html() ?>
</head>
<body id="<?php p($PAGE->bodyid) ?>" class="<?php p($PAGE->bodyclasses.' '.join(' ', $bodyclasses)) ?>">
<?php echo $OUTPUT->standard_top_of_body_html() ?>

<?php 

$MODINFO = get_fast_modinfo($PAGE->course);

$course = $PAGE->course;
require_once($CFG->dirroot.'/course/lib.php');
$context = context_course::instance($course->id);

//ADD GRADES GET BRANDING IMAGE
// adds gradeinfo array to each graded activity in MODINFO array
require_once($CFG->libdir.'/gradelib.php');
                //echo $COURSE->id;
//$completioninfo = new completion_info($COURSE->id);
                //$completiondata = $completioninfo->get_data($thismod, true);
//
$branding_urls = array(
    "branding_url" => null,
    "brandback_url" => null,
);
foreach ($MODINFO->cms as $course_modinfo) {
    /*QUIZ GRADES*/
    if ($course_modinfo->modname == 'quiz') {
        $quizid  = $course_modinfo->id;
        $grading_info = grade_get_grades($COURSE->id, 'mod', 'quiz', $quizid, $USER->id);
        if (!empty($grading_info->items)) {
            $item = $grading_info->items[0];
            if (isset($item->grades[$USER->id])) {
                $grade = $item->grades[$USER->id];
                if ($grade->overridden) {
                    $mygrade = $grade->grade + 0; // Convert to number.
                    $mygradeoverridden = true;
                }
                if (!empty($grade->str_feedback)) {
                    $gradebookfeedback = $grade->str_feedback;
                }
            }
            $course_modinfo->gradeinfo = $item;
        }
    }

    /*
     * BRANDING and other images
     * The idea here is that the Course developer adds "resource" modules to 
     * the course.  This code picks up those resources and drops them into 
     * known buckets.
     *
     * Right now, there are two known buckets, defined below.
     */
    if ($course_modinfo->modname == 'resource') {
        $key = $course_modinfo->name . '_url';
        //r_error_log($key);
        $module_context = context_module::instance($course_modinfo->id);
        //r_error_log("PATH: " . $module_context->path);
        $filestore = get_file_storage();
        // context id, component, file area, item id, sort order, include dirs.
        $filelist = $filestore->get_area_files($module_context->id, "mod_resource", "content", 0, "sortorder DESC, id ASC", false);
        //r_error_log("Files:");
        //r_error_log($filelist);
        foreach ($filelist as $f) {
            //r_error_log($f);
            $filename = $f->get_filename();
        }
        $url = moodle_url::make_pluginfile_url($f->get_contextid(), $f->get_component(), $f->get_filearea(), $f->get_itemid(), $f->get_filepath(), $f->get_filename());
        //r_error_log("Need to set $key = " . $url);
        $branding_urls[$key] = $url;
        //r_error_log($currname);
        //$context = context_module::instance($course_modinfo);
        //$course_modinfo->context = $context;
        //$br_contextid = $course_modinfo->context->id;
        //$br_arrpath = explode("/", $course_modinfo->context->path);
        //$filestore = get_file_storage();
        //$br_filelist = $filestore->get_area_files($br_contextid, 'mod_resource', 'content', 0, 'sortorder DESC, id ASC', false);
        //foreach ($br_filelist as $f) { $br_filename = $f->get_filename(); }
        //$currname = "$CFG->wwwroot/pluginfile.php/$br_contextid/mod_resource/content/$br_arrpath[1]/$br_filename";
    }
}

//ADD GRADES/BRANDING END

// CUSTOM-COURSE-DATA HACK

// Uses Moodle's "label" activity to store custom course data
// Recursively traverses $modinfo to find "label" resources
// if the contents of "label" are formated properly extract 
// as an array variable. Pipe "|" is used as a separator.
// format: variable_name_desired|one|or|more|content|elements|separated|by|pipes
// e.g. Top Menu Item (has two array members):
// top_menu_item|'Additional PD'|http://teacherline.org/search.py?terms=term
foreach ($MODINFO as $key => $arr_value) {
    $currentarry = $arr_value; //print_r($currentarry);
    if ($currentarry->mod =='label') {
        $value = $currentarry->extra;
        $value=preg_replace('/<[^>]*>/', '', $value);//clean-out html tags
        $contentsarray=explode("|",$value);          //convert to array
        $newvariable=array_shift($contentsarray);    //remove first array element BUT keep it to name the new variable
        $$newvariable=$contentsarray;                //($$)makes a new variable with $newvariable as its key and $contentsarray as content.
    }
}
//HACK END
?>

<div id="page">
<div id="msgid">
</div>
<div id="headerbox">
    <?php if (isset($top_menu_item1) || isset($top_menu_item2) || isset($top_menu_item3)) { ?>
    <!-- Display the custom menu block if we have a menu. -->
    <div id="custommenu" class="rnd">
        <div id="custom_menu" class="spc-menu spc-menu-horizontal">
            <div class="spc-menu-content">
                <ul>
                    <?php if(isset($top_menu_item1)){?>
                    <li class="spc-menuitem">
                        <a class="spc-menuitem-content" 
                        title="<?php echo $top_menu_item1[0];?>" 
                         href="<?php echo $top_menu_item1[1];?>">
                        <?php echo $top_menu_item1[0];?></a>
                    </li>
                    <?php }?>
                    <?php if(isset($top_menu_item2)){?>
                    <li class="spc-menuitem">
                        <a class="spc-menuitem-content" 
                        title="<?php echo $top_menu_item2[0];?>" 
                         href="<?php echo $top_menu_item2[1];?>">
                        <?php echo $top_menu_item2[0];?></a>
                    </li>
                    <?php }?>       
                    <?php if(isset($top_menu_item3)){?>
                    <li class="spc-menuitem">
                        <a class="spc-menuitem-content" 
                        title="<?php echo $top_menu_item3[0];?>" 
                         href="<?php echo $top_menu_item3[1];?>">
                        <?php echo $top_menu_item3[0];?></a>
                    </li>
                    <?php }?>
                </ul>
            </div>
        </div><!-- custom_menu-->
    </div><!-- custommenu -->
    <?php } ?>
    <!--div id="page-header" class="clearfix">   </div-->
    <?php 

    $brand_main_logo  = ($branding_urls['branding_url']) ? $branding_urls['branding_url'] : $OUTPUT->pix_url('PBSTeacherLine-logoSPC', 'theme');
    $brand_back_art   = ($branding_urls['brandback_url']) ? $branding_urls['brandback_url'] : $OUTPUT->pix_url('base-back_trnsp_blue', 'theme');
    $brand_back_color = 'transparent'; //(isset($branding_color))? $branding_color[0] :'transparent';
    ?>
    <div id="page-branding" class="rnd shdw" <?php echo "style='background:url($brand_back_art)repeat scroll 0 0 $brand_back_color;'";?>>
        <div class="cobrand-logo">
            <?php echo "<img src='$brand_main_logo'/>";?>
        </div>
        <div class="brand-logo">
            <div id="PBSTeacherline" class="powered-by">PBS Teacherline</div>
        </div>            
    </div>
</div>
