<?php
/**
 * Defines the version of spcdata
 *
 * This code fragment is called by moodle_needs_upgrading() and
 * /admin/index.php
 *
 * @package    mod
 * @subpackage spcdata
 * @copyright  PBS
 */

defined('MOODLE_INTERNAL') || die();

$module->version   = 2013062400;      // If version == 0 then module will not be installed
$module->requires  = 2010031900;      // Requires this Moodle version
$module->cron      = 0;               // Period for cron to check this module (secs)
$module->component = 'mod_spcdata';   // To check on upgrade, that module sits in correct place
