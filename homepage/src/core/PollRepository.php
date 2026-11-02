<?php
/**
 * Interface for loading, adding & changing polls.
 * 
 * Handles polls from the database over option representation to backend color
 * computation.
 */
class PollRepository {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    private function get_user_or_session_id() {
        $user_uuid = App::auth()->user()?->uuid();
        $session_id = is_null($user_uuid) ? App::auth()->session()->id() : null;
        return ['user_uuid' => $user_uuid, 'session_id' => $session_id];
    }

    private function compute_colors($option) { // TODO
        $base = $option['color'];
        return [
            'text_color' => color_str_from_bin(
                $option['text_color'] ?? text_color($base),
            ),
            'color' => color_str_from_bin($base),
            'hover_color' => color_str_from_bin(
                $option['hover_color'] ?? adjust_color($base, -5),
            ),
            'outline_color' => color_str_from_bin(
                $option['outline_color'] ?? adjust_color($base, -15),
            ),
        ];
    }

    public function options($poll_uuid_str) {
        $poll_uuid = UUID::to_bytes($poll_uuid_str);
        $sql = 'SELECT
                    o.option_id AS option_id,
                    COUNT(v.option_id) AS vote_count,
                    EXISTS (
                        SELECT 1 FROM poll_votes u
                        WHERE (
                            (:user_uuid IS NOT NULL AND u.user_uuid = :user_uuid)
                            OR
                            (:session_id IS NOT NULL AND u.session_id = :session_id)
                          )
                          AND u.session_id = :session_id
                          AND u.poll_uuid = o.poll_uuid
                          AND u.option_id = o.option_id
                    ) AS selected,
                    c.text_color as text_color,
                    c.color AS color,
                    c.hover_color AS hover_color,
                    c.outline_color AS outline_color
                FROM poll_options o
                JOIN poll_colors c
                    ON c.color_id = o.color_id
                LEFT JOIN poll_votes v
                    ON v.poll_uuid = o.poll_uuid
                   AND v.option_id = o.option_id
                WHERE o.poll_uuid = :poll_uuid
                GROUP BY
                    o.option_id,
                    o.poll_uuid,
                    c.text_color,
                    c.color,
                    c.hover_color,
                    c.outline_color
                ORDER BY o.option_id';
        $data = $this->get_user_or_session_id();
        $data['poll_uuid'] = $poll_uuid;
        $result = $this->db->all($sql, $data);
        return array_values(array_map(fn($option) => new PollOption(
            $option['option_id'],
            'option ' . $option['option_id'], // TODO
            $option['vote_count'],
            (bool) $option['selected'],
            $this->compute_colors($option),
        ), $result));
    }

    private function is_multiselect($poll_uuid) {
        $sql = 'SELECT multiselect FROM polls WHERE poll_uuid = :poll_uuid';
        return (bool) $this->db->value($sql, ['poll_uuid' => $poll_uuid]);
    }

    private function delete_selections_except_clicked($data) {
        $sql = 'DELETE FROM poll_votes
                WHERE (
                    (:user_uuid IS NOT NULL AND user_uuid = :user_uuid)
                    OR
                    (:session_id IS NOT NULL AND session_id = :session_id)
                  )
                  AND poll_uuid = :poll_uuid
                  AND option_id != :option_id';
        $this->db->execute($sql, $data);
    }

    public function update_selection($poll_uuid_str, $clicked_option) {
        if (!is_null($clicked_option)) {
            $poll_uuid = UUID::to_bytes($poll_uuid_str);
            $sql = 'DELETE FROM poll_votes
                    WHERE (
                        (:user_uuid IS NOT NULL AND user_uuid = :user_uuid)
                        OR
                        (:session_id IS NOT NULL AND session_id = :session_id)
                      )
                      AND poll_uuid = :poll_uuid
                      AND option_id = :option_id';
            $data = $this->get_user_or_session_id();
            $data['poll_uuid'] = $poll_uuid;
            $data['option_id'] = $clicked_option;
            if (!$this->is_multiselect($poll_uuid))
                $this->delete_selections_except_clicked($data);
            $deleted = $this->db->execute($sql, $data) == 1;
            if (!$deleted)
                $this->db->insert_row('poll_votes', $data);
        }
        return $this->options($poll_uuid_str);
    }

    private function is_public($poll_uuid) {
        $sql = 'SELECT public FROM polls WHERE poll_uuid = :poll_uuid';
        return (bool) $this->db->value($sql, ['poll_uuid' => $poll_uuid]);
    }

    private function view_permission($poll_uuid, $public = null) {
        if (App::authorization()->has_permission('polls_view_private'))
            return true;

        if (is_null($public))
            $public = $this->repository->is_public($poll_uuid);

        if (App::authorization()->has_permission('polls_view') && $public)
            return true;

        return false;
    }

    public function add_poll($collection_uuid_str, $options) {
        if (!App::authorization()->has_permission('polls_add')) return;
        $poll_uuid = UUID::v4();
        $row = [
            'poll_uuid' => $poll_uuid_str,
            'multiselect' => (int) ($options['multiselect'] ?? false),
            'public' => (int) ($options['public'] ?? false),
        ];
        $this->db->insert_row('polls', $row);
        $sql = 'INSERT INTO collection_polls (collection_uuid, poll_uuid, pos)
                VALUES (:collection_uuid, :poll_uuid, (
                    SELECT COALESCE(MAX(pos), -1) + 1
                    FROM collection_polls
                    WHERE collection_uuid = :collection_uuid
                ))';
        $this->db->execute($sql, [
            'collection_uuid' => UUID::to_bytes($collection_uuid_str),
            'poll_uuid' => $row['poll_uuid'],
        ]);
        foreach ($options['options'] ?? [] as $option)
            $this->add_option($poll_uuid, $option['id'], $option['color_id']);
    }

    private function add_option($poll_uuid, $option_id, $color_id) {
        if (!App::authorization()->has_permission('polls_add')) return;
        $row = [
            'poll_uuid' => $poll_uuid,
            'option_id' => $option_id,
            'color_id' => $color_id,
        ];
        $this->db->insert_row('poll_options', $row);
    }

    public function toggle_poll_visibility($poll_uuid_str) {
        if (!App::authorization()->has_permission('polls_modify_visibility')) return;
        $poll_uuid = UUID::to_bytes($poll_uuid_str);
        $sql = 'UPDATE polls
                SET public = NOT public
                WHERE poll_uuid = :poll_uuid';
        $this->db->execute($sql, ['poll_uuid' => $poll_uuid]);
    }

    public function delete_poll($poll_uuid_str) {
        if (!App::authorization()->has_permission('polls_delete')) return;
        $poll_uuid = UUID::to_bytes($poll_uuid_str);
        $sql = 'DELETE FROM collection_polls WHERE poll_uuid = :poll_uuid;
                DELETE FROM poll_votes WHERE poll_uuid = :poll_uuid;
                DELETE FROM poll_options WHERE poll_uuid = :poll_uuid;
                DELETE FROM polls WHERE poll_uuid = :poll_uuid';
        $this->db->execute($sql, ['poll_uuid' => $poll_uuid]);
    }

    public function delete_option($poll_uuid_str, $option_id) {
        if (!App::authorization()->has_permission('polls_delete')) return;
        $poll_uuid = UUID::to_bytes($poll_uuid_str);
        $sql = 'DELETE FROM poll_votes
                    WHERE poll_uuid = :poll_uuid AND option_id = :option_id;
                DELETE FROM poll_options
                    WHERE poll_uuid = :poll_uuid AND option_id = :option_id';
        $this->db->execute($sql, [
            'poll_uuid' => $poll_uuid,
            'option_id' => $option_id,
        ]);
    }

    public function collection_polls($collection_uuid_str) {
        $collection_uuid = UUID::to_bytes($collection_uuid_str);
        $sql = 'SELECT * FROM collection_polls cp, polls p
                WHERE cp.collection_uuid = :collection_uuid
                  AND cp.poll_uuid = p.poll_uuid
                ORDER BY pos';
        $collection = $this->db->all($sql, ['collection_uuid' => $collection_uuid]);
        
        $result = [];
        foreach ($collection as $e) {
            if (!$this->view_permission($e['poll_uuid'], $e['public']))
                continue;
            $result[] = new Poll($e);
        }
        return $result;
    }
}

class Poll {
    public $poll_uuid_string;
    public $multiselect;
    public $public;

    public function __construct($array) {
        $this->poll_uuid_string = UUID::to_string($array['poll_uuid']);
        $this->multiselect = (bool) $array['multiselect'];
        $this->public = (bool) $array['public'];
    }
}

class PollOption {
    public $id;
    public $label;
    public $vote_count;
    public $selected;

    public $text_color;
    public $color;
    public $hover_color;
    public $outline_color;
    
    public function __construct($id, $label, $vote_count, $selected, $colors) {
        $this->id = $id;
        $this->label = $label;
        $this->vote_count = $vote_count;
        $this->selected = $selected;

        $this->text_color = $colors['text_color'] ?? 'inherit';
        $this->color = $colors['color'];
        $this->hover_color = $colors['hover_color'] ?? 'yellow'; // TODO: compute colors
        $this->outline_color = $colors['outline_color'] ?? 'white';
    }
}
?>
