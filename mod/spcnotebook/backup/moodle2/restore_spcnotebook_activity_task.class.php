<?php

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/mod/spcnotebook/backup/moodle2/restore_spcnotebook_stepslib.php');

class restore_spcnotebook_activity_task extends restore_activity_task {

    protected function define_my_settings() {}

    protected function define_my_steps() {
        $this->add_step(new restore_spcnotebook_activity_structure_step('spcnotebook_structure', 'spcnotebook.xml'));
    }

    static public function define_decode_contents() {

        $contents = array();
        $contents[] = new restore_decode_content('spcnotebook', array('intro'), 'spcnotebook');
        $contents[] = new restore_decode_content('spcnotebook_entries', array('text', 'entrycomment'), 'spcnotebook_entry');

        return $contents;
    }

    static public function define_decode_rules() {

        $rules = array();
        $rules[] = new restore_decode_rule('SPCNOTEBOOKINDEX', '/mod/spcnotebook/index.php?id=$1', 'course');
        $rules[] = new restore_decode_rule('SPCNOTEBOOKVIEWBYID', '/mod/spcnotebook/view.php?id=$1', 'course_module');
        $rules[] = new restore_decode_rule('SPCNOTEBOOKREPORT', '/mod/spcnotebook/report.php?id=$1', 'course_module');
        $rules[] = new restore_decode_rule('SPCNOTEBOOKEDIT', '/mod/spcnotebook/edit.php?id=$1', 'course_module');

        return $rules;

    }

    public static function define_restore_log_rules() {

        $rules = array();
        $rules[] = new restore_log_rule('spcnotebook', 'view', 'view.php?id={course_module}', '{spcnotebook}');
        $rules[] = new restore_log_rule('spcnotebook', 'view responses', 'report.php?id={course_module}', '{spcnotebook}');
        $rules[] = new restore_log_rule('spcnotebook', 'add entry', 'edit.php?id={course_module}', '{spcnotebook}');
        $rules[] = new restore_log_rule('spcnotebook', 'update entry', 'edit.php?id={course_module}', '{spcnotebook}');
        $rules[] = new restore_log_rule('spcnotebook', 'update feedback', 'report.php?id={course_module}', '{spcnotebook}');

        return $rules;
    }

    public static function define_restore_log_rules_for_course() {

        $rules = array();
        $rules[] = new restore_log_rule('spcnotebook', 'view all', 'index.php?id={course}', null);

        return $rules;
    }
}
