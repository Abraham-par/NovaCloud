<?php
require_once 'config.php';

class Database {
    private static $instance = null;
    private $connection;
    
    private function __construct() {
        try {
            $this->connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
            
            if ($this->connection->connect_error) {
                throw new Exception("Connection failed: " . $this->connection->connect_error);
            }
            
            $this->connection->set_charset("utf8mb4");
            
        } catch (Exception $e) {
            error_log($e->getMessage());
            // Don't redirect to error page here to avoid infinite loop
            die("Database connection failed. Please check your configuration.");
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->connection;
    }
    
    public function escape($data) {
        if (is_array($data)) {
            return array_map([$this, 'escape'], $data);
        }
        return $this->connection->real_escape_string(htmlspecialchars(trim($data)));
    }
    
    public function query($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
        } catch (mysqli_sql_exception $e) {
            error_log("Query preparation exception: " . $e->getMessage());
            return false;
        }
        
        if (!$stmt) {
            error_log("Query preparation failed: " . $this->connection->error);
            return false;
        }
        
        if (!empty($params)) {
            $types = '';
            $values = [];
            
            foreach ($params as $param) {
                if (is_int($param)) {
                    $types .= 'i';
                } elseif (is_double($param)) {
                    $types .= 'd';
                } else {
                    $types .= 's';
                }
                $values[] = $param;
            }
            
            $stmt->bind_param($types, ...$values);
        }
        
        try {
            $stmt->execute();
        } catch (mysqli_sql_exception $e) {
            error_log("Query execution failed: " . $e->getMessage());
            $stmt->close();
            return false;
        }

        // If the statement produced a result set (SELECT), return it.
        // For non-SELECT statements (INSERT/UPDATE/DELETE) return true so callers can use insert_id or affected_rows.
        $result = null;
        if ($stmt->field_count > 0) {
            $result = $stmt->get_result();
        } else {
            $result = true;
        }

        $stmt->close();

        return $result;
    }
    
    public function insert($table, $data) {
        $keys = array_keys($data);
        $values = array_values($data);
        
        $placeholders = str_repeat('?,', count($values) - 1) . '?';
        $sql = "INSERT INTO {$table} (" . implode(',', $keys) . ") VALUES ({$placeholders})";
        
        $result = $this->query($sql, $values);
        return $result ? $this->connection->insert_id : false;
    }
    
    public function update($table, $data, $where, $whereParams = []) {
        $setParts = [];
        $values = [];
        
        foreach ($data as $key => $value) {
            $setParts[] = "{$key} = ?";
            $values[] = $value;
        }
        
        $values = array_merge($values, $whereParams);
        $sql = "UPDATE {$table} SET " . implode(', ', $setParts) . " WHERE {$where}";
        
        return $this->query($sql, $values);
    }
    
    public function delete($table, $where, $params = []) {
        $sql = "DELETE FROM {$table} WHERE {$where}";
        return $this->query($sql, $params);
    }
    
    public function select($table, $columns = '*', $where = '', $params = [], $order = '', $limit = '') {
        $sql = "SELECT {$columns} FROM {$table}";
        
        if ($where) {
            $sql .= " WHERE {$where}";
        }
        
        if ($order) {
            $sql .= " ORDER BY {$order}";
        }
        
        if ($limit) {
            $sql .= " LIMIT {$limit}";
        }
        
        $result = $this->query($sql, $params);
        
        if (!$result) {
            return false;
        }
        
        $rows = [];
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
        
        return $rows;
    }
    
    public function getRow($table, $columns = '*', $where = '', $params = []) {
        $rows = $this->select($table, $columns, $where, $params, '', 1);
        return $rows ? $rows[0] : false;
    }
    
    public function count($table, $where = '', $params = []) {
        $sql = "SELECT COUNT(*) as count FROM {$table}";
        
        if ($where) {
            $sql .= " WHERE {$where}";
        }
        
        $result = $this->query($sql, $params);
        
        if ($result && $row = $result->fetch_assoc()) {
            return (int)$row['count'];
        }
        
        return 0;
    }
    
    public function beginTransaction() {
        return $this->connection->begin_transaction();
    }
    
    public function commit() {
        return $this->connection->commit();
    }
    
    public function rollback() {
        return $this->connection->rollback();
    }
    
    public function getLastInsertId() {
        return $this->connection->insert_id;
    }
    
    public function getAffectedRows() {
        return $this->connection->affected_rows;
    }
    
    public function __destruct() {
        if ($this->connection) {
            $this->connection->close();
        }
    }
}