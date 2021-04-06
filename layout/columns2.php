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
 * A two column layout for the CBE theme.
 *
 * @package     theme_cbe
 * @copyright   2021 Tresipunt
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use theme_cbe\course_navigation;
use theme_cbe\output\course_header_navbar_component;
use theme_cbe\output\course_left_section_component;

defined('MOODLE_INTERNAL') || die();

user_preference_allow_ajax_update('drawer-open-nav', PARAM_ALPHA);

global $CFG, $OUTPUT, $PAGE, $SITE;

require_once($CFG->libdir . '/behat/lib.php');

if (isloggedin()) {
    $navdraweropen = (get_user_preferences('drawer-open-nav', 'true') == 'true');
} else {
    $navdraweropen = false;
}
$extraclasses = [];
if ($navdraweropen) {
    $extraclasses[] = 'drawer-open-left';
}

switch ($PAGE->context->contextlevel) {
    case CONTEXT_COURSE:
        $in_course = true;
        $course_id = $PAGE->context->instanceid;
        $output_theme_cbe = $PAGE->get_renderer('theme_cbe');
        $nav_header_course_component = new course_header_navbar_component($course_id);
        $nav_header_course = $output_theme_cbe->render($nav_header_course_component);
        $course_left_menu_component = new course_left_section_component($course_id);
        $course_left_menu = $output_theme_cbe->render($course_left_menu_component);
        $course_page = course_navigation::get_navigation_page();
        break;
    default:
        $in_course = false;
        $nav_header_course = '';
        $course_page = '';
        $course_left_menu = false;
        $course_page = '';
}

if ($course_page === 'board' || $course_page === 'themes') {
    $is_course_blocks = true;
} else {
    $is_course_blocks = false;
}

$bodyattributes = $OUTPUT->body_attributes($extraclasses);
$blockshtml = $OUTPUT->blocks('side-pre');
$hasblocks = strpos($blockshtml, 'data-block=') !== false;
$buildregionmainsettings = !$PAGE->include_region_main_settings_in_header_actions();
// If the settings menu will be included in the header then don't add it here.
$regionmainsettingsmenu = $buildregionmainsettings ? $OUTPUT->region_main_settings_menu() : false;
$templatecontext = [
    'sitename' => format_string($SITE->shortname, true,
        ['context' => context_course::instance(SITEID), "escape" => false]),
    'output' => $OUTPUT,
    'sidepreblocks' => $blockshtml,
    'hasblocks' => $hasblocks,
    'bodyattributes' => $bodyattributes,
    'navdraweropen' => $navdraweropen,
    'regionmainsettingsmenu' => $regionmainsettingsmenu,
    'hasregionmainsettingsmenu' => !empty($regionmainsettingsmenu),
    'in_course' => $in_course,
    'course_left_menu' => $course_left_menu,
    'navbar_header_course'=> $nav_header_course,
    'is_course_blocks'=> $is_course_blocks,
    'course_page'=> $course_page,
];

$nav = $PAGE->flatnav;
$templatecontext['flatnavigation'] = $nav;
$templatecontext['firstcollectionlabel'] = $nav->get_collectionlabel();

if (is_siteadmin()) {
    echo $OUTPUT->render_from_template('theme_cbe/columns2_admin', $templatecontext);
} else {
    echo $OUTPUT->render_from_template('theme_cbe/columns2', $templatecontext);
}

