<?php 

/**
 * Overridden renderers are triggered by the $THEME->rendererfactory = 
 * 'theme_overridden_renderer_factory' setting.
 *
 * Naming convention overrides the Default Core Renderer.  An instance of 
 * this object will be available as $OUTPUT in the Layout files.
 */
class theme_spcmulti_core_renderer extends core_renderer {
     /**
     * Renders a custom menu object (located in outputcomponents.php)
     *
     * The custom menu this method produces makes use of the spc menunav widget
     * and requires very specific html elements and classes.
     *
     * @staticvar int $menucount
     * @param custom_menu $menu
     * @return string
     */
    protected function render_custom_menu(custom_menu $menu) {
        //static $menucount = 0;
        // If the menu has no children return an empty string
        if (!$menu->has_children()) {
            return '';
        }
        // Build the root nodes as required by YUI
        $content = html_writer::start_tag('div', array('id'=>'custom_menu', 'class'=>'spc-menu spc-menu-horizontal'));
        $content .= html_writer::start_tag('div', array('class'=>'spc-menu-content'));
        $content .= html_writer::start_tag('ul');
        // Render each child
        foreach ($menu->get_children() as $item) {
            $content .= $this->render_custom_menu_item($item);
        }
        // Close the open tags
        $content .= html_writer::end_tag('ul');
        $content .= html_writer::end_tag('div');
        $content .= html_writer::end_tag('div');
        // Return the custom menu
        return $content;
    }
    protected function render_custom_menu_item(custom_menu_item $menunode) {
        // Required to ensure we get unique trackable id's
        static $submenucount = 0;
        if ($menunode->has_children()) {
            // If the child has menus render it as a sub menu
            //$submenucount++;
            $content = html_writer::start_tag('li');
            if ($menunode->get_url() !== null) {
                $url = $menunode->get_url();
            } else {
                $url = '#cm_submenu_'.$submenucount;
            }
            $content .= html_writer::link($url, $menunode->get_text(), array('class'=>'spc-menu-label', 'title'=>$menunode->get_title()));
            $content .= html_writer::start_tag('div', array('id'=>'cm_submenu', 'class'=>'spc-menu custom_menu_submenu'));
            $content .= html_writer::start_tag('div', array('class'=>'spc-menu-content'));
            $content .= html_writer::start_tag('ul');
            foreach ($menunode->get_children() as $menunode) {
                $content .= $this->render_custom_menu_item($menunode);
            }
            $content .= html_writer::end_tag('ul');
            $content .= html_writer::end_tag('div');
            $content .= html_writer::end_tag('div');
            $content .= html_writer::end_tag('li');
        } else {
            // The node doesn't have children so produce a final menuitem
            $content = html_writer::start_tag('li', array('class'=>'spc-menuitem'));
            if ($menunode->get_url() !== null) {
                $url = $menunode->get_url();
            } else {
                $url = '#';
            }
            $content .= html_writer::link($url, $menunode->get_text(), array('class'=>'spc-menuitem-content', 'title'=>$menunode->get_title()));
            $content .= html_writer::end_tag('li');
        }
        // Return the sub menu
        return $content;
    }   
    /**
     * Specialized class for displaying a block with a list of icons/text labels
     *
     * The get_content method should set $this->content->items and (optionally)
     * $this->content->icons, instead of $this->content->text.
     */

    public function list_block_contents_spc($items) {
        $detect_first = 0;
        global $topArticle;
        $topArticle=0;
        $row = 0;
        $lis = array();
        foreach ($items as $key => $item_arr) {
            //print_r($item_arr);
            $item_page_id     = $item_arr['page_id'];
            $item_a_title     = $item_arr['a_title'];
            $item_a_url       = $item_arr['a_url'];
            $item_cm_instance = $item_arr['cm_instance'];
            $item_cm_module   = $item_arr['cm_module'];
            $item_cm_visible  = $item_arr['cm_visible'];
            $item_cm_icon_url = $item_arr['cm_icon_url'];
            $item_instancename= $item_arr['instancename'];
            $thispage    = $item_arr['thispage'];
            $item_cm_extra    = $item_arr['cm_extra'];//unused at moment
            //$item_currmod     = $item_arr['currmod'];
            //$item_context     = $item_arr['context'];
            //$item_ac_cm       =>$item_arr['ac_cm'];
            $item_urlid="";
            $foo = parse_url($item_a_url);
            parse_str($foo['query'], $bar);
            $item_urlid= $bar['id'];
            //echo "$thispage == $item_instancename";
            $foopagename = $thispage == $item_instancename;
            if (($item_page_id!==$item_urlid) xor ($foopagename =='1')) {
                $string = '<a './*$item_a_title.'" class="'.$item_cm_visible.'" '. '" '.$item_cm_instance.*/' href="' . $item_a_url . '">' .$item_instancename . '</a>';
                $item = html_writer::tag('li', $string);

            } else {
                $string =$item_instancename;
                $item = html_writer::tag('li', $string, array('class' => 'active'));//
                if ($detect_first==0) {
                    $item .=  "\r\n<script>var topart =1; </script>";
                }
                //this is used to signal that this is the first article in a course which gets special styling
            }
            $detect_first+=1;
            $lis[] = $item;
            $row = 1 - $row; // Flip even/odd.
        }
        $string = html_writer::tag('ul', implode("\n", $lis), array('id' => 'spcmenu'));
        return html_writer::tag('div', $string, array('id' => 'spcmenu_container'));
    }


    /**
     * Renders the blocks for a block region in the page
     *
     * @param type $region
     * @return string
     */
    public function blocks_for_region($region) {
        $blockcontents = $this->page->blocks->get_content_for_region($region, $this);
        $output = '<!-- Begin Block -->';
        foreach ($blockcontents as $bc) {
            if ($bc instanceof block_contents) {
                // Looking to hide settings and navigation.
                $data_block = $bc->attributes['data-block'];
                //r_error_log($bc);
                //r_error_log("data-block: [" . $bc->attributes['data-block'] . "]");
                //r_error_log("user allowed editing: " . (int)$this->page->user_allowed_editing());
                // We don't want to print navigation and settings blocks here.
                if ($data_block == 'settings' && !$this->page->user_allowed_editing()) {
                    //$output .= $this->block($bc, $region);
                    $output .='<!-- Hiding Settings -->';
                }
                elseif ($data_block == 'navigation' && !$this->page->user_allowed_editing()) {
                    //$output .= $this->block($bc, $region);
                    $output .='<!-- Hiding Navigation -->';
                }
                else {
                    $output .= $this->block($bc, $region);
                }
                             
            } else if ($bc instanceof block_move_target) {
                $output .= $this->block_move_target($bc);

            } else {
                throw new coding_exception('Unexpected type of thing (' . get_class($bc) . ') found in list of block contents.');
            }
        }
        $output .= '<!-- End Block -->';
        return $output;
    }


    /**
     * Overrides of rendering of the 'back' link that normally appears in the footer.
     * See: outputrenderers.php
     * @return string HTML fragment.
     */
    public function home_link() {
        global $CFG, $SITE;

        if ($this->page->pagetype == 'site-index') {
            // Special case for site home page - please do not remove
            return '<div class="sitelink moodlelogo">' .
                   '<a title="Moodle" href="http://moodle.org/">' .
                   '<img style="width:100px;height:30px" src="' . $this->pix_url('moodlelogo') . '" alt="moodlelogo" /></a></div>';

        } else if (!empty($CFG->target_release) && $CFG->target_release != $CFG->release) {
            // Special case for during install/upgrade.
            return '<div class="sitelink">'.
                   '<a title="Moodle" href="http://docs.moodle.org/en/Administrator_documentation" onclick="this.target=\'_blank\'">' .
                   '<img style="width:100px;height:30px" src="' . $this->pix_url('moodlelogo') . '" alt="moodlelogo" /></a></div>';

        } else if ($this->page->course->id == $SITE->id || strpos($this->page->pagetype, 'course-view') === 0) {
            return '<div class="homelink"><a href="' . $CFG->wwwroot . '/">' .
                    get_string('home') . '</a></div>';

        } else {
            return '<div class="homelink"><a href="' . $CFG->wwwroot . '/course/view.php?id=' . $this->page->course->id . '">' .
                    format_string($this->page->course->shortname, true, array('context' => $this->page->context)) . '</a></div>';
        }
    }
}


//require_once($CFG->dirroot.'/course/format/renderer.php');
//format_section_renderer_base to format_section_renderer_base
require_once($CFG->dirroot.'/course/format/topics/renderer.php');

/**
 * DEPRECATED.  This has changed since Moodle 2.3:
 * https://docs.moodle.org/dev/Course_formats
 *
 * Overridden renderers are triggered by the $THEME->rendererfactory = 
 * 'theme_overridden_renderer_factory' setting.
 *
 * Naming convention overrides the Format Topics Renderer.
 */
class _theme_spcmulti_format_topics_renderer extends format_topics_renderer {
   
    //protected  function section_header($section, $course, $onsectionpage, $sectionreturn=0) {
    //    global $PAGE;
    //    return 'hello';
    //}

    protected function section_activity_summary($section, $course, $mods) {

        if (empty($section->sequence)) {
            return '';
        }

        // Generate array with count of activities in this section:
        $sectionmods = array();
        $total = 0;
        $complete = 0;
        $cancomplete = isloggedin() && !isguestuser();
        $completioninfo = new completion_info($course);
        $modsequence = explode(',', $section->sequence);
        foreach ($modsequence as $cmid) {
            $thismod = $mods[$cmid];

            if ($thismod->modname == 'label') {
                // Labels are special (not interesting for students)!
                continue;
            }

            if ($thismod->uservisible) {
                if (isset($sectionmods[$thismod->modname])) {
                    $sectionmods[$thismod->modname]['count']++;
                } else {
                    $sectionmods[$thismod->modname]['name'] = $thismod->modplural;
                    $sectionmods[$thismod->modname]['count'] = 1;
                }
                if ($cancomplete && $completioninfo->is_enabled($thismod) != COMPLETION_TRACKING_NONE) {
                    $total++;
                    $completiondata = $completioninfo->get_data($thismod, true);
                    if ($completiondata->completionstate == COMPLETION_COMPLETE) {
                        $complete++;
                    }
                }
            }
        }

        if (empty($sectionmods)) {
            // No sections
            return '';
        }

        // Output section activities summary:
        $o = '';
        $o.= html_writer::start_tag('div', array('class' => 'section-summary-activities mdl-right'));
        foreach ($sectionmods as $mod) {
            $o.= html_writer::start_tag('span', array('class' => 'activity-count'));
            $o.= $mod['name'].': '.$mod['count'];
            $o.= html_writer::end_tag('span');
        }
        $o.= html_writer::end_tag('div');

        return $o;
    }


    public function print_multiple_section_page($course, $sections, $mods, $modnames, $modnamesused) {
        global $PAGE;

        $context = context_course::instance($course->id);
        // Title with completion help icon.
        $completioninfo = new completion_info($course);
        //echo $completioninfo->display_help_icon();
        echo $this->output->heading($this->page_title(), 2, 'accesshide');

        // Copy activity clipboard..
        echo $this->course_activity_clipboard($course);

        // Now the list of sections..
        echo $this->start_section_list();

        // General section if non-empty.
        $thissection = $sections[0];
        unset($sections[0]);
        if ($thissection->section !== 0){//  HIDE TOPIC (0) ZERO 
            if ($thissection->summary or $thissection->sequence or $PAGE->user_is_editing()) {
            
                echo $this->section_header($thissection, $course, false);
                spc_print_section($course, $thissection, $mods, $modnamesused, true);
                if ($PAGE->user_is_editing()) {
                    print_section_add_menus($course, 0, $modnames);
                }
                echo $this->section_footer();
            }
        }
        $canviewhidden = has_capability('moodle/course:viewhiddensections', $context);
        for ($section = 1; $section <= $course->numsections; $section++) {
            if (!empty($sections[$section])) {
                $thissection = $sections[$section];
            } else {
                // This will create a course section if it doesn't exist..
                $thissection = get_course_section($section, $course->id);

                // The returned section is only a bare database object rather than
                // a section_info object - we will need at least the uservisible
                // field in it.
                $thissection->uservisible = true;
                $thissection->availableinfo = null;
                $thissection->showavailability = 0;
            }
            // Show the section if the user is permitted to access it, OR if it's not available
            // but showavailability is turned on
            $showsection = $thissection->uservisible ||
                    ($thissection->visible && !$thissection->available && $thissection->showavailability);
            if (!$showsection) {
                // Hidden section message is overridden by 'unavailable' control
                // (showavailability option).
                if (!$course->hiddensections && $thissection->available) {
                    echo $this->section_hidden($section);
                }

                unset($sections[$section]);
                continue;
            }

            if (!$PAGE->user_is_editing() && $course->coursedisplay == COURSE_DISPLAY_MULTIPAGE) {
                // Display section summary only.
                echo $this->section_summary($thissection, $course, $mods);
            } else {
                echo $this->section_header($thissection, $course, false);
                if ($thissection->uservisible) {
                    spc_print_section($course, $thissection, $mods, $modnamesused);
                    if ($PAGE->user_is_editing()) {
                        print_section_add_menus($course, $section, $modnames);
                    }
                }
                echo $this->section_footer();
            }

            unset($sections[$section]);
        }

        if ($PAGE->user_is_editing() and has_capability('moodle/course:update', $context)) {
            // Print stealth sections if present.
            $modinfo = get_fast_modinfo($course);
            foreach ($sections as $section => $thissection) {
                if (empty($modinfo->sections[$section])) {
                    continue;
                }
                echo $this->stealth_section_header($section);
                spc_print_section($course, $thissection, $mods, $modnamesused);
                echo $this->stealth_section_footer();
            }

            echo $this->end_section_list();

            echo html_writer::start_tag('div', array('id' => 'changenumsections', 'class' => 'mdl-right'));

            // Increase number of sections.
            $straddsection = get_string('increasesections', 'moodle');
            $url = new moodle_url('/course/changenumsections.php',
                array('courseid' => $course->id,
                      'increase' => true,
                      'sesskey' => sesskey()));
            $icon = $this->output->pix_icon('t/switch_plus', $straddsection);
            echo html_writer::link($url, $icon.get_accesshide($straddsection), array('class' => 'increase-sections'));

            if ($course->numsections > 0) {
                // Reduce number of sections sections.
                $strremovesection = get_string('reducesections', 'moodle');
                $url = new moodle_url('/course/changenumsections.php',
                    array('courseid' => $course->id,
                          'increase' => false,
                          'sesskey' => sesskey()));
                $icon = $this->output->pix_icon('t/switch_minus', $strremovesection);
                echo html_writer::link($url, $icon.get_accesshide($strremovesection), array('class' => 'reduce-sections'));
            }

            echo html_writer::end_tag('div');
        } else {
            echo $this->end_section_list();
        }
    }
}
