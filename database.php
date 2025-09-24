<?php
class Database {

    public function connect() {
        $dbHost = 'localhost';
        $dbName = 'your_db_name';
        $username = 'your_db_user';
        $password = 'your_db_password';

        $dsn = "mysql:host=$dbHost;dbname=$dbName";

        try {
            $db = new PDO($dsn, $username, $password);
            return $db;
        } catch (PDOException $e) {
            $this->display_error($e->getMessage());
        }
    }

    public function display_error($error_message) {
        echo "<main>";
        echo "<h1>Database Error</h1>";
        echo "<p>An error occurred while attempting to work with the database.</p>";
        echo "<p>Message: $error_message</p>";
        echo "</main>";
        exit();
    }
}
