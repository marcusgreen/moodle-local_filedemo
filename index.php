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
 * @package    local_filedemo
 * @copyright  2019 Marcus Green
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once('../../config.php');

global $CFG, $PAGE;
require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/formslib.php');
$filecomponent = optional_param('filecomponent', '', PARAM_TEXT);

$PAGE->set_context(context_system::instance());
$PAGE->set_url('/local/filedemo.php');
$filename = '';
class local_filedemo_form extends moodleform {
    protected function definition() {
        global $DB, $USER, $PAGE;
        $component = $this->_customdata['filecomponent'];
        $mform = $this->_form;

        $PAGE->requires->js_call_amd('local_filedemo/events', 'init');

        $mform->addElement('text', 'demotext');
        $mform->setType('demotext', PARAM_RAW);
        $sql = 'SELECT distinct component FROM {files}
        where userid =:userid';
        $components = $DB->get_fieldset_sql($sql, ['userid' => $USER->id]);
        array_unshift($components, 'Select item');

        $label = 'Components owned by current user';
        $components = array_combine($components, $components);
        $mform->addElement('select', 'filecomponent', $label, $components);

        $draftitemid = 0;
        $context = context_system::instance();
        $fs = get_file_storage();
        $filerecord = $fs->get_area_files($context->id, 'local_filedemo', 'file_demo', 0, false, null, false);

        $fileurl = '';
        if ($filerecord) {
            $file = array_shift($filerecord);
            $url = \moodle_url::make_pluginfile_url($file->get_contextid(),
                $file->get_component(), $file->get_filearea(), $file->get_itemid(), $file->get_filepath(), $file->get_filename());
            $fileurl = $url->out();
        }

        $sql = "SELECT id,contextid,component,filearea,itemid,filename
        FROM {files}
        WHERE COMPONENT='user'
        AND userid=:userid
        AND filearea = 'draft'
        UNION
        SELECT id,contextid,component,filearea,itemid,filename
        FROM {files}
        WHERE component = :component
        AND filearea = 'file_demo'
        ";

        $files = $DB->get_records_sql($sql, ['userid' => $USER->id, 'component' => $component]);
        if ($component) {
            $filetable = '<table border =1>';
            $filetable .= '<th>id</th><th>contextid</th><th>component</th><th>filearea</th><th>itemid</th><th>filename</th>';
            foreach ($files as $row) {
                $filetable .= '<tr>';
                foreach ($row as $cell) {
                    $filetable .= '<td>' . $cell . '</td>';
                }
                $filetable .= '</td>';
            }
            $filetable .= '</table>';
            $mform->addElement('static', 'filetable', 'filetable', $filetable);
            $mform->addElement('static', 'file_display', 'Url', $fileurl);
            $image = '<img src="' . $fileurl . '" height="128" width="128"></img>';
            $mform->addElement('static', 'file_display', 'Image', $image);

            $draftitemid = 0;
            $context = context_system::instance();

            $sql = "SELECT max(itemid) maxitemid FROM {files}
            WHERE component = 'local_filedemo'
            AND filearea = 'file_demo'
            ";
            $maxitem = $DB->get_field_sql($sql);
            ++$maxitem;
            file_prepare_draft_area($draftitemid, $context->id, 'local_filedemo', 'file_demo', $maxitem);
            $fileparam = ['maxfiles' => 1];
            $mform->addElement('filemanager', 'file_demo', 'Add a file', null, $fileparam);
        }
        $this->add_action_buttons(true);

    }
}
$mform = new local_filedemo_form(null, ['filecomponent' => $filecomponent]);
echo $OUTPUT->header();
$mform->display();
echo $OUTPUT->footer();
if ($data = $mform->get_data()) {
    $fileparam = ['maxbytes' => 2048, 'areamaxbytes' => 10485760, 'maxfiles' => 1];

    $context = context_system::instance();
    $draftitemid = file_get_submitted_draft_itemid('file_demo');

    $sql = "SELECT max(itemid) maxitemid FROM {files}
    WHERE component = 'local_filedemo'
    AND filearea = 'file_demo'
    ";
    $maxitem = $DB->get_field_sql($sql);
    ++$maxitem;
    file_save_draft_area_files(
        $draftitemid,
        $context->id,
        'local_filedemo',
        'file_demo',
        $maxitem,
        ['subdirs' => 0, 'maxbytes' => 0, 'maxfiles' => 1]
    );
}
