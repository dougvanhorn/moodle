<?php  // Moodle configuration file

unset($CFG);
global $CFG;
$CFG = new stdClass();

$CFG->dbtype    = 'pgsql';
$CFG->dblibrary = 'native';
$CFG->dbhost    = 'localhost';
$CFG->dbname    = 'moodle28';
$CFG->dbuser    = 'doug';
$CFG->dbpass    = 'doug';
$CFG->prefix    = 'mdl_';
$CFG->dboptions = array (
  'dbpersist' => 0,
  'dbport' => '',
  'dbsocket' => '',
);

$CFG->wwwroot   = 'http://pbs.example.com/moodle28';
$CFG->dataroot  = '/home/doug/workspace/pbs/teacherline/moodle/Moodle28/moodledata';
$CFG->admin     = 'admin';

$CFG->directorypermissions = 0777;

// TeacherLine Configuration
// =============================================================================
$CFG->passwordsaltmain = 'm1d*k5n@a12anp>U?Ur_}@2D[82Y';

// MOODLE28_USERNAME, MOODLE28_PASSWORD
$CFG->tl_user = 'doug';
$CFG->tl_pass = 'doug';
// =============================================================================

// There is no php closing tag in this file,
// it is intentional because it prevents trailing whitespace problems!
