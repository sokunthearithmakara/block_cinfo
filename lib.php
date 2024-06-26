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
 * Lib for cinfo block.
 *
 * @copyright 2024 Sokunthearith Makara
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package   block_cinfo
 */

/**
 * Get files
 * @category  files
 * @param stdClass $course course object
 * @param stdClass $birecordorcm block instance record
 * @param stdClass $context context object
 * @param string $filearea file area
 * @param array $args extra arguments
 * @param bool $forcedownload whether or not force download
 * @param array $options additional options affecting the file serving
 * @return bool
 * @todo MDL-36050 improve capability check on stick blocks, so we can check user capability before sending images.
 */
function block_cinfo_pluginfile($course, $birecordorcm, $context, $filearea, $args, $forcedownload, array $options=[]) {
    global $CFG;

    if ($context->contextlevel != CONTEXT_BLOCK) {
        send_file_not_found();
    }

    // If block is in course context, then check if user has capability to access course.
    if ($context->get_course_context(false)) {
        require_course_login($course);
    } else if ($CFG->forcelogin) {
        require_login();
    }

    if ($filearea !== 'content') {
        send_file_not_found();
    }

    $fs = get_file_storage();

    $filename = array_pop($args);
    $filepath = $args ? '/' . implode('/', $args) . '/' : '/';

    if (!$file = $fs->get_file($context->id, 'block_cinfo', 'content', 0, $filepath, $filename) || $file->is_directory()) {
        send_file_not_found();
    }

    \core\session\manager::write_close();
    send_stored_file($file, null, 0, true, $options);
}

/**
 * Perform global search replace such as when migrating site to new URL.
 * @param  string $search
 * @param  string $replace
 * @return void
 * @package block_cinfo
 */
function block_cinfo_global_db_replace($search, $replace) {
    global $DB;

    $instances = $DB->get_recordset('block_instances', ['blockname' => 'cinfo']);
    foreach ($instances as $instance) {
        // TODO: intentionally hardcoded until MDL-26800 is fixed.
        $config = unserialize_object(base64_decode($instance->configdata));
        if (isset($config->text) && is_string($config->text)) {
            $config->text = str_replace($search, $replace, $config->text);
            $DB->update_record('block_instances', ['id' => $instance->id,
                    'configdata' => base64_encode(serialize($config)), 'timemodified' => time()]);
        }
    }
    $instances->close();
}

/**
 * Given an array with a file path, it returns the itemid and the filepath for the defined filearea.
 *
 * @param string $filearea The filearea.
 * @param array  $args The path (the part after the filearea and before the filename).
 * @return array
 * @package block_cinfo
 */
function block_cinfo_get_path_from_pluginfile(string $filearea, array $args): array {
    // This block never has an itemid (the number represents the revision but it's not stored in database).
    array_shift($args);

    // Get the filepath.
    if (empty($args)) {
        $filepath = '/';
    } else {
        $filepath = '/' . implode('/', $args) . '/';
    }

    return [
        'itemid' => 0,
        'filepath' => $filepath,
    ];
}

/**
 * Output the content of the block.
 *
 * @param array $arg the argument
 * @return string the content
 */
function block_cinfo_output_fragment_course_intro ($arg) {
    global $DB;
    $parentcontextid = $arg['parentcontextid'];
    $contextid = $arg['contextid'];
    $instance = $DB->get_record('block_instances', ['blockname' => 'cinfo', 'parentcontextid' => $parentcontextid]);
    $config = unserialize_object(base64_decode($instance->configdata));
    if (isset($config->text)) {
        $config->text = file_rewrite_pluginfile_urls($config->text, 'pluginfile.php', $contextid, 'block_cinfo', 'content', null);
        $format = FORMAT_HTML;
        if (isset($config->format)) {
            $format = $config->format;
        }
        $modalcontent = format_text($config->text, $format);
        return $modalcontent;
    }
}
