<?php include 'common_header.php';?>
<!-- END OF HEADER -->
<div id="page-wrap">
<div id="page-content">
    <div id="region-pre" class="block-region">
        <div class="region-content">
        <?php
        if ($hassidepre) {
            echo $OUTPUT->blocks_for_region('side-pre');
        } 
        if ($hassidepost) {
            echo $OUTPUT->blocks_for_region('side-post');
        } 
        ?>
        </div>
    </div>
    <div id="region-main-wrap">
        <div id="region-main">
        <h1 class="headermain"><?php echo $PAGE->heading ?></h1>
            <div class="region-content">
                <?php echo $OUTPUT->main_content() ?>
            </div>
        </div>
    </div>
</div>
</div>

<!-- START OF FOOTER -->
<?php $tmpn=__FILE__; include 'common_footer.php';?>
