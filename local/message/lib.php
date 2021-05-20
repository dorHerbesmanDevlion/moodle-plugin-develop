<?php
// This file is part of Moodle Course Rollover Plugin
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
 * @package     local_message
 * @author      Kristian
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @var stdClass $plugin
 */

function local_message_before_footer(){

    global $DB, $USER;
    $sql = "SELECT lm.id, lm.messagetext, lm.messagetype 
            FROM {local_message} lm 
            left outer join {local_message_read} lmr ON lm.id = lmr.messageid
            WHERE lmr.userid <> :userid
            OR lmr.userid IS NULL";

    $params = [
        'userid' => $USER->id,
    ];

    $messages = $DB->get_records_sql($sql,$params);

    foreach ($messages as $message) {
        switch ($message->messagetype) {
            case 0:
                \core\notification::add($message->messagetext, \core\output\notification::NOTIFY_WARNING);
                break;
            case 1:
                \core\notification::add($message->messagetext, \core\output\notification::NOTIFY_SUCCESS);
                break;
            case 2:
                \core\notification::add($message->messagetext, \core\output\notification::NOTIFY_ERROR);
                break;
            case 3:
                \core\notification::add($message->messagetext, \core\output\notification::NOTIFY_INFO);
                break;
            default:
                \core\notification::add('error!', \core\output\notification::NOTIFY_ERROR);
        }
        $readrecord = new stdClass();
        $readrecord->messageid = $message->id;
        $readrecord->userid = $USER->id;
        $readrecord->timeread = time();
        $DB->insert_record('local_message_read', $readrecord);
    }
}