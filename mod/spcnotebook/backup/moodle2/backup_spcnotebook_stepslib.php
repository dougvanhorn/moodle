<?php

class backup_spcnotebook_activity_structure_step extends backup_activity_structure_step {

    protected function define_structure() {

        $spcnotebook = new backup_nested_element('spcnotebook', array('id'), array(
            'name', 'intro', 'introformat', 'days', 'grade', 'timemodified'));

        $entries = new backup_nested_element('entries');

        $entry = new backup_nested_element('entry', array('id'), array(
            'userid', 'modified', 'text', 'format', 'rating',
            'entrycomment', 'teacher', 'timemarked', 'mailed'));

        // spcnotebook -> entries -> entry
        $spcnotebook->add_child($entries);
        $entries->add_child($entry);

        // Sources
        $spcnotebook->set_source_table('spcnotebook', array('id' => backup::VAR_ACTIVITYID));

        if ($this->get_setting_value('userinfo')) {
            $entry->set_source_table('spcnotebook_entries', array('spcnotebook' => backup::VAR_PARENTID));
        }

        // Define id annotations
        $entry->annotate_ids('user', 'userid');
        $entry->annotate_ids('user', 'teacher');

        // Define file annotations
        $spcnotebook->annotate_files('mod_spcnotebook', 'intro', null); // This file areas haven't itemid
        $entry->annotate_files('mod_spcnotebook_entries', 'text', null); // This file areas haven't itemid
        $entry->annotate_files('mod_spcnotebook_entries', 'entrycomment', null); // This file areas haven't itemid

        return $this->prepare_activity_structure($spcnotebook);
    }
}
