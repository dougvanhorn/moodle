<?php 

class theme_spc_core_renderer extends core_renderer {
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
        if (($item_page_id!==$item_urlid) xor ($foopagename =='1'))
            {
            $string = '<a './*$item_a_title.'" class="'.$item_cm_visible.'" '. '" '.$item_cm_instance.*/' href="' . $item_a_url . '">' .$item_instancename . '</a>';
            $item = html_writer::tag('li', $string);
            }
        else
            {
            $string =$item_instancename;
            $item = html_writer::tag('li', $string, array('class' => 'active'));//
            if ($detect_first==0){$item .=  "\r\n<script>var topart =1; </script>";}
            //this is used to signal that this is the first article in a course which gets special styling
            }

            $detect_first+=1;

            //$item = html_writer::tag('li', $string, array('class' => 'active'));
            //$item = html_writer::start_tag('li', array('class' => 'spcmenu' . $row));
            //if (!empty($icons[$key])) { //test if the content has an assigned icon
            //    $item .= html_writer::tag('div', $icons[$key], array('class' => 'icon spcmenu'));
            //}
            //$item .= html_writer::tag('div', $string, array('class' => 'alex column c1'));
            //$item .= html_writer::end_tag('li');
            //$item = $item.$varcm.$cm->instance.$cm->module ;
            //$item = $string;//"W";//$item_arr;

            /*
            $item = $item.'<pre>'
                        .'<br>pagid|'.$item_page_id     
                        .'<br>a_tit|'.$item_a_title     
                        .'<br>a_url|'.$item_a_url       
                        .'<br>insta|'.$item_cm_instance 
                        .'<br>instn|'.$item_instancename
                        .'<br>modul|'.$item_cm_module   
                        .'<br>visib|'.$item_cm_visible  
                        .'<br>iconu|'.$item_cm_icon_url 
                        .'<br>extra|'.$item_cm_extra 
                        //.'<br>ac_cm|'.$item_ac_cm
                        //.'<br>c_mod|'.print_r($item_currmod)
                        //.'<br>cntxt|'.print_r($item_context)
                        .'</pre>';
            */            
            $lis[] = $item;
            //.'['.$item_page_id.'|'.$item_cm_instance.']';
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
        $output = '<!--BeginBlock-->';
        foreach ($blockcontents as $bc) {
            if ($bc instanceof block_contents) {
               
              // We don't want to print navigation and settings blocks here.
              if ($bc->attributes['class'] == 'block_settings  block' && !$this->page->user_allowed_editing()) 
                    {$output .= $this->block($bc, $region);}
                elseif ($bc->attributes['class'] == 'block_navigation  block' && !$this->page->user_allowed_editing()) 
                    {$output .= $this->block($bc, $region);$output .='<!--SUPPRESSED-->';}
                else{$output .= $this->block($bc, $region);}          
                             
            } else if ($bc instanceof block_move_target) {
                $output .= $this->block_move_target($bc);
            } else {
                throw new coding_exception('Unexpected type of thing (' . get_class($bc) . ') found in list of block contents.');
            }
        }
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


