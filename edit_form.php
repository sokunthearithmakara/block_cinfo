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
 * Form for editing cinfo block instances.
 *
 * @package   block_cinfo
 * @copyright 2024 Sokunthearith Makara
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * block_cinfo_edit_form class.
 */
class block_cinfo_edit_form extends block_edit_form {
    /**
     * Define the form.
     * @param moodleform $mform
     */
    protected function specific_definition($mform) {
        $this->page->add_body_class('mediumwidth');

        // Show search.
        $mform->addElement('advcheckbox', 'config_coursesearch', '',
        get_string('showcoursesearch', 'block_cinfo'), null, [0, 1]);
        $mform->setDefault('config_coursesearch', 1);
        $mform->addHelpButton('config_coursesearch', 'showcoursesearch', 'block_cinfo');

        // Show search expand.
        $mform->addElement('advcheckbox', 'config_showsearchexpand', '',
        get_string('showsearchexpand', 'block_cinfo'), null, [0, 1]);
        $mform->setDefault('config_showsearchexpand', 0);
        $mform->addHelpButton('config_showsearchexpand', 'showsearchexpand', 'block_cinfo');

        // Show progress.
        $mform->addElement('advcheckbox', 'config_showprogress', '',
        get_string('showprogress', 'block_cinfo'), null, [0, 1]);
        $mform->setDefault('config_showprogress', 1);
        $mform->addHelpButton('config_showprogress', 'showprogress', 'block_cinfo');

        // Show grade.
        $mform->addElement('advcheckbox', 'config_showgrade', '',
        get_string('showgrade', 'block_cinfo'), null, [0, 1]);
        $mform->setDefault('config_showgrade', 1);
        $mform->addHelpButton('config_showgrade', 'showgrade', 'block_cinfo');

        // Show course news.
        $mform->addElement('advcheckbox', 'config_shownews', '',
        get_string('shownews', 'block_cinfo'), null, [0, 1]);
        $mform->setDefault('config_shownews', 0);
        $mform->addHelpButton('config_shownews', 'shownews', 'block_cinfo');

        // Show activity report.
        $mform->addElement('advcheckbox', 'config_showactivityreport', '',
        get_string('showactivityreport', 'block_cinfo'), null, [0, 1]);
        $mform->setDefault('config_showactivityreport', 0);
        $mform->addHelpButton('config_showactivityreport', 'showactivityreport', 'block_cinfo');

        // Align items center.
        $mform->addElement('advcheckbox', 'config_aligncenter', '',
        get_string('aligncenter', 'block_cinfo'), null, [0, 1]);
        $mform->setDefault('config_aligncenter', 1);
        $mform->addHelpButton('config_aligncenter', 'aligncenter', 'block_cinfo');

        // Course info section.
        $mform->addElement('header', 'configheader', get_string('courseinfo', 'block_cinfo'));
        $mform->addElement('advcheckbox', 'config_showcourseinfo', '',
        get_string('showcourseinfo', 'block_cinfo'), null, [0, 1]);

        $mform->addElement('text', 'config_title', get_string('label', 'block_cinfo'), ['size' => '100']);
        $mform->setType('config_title', PARAM_TEXT);
        $mform->setDefault('config_title', get_string('courseinfo', 'block_cinfo'));

        $editoroptions = ['maxfiles' => EDITOR_UNLIMITED_FILES, 'noclean' => true, 'context' => $this->block->context];
        $mform->addElement('editor', 'config_text', get_string('configcontent', 'block_cinfo'), null, $editoroptions);
        $mform->setType('config_text', PARAM_RAW); // XSS is prevented when printing the block contents and serving files.

        // Course virtual room.
        $mform->addElement('header', 'configheader_vr', get_string('coursevirtualroom', 'block_cinfo'));
        $mform->addElement('advcheckbox', 'config_showvirtualroom', '', get_string('showvirtualroom', 'block_cinfo'), null, [0, 1]);
        $mform->addElement('select', 'config_vrtype', get_string('virtualroomtype', 'block_cinfo'), [
            "othervirtual" => get_string('default', 'block_cinfo'),
            "bbb" => "BigBlueButton",
            "bb" => "Blackboard Collaborate",
            "meet" => "Meet",
            "teams" => "Teams",
            "zoom" => "Zoom",
        ]);
        $mform->addElement('text', 'config_vrlabel', get_string('label', 'block_cinfo'), ['size' => '100']);
        $mform->setType('config_vrlabel', PARAM_TEXT);
        $mform->addElement('text', 'config_virtualroomlink', get_string('coursevirtualroomurl', 'block_cinfo'), ['size' => '100']);
        $mform->setType('config_virtualroomlink', PARAM_TEXT);

        // Course chat room.
        $mform->addElement('header', 'configheader_chat', get_string('coursechatroom', 'block_cinfo'));
        $mform->addElement('advcheckbox', 'config_showchatroom', '', get_string('showchatroom', 'block_cinfo'), null, [0, 1]);
        $mform->addElement('select', 'config_crtype', get_string('chatroomtype', 'block_cinfo'), [
            "otherchat" => get_string('default', 'block_cinfo'),
            "chat" => "Google Chat",
            "messenger" => "Messenger",
            "teams" => "Teams",
            "telegram" => "Telegram",
            "whatsapp" => "WhatsApp",
        ]);
        $mform->addElement('text', 'config_chatlabel', get_string('label', 'block_cinfo'), ['size' => '100']);
        $mform->setType('config_chatlabel', PARAM_TEXT);
        $mform->addElement('text', 'config_chatroomlink', get_string('coursechatroomurl', 'block_cinfo'), ['size' => '100']);
        $mform->setType('config_chatroomlink', PARAM_TEXT);

        // Course shared folder.
        $mform->addElement('header', 'configheader_folder', get_string('coursefolder', 'block_cinfo'));
        $mform->addElement('advcheckbox', 'config_showfolder', '', get_string('showcoursefolder', 'block_cinfo'), null, [0, 1]);
        $mform->addElement('select', 'config_foldertype', get_string('foldertype', 'block_cinfo'), [
            "otherfolder" => get_string('default', 'block_cinfo'),
            "dropbox" => "Dropbox",
            "gdrive" => "Google Drive",
            "onedrive" => "OneDrive",
        ]);
        $mform->addElement('text', 'config_folderlabel', get_string('label', 'block_cinfo'), ['size' => '100']);
        $mform->setType('config_folderlabel', PARAM_TEXT);
        $mform->addElement('text', 'config_folderlink', get_string('coursefolderurl', 'block_cinfo'), ['size' => '100']);
        $mform->setType('config_folderlink', PARAM_TEXT);
    }

    /**
     * Set the default data for the form.
     *
     * @param stdClass $defaults
     */
    public function set_data($defaults) {
        if (!empty($this->block->config) && !empty($this->block->config->text)) {
            $text = $this->block->config->text;
            $draftideditor = file_get_submitted_draft_itemid('config_text');
            if (empty($text)) {
                $currenttext = '';
            } else {
                $currenttext = $text;
            }
            $defaults->config_text['text'] = file_prepare_draft_area($draftideditor, $this->block->context->id,
            'block_cinfo', 'content', 0, ['subdirs' => true], $currenttext);
            $defaults->config_text['itemid'] = $draftideditor;
            $defaults->config_text['format'] = $this->block->config->format ?? FORMAT_MOODLE;
        } else {
            $text = '';
        }

        // Have to delete text here, otherwise parent::set_data will empty content.
        // of editor.
        unset($this->block->config->text);
        parent::set_data($defaults);
        // Restore $text.
        if (!isset($this->block->config)) {
            $this->block->config = new stdClass();
        }
        $this->block->config->text = $text;
        $this->block->config->coursesearch = true;
    }

    /**
     * Display the configuration form when block is being added to the page
     *
     * @return bool
     */
    public static function display_form_when_adding(): bool {
        return true;
    }
}
