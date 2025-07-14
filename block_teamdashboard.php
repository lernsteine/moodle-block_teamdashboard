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
 * @package    block_teamdashboard
 * @copyright  2025 Ralf Hagemeister <ralf.hagemeister@lernsteine.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
 
 
defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/completionlib.php');

class block_teamdashboard extends block_base {

    public function init() {
        $this->title = get_string('pluginname', 'block_teamdashboard');
    }

    public function get_content() {
        global $OUTPUT, $USER, $DB;

        if ($this->content !== null) {
            return $this->content;
        }

        $this->content = new stdClass();

        $perpage = 20;
        $page = optional_param('tdpage', 0, PARAM_INT);
        $offset = $page * $perpage;

        $teacherrole = $DB->get_record('role', ['shortname' => 'teacher']);
        $studentrole = $DB->get_record('role', ['shortname' => 'student']);
        if (!$teacherrole || !$studentrole) {
            $this->content->text = 'Role "teacher" or "student" not found.';
            return $this->content;
        }

        $sql = "
            SELECT * FROM {course}
            WHERE visible = 1 AND id > 1
            ORDER BY sortorder ASC
            LIMIT $perpage OFFSET $offset
        ";
        $courses = $DB->get_records_sql($sql);

        $coursedata = [];
        $now = time();

        foreach ($courses as $course) {
            $context = context_course::instance($course->id);
            if (!user_has_role_assignment($USER->id, $teacherrole->id, $context->id)) {
                continue;
            }

            $groupmode = groups_get_course_groupmode($course);
            $canseeallgroups = has_capability('moodle/site:accessallgroups', $context);
            $users = [];

            if ($groupmode && !$canseeallgroups) {
                $trainergroups = groups_get_user_groups($course->id, $USER->id);
                foreach ($trainergroups[0] as $groupid) {
                    $members = groups_get_members($groupid, 'u.*');
                    foreach ($members as $id => $member) {
                        $users[$id] = $member;
                    }
                }
            } else {
                $users = get_enrolled_users($context, 'moodle/course:viewparticipants');
            }

            $students = array_filter($users, function($u) use ($studentrole, $context) {
                return user_has_role_assignment($u->id, $studentrole->id, $context->id);
            });

            $completion = new completion_info($course);
            $hascompletion = $completion->is_enabled();
            $completed = $inprogress = $overdue = 0;

            foreach ($students as $user) {
                if (!$hascompletion) {
                    $inprogress++;
                    continue;
                }

                if ($completion->is_course_complete($user->id)) {
                    $completed++;
                } elseif (!empty($course->enddate) && $now > $course->enddate) {
                    $overdue++;
                } else {
                    $inprogress++;
                }
            }

            $total = max(count($students), 1);

            $coursedata[] = [
                'name' => format_string($course->fullname),
                'url' => (new moodle_url('/course/view.php', ['id' => $course->id]))->out(),
                'participants' => count($students),
                'progress' => [
                    'completed' => round($completed / $total * 100),
                    'inprogress' => round($inprogress / $total * 100),
                    'overdue' => round($overdue / $total * 100)
                ]
            ];
        }

        $nexturl = new moodle_url($this->page->url, ['tdpage' => $page + 1]);

        $templatecontext = [
            'courses' => $coursedata,
            'hasnext' => count($courses) === $perpage,
            'nexturl' => $nexturl->out(),
            'nextlabel' => get_string('nextpage', 'block_teamdashboard'),
            'legend_completed' => get_string('legend_completed', 'block_teamdashboard'),
            'legend_inprogress' => get_string('legend_inprogress', 'block_teamdashboard'),
            'legend_overdue' => get_string('legend_overdue', 'block_teamdashboard')
        ];

        $this->content->text = $OUTPUT->render_from_template('block_teamdashboard/courselist', $templatecontext);
        return $this->content;
    }
}
