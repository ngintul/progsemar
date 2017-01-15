<?php

class Core extends CI_Model
{
    function __construct()
    {
        parent::__construct();
    }

    function exists($table, $param)
    {
        $t = $this->table;
        $s_t = $t[$table];
        $this->db->from($s_t);
        $this->db->where($param);
        $this->db->where($s_t . '.deleted', 0);
        $query = $this->db->get();

        return ($query->num_rows() == 1);
    }

    function existsKey($table, $key)
    {
        $this->db->from($table);
        $this->db->where($table . ".key", $key);
        $query = $this->db->get();

        return ($query->num_rows() == 1);
    }

    function create($table, $data)
    {
        $t = $this->table;
        $s_t = $t[$table];
        $this->db->trans_begin();
        $this->db->trans_strict(FALSE);
        $new_id = FALSE;
        if ($this->db->insert($s_t, $data)) {
            $new_id = $this->db->insert_id();
        }
        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
        } else {
            $this->db->trans_commit();
        }

        return $new_id;
    }

    function update($table, $data, $param)
    {
        $t = $this->table;
        $s_t = $t[$table];
        $this->db->trans_begin();
        $this->db->trans_strict(FALSE);
        $result = FALSE;
        if ($this->exists($table, $param)) {
            $this->db->where($param);

            $result = $this->db->update($s_t, $data);
        }
        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
        } else {
            $this->db->trans_commit();
        }

        return $result;
    }

    function updateBatchConfig($table, $data)
    {
        $t = $this->table;
        $s_t = $t[$table];
        $result = TRUE;
        $this->db->trans_start();
        $this->db->trans_strict(FALSE);
        foreach ($data as $key => $value) {
            $dataArray = array(
                'key' => $key,
                'value' => $value
            );
            $this->db->where('key', $key);
            if (!$this->db->update($s_t, $dataArray)) {
                $result = FALSE;
                $this->db->trans_rollback();
                break;
            } else {
                $this->db->trans_commit();
            }
        }

        return $result;
    }

    function readDetail($table, $selected_columns = FALSE, $param)
    {
        $t = $this->table;
        $s_t = $t[$table];
        if ($selected_columns) $this->db->select($selected_columns);
        $this->db->from($s_t);
        $this->db->where($param);
        $query = $this->db->get();

        if ($query->num_rows() == 1) {
            return $query->row();
        } else {
            $person_obj = new stdClass();
            $fields = $this->db->list_fields($s_t);

            foreach ($fields as $field) {
                $person_obj->$field = NULL;
            }

            return $person_obj;
        }
    }

    function readList($table, $drop_down = FALSE, $key = NULL, $value = NULL, $param = FALSE, $order = FALSE, $limit = FALSE)
    {
        $t = $this->table;
        $s_t = $t[$table];
        $this->db->from($s_t);
        if ($param) $this->db->where($param);
        if ($order) $this->db->order_by($order);
        if ($limit) $this->db->limit($limit);
        if ($drop_down) {
            $result = $this->db->get();
            $list = array();
            foreach ($result->result_array() as $item) {
                $list[$item[$key]] = is_array($value) ? '(' . $item[$value[0]] . ') ' . $item[$value[1]] . '' : $item[$value];
            }

            return $list;
        } else {

            return $this->db->get()->result();
        }
    }

    function readSettings()
    {
        $this->db->from('settings');
        $this->db->order_by("key", "asc");
        return $this->db->get();
    }

    function delete($table, $param, $permanent = FALSE)
    {
        $t = $this->table;
        $s_t = $t[$table];
        $this->db->where($param);
        if ($permanent) {
            return $this->db->delete($s_t);
        } else {
            return $this->db->update($s_t, array('deleted' => 1));
        }
    }

    function deleteList($table, $param_column, $param_values, $permanent = FALSE)
    {
        $t = $this->table;
        $s_t = $t[$table];
        $this->db->where_in($s_t . '.' . $param_column, $param_values);
        if ($permanent) {
            return $this->db->delete($s_t);
        } else {
            return $this->db->update($s_t, array('deleted' => 1));
        }
    }
}