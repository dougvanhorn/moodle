<?php  //$Id: upgrade.php,v 1.4 2011/04/26 06:20:25 jacolina from journal.php by davmon Exp $

require_once($CFG->dirroot.'/mod/spcnotebook/lib.php');

function xmldb_spcnotebook_upgrade($oldversion=0) {

    global $CFG, $DB;
    $dbman = $DB->get_manager();

    $result = true;

    if ($oldversion < 2013060600) {

        // Define field question to be added to spcnotebook
        $table = new xmldb_table('spcnotebook');
        $field = new xmldb_field('question', XMLDB_TYPE_TEXT, null, null, null, null, null, 'introformat');

        // Conditionally launch add field question
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // spcnotebook savepoint reached
        upgrade_mod_savepoint(true, 2013060600, 'spcnotebook');
    }

    // Add spcnotebook instances to the gradebook
    if ($oldversion < 2010120300) {

        spcnotebook_update_grades();
        upgrade_mod_savepoint(true, 2010120300, 'spcnotebook');
    }

    // Change assessed field for grade
    if ($result && $oldversion < 2011040600) {

        // Rename field assessed on table spcnotebook to grade
        $table = new xmldb_table('spcnotebook');
        $field = new xmldb_field('assessed', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'days');

        // Launch rename field grade
        $dbman->rename_field($table, $field, 'grade');

        // spcnotebook savepoint reached
        upgrade_mod_savepoint(true, 2011040600, 'spcnotebook');
    }

    if ($result && $oldversion < 2012032001) {

        // Changing the default of field rating on table spcnotebook_entries to drop it
        $table = new xmldb_table('spcnotebook_entries');
        $field = new xmldb_field('rating', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'format');

        // Launch change of default for field rating
        $dbman->change_field_default($table, $field);

        // Updating the non-marked entries with rating = NULL
        $entries = $DB->get_records('spcnotebook_entries', array('timemarked' => 0));
        if ($entries) {
            foreach ($entries as $entry) {
                $entry->rating = NULL;
                $DB->update_record('spcnotebook_entries', $entry);
            }
        }

        // spcnotebook savepoint reached
        upgrade_mod_savepoint(true, 2012032001, 'spcnotebook');
    }


    return $result;
}
