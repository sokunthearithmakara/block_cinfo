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
 * Specialised backup task for the cinfo block
 *
 * @package   block_cinfo
 * @copyright 2024 Sokunthearith Makara
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Specialised backup task for the cinfo block
 * (requires encode_content_links in some configdata attrs)
 */
class backup_cinfo_block_task extends backup_block_task {
    /**
     * Define settings
     */
    protected function define_my_settings() {
    }

    /**
     * Define the structure of cinfo block
     */
    protected function define_my_steps() {
    }

    /**
     * Define the contents of cinfo block
     */
    public function get_fileareas() {
        return ['content'];
    }

    /**
     * Get configuration data encoded attributes
     */
    public function get_configdata_encoded_attributes() {
        return ['text']; // We need to encode some attrs in configdata.
    }

    /**
     * Encode content links
     * @param string $content
     */
    public static function encode_content_links($content) {
        return $content; // No special encoding of links.
    }
}

