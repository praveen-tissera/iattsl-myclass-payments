<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Student_portal_model extends CI_Model
{
    private $registration_table = 'wp_wlsm_student_records';
    private $registration_meta_key = 'iattsl_student_record_ids';
    private $account_meta_key = 'iattsl_student_account';
    private $password_change_meta_key = 'iattsl_student_force_password_change';
    private $auth_version_meta_key = 'iattsl_student_auth_version';

    public function resolve_base_registration($identifier, $session_id = NULL)
    {
        if (!is_string($identifier)) {
            return array('status' => 'invalid', 'base' => '', 'candidates' => array(), 'authorized' => FALSE);
        }

        $identifier = trim($identifier);
        if (!preg_match('/^([A-Z]{2,10}\/[0-9]{2}-[0-9]{1,6})(?:-[0-9]+)?$/D', $identifier, $matches)) {
            return array('status' => 'invalid', 'base' => '', 'candidates' => array(), 'authorized' => FALSE);
        }
        if ($session_id !== NULL && (!is_int($session_id) || $session_id < 1)) {
            return array('status' => 'invalid', 'base' => $matches[1], 'candidates' => array(), 'authorized' => FALSE);
        }

        $base = $matches[1];
        $this->db
            ->select(
                'students.ID AS student_record_id, students.admission_number, students.name, ' .
                'students.section_id AS subject_id, students.session_id, students.is_active, students.user_id AS existing_user_id, ' .
                'subjects.label AS subject_name, classes.ID AS class_id, classes.label AS grade_name, ' .
                'sessions.label AS academic_year'
            )
            ->from($this->registration_table . ' AS students')
            ->join('wp_wlsm_sections AS subjects', 'subjects.ID = students.section_id', 'left')
            ->join('wp_wlsm_class_school AS class_school', 'class_school.ID = subjects.class_school_id', 'left')
            ->join('wp_wlsm_classes AS classes', 'classes.ID = class_school.class_id', 'left')
            ->join('wp_wlsm_sessions AS sessions', 'sessions.ID = students.session_id', 'left')
            ->group_start()
            ->where('students.admission_number', $base)
            ->or_like('students.admission_number', $base . '-', 'after')
            ->group_end();
        if ($session_id !== NULL) {
            $this->db->where('students.session_id', $session_id);
        }

        $matches = array();
        $candidate_pattern = '/^' . preg_quote($base, '/') . '(?:-[0-9]+)?$/D';
        foreach ($this->db->order_by('students.session_id', 'DESC')->order_by('students.ID', 'ASC')->get()->result() as $row) {
            if (preg_match($candidate_pattern, $row->admission_number)) {
                $matches[] = $row;
            }
        }

        if (empty($matches)) {
            $status = 'not_found';
        } elseif (count($matches) > 1) {
            $status = 'ambiguous';
        } else {
            $status = 'single_candidate';
        }

        return array(
            'status' => $status,
            'base' => $base,
            'candidates' => $matches,
            'authorized' => FALSE
        );
    }

    public function get_user_for_student_login($username)
    {
        if (!is_string($username) || $username === '' || strlen($username) > 60) {
            return FALSE;
        }

        $users = $this->db
            ->select('ID, user_login, user_pass, user_status')
            ->where('user_login', $username)
            ->get('wp_users')
            ->result();
        if (count($users) !== 1 || (int) $users[0]->user_status !== 0) {
            return FALSE;
        }

        $user = $users[0];
        $is_student = $this->get_unique_meta_value((int) $user->ID, $this->account_meta_key);
        $registration_ids = $this->get_unique_meta_value((int) $user->ID, $this->registration_meta_key);
        $must_change = $this->get_unique_meta_value((int) $user->ID, $this->password_change_meta_key);
        $auth_version = $this->get_unique_meta_value((int) $user->ID, $this->auth_version_meta_key);
        if ($is_student !== '1' || !in_array($must_change, array('0', '1'), TRUE) ||
            !is_string($auth_version) || !ctype_digit($auth_version) || (int) $auth_version < 1) {
            return FALSE;
        }

        $registration_ids = $this->decode_registration_ids($registration_ids);
        if ($registration_ids === FALSE) {
            return FALSE;
        }

        $user->registration_ids = $registration_ids;
        $user->must_change_password = $must_change === '1';
        $user->auth_version = (int) $auth_version;
        return $user;
    }

    public function get_student_registrations($user_id, $registration_ids)
    {
        $registration_ids = $this->normalize_registration_ids($registration_ids);
        if ($registration_ids === FALSE || empty($registration_ids) || !$this->registration_mapping_is_unique($user_id, $registration_ids)) {
            return FALSE;
        }

        $rows = $this->registration_rows($registration_ids);
        if (count($rows) !== count($registration_ids)) {
            return FALSE;
        }
        foreach ($rows as $row) {
            if ((int) $row->subject_id < 1 || (int) $row->class_id < 1 ||
                $row->subject_name === NULL || $row->grade_name === NULL ||
                $row->academic_year === NULL) {
                return FALSE;
            }
        }

        return $rows;
    }

    public function get_student_account_state($user_id)
    {
        $user_id = (int) $user_id;
        if ($user_id < 1) {
            return FALSE;
        }
        $user = $this->db
            ->select('user_status')
            ->where('ID', $user_id)
            ->get('wp_users')
            ->row();
        if (!$user || (int) $user->user_status !== 0 ||
            $this->get_unique_meta_value($user_id, $this->account_meta_key) !== '1' ||
            $this->get_unique_meta_value($user_id, 'wp_capabilities') !== 'a:0:{}' ||
            $this->get_unique_meta_value($user_id, 'wp_user_level') !== '0') {
            return FALSE;
        }
        $ids = $this->decode_registration_ids($this->get_unique_meta_value($user_id, $this->registration_meta_key));
        $version = $this->get_unique_meta_value($user_id, $this->auth_version_meta_key);
        $must_change = $this->get_unique_meta_value($user_id, $this->password_change_meta_key);
        if ($ids === FALSE || !is_string($version) || !ctype_digit($version) || (int) $version < 1 ||
            !in_array($must_change, array('0', '1'), TRUE)) {
            return FALSE;
        }

        $rows = $this->get_student_registrations($user_id, $ids);
        if ($rows === FALSE) {
            return FALSE;
        }

        $active = array();
        $inactive_count = 0;
        foreach ($rows as $row) {
            if ((int) $row->is_active === 1) {
                $active[] = $row;
            } else {
                $inactive_count++;
            }
        }

        return array(
            'registrations' => $active,
            'inactive_count' => $inactive_count,
            'all_inactive' => empty($active),
            'must_change_password' => $must_change === '1',
            'auth_version' => (int) $version
        );
    }

    public function get_password_hash($user_id)
    {
        $row = $this->db
            ->select('user_pass')
            ->where('ID', (int) $user_id)
            ->get('wp_users')
            ->row();

        return $row ? $row->user_pass : FALSE;
    }

    public function get_student_username($user_id)
    {
        $row = $this->db
            ->select('user_login')
            ->where('ID', (int) $user_id)
            ->get('wp_users')
            ->row();

        return $row ? $row->user_login : '';
    }

    public function change_student_password($user_id, $current_hash, $new_hash)
    {
        return $this->change_student_account($user_id, $current_hash, NULL, $new_hash);
    }

    public static function is_valid_username($username)
    {
        return is_string($username) &&
            preg_match('/\A[A-Za-z0-9][A-Za-z0-9._-]{3,29}\z/', $username) === 1 &&
            preg_match('/\A[A-Za-z]{2,5}[_-]\d{2}[_-]\d/', $username) !== 1;
    }

    public function username_is_taken($username, $except_user_id)
    {
        $row = $this->db->query(
            'SELECT COUNT(*) AS total FROM wp_users WHERE ID <> ? AND (user_login = ? OR user_nicename = ? OR user_email = ?)',
            array((int) $except_user_id, $username, $username, $username)
        )->row();
        return !$row || (int) $row->total > 0;
    }

    // Returns the new auth version, FALSE on failure, or 'taken' when the username is unavailable.
    public function change_student_account($user_id, $current_hash, $new_username, $new_hash)
    {
        if ($new_username === NULL && $new_hash === NULL) {
            return FALSE;
        }
        if ($new_username !== NULL && !self::is_valid_username($new_username)) {
            return FALSE;
        }
        $state = $this->get_student_account_state($user_id);
        if ($state === FALSE || $state['all_inactive']) {
            return FALSE;
        }

        if ($this->db->trans_begin() === FALSE) {
            return FALSE;
        }
        $version = $state['auth_version'] + 1;
        if (!$this->lock_auth_version($user_id, $state['auth_version'])) {
            $this->db->trans_rollback();
            return FALSE;
        }
        if ($new_username !== NULL && $this->username_is_taken($new_username, $user_id)) {
            $this->db->trans_rollback();
            return 'taken';
        }
        $changes = array();
        if ($new_hash !== NULL) {
            $changes['user_pass'] = $new_hash;
        }
        if ($new_username !== NULL) {
            $changes['user_login'] = $new_username;
            $changes['user_nicename'] = $new_username;
        }
        $this->db
            ->where('ID', (int) $user_id)
            ->where('user_pass', $current_hash)
            ->update('wp_users', $changes);
        if ($this->db->affected_rows() < 0 ||
            ($new_hash !== NULL && $new_hash !== $current_hash && $this->db->affected_rows() !== 1) ||
            !$this->set_unique_meta_value($user_id, $this->password_change_meta_key, '0') ||
            !$this->set_unique_meta_value($user_id, $this->auth_version_meta_key, (string) $version) ||
            $this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            return FALSE;
        }

        if ($this->db->trans_commit() === FALSE) {
            return FALSE;
        }
        return $version;
    }

    public function find_student_accounts()
    {
        $links = $this->db
            ->select('user_id')
            ->where('meta_key', $this->account_meta_key)
            ->where('meta_value', '1')
            ->order_by('user_id', 'ASC')
            ->get('wp_usermeta')
            ->result();

        $accounts = array();
        foreach ($links as $link) {
            $user_id = (int) $link->user_id;
            $user = $this->db
                ->select('ID, user_login')
                ->where('ID', $user_id)
                ->get('wp_users')
                ->row();
            if (!$user) {
                continue;
            }

            $ids = $this->decode_registration_ids($this->get_unique_meta_value($user_id, $this->registration_meta_key));
            $rows = $ids === FALSE ? FALSE : $this->get_student_registrations($user_id, $ids);
            $accounts[] = array(
                'user_id' => $user_id,
                'user_login' => $user->user_login,
                'registrations' => $rows === FALSE ? array() : $rows,
                'mapping_valid' => $rows !== FALSE
            );
        }

        return $accounts;
    }

    public function create_student_account($username, $password_hash, $registration_ids)
    {
        $registration_ids = $this->normalize_registration_ids($registration_ids);
        if (!is_string($username) || !self::is_valid_username($username) ||
            !is_string($password_hash) || $registration_ids === FALSE || empty($registration_ids)) {
            return array('success' => FALSE, 'error' => 'invalid');
        }

        if ($this->db->trans_begin() === FALSE) {
            return array('success' => FALSE, 'error' => 'database');
        }
        $lock_suffix = $this->db->dbdriver === 'mysqli' ? ' FOR UPDATE' : '';
        $mapping_rows = $this->db->query(
            'SELECT user_id, meta_value FROM wp_usermeta WHERE meta_key = ?' . $lock_suffix,
            array($this->registration_meta_key)
        )->result();
        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            return array('success' => FALSE, 'error' => 'database');
        }

        $already_mapped = array();
        foreach ($mapping_rows as $mapping) {
            $mapped_ids = $this->decode_registration_ids($mapping->meta_value);
            if ($mapped_ids === FALSE) {
                $this->db->trans_rollback();
                return array('success' => FALSE, 'error' => 'ambiguous');
            }
            foreach ($mapped_ids as $mapped_id) {
                $already_mapped[$mapped_id] = TRUE;
            }
        }
        foreach ($registration_ids as $registration_id) {
            if (isset($already_mapped[$registration_id])) {
                $this->db->trans_rollback();
                return array('success' => FALSE, 'error' => 'mapped');
            }
        }

        $records = $this->registration_rows($registration_ids);
        if (count($records) !== count($registration_ids)) {
            $this->db->trans_rollback();
            return array('success' => FALSE, 'error' => 'invalid');
        }
        foreach ($records as $record) {
            if ((int) $record->is_active !== 1) {
                $this->db->trans_rollback();
                return array('success' => FALSE, 'error' => 'inactive');
            }
            if (!empty($record->existing_user_id)) {
                $this->db->trans_rollback();
                return array('success' => FALSE, 'error' => 'mapped');
            }
        }

        $username_exists = $this->db
            ->where('user_login', $username)
            ->count_all_results('wp_users') > 0;
        if ($username_exists) {
            $this->db->trans_rollback();
            return array('success' => FALSE, 'error' => 'username');
        }

        $now = date('Y-m-d H:i:s');
        $this->db->insert('wp_users', array(
            'user_login' => $username,
            'user_pass' => $password_hash,
            'user_nicename' => $username,
            'user_email' => '',
            'user_url' => '',
            'user_registered' => $now,
            'user_activation_key' => '',
            'user_status' => 0,
            'display_name' => 'IATTSL Student'
        ));
        $user_id = (int) $this->db->insert_id();
        if ($user_id < 1 || $this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            return array('success' => FALSE, 'error' => 'database');
        }

        $metadata = array(
            array('user_id' => $user_id, 'meta_key' => $this->account_meta_key, 'meta_value' => '1'),
            array('user_id' => $user_id, 'meta_key' => $this->registration_meta_key, 'meta_value' => json_encode($registration_ids)),
            array('user_id' => $user_id, 'meta_key' => $this->password_change_meta_key, 'meta_value' => '1'),
            array('user_id' => $user_id, 'meta_key' => $this->auth_version_meta_key, 'meta_value' => '1'),
            array('user_id' => $user_id, 'meta_key' => 'wp_capabilities', 'meta_value' => 'a:0:{}'),
            array('user_id' => $user_id, 'meta_key' => 'wp_user_level', 'meta_value' => '0')
        );
        $this->db->insert_batch('wp_usermeta', $metadata);
        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            return array('success' => FALSE, 'error' => 'database');
        }

        if ($this->db->trans_commit() === FALSE) {
            return array('success' => FALSE, 'error' => 'database');
        }
        return array('success' => TRUE, 'user_id' => $user_id, 'username' => $username);
    }

    public function reset_student_password($user_id, $password_hash)
    {
        $state = $this->get_student_account_state($user_id);
        if ($state === FALSE || $state['all_inactive']) {
            return FALSE;
        }

        if ($this->db->trans_begin() === FALSE) {
            return FALSE;
        }
        if (!$this->lock_auth_version($user_id, $state['auth_version'])) {
            $this->db->trans_rollback();
            return FALSE;
        }
        $this->db
            ->where('ID', (int) $user_id)
            ->update('wp_users', array('user_pass' => $password_hash));
        $version = $state['auth_version'] + 1;
        if ($this->db->affected_rows() !== 1 ||
            !$this->set_unique_meta_value($user_id, $this->password_change_meta_key, '1') ||
            !$this->set_unique_meta_value($user_id, $this->auth_version_meta_key, (string) $version) ||
            $this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            return FALSE;
        }

        return $this->db->trans_commit() !== FALSE;
    }

    // Sections are visible to every student registered for the same Grade and Subject, in any academic year or branch.
    // Returns NULL when the section table is not installed yet. Only names are returned.
    public function get_subject_teaching_sections($registration)
    {
        if (!$this->db->table_exists('wp_wlsm_ai_sections_iattsl')) {
            return NULL;
        }
        if ((int) $registration->class_id < 1 || (int) $registration->subject_id < 1) {
            return array();
        }

        return $this->db->query(
            'SELECT sections.name, MAX(sections.is_default) AS is_default ' .
            'FROM wp_wlsm_ai_sections_iattsl AS sections ' .
            $this->visibility_join('sections') .
            'WHERE sections.class_id = ? AND sections.subject_id = ? ' .
            $this->visibility_filter() .
            'GROUP BY sections.name ORDER BY is_default DESC, sections.name ASC',
            array((int) $registration->class_id, (int) $registration->subject_id)
        )->result();
    }

    // Published activities only for the registration's Grade and Subject (any academic year or branch),
    // linked to a section of the same teacher for the same Grade and Subject. evaluation_data is never selected.
    // Returns NULL when the activity tables are not installed.
    public function get_subject_published_activities($registration)
    {
        $rows = $this->query_published_activities($registration, NULL);
        if ($rows === NULL) {
            return NULL;
        }
        foreach ($rows as $row) {
            $content = json_decode((string) $row->student_content, TRUE);
            $row->description = is_array($content) && isset($content['instructions']) && is_string($content['instructions'])
                ? $content['instructions'] : '';
            unset($row->student_content);
        }
        return $rows;
    }

    // One playable activity under the same access rules, with its decoded student-visible content. NULL when not accessible.
    public function get_playable_activity($registration, $activity_id)
    {
        $rows = $this->query_published_activities($registration, (int) $activity_id);
        if (empty($rows)) {
            return NULL;
        }
        $row = $rows[0];
        $row->student_content = json_decode((string) $row->student_content, TRUE);
        return is_array($row->student_content) ? $row : NULL;
    }

    // Answer key, only for scoring after access to the activity has been verified. Never pass this to a view.
    public function get_activity_evaluation($activity_id)
    {
        $row = $this->db->select('evaluation_data')->where('ID', (int) $activity_id)->where('status', 'published')
            ->get('wp_wlsm_ai_activities_iattsl')->row();
        $data = $row ? json_decode((string) $row->evaluation_data, TRUE) : NULL;
        return is_array($data) ? $data : NULL;
    }

    // A teacher can hide a Teaching Section until the lesson is taught. No row means visible, so behaviour is unchanged
    // until the visibility table exists and a teacher hides something.
    private function visibility_join($alias)
    {
        return $this->db->table_exists('wp_wlsm_ai_section_visibility_iattsl')
            ? 'LEFT JOIN wp_wlsm_ai_section_visibility_iattsl AS visibility ON visibility.section_id = ' . $alias . '.ID '
            : '';
    }

    private function visibility_filter()
    {
        return $this->db->table_exists('wp_wlsm_ai_section_visibility_iattsl')
            ? 'AND (visibility.is_hidden IS NULL OR visibility.is_hidden = 0) ' : '';
    }

    // Newly created activities get a hidden row until the allocated teacher activates them. No row means visible.
    private function activity_visibility_join()
    {
        return $this->db->table_exists('wp_wlsm_ai_activity_visibility_iattsl')
            ? 'LEFT JOIN wp_wlsm_ai_activity_visibility_iattsl AS activity_visibility ON activity_visibility.activity_id = activities.ID '
            : '';
    }

    private function activity_visibility_filter()
    {
        return $this->db->table_exists('wp_wlsm_ai_activity_visibility_iattsl')
            ? 'AND (activity_visibility.is_hidden IS NULL OR activity_visibility.is_hidden = 0) ' : '';
    }

    private function query_published_activities($registration, $activity_id)
    {
        if (!$this->db->table_exists('wp_wlsm_ai_activities_iattsl') ||
            !$this->db->table_exists('wp_wlsm_ai_sections_iattsl')) {
            return NULL;
        }
        if ((int) $registration->class_id < 1 || (int) $registration->subject_id < 1) {
            return array();
        }

        $bindings = array('published', (int) $registration->class_id, (int) $registration->subject_id);
        $filter = '';
        if ($activity_id !== NULL) {
            if ($activity_id < 1) {
                return array();
            }
            $filter = 'AND activities.ID = ? ';
            $bindings[] = $activity_id;
        }

        return $this->db->query(
            'SELECT activities.ID, activities.title, activities.activity_type, activities.difficulty, ' .
                'activities.estimated_minutes, activities.student_content, activities.updated_at, activities.section_id, sections.name AS section_name ' .
            'FROM wp_wlsm_ai_activities_iattsl AS activities ' .
            'INNER JOIN wp_wlsm_ai_sections_iattsl AS sections ON sections.ID = activities.section_id ' .
                'AND sections.teacher_id = activities.teacher_id AND sections.class_id = activities.class_id ' .
                'AND sections.subject_id = activities.subject_id ' .
            $this->visibility_join('sections') .
            $this->activity_visibility_join() .
            'WHERE activities.status = ? AND activities.class_id = ? AND activities.subject_id = ? ' .
            $this->visibility_filter() .
            $this->activity_visibility_filter() .
            $filter .
            'ORDER BY sections.name ASC, activities.published_at DESC, activities.ID DESC',
            $bindings
        )->result();
    }

    // Server-only source for AI practice. Access is verified with the same rule as the player; nothing here reaches a view.
    public function get_practice_source($registration, $activity_id)
    {
        $rows = $this->query_published_activities($registration, (int) $activity_id);
        if (empty($rows)) {
            return NULL;
        }
        $row = $this->db->select('teacher_id, class_id, subject_id, section_id, title, activity_type, difficulty, student_content, evaluation_data')
            ->where('ID', (int) $activity_id)->where('status', 'published')
            ->get('wp_wlsm_ai_activities_iattsl')->row();
        if (!$row) {
            return NULL;
        }
        $content = json_decode((string) $row->student_content, TRUE);
        $evaluation = json_decode((string) $row->evaluation_data, TRUE);
        if (!is_array($content) || !is_array($evaluation)) {
            return NULL;
        }
        $material_ids = array();
        if ($this->db->table_exists('wp_wlsm_ai_activity_sources_iattsl')) {
            foreach ($this->db->select('source_id')->where('activity_id', (int) $activity_id)->where('source_type', 'material')
                ->get('wp_wlsm_ai_activity_sources_iattsl')->result() as $source) {
                $material_ids[] = (int) $source->source_id;
            }
        }
        return array(
            'teacher_id' => (int) $row->teacher_id,
            'class_id' => (int) $row->class_id,
            'subject_id' => (int) $row->subject_id,
            'section_id' => (int) $row->section_id,
            'section_name' => (string) $rows[0]->section_name,
            'title' => (string) $row->title,
            'activity_type' => (string) $row->activity_type,
            'difficulty' => (string) $row->difficulty,
            'learning_objective' => isset($content['learning_objective']) && is_string($content['learning_objective']) ? $content['learning_objective'] : '',
            'content' => $content,
            'evaluation' => $evaluation,
            'material_ids' => $material_ids
        );
    }

    public function attempts_ready()
    {
        return $this->db->table_exists('wp_wlsm_ai_activity_attempts_iattsl') &&
            $this->db->table_exists('wp_wlsm_ai_activity_responses_iattsl');
    }

    public function get_activity_attempts($student_record_id, $activity_id)
    {
        return $this->db->select('ID, attempt_number, score, max_score, percentage, submitted_at')
            ->where('student_record_id', (int) $student_record_id)->where('activity_id', (int) $activity_id)
            ->order_by('attempt_number', 'DESC')->limit(20)->get('wp_wlsm_ai_activity_attempts_iattsl')->result();
    }

    // Returns an attempt only when it belongs to this user, registration record and activity.
    public function get_attempt($attempt_id, $user_id, $student_record_id, $activity_id)
    {
        $attempt = $this->db->where('ID', (int) $attempt_id)->where('user_id', (int) $user_id)
            ->where('student_record_id', (int) $student_record_id)->where('activity_id', (int) $activity_id)
            ->get('wp_wlsm_ai_activity_attempts_iattsl')->row();
        if (!$attempt) {
            return NULL;
        }
        $attempt->responses = $this->db->select('item_id, item_label, response_text, is_correct')
            ->where('attempt_id', (int) $attempt->ID)->order_by('item_id', 'ASC')
            ->get('wp_wlsm_ai_activity_responses_iattsl')->result();
        return $attempt;
    }

    public function find_attempt_by_hash($hash, $user_id, $student_record_id, $activity_id)
    {
        $row = $this->db->select('ID')->where('submission_hash', (string) $hash)->where('user_id', (int) $user_id)
            ->where('student_record_id', (int) $student_record_id)->where('activity_id', (int) $activity_id)
            ->get('wp_wlsm_ai_activity_attempts_iattsl')->row();
        return $row ? (int) $row->ID : 0;
    }

    // Per-activity progress derived from saved attempts for one authorized registration record.
    // Returns NULL when the attempt tables are not installed, otherwise activity_id => summary.
    public function get_registration_attempt_summary($student_record_id, $user_id = NULL)
    {
        if (!$this->attempts_ready()) {
            return NULL;
        }
        // $user_id NULL is used only by staff views, where the authorized registration record ID is the scope.
        $by_user = $user_id === NULL ? '' : ' AND user_id = ' . (int) $user_id;
        $by_user_t = $user_id === NULL ? '' : ' AND t.user_id = ' . (int) $user_id;
        $summary = array();
        $totals = $this->db->query(
            'SELECT activity_id, COUNT(*) AS attempts, MAX(percentage) AS best_percentage ' .
            'FROM wp_wlsm_ai_activity_attempts_iattsl WHERE student_record_id = ?' . $by_user . ' GROUP BY activity_id',
            array((int) $student_record_id)
        )->result();
        foreach ($totals as $row) {
            $summary[(int) $row->activity_id] = array(
                'attempts' => (int) $row->attempts,
                'best_percentage' => (float) $row->best_percentage
            );
        }
        $latest = $this->db->query(
            'SELECT t.activity_id, t.ID, t.score, t.max_score, t.percentage, t.submitted_at ' .
            'FROM wp_wlsm_ai_activity_attempts_iattsl AS t ' .
            'INNER JOIN (SELECT activity_id, MAX(attempt_number) AS last_number FROM wp_wlsm_ai_activity_attempts_iattsl ' .
                'WHERE student_record_id = ?' . $by_user . ' GROUP BY activity_id) AS m ' .
                'ON m.activity_id = t.activity_id AND m.last_number = t.attempt_number ' .
            'WHERE t.student_record_id = ?' . $by_user_t,
            array((int) $student_record_id, (int) $student_record_id)
        )->result();
        foreach ($latest as $row) {
            $id = (int) $row->activity_id;
            if (!isset($summary[$id])) {
                continue;
            }
            $summary[$id]['latest_attempt_id'] = (int) $row->ID;
            $summary[$id]['latest_score'] = (int) $row->score;
            $summary[$id]['latest_max_score'] = (int) $row->max_score;
            $summary[$id]['latest_percentage'] = (float) $row->percentage;
            $summary[$id]['latest_at'] = $row->submitted_at;
        }
        return $summary;
    }

    // Appends a new numbered attempt with its responses in one transaction. Returns the attempt ID or 0.
    public function save_attempt($attempt, $responses)
    {
        $previous_debug = $this->db->db_debug;
        $this->db->db_debug = FALSE;
        $attempt_id = 0;
        for ($try = 0; $try < 3 && $attempt_id === 0; $try++) {
            $this->db->trans_begin();
            $row = $this->db->query(
                'SELECT MAX(attempt_number) AS last_number FROM wp_wlsm_ai_activity_attempts_iattsl WHERE activity_id = ? AND student_record_id = ?',
                array((int) $attempt['activity_id'], (int) $attempt['student_record_id'])
            )->row();
            $attempt['attempt_number'] = ($row && $row->last_number !== NULL ? (int) $row->last_number : 0) + 1;
            $inserted = $this->db->insert('wp_wlsm_ai_activity_attempts_iattsl', $attempt);
            $new_id = $inserted ? (int) $this->db->insert_id() : 0;
            $saved = $new_id > 0;
            if ($saved) {
                foreach ($responses as $response) {
                    $response['attempt_id'] = $new_id;
                    if (!$this->db->insert('wp_wlsm_ai_activity_responses_iattsl', $response)) {
                        $saved = FALSE;
                        break;
                    }
                }
            }
            if ($saved && $this->db->trans_status() !== FALSE && $this->db->trans_commit() !== FALSE) {
                $attempt_id = $new_id;
            } else {
                $this->db->trans_rollback();
            }
        }
        $this->db->db_debug = $previous_debug;
        return $attempt_id;
    }

    public function practice_results_ready()
    {
        return $this->db->table_exists('wp_wlsm_ai_practice_results_iattsl');
    }

    // Stores only an aggregate summary of a scored AI practice session. Returns 'saved', 'duplicate' or 'failed'.
    public function save_practice_result($row)
    {
        $previous_debug = $this->db->db_debug;
        $this->db->db_debug = FALSE;
        $exists = $this->db->select('ID')->where('practice_ref', (string) $row['practice_ref'])
            ->get('wp_wlsm_ai_practice_results_iattsl');
        if ($exists && $exists->row()) {
            $this->db->db_debug = $previous_debug;
            return 'duplicate';
        }
        $ok = $this->db->insert('wp_wlsm_ai_practice_results_iattsl', $row);
        $this->db->db_debug = $previous_debug;
        if ($ok) {
            return 'saved';
        }
        // A concurrent submission can win the unique key between the check and the insert.
        $again = $this->db->select('ID')->where('practice_ref', (string) $row['practice_ref'])->get('wp_wlsm_ai_practice_results_iattsl');
        return $again && $again->row() ? 'duplicate' : 'failed';
    }

    // Aggregate AI practice rows for one authorized registration record, oldest first. NULL when the table is not installed.
    public function get_registration_practice_results($student_record_id, $user_id = NULL)
    {
        if (!$this->practice_results_ready()) {
            return NULL;
        }
        if ($user_id !== NULL) {
            $this->db->where('user_id', (int) $user_id);
        }
        return $this->db->select('ID, activity_id, section_id, activity_type, difficulty, questions_total, questions_correct, percentage, completed_at')
            ->where('student_record_id', (int) $student_record_id)
            ->order_by('completed_at', 'ASC')->order_by('ID', 'ASC')->limit(500)
            ->get('wp_wlsm_ai_practice_results_iattsl')->result();
    }
    private function registration_rows($registration_ids)
    {
        return $this->db
            ->select(
                'students.ID AS student_record_id, students.admission_number, students.name AS student_name, students.section_id AS subject_id, ' .
                'students.session_id, students.is_active, students.user_id AS existing_user_id, subjects.label AS subject_name, ' .
                'classes.ID AS class_id, classes.label AS grade_name, sessions.label AS academic_year'
            )
            ->from($this->registration_table . ' AS students')
            ->join('wp_wlsm_sections AS subjects', 'subjects.ID = students.section_id', 'left')
            ->join('wp_wlsm_class_school AS class_school', 'class_school.ID = subjects.class_school_id', 'left')
            ->join('wp_wlsm_classes AS classes', 'classes.ID = class_school.class_id', 'left')
            ->join('wp_wlsm_sessions AS sessions', 'sessions.ID = students.session_id', 'left')
            ->where_in('students.ID', $registration_ids)
            ->order_by('students.session_id', 'DESC')
            ->order_by('classes.label', 'ASC')
            ->order_by('subjects.label', 'ASC')
            ->get()
            ->result();
    }

    private function registration_mapping_is_unique($user_id, $registration_ids)
    {
        $links = $this->db
            ->select('user_id, meta_value')
            ->where('meta_key', $this->registration_meta_key)
            ->get('wp_usermeta')
            ->result();
        $owned = array();
        foreach ($links as $link) {
            $ids = $this->decode_registration_ids($link->meta_value);
            if ($ids === FALSE) {
                return FALSE;
            }
            foreach ($ids as $id) {
                if (!in_array($id, $registration_ids, TRUE)) {
                    continue;
                }
                if (isset($owned[$id]) && $owned[$id] !== (int) $link->user_id) {
                    return FALSE;
                }
                $owned[$id] = (int) $link->user_id;
            }
        }

        foreach ($registration_ids as $id) {
            if (!isset($owned[$id]) || $owned[$id] !== (int) $user_id) {
                return FALSE;
            }
        }

        return TRUE;
    }

    private function get_unique_meta_value($user_id, $meta_key)
    {
        $rows = $this->db
            ->select('meta_value')
            ->where('user_id', (int) $user_id)
            ->where('meta_key', $meta_key)
            ->get('wp_usermeta')
            ->result();

        return count($rows) === 1 ? $rows[0]->meta_value : FALSE;
    }

    private function set_unique_meta_value($user_id, $meta_key, $value)
    {
        $count = $this->db
            ->where('user_id', (int) $user_id)
            ->where('meta_key', $meta_key)
            ->count_all_results('wp_usermeta');
        if ($count !== 1) {
            return FALSE;
        }

        return $this->db
            ->where('user_id', (int) $user_id)
            ->where('meta_key', $meta_key)
            ->update('wp_usermeta', array('meta_value' => $value)) !== FALSE;
    }

    private function lock_auth_version($user_id, $expected_version)
    {
        $lock_suffix = $this->db->dbdriver === 'mysqli' ? ' FOR UPDATE' : '';
        $row = $this->db->query(
            'SELECT meta_value FROM wp_usermeta WHERE user_id = ? AND meta_key = ?' . $lock_suffix,
            array((int) $user_id, $this->auth_version_meta_key)
        )->row();

        return $row && ctype_digit((string) $row->meta_value) &&
            (int) $row->meta_value === (int) $expected_version;
    }

    private function decode_registration_ids($json)
    {
        if (!is_string($json)) {
            return FALSE;
        }
        $ids = json_decode($json, TRUE);
        return $this->normalize_registration_ids($ids);
    }

    private function normalize_registration_ids($ids)
    {
        if (!is_array($ids) || empty($ids) || count($ids) > 100) {
            return FALSE;
        }

        $normalized = array();
        foreach ($ids as $id) {
            if (!is_int($id) && !(is_string($id) && ctype_digit($id))) {
                return FALSE;
            }
            $id = (int) $id;
            if ($id < 1 || isset($normalized[$id])) {
                return FALSE;
            }
            $normalized[$id] = $id;
        }

        return array_values($normalized);
    }
}
