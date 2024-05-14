<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 *
 * @package   block_cinfo
 * @copyright 2024 Sokunthearith Makara
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core_completion\progress;

/**
 * Block class
 */
class block_cinfo extends block_base {

    /**
     * Initialize the block
     */
    public function init() {
        $this->title = get_string('pluginname', 'block_cinfo');
    }

    /**
     * Allow the block to have a configuration page
     * @return bool
     */
    public function has_config() {
        return true;
    }

    /**
     * Allow the block to be added to the course page
     * @return array
     */
    public function applicable_formats() {
        return ['all' => false, 'course' => true];
    }

    /**
     * Set the title of the block
     */
    public function specialization() {
        $this->title = get_string('pluginname', 'block_cinfo');
    }

    /**
     * Allow only one instance of the block
     * @return bool
     */
    public function instance_allow_multiple() {
        return false;
    }

    /**
     * Get the content of the block
     * @return stdClass
     */
    public function get_content() {
        global $CFG, $OUTPUT, $USER;

        require_once($CFG->libdir . '/filelib.php');

        if ($this->content !== null) {
            return $this->content;
        }

        $filteropt = new stdClass;
        $filteropt->overflowdiv = true;
        if ($this->content_is_trusted()) {
            // Fancy html allowed only on course, category and system blocks.
            $filteropt->noclean = true;
        }

        $this->content = new stdClass;
        $this->content->footer = '';
        $datafortemplate = new stdClass();
        $datafortemplate->courseid = $this->page->course->id;
        $datafortemplate->contextid = $this->context->id;
        $datafortemplate->moodleversion = "m44";
        if ($CFG->version < 2023042400) {
            $datafortemplate->moodleversion = "m41";
        }

        // Course search.
        // Get all activities in the course and display them for a search box.
        // If the course search is set to be shown expanded, then show it expanded.
        $datafortemplate->searchexpand = false;
        if (!isset($this->config->coursesearch) || $this->config->coursesearch) {
            $modinfo = get_fast_modinfo($this->page->course);
            $modarray = [];
            foreach ($modinfo->cms as $cm) {
                $mod = [];
                // Exclude activities that aren't visible or have no view link (e.g. label).
                // Account for folder being displayed inline.
                if (!$cm->uservisible || !$cm->has_view()) {
                    continue;
                } else {
                    $mod["name"] = format_string($cm->name);
                    $mod["url"] = $cm->url->__toString();
                    $modname = $cm->modname;
                    $mod["icon"] = $OUTPUT->image_icon('monologo', get_string('pluginname', $modname), $modname);
                }
                array_push($modarray, $mod);
            }

            $datafortemplate->showsearch = true;
            $datafortemplate->modarray = json_encode($modarray);
            $datafortemplate->searchexpand = isset($this->config->showsearchexpand) && $this->config->showsearchexpand == 1;
        }

        // Course progress.
        // If course completion is enabled in the course settings.
        // and the block is configured to show progress, then show the progress bar.
        // If the course has completion criteria, then link to the course completion report.
        if (!$datafortemplate->searchexpand && $this->page->course->enablecompletion
        && $this->config && is_enrolled($this->context)) {
            if ($this->config->showprogress == 1) {
                $progress = progress::get_course_progress_percentage($this->page->course);
                $percentage = floor($progress ?? 0);
                // If course has completion criteria, link to the course completion report.
                $info = new completion_info($this->page->course);
                $datafortemplate->showprogress = true;
                $datafortemplate->progress = $percentage;
                $datafortemplate->progressurl = new moodle_url('/blocks/completionstatus/details.php',
                ['course' => $this->page->course->id]);
                $datafortemplate->hascriteria = $info->is_enabled() && $info->has_criteria();
            }
        }

        // Course grade.
        // If showgrades is enabled in the course settings and the block is configured to show grades
        // and user has the capability to see grades, then show the grade with link to the grade report for the user.
        if (!$datafortemplate->searchexpand && $this->config && isset($this->config->showgrade) && $this->config->showgrade == 1
        && $this->page->course->showgrades
        && has_capability('moodle/grade:view', context_course::instance($this->page->course->id))) {
            require_once($CFG->libdir . '/gradelib.php');
            require_once($CFG->dirroot . '/grade/querylib.php');
            $gradeobj = grade_get_course_grade($USER->id, $this->page->course->id);
            if (!empty($grademax = floatval($gradeobj->item->grademax))) {
                // Avoid divide by 0 error if no grades have been defined.
                $grade = (int) ($gradeobj->grade / floatval($grademax) * 100) ?? 0;
            } else {
                $grade = 0;
            }

            $datafortemplate->showgrade = true;
            $datafortemplate->grade = $grade;
            $datafortemplate->gradeurl = new moodle_url('/grade/report/user/index.php', ['id' => $this->page->course->id]);
        }

        // Course info.
        // If showcourseinfo is enabled in the block settings, then show the course info block.
        if (!$datafortemplate->searchexpand && isset($this->config->showcourseinfo)
        && $this->config->showcourseinfo && isset($this->config->title) && $this->config->text) {
            $datafortemplate->showcourseinfo = true;
            $datafortemplate->courseinfotitle = format_text($this->config->title, FORMAT_HTML);
        }

        // Course announcements.
        // If newsitems is enabled in the course settings and.
        // the block is configured to show announcements, then show the announcements.
        if (!$datafortemplate->searchexpand && $this->page->course->newsitems
        && isset($this->config->shownews) && $this->config->shownews) {

            require_once($CFG->dirroot . '/mod/forum/lib.php');   // We'll need this.

            $datafortemplate->shownews = true;

            if (!$forum = forum_get_course_forum($this->page->course->id, 'news')) {
                $datafortemplate->shownews = false;
            }

            $modinfo = get_fast_modinfo($this->page->course);
            if (empty($modinfo->instances['forum'][$forum->id])) {
                $datafortemplate->shownews = false;
            }
            $cm = $modinfo->instances['forum'][$forum->id];

            if (!$cm->uservisible) {
                $datafortemplate->shownews = false;
            }

            $context = context_module::instance($cm->id);

            // User must have perms to view discussions in that forum.
            if (!has_capability('mod/forum:viewdiscussion', $context)) {
                $datafortemplate->shownews = false;
            }

            // First work out whether we can post to this group and if so, include a link.
            $datafortemplate->newsurl = $CFG->wwwroot . '/mod/forum/view.php?f=' . $forum->id;

            // Get unread posts.
            $unreadposts = forum_get_discussions_unread($cm);
            $datafortemplate->unreadposts = count($unreadposts);
        }

        // Activity reports.
        // If showactivityreport is enabled in the course settings.
        // and the block is configured to show activity reports, then show the activity reports.

        if (!$datafortemplate->searchexpand && $this->page->course->showreports
        && isset($this->config->showactivityreport) && $this->config->showactivityreport) {
            $datafortemplate->showreport = true;
            $datafortemplate->reporturl = new moodle_url('/report/outline/user.php',
            ['course' => $this->page->course->id, 'mode' => 'complete', 'id' => $USER->id]);
        }

        // Course virtual room.
        if (!$datafortemplate->searchexpand && isset($this->config->virtualroomlink)
        && $this->config->showvirtualroom && $this->config->virtualroomlink != '') {
            $datafortemplate->showvr = true;
            $datafortemplate->vrurl = $this->config->virtualroomlink;
            $datafortemplate->vrlabel = format_text($this->config->vrlabel, FORMAT_HTML);
            $datafortemplate->vrtype = $this->config->vrtype;
        }

        // Course chat room.
        if (!$datafortemplate->searchexpand && isset($this->config->chatroomlink)
        && $this->config->showchatroom && $this->config->chatroomlink != '') {
            $datafortemplate->showcr = true;
            $datafortemplate->crurl = $this->config->chatroomlink;
            $datafortemplate->crlabel = format_text($this->config->chatlabel, FORMAT_HTML);
            $datafortemplate->crtype = $this->config->crtype;
        }

        // Course shared folder.
        if (!$datafortemplate->searchexpand && isset($this->config->folderlink)
        && $this->config->showfolder && $this->config->folderlink != '') {
            $datafortemplate->showfolder = true;
            $datafortemplate->folderurl = $this->config->folderlink;
            $datafortemplate->folderlabel = format_text($this->config->folderlabel, FORMAT_HTML);
            $datafortemplate->foldertype = $this->config->foldertype;
        }

        $textcenter = !isset($this->config->aligncenter) || $this->config->aligncenter ? 'text-center' : '';
        $text = '<div id="cinfo-wrapper" class="d-none">
        <div class="text-nowrap ' . $textcenter . ' scrollbar-0 p-0">'
        . $OUTPUT->render_from_template('block_cinfo/main', $datafortemplate)
        . '</div></div>';

        unset($filteropt); // Memory footprint.

        $this->content->text = get_string('instructionstext', 'block_cinfo') . $text;
        return $this->content;
    }

    /**
     * Serialize and store config data
     * @param stdClass $data
     * @param bool $nolongerused
     * @return bool
     */
    public function instance_config_save($data, $nolongerused = false) {

        $config = clone ($data);
        // Move embedded files into a proper filearea and adjust HTML links to match.
        $config->text = file_save_draft_area_files($data->text['itemid'], $this->context->id,
        'block_cinfo', 'content', 0, ['subdirs' => true], $data->text['text']);
        $config->format = $data->text['format'];

        parent::instance_config_save($config, $nolongerused);
    }

    /**
     * Delete any block-specific data when deleting a block instance.
     * @return bool
     */
    public function instance_delete() {
        $fs = get_file_storage();
        $fs->delete_area_files($this->context->id, 'block_cinfo');
        return true;
    }

    /**
     * Copy any block-specific data when copying to a new block instance.
     * @param int $fromid the id number of the block instance to copy from
     * @return boolean
     */
    public function instance_copy($fromid) {
        $fromcontext = context_block::instance($fromid);
        $fs = get_file_storage();
        // This extra check if file area is empty adds one query if it is not empty but saves several if it is.
        if (!$fs->is_area_empty($fromcontext->id, 'block_cinfo', 'content', 0, false)) {
            $draftitemid = 0;
            file_prepare_draft_area($draftitemid, $fromcontext->id, 'block_cinfo', 'content', 0, ['subdirs' => true]);
            file_save_draft_area_files($draftitemid, $this->context->id, 'block_cinfo', 'content', 0, ['subdirs' => true]);
        }
        return true;
    }

    /**
     * Check if the content is trusted
     * @return bool
     */
    public function content_is_trusted() {

        if (!context::instance_by_id($this->instance->parentcontextid, IGNORE_MISSING)) {
            return false;
        }

        return true;
    }

    /**
     * The block should only be dockable when the title of the block is not empty
     * and when parent allows docking.
     *
     * @return bool
     */
    public function instance_can_be_docked() {
        return (!empty($this->config->title) && parent::instance_can_be_docked());
    }


}
