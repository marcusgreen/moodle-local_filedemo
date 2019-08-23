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
 * Serve question type files
 *
 * @package    local_filedemo
 * @copyright  Marcus Green 2019
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();
/**
 * Checks file access for local_filedemo to illustrate the Moodle file api
 * @package  local_filedemo
 * @category files
 * @param stdClass $course course object //not ap
 * @param stdClass $cm course module object
 * @param stdClass $context context object
 * @param string $filearea file area
 * @param array $args extra arguments
 * @param bool $forcedownload whether or not force download
 * @param array $options additional options affecting the file serving
 *
 */
function local_filedemo_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = array()) {

    if ($context->contextlevel != (CONTEXT_MODULE || CONTEXT_SYSTEM)) {
      //  send_file_not_found();
    }
    $itemid = array_shift($args); // The first item in the $args array.
      $filename = array_pop($args); // The last item in the $args array.
    if (!$args) {
        $filepath = '/'; // ...$args is empty => the path is '/'.
    } else {
        $filepath = '/' . implode('/', $args) . '/'; // ...$args contains elements of the filepath.
    }

    // Retrieve the file from the Files API.
    $fs = get_file_storage();

    $file = $fs->get_file($context->id, 'local_filedemo', $filearea, $itemid, $filepath, $filename);

    if (!$file) {
        return false; // The file does not exist.
    }

    send_stored_file($file, DAYSECS, 0, $forcedownload, $options);

}

