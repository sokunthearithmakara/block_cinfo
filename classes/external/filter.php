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

namespace block_cinfo\external;

use context;
use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_single_structure;
use core_external\external_value;
use filter_manager;

/**
 * Class filter
 *
 * @package    block_cinfo
 * @copyright  2026 Sokunthearith Makara <sokunthearithmakara@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class filter extends external_api {
    /**
     * Describes the parameters for filtering the content.
     *
     * @return external_function_parameters
     * @since Moodle 4.1
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'contextid' => new external_value(PARAM_INT, 'The context ID', VALUE_REQUIRED),
            'parentcontextid' => new external_value(PARAM_INT, 'The parent context ID', VALUE_REQUIRED),
        ]);
    }

    /**
     * External function to filter the editor.
     *
     * @param int $contextid Context ID.
     * @param int $parentcontextid Parent context ID.
     * @return array
     * @since Moodle 4.1
     */
    public static function execute(int $contextid, int $parentcontextid): array {
        [
            'contextid' => $contextid,
            'parentcontextid' => $parentcontextid,
        ] = self::validate_parameters(self::execute_parameters(), [
            'contextid' => $contextid,
            'parentcontextid' => $parentcontextid,
        ]);

        $context = context::instance_by_id($contextid);
        self::validate_context($context);

        global $DB;
        $instance = $DB->get_record('block_instances', ['blockname' => 'cinfo', 'parentcontextid' => $parentcontextid]);
        $config = unserialize_object(base64_decode($instance->configdata));
        if (isset($config->text)) {
            $config->text = file_rewrite_pluginfile_urls(
                $config->text,
                'pluginfile.php',
                $contextid,
                'block_cinfo',
                'content',
                null
            );
            $format = FORMAT_HTML;
            if (isset($config->format)) {
                $format = $config->format;
            }
            $modalcontent = format_text($config->text, $format);
            return [
                'content' => $modalcontent,
            ];
        }

        return [
            'content' => '',
        ];
    }

    /**
     * Describes the data returned from the external function.
     *
     * @return external_single_structure
     * @since Moodle 4.1
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'content' => new external_value(PARAM_RAW, 'Filtered content'),
        ]);
    }
}
