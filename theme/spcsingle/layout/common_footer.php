<?php if ($PAGE->user_allowed_editing()) { ?>
 
    <?php if (empty($PAGE->layout_options['nofooter'])) { ?>
        <div id="page-footer" class="clearfix">
            <div id="spc-template-name" class="clearfix">
                <?php //echo $tmpn; /* template name for debugging*/ ?>
            </div>
            <?php
            //echo $OUTPUT->login_info();
            echo $OUTPUT->home_link();
            echo $OUTPUT->standard_footer_html();
            $buttons = $OUTPUT->edit_button($PAGE->url);
            echo $buttons;
            ?>
        </div>
    <?php } ?>
    
    </div>
<?php }?>

<div class="tl_byline">
    &copy; 2013 PBS - <a href="http://www.pbs.org/teacherline/">PBS TeacherLine</a> 
    <span class='tlref'><?php $tlref=$COURSE->idnumber; $tlref2=$COURSE->id;echo $tlref.$tlref2?></span>
</div>

</div>

<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-27129672-2', 'pbs.org');
  ga('send', 'pageview');

</script>

<?php echo $OUTPUT->standard_end_of_body_html() ?>
      
<?php
/*
$completioninfo = new completion_info($course->id);
$mycompletiondata = $completioninfo->get_data($course->id,true);
echo "<pre class=tmpmenu>";
print_r($mycompletiondata);
echo "</pre>";
*/
// echo "<div class='headermenu'>";
// echo $OUTPUT->login_info();
// echo $OUTPUT->lang_menu();
// echo $PAGE->headingmenu;
// echo "</div> ";          

//echo "<div class='debuginfo'>";
//htmlentities (print_r($mymodinfo)); 
/*
ob_start();
print_r($PAGE->navigation); 
$navString = ob_get_contents();
ob_end_clean();
$f = fopen("file02.txt", "w"); 
fwrite($f, $navString); 
fclose($f);
*/
//echo "</div>";
/*
echo "<pre class='debug-hide'>";
print_r($mymodinfo);
echo "</pre>";    
*/
?>    
</body>
</html>
