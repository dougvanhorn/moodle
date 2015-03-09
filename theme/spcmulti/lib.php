<?php

function spc_print_section($course, $section, $mods, $modnamesused, $absolute=false, $width="100%", $hidecompletion=false, $sectionreturn=0) {
    global $CFG, $USER, $DB, $PAGE, $OUTPUT;

    static $initialised;

    static $groupbuttons;
    static $groupbuttonslink;
    static $isediting;
    static $ismoving;
    static $strmovehere;
    static $strmovefull;
    static $strunreadpostsone;
    static $modulenames;

    if (!isset($initialised)) {
        $groupbuttons     = ($course->groupmode or (!$course->groupmodeforce));
        $groupbuttonslink = (!$course->groupmodeforce);
        $isediting        = $PAGE->user_is_editing();
        $ismoving         = $isediting && ismoving($course->id);
        if ($ismoving) {
            $strmovehere  = get_string("movehere");
            $strmovefull  = strip_tags(get_string("movefull", "", "'$USER->activitycopyname'"));
        }
        $modulenames      = array();
        $initialised = true;
    }

    $modinfo = get_fast_modinfo($course);
    $completioninfo = new completion_info($course);

    // Accessibility: replace table with list <ul>, but don't output empty list.
    if (!empty($section->sequence)) {
        //print_r($section->section);
        // Fix bug #5027, don't want style=\"width:$width\".
        echo "<ul class=\"section img-text\">\n";
        $sectionmods = explode(",", $section->sequence);

        foreach ($sectionmods as $modnumber) {
            if ($section->section == 0){continue;}
            if (empty($mods[$modnumber])) {
                continue;
            }

            /**
             * @var cm_info
             */
            $mod = $mods[$modnumber];

            if ($ismoving and $mod->id == $USER->activitycopy) {
                // do not display moving mod
                continue;
            }

            if (isset($modinfo->cms[$modnumber])) {
                // We can continue (because it will not be displayed at all)
                // if:
                // 1) The activity is not visible to users
                // and
                // 2a) The 'showavailability' option is not set (if that is set,
                //     we need to display the activity so we can show
                //     availability info)
                // or
                // 2b) The 'availableinfo' is empty, i.e. the activity was
                //     hidden in a way that leaves no info, such as using the
                //     eye icon.
                if (!$modinfo->cms[$modnumber]->uservisible &&
                    (empty($modinfo->cms[$modnumber]->showavailability) ||
                      empty($modinfo->cms[$modnumber]->availableinfo))) {
                    // visibility shortcut
                    continue;
                }
            } else {
                if (!file_exists("$CFG->dirroot/mod/$mod->modname/lib.php")) {
                    // module not installed
                    continue;
                }
                if (!coursemodule_visible_for_user($mod) &&
                    empty($mod->showavailability)) {
                    // full visibility check
                    continue;
                }
            }

            if (!isset($modulenames[$mod->modname])) {
                $modulenames[$mod->modname] = get_string('modulename', $mod->modname);
            }
            $modulename = $modulenames[$mod->modname];

            // In some cases the activity is visible to user, but it is
            // dimmed. This is done if viewhiddenactivities is true and if:
            // 1. the activity is not visible, or
            // 2. the activity has dates set which do not include current, or
            // 3. the activity has any other conditions set (regardless of whether
            //    current user meets them)
            $modcontext = context_module::instance($mod->id);
            $canviewhidden = has_capability('moodle/course:viewhiddenactivities', $modcontext);
            $accessiblebutdim = false;
            if ($canviewhidden) {
                $accessiblebutdim = !$mod->visible;
                if (!empty($CFG->enableavailability)) {
                    $accessiblebutdim = $accessiblebutdim ||
                        $mod->availablefrom > time() ||
                        ($mod->availableuntil && $mod->availableuntil < time()) ||
                        count($mod->conditionsgrade) > 0 ||
                        count($mod->conditionscompletion) > 0;
                }
            }

            $liclasses = array();
            $liclasses[] = 'activity';
            $liclasses[] = $mod->modname;
            $liclasses[] = 'modtype_'.$mod->modname;
            $extraclasses = $mod->get_extra_classes();
            if ($extraclasses) {
                $liclasses = array_merge($liclasses, explode(' ', $extraclasses));
            }
            echo html_writer::start_tag('li', array('class'=>join(' ', $liclasses), 'id'=>'module-'.$modnumber));
            if ($ismoving) {
                echo '<a title="'.$strmovefull.'"'.
                     ' href="'.$CFG->wwwroot.'/course/mod.php?moveto='.$mod->id.'&amp;sesskey='.sesskey().'">'.
                     '<img class="movetarget" src="'.$OUTPUT->pix_url('movehere') . '" '.
                     ' alt="'.$strmovehere.'" /></a><br />
                     ';
            }

            $classes = array('mod-indent');
            if (!empty($mod->indent)) {
                $classes[] = 'mod-indent-'.$mod->indent;
                if ($mod->indent > 15) {
                    $classes[] = 'mod-indent-huge';
                }
            }
            echo html_writer::start_tag('div', array('class'=>join(' ', $classes)));

            // Get data about this course-module
            list($content, $instancename) =
                    get_print_section_cm_text($modinfo->cms[$modnumber], $course);

            //Accessibility: for files get description via icon, this is very ugly hack!
            $altname = '';
            $altname = $mod->modfullname;
            // Avoid unnecessary duplication: if e.g. a forum name already
            // includes the word forum (or Forum, etc) then it is unhelpful
            // to include that in the accessible description that is added.
            if (false !== strpos(textlib::strtolower($instancename),
                    textlib::strtolower($altname))) {
                $altname = '';
            }
            // File type after name, for alphabetic lists (screen reader).
            if ($altname) {
                $altname = get_accesshide(' '.$altname);
            }

            // We may be displaying this just in order to show information
            // about visibility, without the actual link
            $contentpart = '';
            if ($mod->uservisible) {
                // Nope - in this case the link is fully working for user
                $linkclasses = '';
                $textclasses = '';
                if ($accessiblebutdim) {
                    $linkclasses .= ' dimmed';
                    $textclasses .= ' dimmed_text';
                    $accesstext = '<span class="accesshide">'.
                        get_string('hiddenfromstudents').': </span>';
                } else {
                    $accesstext = '';
                }
                if ($linkclasses) {
                    $linkcss = 'class="' . trim($linkclasses) . '" ';
                } else {
                    $linkcss = '';
                }
                if ($textclasses) {
                    $textcss = 'class="' . trim($textclasses) . '" ';
                } else {
                    $textcss = '';
                }

                // Get on-click attribute value if specified
                $onclick = $mod->get_on_click();
                if ($onclick) {
                    $onclick = ' onclick="' . $onclick . '"';
                }

                if ($url = $mod->get_url()) {
                    // Display link itself
                    echo '<a ' . $linkcss . $mod->extra . $onclick .
                            ' href="' . $url . '"><img src="' . $mod->get_icon_url() .
                            '" class="activityicon" alt="' .
                            $modulename . '" /> ' .
                            $accesstext . '<span class="instancename">' .
                            $instancename . $altname . '</span></a>';

                    // If specified, display extra content after link
                    if ($content) {
                        $contentpart = '<div class="' . trim('contentafterlink' . $textclasses) .
                                '">' . $content . '</div>';
                    }
                } else {
                    // No link, so display only content
                    $contentpart = '<div ' . $textcss . $mod->extra . '>' .
                            $accesstext . $content . '</div>';
                }

                if (!empty($mod->groupingid) && has_capability('moodle/course:managegroups', get_context_instance(CONTEXT_COURSE, $course->id))) {
                    $groupings = groups_get_all_groupings($course->id);
                    echo " <span class=\"groupinglabel\">(".format_string($groupings[$mod->groupingid]->name).')</span>';
                }
            } else {
                $textclasses = $extraclasses;
                $textclasses .= ' dimmed_text';
                if ($textclasses) {
                    $textcss = 'class="' . trim($textclasses) . '" ';
                } else {
                    $textcss = '';
                }
                $accesstext = '<span class="accesshide">' .
                        get_string('notavailableyet', 'condition') .
                        ': </span>';

                if ($url = $mod->get_url()) {
                    // Display greyed-out text of link
                    echo '<div ' . $textcss . $mod->extra .
                            ' >' . '<img src="' . $mod->get_icon_url() .
                            '" class="activityicon" alt="' .
                            $modulename .
                            '" /> <span>'. $instancename . $altname .
                            '</span></div>';

                    // Do not display content after link when it is greyed out like this.
                } else {
                    // No link, so display only content (also greyed)
                    $contentpart = '<div ' . $textcss . $mod->extra . '>' .
                            $accesstext . $content . '</div>';
                }
            }

            // Module can put text after the link (e.g. forum unread)
            echo $mod->get_after_link();

            // If there is content but NO link (eg label), then display the
            // content here (BEFORE any icons). In this case cons must be
            // displayed after the content so that it makes more sense visually
            // and for accessibility reasons, e.g. if you have a one-line label
            // it should work similarly (at least in terms of ordering) to an
            // activity.
            if (empty($url)) {
                echo $contentpart;
            }

            if ($isediting) {
                if ($groupbuttons and plugin_supports('mod', $mod->modname, FEATURE_GROUPS, 0)) {
                    if (! $mod->groupmodelink = $groupbuttonslink) {
                        $mod->groupmode = $course->groupmode;
                    }

                } else {
                    $mod->groupmode = false;
                }
                echo '&nbsp;&nbsp;';
                echo make_editing_buttons($mod, $absolute, true, $mod->indent, $sectionreturn);
                echo $mod->get_after_edit_icons();
            }

            // Completion
            $completion = $hidecompletion
                ? COMPLETION_TRACKING_NONE
                : $completioninfo->is_enabled($mod);
            if ($completion!=COMPLETION_TRACKING_NONE && isloggedin() &&
                !isguestuser() && $mod->uservisible) {
                $completiondata = $completioninfo->get_data($mod,true);
                //echo '<pre>';print_r( $mod);echo '</pre>';
                $completionicon = '';
                if ($isediting) {
                    switch ($completion) {
                        case COMPLETION_TRACKING_MANUAL :
                            $completionicon = 'manual-enabled'; break;
                        case COMPLETION_TRACKING_AUTOMATIC :
                            $completionicon = 'auto-enabled'; break;
                        default: // wtf
                    }
                } else if ($completion==COMPLETION_TRACKING_MANUAL) {
                    switch($completiondata->completionstate) {
                        case COMPLETION_INCOMPLETE:
                            $completionicon = 'manual-n'; break;
                        case COMPLETION_COMPLETE:
                            $completionicon = 'manual-y'; break;
                    }
                } else { // Automatic
                    switch($completiondata->completionstate) {
                        case COMPLETION_INCOMPLETE:
                            $completionicon = 'auto-n'; break;
                        case COMPLETION_COMPLETE:
                            $completionicon = 'auto-y'; break;
                        case COMPLETION_COMPLETE_PASS:
                            $completionicon = 'auto-pass'; break;
                        case COMPLETION_COMPLETE_FAIL:
                            $completionicon = 'auto-fail'; break;
                    }
                }
                if ($completionicon) {
                    $imgsrc = $OUTPUT->pix_url('i/completion-'.$completionicon);
                    $formattedname = format_string($mod->name, true, array('context' => $modcontext));
                    $imgalt = get_string('completion-alt-' . $completionicon, 'completion', $formattedname);
                    if ($completion == COMPLETION_TRACKING_MANUAL && !$isediting) {
                        $imgtitle = get_string('completion-title-' . $completionicon, 'completion', $formattedname);
                        $newstate =
                            $completiondata->completionstate==COMPLETION_COMPLETE
                            ? COMPLETION_INCOMPLETE
                            : COMPLETION_COMPLETE;
                        // In manual mode the icon is a toggle form...

                        // If this completion state is used by the
                        // conditional activities system, we need to turn
                        // off the JS.
                        if (!empty($CFG->enableavailability) &&
                            condition_info::completion_value_used_as_condition($course, $mod)) {
                            $extraclass = ' preventjs';
                        } else {
                            $extraclass = '';
                        }
/*                      echo "
<form class='togglecompletion$extraclass' method='post' action='".$CFG->wwwroot."/course/togglecompletion.php'><div>
<input type='hidden' name='id' value='{$mod->id}' />
<input type='hidden' name='modulename' value='".s($mod->name)."' />
<input type='hidden' name='sesskey' value='".sesskey()."' />
<input type='hidden' name='completionstate' value='$newstate' />
<input type='image' src='$imgsrc' alt='$imgalt' title='$imgtitle' />
</div></form>";*/
                    } else {
                        // In auto mode, or when editing, the icon is just an image
                        //echo "<span class='autocompletion'>";
                        //echo "<img src='$imgsrc' alt='$imgalt' title='$imgalt' /></span>";
                    }
                }
            }

            // If there is content AND a link, then display the content here
            // (AFTER any icons). Otherwise it was displayed before
            if (!empty($url)) {
                echo $contentpart;
            }

            // Show availability information (for someone who isn't allowed to
            // see the activity itself, or for staff)
            if (!$mod->uservisible) {
                echo '<div class="availabilityinfo">'.$mod->availableinfo.'</div>';
            } else if ($canviewhidden && !empty($CFG->enableavailability) && $mod->visible) {
                $ci = new condition_info($mod);
                $fullinfo = $ci->get_full_information();
                if($fullinfo) {
                    echo '<div class="availabilityinfo">'.get_string($mod->showavailability
                        ? 'userrestriction_visible'
                        : 'userrestriction_hidden','condition',
                        $fullinfo).'</div>';
                }
            }

            echo html_writer::end_tag('div');
            echo html_writer::end_tag('li')."\n";
        }

    } elseif ($ismoving) {
        echo "<ul class=\"section\">\n";
    }

    if ($ismoving) {
        echo '<li><a title="'.$strmovefull.'"'.
             ' href="'.$CFG->wwwroot.'/course/mod.php?movetosection='.$section->id.'&amp;sesskey='.sesskey().'">'.
             '<img class="movetarget" src="'.$OUTPUT->pix_url('movehere') . '" '.
             ' alt="'.$strmovehere.'" /></a></li>
             ';
    }
    if (!empty($section->sequence) || $ismoving) {
        echo "</ul><!--class='section'-->\n\n";
    }
}

