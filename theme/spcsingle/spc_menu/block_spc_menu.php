<?php

class block_spc_menu extends block_list_spc {

    /** @var int */
    public static $navcount;
    /** @var bool */
    protected $contentgenerated = false;
    /** @var bool|null */
    protected $docked = null;
    
    function init() {
        $this->title = get_string('pluginname', 'block_spc_menu');
    }

    /**
     * Multiple instances allowed
     **/
    function instance_allow_multiple() {
        return true;
    }

    /**
     * Set what pages block can appear
     **/
    function is_empty() {
        if ( !has_capability('moodle/block:view', $this->context) ) {
            return true;
        }

        $this->get_content();
        return (empty($this->content->items) && empty($this->content->footer));
    }

    protected function formatted_contents($output) {
        if (!method_exists($output, 'list_block_contents_spc')) {
            return parent::formatted_contents($output);
        }
        $this->get_content();
        $this->get_required_javascript();
        if (!empty($this->content->items)) {
            return $output->list_block_contents_spc($this->content->items);
        } else {
        //$formatted_contents=print_r($modinfo,true);
            return 'Block not available on this page';
        }
    }

    function html_attributes() {
        $attributes = parent::html_attributes();
        $attributes['class'] .= ' list_block';
        return $attributes;
    }


    function applicable_formats() {
        return array(
        /*'all'=>true,*/
                      'site' => false,
               'course-view' => true ,
        'course-view-topics' => true ,
                       'mod' => true , 
                  'mod-quiz' => true , 
             'mod-quiz-view' => true);
    }

    /**
     * The navigation block cannot be hidden
     **/
    function  instance_can_be_hidden() {
        return false;
    }
    
    function get_content() {
        /**
         * if id is empty/unexistent, the url is missing/incorrect
         * id is not critical. so we pass a '0' instead
         **/
        if (!isset($_GET["id"]) || empty($_GET["id"])) {
            $varcm ='0';
        } else {
            $varcm=$_GET["id"];
        }

        global $CFG, $USER, $DB, $OUTPUT, $PAGE;

        if ($this->content !== NULL) {
            return $this->content;
        }

        $this->content = new stdClass();
        $this->content->items = array();
        $this->content->icons = array();
        $this->content->footer = '';

        if (empty($this->instance)) {
            return $this->content;
        }

        $course = $this->page->course;
        
        require_once($CFG->dirroot.'/course/lib.php');
        
        $context = get_context_instance(CONTEXT_COURSE, $course->id);
        $currmod = get_context_instance(CONTEXT_MODULE, $course->id);
        $isediting = $this->page->user_is_editing() && has_capability('moodle/course:manageactivities', $context);
        //echo '<pre>';print_r($this);echo '</pre>';
        //echo '<pre>';print_r($course->modinfo);echo '</pre>';
        $modinfo = get_fast_modinfo($course);
        //echo '<pre>';print_r($modinfo);echo '</pre>';
        
        //$item = null;
        //foreach($array as $struct) {
        //    if ($v == $struct->ID) {
        //        $item = $struct;
        //        break;
        //    }
        //}
        $options = array('overflowdiv'=>true);

        if (!empty($modinfo->sections[0])) {
            foreach($modinfo->sections[0] as $cmid) {
                $cm = $modinfo->cms[$cmid];
                if (!$cm->uservisible) {
                    continue;
                }
                list($content, $instancename) = get_print_section_cm_text($cm, $course);

                if (!($url = $cm->get_url())) {
                    $this->content->items[] = $content;
                    //$this->content->icons[] = '';
                } else {
                    $cm_visible = $cm->visible ? '' : 'dimmed';
                    //$icon =     '';
                    ////$varcm.$cm->instance.$cm->module .'<img src="' . $cm->get_icon_url() . '" class="icon" alt="" />&nbsp;';
                    $this->content->items[] = array(
                        'page_id'       => $varcm
                        ,'a_title'      => $cm->modplural
                        ,'a_url'        => $url
                        ,'cm_instance'  => $cm->instance
                        ,'cm_module'    => $cm->module
                        ,'cm_visible'   => $cm_visible
                        ,'cm_extra'     => $cm->extra
                        ,'cm_icon_url'  => $cm->get_icon_url()
                        ,'instancename' => $instancename
                        ,'thispage'     => $PAGE->title
                        //,'ac_cm'       => $ac_cm
                        //,'context'       => $context
                        //,'currmod'      => $currmod
                    );
                        
                    //$this->content->items[] = '<a title="'.$cm->modplural.'" '.$linkcss.' '.$cm->extra.
                    //        ' href="' . $url . '">' . $icon .$instancename . '</a>';
                }
            }
        }
        return $this->content;

        if (!empty($modnames)) {
            $this->content->footer = print_section_add_menus($course, 0, $modnames, true, true);
        } else {
            $this->content->footer = '';
        }

        return $this->content;
    }
}


class block_list_spc extends block_base {
    var $content_type  = BLOCK_TYPE_LIST;
    function is_empty() {
        if ( !has_capability('moodle/block:view', $this->context) ) {
            return true;
        }

        $this->get_content();
        return (empty($this->content->items) && empty($this->content->footer));
    }

    protected function formatted_contents($output) {
    return 'TEST';
        $this->get_content();
        $this->get_required_javascript();
        if (!empty($this->content->items)) {
            if (method_exists($output, 'list_block_contents_spc')) {
                return $output->list_block_contents_spc($this->content->items);
            }
        } else {
            return 'Block not available on this page';
        }
    }

    function html_attributes() {
        $attributes = parent::html_attributes();
        $attributes['class'] .= ' list_block';
        return $attributes;
    }
}
