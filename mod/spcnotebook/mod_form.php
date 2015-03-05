<?php
require_once ($CFG->dirroot.'/course/moodleform_mod.php');

class mod_spcnotebook_mod_form extends moodleform_mod {

    function definition() {

        global $COURSE;
        $mform    =& $this->_form;

        $mform->addElement('header', 'general', get_string('general', 'form'));

        $mform->addElement('text', 'name', get_string('spcnotebookname', 'spcnotebook'), array('size'=>'64'));
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');

        $this->add_intro_editor(true, get_string('spcnotebookintro', 'spcnotebook'));
        /*    $mform->addElement('htmleditor', 'intro', get_string('spcnotebookintro', 'spcnotebook'), array('rows'=>'20'));
		   $mform->setType('intro', PARAM_CLEANHTML);
		   $mform->addRule('intro', null, 'required', null, 'client');*/

		$mform->addElement('htmleditor', 'question', get_string('spcnotebookquestion', 'spcnotebook'));
		$mform->setType('question', PARAM_RAW);

        $options = array();
        $options[0] = get_string('alwaysopen', 'spcnotebook');
        for ($i=1;$i<=13;$i++) {
            $options[$i] = get_string('numdays', '', $i);
        }
        for ($i=2;$i<=16;$i++) {
            $days = $i * 7;
            $options[$days] = get_string('numweeks', '', $i);
        }
        $options[365] = get_string('numweeks', '', 52);
        $mform->addElement('select', 'days', get_string('daysavailable', 'spcnotebook'), $options);
        if ($COURSE->format == 'weeks') {
            $mform->setDefault('days', '7');
        } else {
            $mform->setDefault('days', '0');
        }

        $this->standard_grading_coursemodule_elements();

        $this->standard_coursemodule_elements();

        $this->add_action_buttons();
    }

}
