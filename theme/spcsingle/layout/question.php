<?php include 'common_header.php';?>
<!-- END OF HEADER -->
<div id=maincontainer class="shdw-dn rnd main">
	<div id="page-wrap" >
		<div id="page-content">
			<div class="block-region region-content" id="region-pre">
				<div class="side-pre-content">
					<?php if ($hassidepre) {echo $OUTPUT->blocks_for_region('side-pre');}?>
				</div>
				<div class="side-post-content">
					<?php if ($hassidepost) {echo $OUTPUT->blocks_for_region('side-post');} ?>
				</div>
			</div>
			<div id="region-main-wrap">
				<div id="region-main">
					<h1 class="headermain"><?php echo $PAGE->heading ?></h1>
					<div class="question">
						<!--?php echo $OUTPUT->main_content() ?-->
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<!-- START OF FOOTER -->
<?php $tmpn=__FILE__; include 'common_footer.php';?>