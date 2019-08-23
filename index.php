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
 * Run the code checker from the web.
 *
 * @package    local_autocomplete
 * @copyright  2019 Marcus Green
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
global $CFG, $PAGE;
require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/formslib.php');
//require_login();

$PAGE->set_context(context_system::instance());
$PAGE->set_url('/local/filedemo.php');
$filename = '';
class local_filedemo_form extends moodleform {
    protected function definition() {
        $mform = $this->_form;

        $mform->addElement('text','demotext');
        $mform->setType('demotext',PARAM_RAW);
        //https://docs.moodle.org/dev/Using_the_File_API_in_Moodle_forms
        // moodle_url::make_pluginfile_url(context_system::instance()->id, 'logo_filedemo', 'filedemo', $filepath,
        // theme_get_revision(), $logo);
        
        //$context = context_system::instance();
        //$fs = get_file_storage();

       // $files = $fs->get_area_files($context->id, 'local_filedemo', 'filedemo',0);

        //$urlimage = moodle_url::make_pluginfile_url(context_system::instance()->id, 'local_filedemo', 'filedemo', 1, '/',reset($files)->get_filename())->out(false);
        //file_prepare_draft_area

        $draftitemid = 0;
        $context=context_system::instance();
        $fs = get_file_storage();
        $filerecord = $fs->get_area_files($context->id,'local_filedemo','file_demo',0, false, null, false);

        $fileurl = '';
        if ($filerecord) {
            $file = array_shift($filerecord);
            $url = \moodle_url::make_pluginfile_url($file->get_contextid(),
            $file->get_component(), $file->get_filearea(), $file->get_itemid(), $file->get_filepath(), $file->get_filename());
            $fileurl= $url->out();
        }
   
        $mform->addElement('static', 'file_display', 'Url',$fileurl);
        $image = '<img src="'.$fileurl.'" height="128" width="128"></img>';
        $mform->addElement('static', 'file_display', 'Image', $image);

        $draftitemid = 0;
        $context=context_system::instance();
        file_prepare_draft_area($draftitemid, $context->id, 'local_filedemo', 'file_demo', 0);
        $fileparam = ['maxfiles' => 1];
        $mform->addElement('filemanager', 'file_demo', 'Add a file', null, $fileparam);
        $this->add_action_buttons(true, 'Go');
    }
}
$mform = new local_filedemo_form();
echo $OUTPUT->header();
$mform->display();
echo $OUTPUT->footer();
if($data =$mform->get_data()){
    $fileparam = ['maxbytes' => 2048, 'areamaxbytes' => 10485760, 'maxfiles' => 1];

    $context=context_system::instance();
    $draftitemid = file_get_submitted_draft_itemid('file_demo');

        file_save_draft_area_files(
            $draftitemid,
            $context->id,
            'local_filedemo',
            'file_demo',
            0,
            ['subdirs' => 0, 'maxbytes' => 0, 'maxfiles' => 1]
        );
echo('1');
}
