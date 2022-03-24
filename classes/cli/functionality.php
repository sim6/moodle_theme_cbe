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
 * @package     theme_cbe
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright   2022 Tresipunt
 */

namespace theme_cbe\cli;

use context_system;
use core_course_external;
use dml_exception;
use stdClass;

global $CFG;
require_once($CFG->dirroot . '/my/lib.php');

defined('MOODLE_INTERNAL') || die();

class functionality {

    /**
     * Execute
     * @throws dml_exception
     */
    static public function execute() {
        self::blocks();
        self::modules();
    }

    /**
     * Execute
     * @throws dml_exception
     */
    static public function blocks() {
        self::create_block('tresipuntmodspend');
        self::blocks_position();
        self::blocks_hide();
        my_reset_page_for_all_users(MY_PAGE_PRIVATE, 'my-index');
    }

    /**
     * Modules
     * @throws dml_exception
     */
    static public function modules() {
        self::module_hide('book');
        self::module_hide('chat');
        self::module_hide('choice');
        self::module_hide('data');
        self::module_hide('imscp');
        self::module_hide('lesson');
        self::module_hide('lti');
        self::module_hide('scorm');
        self::module_hide('survey');
        self::module_hide('wiki');
        self::module_hide('workshop');
        self::module_hide('jitsi');
        self::module_recommended_reset();
        self::module_recommended('assign');
        self::module_recommended('resource');
        self::module_recommended('folder');
        self::module_recommended('url');
    }

    /**
     * Module Hide
     * @throws dml_exception
     */
    static public function module_hide(string $modname) {
        global $DB;
        $params = new stdClass;
        $params->name = $modname;
        $record = $DB->get_record('modules', (array)$params);
        if ($record) {
            $params->id = $record->id;
            $params->visible = 0;
            $DB->update_record('modules', $params);
            cli_writeln('Module: ' . $modname . ' hide!');
        }
    }

    /**
     * Module Recommended Reset.
     *
     * @throws dml_exception
     */
    static public function module_recommended_reset() {
        global $DB;
        $tablefav = 'favourite';
        $records = $DB->get_records($tablefav, [
            'component' => 'core_course'
        ]);

        foreach ($records as $record) {
            $find = 'recommend_mod_';
            $itemtype = isset($record->itemtype) ? $record->itemtype : null;
            if (strpos($itemtype, $find) === 0) {
                $modname = str_replace($find, '', $itemtype);
                $DB->delete_records($tablefav, ['id' => $record->id]);
                cli_writeln('Module: ' . $modname . ' reset recommended!');
            }
        }

    }

    /**
     * Module Recommended.
     *
     * @param string $modname
     * @throws dml_exception
     */
    static public function module_recommended(string $modname) {
        global $DB;
        $tablemodules = 'modules';
        $paramsmods = new stdClass();
        $paramsmods->name = $modname;
        $recordmod = $DB->get_record($tablemodules, (array)$paramsmods);
        if ($recordmod) {
            $context = context_system::instance();
            $tablefav = 'favourite';
            $params = new stdClass;
            $params->component = 'core_course';
            $params->itemtype = 'recommend_mod_' . $modname;
            $params->itemid = $recordmod->id;
            $params->contextid = $context->id;
            $params->contextid = $context->id;
            $record = $DB->get_record($tablefav, (array)$params);
            if (!$record) {
                $params->userid = 1;
                $params->timecreated = time();
                $params->timemodified = time();
                $DB->insert_record($tablefav, $params);
                cli_writeln('Module Recommended: ' . $modname);
            }
        }
    }

    /**
     * Create block
     *
     * @param string $blockname
     * @throws dml_exception
     */
    static protected function create_block(string $blockname) {
        global $DB;
        $tablename = 'block_instances';
        $params = new stdClass;
        $params->blockname = $blockname;
        $params->pagetypepattern = 'my-index';
        $params->defaultregion = 'side-post';
        $record = $DB->get_record($tablename, (array)$params);
        if (!$record) {
            $params->parentcontextid = 1;
            $params->showinsubcontexts = 0;
            $params->subpagepattern = null;
            $params->defaultweight = 2;
            $params->configdata = '';
            $params->timecreated = time();
            $params->timemodified = time();
            $DB->insert_record($tablename, $params);
            cli_writeln('Block: ' . $blockname);
        }
    }

    /**
     * Position blocks
     *
     * @throws dml_exception
     */
    static protected function blocks_position() {
        self::block_position('timeline', 3);
        self::block_position('calendar_month', 1);
    }

    /**
     * Position blocks
     *
     * @param string $blockname
     * @param int $weight
     * @throws dml_exception
     */
    static protected function block_position(string $blockname, int $weight) {
        global $DB;
        $tablename = 'block_instances';
        $params = new stdClass();
        $params->blockname = $blockname;
        $params->pagetypepattern = 'my-index';
        $record = $DB->get_record($tablename, (array)$params);
        if ($record) {
            $params->id = $record->id;
            $params->defaultweight = $weight;
            $DB->update_record($tablename, $params);
            cli_writeln('Block Position: ' . $blockname);
        }
    }

    /**
     * Hide blocks
     *
     * @throws dml_exception
     */
    static protected function blocks_hide() {
        self::block_hide('lp');
        self::block_hide('private_files');
        self::block_hide('online_users');
        self::block_hide('badges');
        self::block_hide('calendar_upcoming');
        self::block_hide('recentlyaccessedcourses');
    }

    /**
     * Hide blocks
     *
     * @param string $blockname
     * @throws dml_exception
     */
    static protected function block_hide(string $blockname) {
        global $DB;

        $tableinst = 'block_instances';
        $tablepos = 'block_positions';

        $params = new stdClass();
        $params->blockname = $blockname;
        $params->pagetypepattern = 'my-index';
        $record = $DB->get_record($tableinst, (array)$params);
        if ($record) {
            $paramshide = new stdClass();
            $paramshide->blockinstanceid = $record->id;
            $paramshide->contextid = 1;
            $paramshide->pagetype = 'my-index';
            $recordhide = $DB->get_record($tablepos, (array)$paramshide);
            if ($recordhide) {
                $recordhide->visible = 0;
                $recordhide->weight = 0;
                $DB->update_record($tablepos, $recordhide);
            } else {
                $paramshide->subpage = $record->subpagepattern;
                $paramshide->visible = 0;
                $paramshide->region = $record->defaultregion;
                $paramshide->weight = 0;
                $DB->insert_record($tablepos, $paramshide);
            }
            cli_writeln('Block Hide: ' . $blockname);
        }
    }




}
