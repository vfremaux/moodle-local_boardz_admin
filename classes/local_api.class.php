<?php

namespace boardz;

use \context_system;

class local_api {

    public static function get_local_select_options($scope, $hierarchic = false) {
        global $DB;

        $parts = explode('::', $scope);

        if ($parts[0] == 'user') {

            switch ($parts[1]) {
                case 'departments': {
                    $sql = "
                        SELECT DISTINCT
                            department,
                            department
                        FROM
                            {user}
                        WHERE
                            department != ''
                        ORDER BY
                            department
                    ";
                    $departmentlist = $DB->get_records_sql_menu($sql, []);
                    $departmentlist = array_combine(array_keys($departmentlist), array_keys($departmentlist));
                    $departmentlist[''] = '-- '.get_string('any', 'local_boardz_admin');
                    $departmentlist['*'] = '-- '.get_string('each', 'local_boardz_admin');
                    $departmentlist['a'] = '-- '.get_string('agregate', 'local_boardz_admin');
                    asort($departmentlist);
                    return $departmentlist;
                }

                case 'organisations': {
                    $sql = "
                        SELECT DISTINCT
                            institution,
                            institution
                        FROM
                            {user}
                        WHERE
                            institution != '' AND institution IS NOT NULL
                        ORDER BY
                            institution
                    ";
                    $orglist = $DB->get_records_sql_menu($sql, []);
                    $orglist = array_combine(array_keys($orglist), array_keys($orglist));
                    $orglist[''] = '-- '.get_string('any', 'local_boardz_admin');
                    $orglist['*'] = '-- '.get_string('each', 'local_boardz_admin');
                    $orglist['a'] = '-- '.get_string('agregate', 'local_boardz_admin');
                    asort($orglist);
                    return $orglist;
                }

                case 'countries': {
                    $sql = "
                        SELECT DISTINCT
                            country as value,
                            country as label
                        FROM
                            {user}
                        WHERE
                            country != ''
                        ORDER BY
                            country
                    ";
                    $countrylist = $DB->get_records_sql_menu($sql, []);
                    // $countrylist = array_combine(array_keys($countrylist), array_keys($countrylist));
                    $countrylist[''] = '-- '.get_string('any', 'local_boardz_admin');
                    $countrylist['*'] = '-- '.get_string('each', 'local_boardz_admin');
                    $countrylist['a'] = '-- '.get_string('agregate', 'local_boardz_admin');
                    asort($countrylist);
                    return $countrylist;
                }
            }
        }

        if ($parts[0] == 'users') {
            // Plain user.
            $userlist = $DB->get_records_menu('user', ['deleted' => 0], 'lastname,firstname', 'id, CONCAT(lastname, " ", firstname)');
            $userlist[''] = '-- '.get_string('any', 'local_boardz_admin');
            $userlist['*'] = '-- '.get_string('each', 'local_boardz_admin');
            $userlist['a'] = '-- '.get_string('agregate', 'local_boardz_admin');
            asort($userlist);
            return $userlist;
        }

        if ($parts[0] == 'cohorts') {
            $cohortlist = $DB->get_records_menu('cohort', [], 'name', 'id,name');
            $cohortlist[''] = '-- '.get_string('any', 'local_boardz_admin');
            $cohortlist['*'] = '-- '.get_string('each', 'local_boardz_admin');
            $cohortlist['a'] = '-- '.get_string('agregate', 'local_boardz_admin');
            asort($cohortlist);
            return $cohortlist;
        }

        if ($parts[0] == 'coursecategories') {
            $catlist = \core_course_category::make_categories_list();
            $catlist[''] = '-- '.get_string('any', 'local_boardz_admin');
            $catlist['*'] = '-- '.get_string('each', 'local_boardz_admin');
            $catlist['a'] = '-- '.get_string('agregate', 'local_boardz_admin');
            asort($catlist);
            return $catlist;
        }

        if ($parts[0] == 'courses') {
            $courselist = $DB->get_records_menu('course', [], 'fullname', 'id, CONCAT("[", shortname, "] ", fullname)');
            $courselist[''] = '-- '.get_string('any', 'local_boardz_admin');
            $courselist['*'] = '-- '.get_string('each', 'local_boardz_admin');
            $courselist['a'] = '-- '.get_string('agregate', 'local_boardz_admin');
            asort($courselist);
            return $courselist;
        }

        if ($parts[0] == 'groups') {
            $sql = '
                SELECT DISTINCT
                    CONCAT(c.shortname, "|",.g.name) AS value,
                    CONCAT(c.shortname, " - ", g.name) AS label
                FROM
                    {course} c,
                    {groups} g
                WHERE
                    c.id = g.courseid
                ORDER BY
                    c.shortname, g.name
            ';
            $grouplist = $DB->get_records_sql_menu($sql, []);
            $grouplist[''] = '-- '.get_string('any', 'local_boardz_admin');
            $grouplist['*'] = '-- '.get_string('each', 'local_boardz_admin');
            $grouplist['a'] = '-- '.get_string('agregate', 'local_boardz_admin');
            asort($grouplist);
            return $grouplist;
        }

        if ($parts[0] == 'coursesections') {
            $sql = '
                SELECT
                    s.id,
                    CASE WHEN s.name IS NULL THEN CONCAT("[", c.shortname, "] - S", s.section) ELSE 
                    CONCAT("[", c.shortname, "] - ", s.name) END
                FROM
                    {course} c,
                    {course_sections} s
                WHERE
                    c.id = s.course AND
                    c.id != 1 AND
                    c.shortname != "" AND
                    c.shortname IS NOT NULL
                ORDER BY
                    c.shortname, s.section
            ';
            $sectionlist = $DB->get_records_sql_menu($sql, []);
            $sectionlist[''] = '-- '.get_string('any', 'local_boardz_admin');
            $sectionlist['*'] = '-- '.get_string('each', 'local_boardz_admin');
            $sectionlist['a'] = '-- '.get_string('agregate', 'local_boardz_admin');
            asort($sectionlist);
            return $sectionlist;
        }

        if ($parts[0] == 'contextlevels') {
            $levellist[''] = '-- '.get_string('any', 'local_boardz_admin');
            $levellist['*'] = '-- '.get_string('each', 'local_boardz_admin');
            $levellist['a'] = '-- '.get_string('agregate', 'local_boardz_admin');
            $levellist[CONTEXT_SYSTEM] = get_string('site');
            $levellist[CONTEXT_USER] = get_string('user');
            $levellist[CONTEXT_COURSECAT] = get_string('category');
            $levellist[CONTEXT_COURSE] = get_string('course');
            $levellist[CONTEXT_MODULE] = get_string('activitymodule');
            $levellist[CONTEXT_BLOCK] = get_string('block');
            return $levellist;
        }

        if ($parts[0] == 'components') {
            $components = \core_component::get_component_names();
            $componentlist = array_combine($components, $components);
            $componentlist[''] = '-- '.get_string('any', 'local_boardz_admin');
            $componentlist['*'] = '-- '.get_string('each', 'local_boardz_admin');
            $componentlist['a'] = '-- '.get_string('agregate', 'local_boardz_admin');
            $componentlist['core'] = get_string('core', 'local_boardz_admin');
            asort($componentlist);
            return $componentlist;
            return ;
        }

        if ($parts[0] == 'actions') {
            $sql = '
                SELECT DISTINCT
                    CONCAT(component, "-", action, "_", target) as value,
                    CONCAT(component, " - ", action, "_", target) as label
                FROM
                    {logstore_standard_log}
                WHERE
                    action != ""
                ORDER BY
                    component,action
            ';
            $actionlist = $DB->get_records_sql_menu($sql, []);

            if ($hierarchic) {
                $hierarchiclist = [];
                foreach ($actionlist as $value => $label) {
                    list($component, $action) = explode('-', $value);
                    $hierarchiclist[$component][$action] = $label;
                }

                foreach (array_keys($hierarchiclist) as $component) {
                    $hierarchiclist[$component][''] = '-- '.get_string('any', 'local_boardz_admin');
                    $hierarchiclist[$component]['*'] = '-- '.get_string('each', 'local_boardz_admin');
                    $hierarchiclist[$component]['a'] = '-- '.get_string('agregate', 'local_boardz_admin');
                    asort($hierarchiclist[$component]);
                }
                return $hierarchiclist;
            }

            $actionlist[''] = '-- '.get_string('any', 'local_boardz_admin');
            $actionlist['*'] = '-- '.get_string('each', 'local_boardz_admin');
            $actionlist['a'] = '-- '.get_string('agregate', 'local_boardz_admin');
            asort($actionlist);
            return $actionlist;
        }

        if ($parts[0] == 'roles') {
            $roleslist[''] = '-- '.get_string('any', 'local_boardz_admin');
            $roleslist['*'] = '-- '.get_string('each', 'local_boardz_admin');
            $roleslist['a'] = '-- '.get_string('agregate', 'local_boardz_admin');

            $roles = $DB->get_records('role', [], 'sortorder');
            foreach ($roles as $r) {
                $roleslist[$r->shortname] = role_get_name($r);
            }

            return $roleslist;
        }

    }

    public static function get_local_calendar_options($scope) {
        switch ($scope) {
            case 'yeardays': {
                $days[''] = '-- '.get_string('any', 'local_boardz_admin');
                $days['*'] = '-- '.get_string('each', 'local_boardz_admin');
                $days['a'] = '-- '.get_string('agregate', 'local_boardz_admin');
                for ($i = 1; $i < 366; $i++) {
                    $days[$i] = $i;
                }
                return $days;
            }

            case 'weekdays': {
                $DAYS = ['sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat'];
                $days[''] = '-- '.get_string('any', 'local_boardz_admin');
                $days['*'] = '-- '.get_string('each', 'local_boardz_admin');
                $days['a'] = '-- '.get_string('agregate', 'local_boardz_admin');
                for ($i = 1; $i < 8; $i++) {
                    $days[$i] = get_string($DAYS[$i - 1], 'calendar');
                }
                return $days;
            }

            case 'days': {
                $days[''] = '-- '.get_string('any', 'local_boardz_admin');
                $days['*'] = '-- '.get_string('each', 'local_boardz_admin');
                $days['a'] = '-- '.get_string('agregate', 'local_boardz_admin');
                for ($i = 1; $i < 32; $i++) {
                    $days[$i] = $i;
                }
                return $days;
            }

            case 'weeks': {
                $weeks[''] = '-- '.get_string('any', 'local_boardz_admin');
                $weeks['*'] = '-- '.get_string('each', 'local_boardz_admin');
                $weeks['a'] = '-- '.get_string('agregate', 'local_boardz_admin');
                for ($i = 1; $i <= 52; $i++) {
                    $weeks[$i] = $i;
                }
                return $weeks;
            }

            case 'months': {
                $months[''] = '-- '.get_string('any', 'local_boardz_admin');
                $months['*'] = '-- '.get_string('each', 'local_boardz_admin');
                $months['a'] = '-- '.get_string('agregate', 'local_boardz_admin');
                for ($i = 1; $i <= 12; $i++) {
                    $months[$i] = $i;
                }
                return $months;
            }

            case 'years': {
                $years[''] = '-- '.get_string('any', 'local_boardz_admin');
                $years['*'] = '-- '.get_string('each', 'local_boardz_admin');
                $years['a'] = '-- '.get_string('agregate', 'local_boardz_admin');
                for ($i = 0; $i <= 20; $i++) {
                    $years[2010 + $i] = 2010 + $i;
                }
                return $years;
            }
        }
    }

}