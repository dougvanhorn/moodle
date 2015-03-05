<?php
/**
 * Definition of log events
 *
 * NOTE: this is an example how to insert log event during installation/update.
 * It is not really essential to know about it, but these logs were created as example
 * in the previous 1.9 NEWMODULE.
 *
 * @package    mod
 * @subpackage spcdata
 * @copyright  PBS
 */

defined('MOODLE_INTERNAL') || die();

global $DB;

$logs = array(
    array('module'=>'spcdata', 'action'=>'add', 'mtable'=>'spcdata', 'field'=>'name'),
    array('module'=>'spcdata', 'action'=>'update', 'mtable'=>'spcdata', 'field'=>'name'),
    array('module'=>'spcdata', 'action'=>'view', 'mtable'=>'spcdata', 'field'=>'name'),
    array('module'=>'spcdata', 'action'=>'view all', 'mtable'=>'spcdata', 'field'=>'name')
);
