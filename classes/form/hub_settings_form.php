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
 * This form displays hub settings
 *
 * @package     local_hub
 * @copyright   2021, ISB Bayern
 * @author      Peter Mayer
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_hub\form;

defined('MOODLE_INTERNAL') || die();
/**
 * This form displays hub settings
 *
 * @package     local_hub
 * @copyright   2021, ISB Bayern
 * @author      Peter Mayer
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class hub_settings_form extends \moodleform {

    public function definition() {
        global $CFG, $SITE, $USER;

        // Get name (default) value.
        $hubname = get_config('local_hub', 'name');
        if ($hubname === false) {
            $hubname = $SITE->fullname;
        }

        // Get description (default) value.
        $hubdescription = get_config('local_hub', 'description');
        if ($hubdescription === false) {
            $hubdescription = $SITE->summary;
        }

        // Get contactname (default) value.
        $contactname = get_config('local_hub', 'contactname');
        if ($contactname === false) {
            $contactname = $USER->firstname . " " . $USER->lastname;
        }

        // Get contactemail (default) value.
        $contactemail = get_config('local_hub', 'contactemail');
        if ($contactemail === false) {
            $contactemail = $USER->email;
        }

        // Get imageurl (default) value.
        $imageurl = get_config('local_hub', 'imageurl');
        if ($imageurl === false) {
            $imageurl = '';
        }

        // Get $availability (default) value.
        $privacy = get_config('local_hub', 'privacy');
        if ($privacy === false) {
            $privacy = HUBPRIVATE;
        }

        // Get language (default) value.
        $hublanguage = get_config('local_hub', 'language');
        if (empty($hublanguage)) {
            $hublanguage = current_language();
        }

        // Get max course publication per site per day (default) value.
        $hubmaxpublication = get_config('local_hub', 'maxcoursesperday');
        if ($hubmaxpublication === false) {
            $hubmaxpublication = HUB_MAXCOURSESPERSITEPERDAY;
        }

        // Get front page search form is displayed.
        $searchfornologin = get_config('local_hub', 'searchfornologin');
        if ($searchfornologin === false) {
            $searchfornologin = 1;
        }

        // Get max course publication per site per day (default) value.
        $hubmaxwscourseresult = get_config('local_hub', 'maxwscourseresult');
        if ($hubmaxwscourseresult === false) {
            $hubmaxwscourseresult = HUB_MAXWSCOURSESRESULT;
        }

        // Password (default) value.
        if (!empty(get_config('local_hub', 'password'))) {
            $password = get_config('local_hub', 'password');
        } else {
            $password = random_string();
        }

        // Get rss secret - secret to display invisible course only.
        $rsssecret = get_config('local_hub', 'rsssecret');
        if ($rsssecret === false) {
            $rsssecret = '';
        }

        $enabled = get_config('local_hub', 'hubenabled');

        $languages = get_string_manager()->get_list_of_languages();

        $mform = &$this->_form;
        $mform->addElement('header', 'moodle', get_string('settings', 'local_hub'));

        // Get hub version.
        $plugin = new \stdClass();
        include($CFG->dirroot . '/local/hub/version.php');
        $year = substr($plugin->version, 0, 4);
        $month = substr($plugin->version, 4, 2);
        $day = substr($plugin->version, 6, 2);
        $versiondate = mktime(0, 0, 0, $day, $month, $year);
        $mform->addElement(
            'static',
            'version',
            get_string('hubversion', 'local_hub'),
            userdate($versiondate, get_string('strftimedaydate', 'langconfig')) . ' (' . $plugin->version . ')'
        );

        $mform->addElement(
            'text',
            'name',
            get_string('name', 'local_hub')
        );

        $mform->setType('name', PARAM_TEXT);
        $mform->setDefault('name', $hubname);
        $mform->addRule('name', get_string('required'), 'required');
        $mform->addHelpButton('name', 'name', 'local_hub');

        $mform->addElement(
            'checkbox',
            'enabled',
            get_string('enabled', 'local_hub'),
            ''
        );
        $mform->setDefault('enabled', $enabled);
        $mform->addHelpButton('enabled', 'enabled', 'local_hub');

        $mform->addElement(
            'select',
            'lang',
            get_string('language', 'local_hub'),
            $languages
        );
        $mform->setDefault('lang', $hublanguage);
        $mform->addHelpButton('lang', 'hublang', 'local_hub');

        $mform->addElement(
            'textarea',
            'desc',
            get_string('description', 'local_hub'),
            ['rows' => 5, 'cols' => 50]
        );

        $mform->addRule('desc', get_string('required'), 'required');
        $mform->setDefault('desc', $hubdescription);
        $mform->setType('desc', PARAM_TEXT);
        $mform->addHelpButton('desc', 'description', 'local_hub');

        $mform->addElement(
            'text',
            'contactname',
            get_string('contactname', 'local_hub')
        );
        $mform->setType('contactname', PARAM_TEXT);
        $mform->setDefault('contactname', $contactname);
        $mform->addRule('contactname', get_string('required'), 'required');
        $mform->addHelpButton('contactname', 'contactname', 'local_hub');

        $mform->addElement(
            'text',
            'contactemail',
            get_string('contactemail', 'local_hub')
        );
        $mform->setType('contactemail', PARAM_EMAIL);
        $mform->setDefault('contactemail', $contactemail);
        // $mform->addRule('contactemail', get_string('required'), 'required');
        $mform->addHelpButton('contactemail', 'contactemail', 'local_hub');

        $this->update_hublogo();

        $mform->addElement(
            'filepicker',
            'hubimage',
            get_string('hubimage', 'local_hub'),
            null,
            [
                'subdirs' => 0,
                'maxfiles' => 1
            ]
        );
        $mform->addHelpButton('hubimage', 'hubimage', 'local_hub');

        $mform->addElement('text', 'password', get_string('password', 'local_hub'));
        $mform->setType('password', PARAM_RAW);
        $mform->setDefault('password', $password);
        $mform->addHelpButton('password', 'hubpassword', 'local_hub');

        $mform->addElement(
            'text',
            'maxwscourseresult',
            get_string('maxwscourseresult', 'local_hub')
        );
        $mform->setType('maxwscourseresult', PARAM_INT);
        $mform->addHelpButton(
            'maxwscourseresult',
            'maxwscourseresult',
            'local_hub'
        );
        // $mform->setAdvanced('maxwscourseresult');
        $mform->setDefault('maxwscourseresult', $hubmaxwscourseresult);

        $mform->addElement(
            'text',
            'maxcoursesperday',
            get_string('maxcoursesperday', 'local_hub')
        );
        $mform->setType('maxcoursesperday', PARAM_INT);
        $mform->addHelpButton(
            'maxcoursesperday',
            'maxcoursesperday',
            'local_hub'
        );
        // $mform->setAdvanced('maxcoursesperday');
        $mform->setDefault('maxcoursesperday', $hubmaxpublication);

        $this->add_action_buttons(false, get_string('update'));
    }

    /**
     * Validate fields
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        $name = $this->_form->_submitValues['name'];
        if (!empty($name)) {
            if (strcmp(clean_param($name, PARAM_TEXT), $name) != 0) {
                $errors['name'] = get_string('mustbetext', 'local_hub');
            }
        }

        $maxwscourseresult = $this->_form->_submitValues['maxwscourseresult'];
        if (empty($maxwscourseresult) or $maxwscourseresult < 1) {
            $errors['maxwscourseresult'] = get_string('maxwscourseresultempty', 'local_hub');
        }
        $desc = $this->_form->_submitValues['desc'];
        if (!empty($desc)) {
            if (strcmp(clean_param($desc, PARAM_TEXT), $desc) != 0) {
                $errors['desc'] = get_string('mustbetext', 'local_hub');
            }
        }

        $maxcoursesperday = $this->_form->_submitValues['maxcoursesperday'];
        if (!empty($maxcoursesperday)) {
            if (strcmp(clean_param($maxcoursesperday, PARAM_INT), $maxcoursesperday) != 0) {
                $errors['maxcoursesperday'] = get_string('mustbeinteger', 'local_hub');
            }
        }

        return $errors;
    }

    /**
     * Add/remove the hub logo form element
     */
    public function update_hublogo() {
        global $SITE;

        $mform = &$this->_form;
        $logocheckbox = '';
        if ($mform->elementExists('keepcurrentimage')) {
            $logocheckbox = $mform->getElement('keepcurrentimage');
        }
        $hublogo = get_config('local_hub', 'hublogo');
        if (!empty($hublogo)) {

            $params = [
                'filetype' => HUB_HUBSCREENSHOT_FILE_TYPE,
                'time' => time()
            ];
            $imageurl = new \moodle_url("/local/hub/webservice/download.php", $params);

            $hubname = get_config('local_hub', 'name');
            if ($hubname === false) {
                $hubname = $SITE->fullname;
            }

            $imagetag = \html_writer::empty_tag(
                'img',
                [
                    'src' => $imageurl, 'alt' => $hubname,
                    'class' => 'admincurrentimage'
                ]
            );
            if (!empty($logocheckbox)) {
                $logocheckbox->setText($imagetag);
            } else {
                $mform->addElement(
                    'checkbox',
                    'keepcurrentimage',
                    get_string('keepcurrentimage', 'local_hub'),
                    ' ' . $imagetag
                );
                $mform->addHelpButton('keepcurrentimage', 'keepcurrentimage', 'local_hub');
                $mform->setDefault('keepcurrentimage', true);
                //need to move the element to the right position
                // =>fix the issue where saving settings add the element at the bottom of the form
                // $this->move_element_back_after('keepcurrentimage', 'contactemail');
            }
        } else {
            //if no logo, remove the hub logo element
            if (!empty($logocheckbox)) {
                $mform->removeElement('keepcurrentimage');
            }
        }
    }

    /**
     * Move a mform element right after another element
     * @param string $elementname
     * @param string $previouselementname
     */
    // protected function move_element_back_after($elementname, $previouselementname) {
    //     $previouselementindex = $this->_form->_elementIndex[$previouselementname];
    //     $elementindex = $this->_form->_elementIndex[$elementname];

    //     //only move the $element if it is after the $previouselement
    //     if ($previouselementindex + 1 < $elementindex) { //backup elements currently between the previous element and the element to move for ($i=$previouselementindex + 1; ($i <=(count($this->_form->_elements) - 1)) and ($i != $elementindex); $i = $i +1) {
    //         $followingelements[$i + 1] = $this->_form->_elements[$i];
    //     }

    //     //move the element
    //     $this->_form->_elementIndex[$elementname] = $previouselementindex + 1;
    //     $this->_form->_elements[$previouselementindex + 1] = $this->_form->_elements[$elementindex];

    //     //move the betweener elements after the moved element
    //     foreach ($followingelements as $newindex => $element) {
    //         $this->_form->_elementIndex[$element->_attributes['name']] = $newindex;
    //         $this->_form->_elements[$newindex] = $element;
    //     }
    // }
}
