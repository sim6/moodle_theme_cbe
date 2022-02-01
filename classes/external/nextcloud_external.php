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
 * @copyright   3iPunt <https://www.tresipunt.com/>
 */

namespace theme_cbe\external;

use coding_exception;
use dml_exception;
use external_api;
use external_function_parameters;
use external_single_structure;
use external_value;
use invalid_parameter_exception;
use theme_cbe\api\nextcloud;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/externallib.php');
require_once($CFG->dirroot . '/webservice/lib.php');

class nextcloud_external extends external_api {

    /**
     * @return external_function_parameters
     */
    public static function createfile_parameters(): external_function_parameters {
        return new external_function_parameters(
            array()
        );
    }

    /**
     * @return array
     * @throws invalid_parameter_exception
     * @throws dml_exception
     * @throws coding_exception
     */
    public static function createfile(): array {

        self::validate_parameters(
            self::createfile_parameters(), [
            ]
        );

        if (empty(get_config('theme_cbe', 'hostnccreate'))) {
            $success = false;
            $error = get_string('nextcloud_create_not_config', 'theme_cbe');
            $link = '';
        } else {
            $link = get_config('theme_cbe', 'hostnccreate');
            $success = true;
            $error = '';
        }

        return [
            'success' => $success,
            'error' => $error,
            'link' => $link
        ];
    }

    /**
     * @return external_single_structure
     */
    public static function createfile_returns(): external_single_structure {
        return new external_single_structure(
            array(
                'success' => new external_value(PARAM_BOOL, 'Was it a success?'),
                'error' => new external_value(PARAM_TEXT, 'Error message'),
                'link' => new external_value(PARAM_TEXT, 'Link'),
            )
        );
    }
}
