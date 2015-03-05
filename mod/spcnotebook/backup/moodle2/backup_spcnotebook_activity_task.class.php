<?php

require_once($CFG->dirroot.'/mod/spcnotebook/backup/moodle2/backup_spcnotebook_stepslib.php');

class backup_spcnotebook_activity_task extends backup_activity_task {

    protected function define_my_settings() {}

    protected function define_my_steps() {
        $this->add_step(new backup_spcnotebook_activity_structure_step('spcnotebook_structure', 'spcnotebook.xml'));
    }

    static public function encode_content_links($content) {
        global $CFG;

        $base = preg_quote($CFG->wwwroot.'/mod/spcnotebook','#');

        $pattern = "#(".$base."\/index.php\?id\=)([0-9]+)#";
        $content = preg_replace($pattern, '$@SPCNOTEBOOKINDEX*$2@$', $content);

        $pattern = "#(".$base."\/view.php\?id\=)([0-9]+)#";
        $content = preg_replace($pattern, '$@SPCNOTEBOOKVIEWBYID*$2@$', $content);

        $pattern = "#(".$base."\/report.php\?id\=)([0-9]+)#";
        $content = preg_replace($pattern, '$@SPCNOTEBOOKREPORT*$2@$', $content);

        $pattern = "#(".$base."\/edit.php\?id\=)([0-9]+)#";
        $content = preg_replace($pattern, '$@SPCNOTEBOOKEDIT*$2@$', $content);

        return $content;
    }
}
