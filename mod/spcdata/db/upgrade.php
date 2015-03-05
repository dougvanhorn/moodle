<?php
/**
 * This file keeps track of upgrades to the spcdata module
 *
 * Sometimes, changes between versions involve alterations to database
 * structures and other major things that may break installations. The upgrade
 * function in this file will attempt to perform all the necessary actions to
 * upgrade your older installation to the current version. If there's something
 * it cannot do itself, it will tell you what you need to do.  The commands in
 * here will all be database-neutral, using the functions defined in DLL libraries.
 *
 * @package    mod
 * @subpackage spcdata
 * @copyright  PBS
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Execute spcdata upgrade from the given old version
 *
 * @param int $oldversion
 * @return bool
 */
function xmldb_spcdata_upgrade($oldversion) {
    global $DB;

    //$dbman = $DB->get_manager(); // loads ddl manager and xmldb classes

    // Documentation:
    // http://docs.moodle.org/en/Development:XMLDB_Documentation

    // First example, some fields were added to install.xml on 2007/04/01
    // For each upgrade block, the file spcdata/version.php
    // needs to be updated . Such change allows Moodle to know
    // that this file has to be processed.


    // DVH Mar 5 2015: Disabled migrations, not needed in new 2.8 installation.
    //if ($oldversion < 2013060700) {

    //    // Define field userid to be added to spcdata
    //    $table = new xmldb_table('spcdata');
    //    $field = new xmldb_field('userid', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'timemodified');

    //    // Conditionally launch add field userid
    //    if (!$dbman->field_exists($table, $field)) {
    //        $dbman->add_field($table, $field);
	//    }
    //    // Define field certificatedate to be added to spcdata
    //    $table = new xmldb_table('spcdata');
    //    $field = new xmldb_field('certificatedate', XMLDB_TYPE_NUMBER, '8', null, null, null, null, 'userid');

    //    // Conditionally launch add field certificatedate
    //    if (!$dbman->field_exists($table, $field)) {
    //        $dbman->add_field($table, $field);
    //    }

    //    // spcdata savepoint reached
    //    upgrade_mod_savepoint(true, 2013060700, 'spcdata');
    //}
	
   
    //if ($oldversion < 2013062400) {

    //    // Redefine `certificatedate` to be a proper date in the database.
    //    $table = new xmldb_table('spcdata');

    //    // Date is a BigInt, like all other date fields.
    //    $field = new xmldb_field('certificatedate', XMLDB_TYPE_INTEGER, '10');

    //    // Modify the certificatedate field type.
    //    $dbman->change_field_type($table, $field);

    //    // spcdata savepoint reached
    //    upgrade_mod_savepoint(true, 2013062400, 'spcdata');
    //}

    // Additional upgrade blocks here.

    // Final return of upgrade result (true, all went good) to Moodle.
    return true;
}
