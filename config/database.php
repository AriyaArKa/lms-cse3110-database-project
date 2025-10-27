<?php
/**
 * Database Configuration and Connection Class
 * Uses PDO for secure database operations
 */

class Database {
    private $host = "localhost";
    private $db_name = "university_db";
    private $username = "root";
    private $password = "";
    private $conn;

    /**
     * Get database connection
     * @return PDO|null
     */
    public function getConnection() {
        $this->conn = null;

        try {
            // First, connect without database to check if it exists
            $temp_conn = new PDO(
                "mysql:host=" . $this->host,
                $this->username,
                $this->password
            );
            $temp_conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Check if database exists
            $stmt = $temp_conn->query("SHOW DATABASES LIKE '{$this->db_name}'");
            $db_exists = $stmt->rowCount() > 0;

            if (!$db_exists) {
                // Create database if it doesn't exist
                $temp_conn->exec("CREATE DATABASE {$this->db_name} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                
                // Run setup script
                $this->runSetupScript();
            }

            // Connect to the database
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4",
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        } catch(PDOException $e) {
            echo "Connection Error: " . $e->getMessage();
        }

        return $this->conn;
    }

    /**
     * Run setup script on first installation
     */
    private function runSetupScript() {
        $setup_file = __DIR__ . '/../sql/setup.sql';
        
        if (file_exists($setup_file)) {
            try {
                $sql = file_get_contents($setup_file);
                
                // Connect to newly created database
                $conn = new PDO(
                    "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4",
                    $this->username,
                    $this->password
                );
                $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                
                // Execute the SQL file
                $conn->exec($sql);
                
            } catch(PDOException $e) {
                echo "Setup Error: " . $e->getMessage();
            }
        }
    }

    /**
     * Close database connection
     */
    public function closeConnection() {
        $this->conn = null;
    }
}

// Create a global PDO connection for convenience
$database = new Database();
$pdo = $database->getConnection();
$conn = $pdo; // Alias for backward compatibility
