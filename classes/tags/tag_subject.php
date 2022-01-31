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
 * Class tags which subject the template is assigned to.
 *
 * Please note that tags are save using one table:
 * - All values are already stored in hub_tag_options
 * - Relationsship between template and options are stored in table hub_tag
 *
 * @package    local_hub
 * @copyright  2022 ISB Bayern
 * @author     Peter Mayer <peter.mayer@isb.bayern.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_hub\tags;

defined('MOODLE_INTERNAL') || die();

/**
 * Class tags which subject the template is assigned to.
 *
 * Please note that tags are save using one table:
 * - All values are already stored in hub_tag_options
 * - Relationsship between template and options are stored in table hub_tag
 *
 * @package    local_hub
 * @copyright  2022 ISB Bayern
 * @author     Peter Mayer <peter.mayer@isb.bayern.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tag_subject extends tag_base_options {

    /** @var string tagtype of this element */
    protected $name = 'subject';

    /** @var boolean store value returned by formelement, when false */
    protected $usekeys = false;

    /**
     * Get all (fixed) options stored in database for this element
     * @param object $mform
     * @return array values of options indexed by id of options table.
     */
    public function add_formelement(&$mform) {
        global $DB;

        $subjects = $DB->get_records_menu('hub_tag_options', ['tagtype' => 'subject'], '', 'id, value');
        sort($subjects, SORT_LOCALE_STRING);  // For sorting German umlauts. de_DE is set to production as system-local.
        $mform->addElement('searchableselector', 'subject', get_string('subject', 'block_mbsteachshare'), $subjects, ['multiple' => 'multiple']);
        $mform->setType('subject', PARAM_INT);
        $mform->addHelpButton('subject', 'subject', 'block_mbsteachshare');
    }

    /**
     * This is called when the plugin is to be installed or upgraded only.
     */
    public function create_tag_default_options() {
        global $DB;

        // List of all current school subjects in the Bavarian school system.
        $subjects = [
            "Agrarwirtschaft", "Allgemeine Betriebswirtschaftslehre", "Angewandte Informatik", "Arbeitssicherheit",
                "Ästhetische Bildung", "Astrophysik", "Augenoptik", "Aussenwirtschaft",
            "Bautechnik", "Bekleidungstechnik", "Beruf und Arbeit", "Berufliche Orientierung",
                "Berufs- und Lebensorientierung - Praxis Ernährung und Soziales", "Berufs- und Lebensorientierung - Praxis Technik",
                "Berufs- und Lebensorientierung - Theorie", "Betriebswirtschaftliche Steuerung und Kontrolle",
                "Betriebswirtschaftslehre mit Rechnungswesen", "Biologie", "Biologisch-chemisches Praktikum",
                "Biophysik", "Blindenkurzschrift", "Blindheit und Lebenspraxis", "Blumenkunst", "Buchführung", "Bürokommunikation",
            "Chemie", "Chemietechnik", "Chinesisch",
            "Darstellung", "Datenverarbeitung", "Datenverarbeitungstechnik", "Deutsch", "Deutsch als Zweitsprache",
                "Deutsch als Gebärdensprache", "Drucktechnik",
            "Elektrotechnik", "Englisch", "Ergotherapie", "Ernährung und Gesundheit", "Ernährung und Hauswirtschaft",
                "Ernährung und Soziales", "Ernährung und Versorgung", "Ethik", "Euro-Management-Assistenten",
                "Evangelische Religionslehre", "Experimentelles Gestalten",
            "Fachpraktische Ausbildung", "Fahrzeugtechnik und Elektromobilität", "Familienpflege", "Farbtechnik und Raumgestaltung",
                "Fleischtechnik", "Förderschwerpunkt emotionale und soziale Entwicklung", "Förderschwerpunkt geistige Entwicklung",
                "Förderschwerpunkt Hören", "Förderschwerpunkt körperliche und motorische Entwicklung", "Förderschwerpunkt Lernen",
                "Förderschwerpunkt Sehen", "Förderschwerpunkt Sprache", "Französisch", "Fremdsprachenberufe", "Freizeit",
            "Gastgewerbliche Berufe", "Geisteswissenschaften", "Geographie", "Geologie", "Geschichte",
                "Geschichte/Politik/Geographie", "Geschichte/Politik/Geographie und Natur und Technik", "Geschichte/Sozialkunde",
                "Gestaltung", "Gestaltungslehre", "Gesundheit", "Gesundheit und Soziales", "Gesundheitswesen",
                "Gesundheitswirtschaft und Recht", "Gesundheitswissenschaft", "Glashüttentechnik", "Griechisch",
                "Grundlegender entwicklungsbezogener Unterricht",
            "Hauswirtschaft", "Heilerziehungspflege", "Heilpädagogik", "Heimat- und Sachunterricht", "Holztechnik",
                "Hotel- und Gaststättengewerbe ",
            "Informatik", "Informatik und digitales Gestalten", "Informations- und kommunikationstechnische Bildung",
                "Informatiktechnik", "Informationstechnologie", "Informationsverarbeitung", "International Business Studies",
                "Internationale Betriebswirtschaftslehre und Volkswirtschaftslehre", "Islamischer Unterricht", "Italienisch",
            "Japanisch",
            "Katholische Religionslehre", "Kaufmännische Assistenten", "Keramik und Design", "Kommunikation und Interaktion",
                "Körperpflege", "Kunst", "Kunsterziehung", "Kunststofftechnik",
            "Landeskunde", "Latein", "Leben in der Gesellschaft", "Lebensmittelverarbeitungstechnik",
            "Maschinenbautechnik", "Mathematik", "Mechatronik", "Medien", "Medizinische Fachangestellte", "Meisterschule",
                "Mensch und Umwelt", "Metallbautechnik", "Metalltechnik", "Mobilität", "Musik", "Musisch-ästhetische Bildung",
                "Muttersprache",
            "Natur und Technik", "Naturwissenschaften", "Neugriechisch",
            "Pädagogik", "Pädagogik/Psychologie", "Persönlichkeit und Soziale Beziehungen", "Physik", "Physiotherapie", "Politik",
                "Politik und Gesellschaft", "Politik und Gesellschaft/Sozialkunde", "Polnisch", "Psychologie",
            "Raum- und Objektdesign", "Rechnungswesen", "Rechtslehre", "Rechtswesen", "Russisch", "Rhythmik und Musik",
            "Sach- und lebensbezogener Unterricht", "Sanitär-, Heizungs- und Klimatechnik", "Sozialkunde", "Sozialpädagogik",
                "Sozialpraktische Grundbildung", "Sozialpsychologie", "Sozialwesen", "Sozialwirtschaft und Recht",
                "Sozialwissenschaftliche Arbeitsfelder", "Sozialwissenschaftliche Grundbildung", "Soziologie", "Spanisch",
                "Spektrum der Gesundheit", "Sport", "Sport und Bewegung", "Steintechnik", "Studier- und Arbeitstechniken",
            "Tastschreiben", "Technik", "Technische Assistenten", "Technisches Zeichnen", "Technologie", "Textiles Gestalten",
                "Textiltechnik", "Textiltechnik und Bekleidung", "Textverarbeitung", "Tschechisch", "Türkisch",
            "Übungsunternehmen", "Umweltschutztechnik und regenerative Energien",
            "Volkswirtschaft", "Volkswirtschaftslehre",
            "Werken", "Werken und Gestalten", "Werken und Gestalten/Kunst", "Wirtschaft", "Wirtschaft aktuell",
                "Wirtschaft und Beruf", "Wirtschaft und Kommunikation", "Wirtschaft und Recht", "Wirtschaft und Verwaltung",
                "Wirtschaftsgeographie", "Wirtschaftsinformatik", "Wirtschaftslehre", "Wirtschaftsmathematik", "Wohnen"
        ];

        $existingsubjects = $DB->get_fieldset_select('hub_tag_options', 'value', 'tagtype = ?', [$this->name]);

        foreach ($subjects as $subject) {

            // Only insert new subjects to DB.
            if (!in_array($subject, $existingsubjects)) {
                $record = (object) [
                    'tagtype' => $this->name,
                    'value' => $subject
                ];

                $DB->insert_record('hub_tag_options', $record);
            }
        }
    }

}
