<?php 
require_once __DIR__ . '/../database.php';

class LoginDB{
    private $db;

    // connect to the database via contructor
    public function __construct(){
        $database = new Database();
        $this->db = $database->connect();
    }

    // register a new user
    public function registerUser($email, $password_hash, $username){
        try{
            $query = 'INSERT INTO users (email, password_hash, username) VALUES (:email, :password_hash, :username)';
            $statement = $this->db->prepare($query);
            $statement->bindValue(':email', $email);
            $statement->bindValue(':password_hash', $password_hash);
            $statement->bindValue(':username', $username);
            $statement->execute();
        }
        catch (PDOException $e) {
            throw new Exception("Error registering user: " . $e->getMessage());
        }
    }

    // check if a user exists in the database, if they do return the password hash
    public function returnPasswordHash($email){
        try{         
            $query = 'SELECT password_hash FROM users WHERE email = :email';
            $statement = $this->db->prepare($query);
            $statement->bindValue(':email', $email);
            $statement->execute();
            $result = $statement->fetchAll();
            return $result; 
        } catch (PDOException $e) {
            throw new Exception("Incorrect email/password: " . $e->getMessage());
        }
    }

    // check if the user is logged in
    public function isLoggedIn(){
        return isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true;
    }

    // get the user id from the database
    public function getUserID($email){
        try{
            $query = 'SELECT id FROM users WHERE email = :email';
            $statement = $this->db->prepare($query);
            $statement->bindValue(':email', $email);
            $statement->execute();
            $result = $statement->fetch(PDO::FETCH_ASSOC);
            return $result;
        } catch (PDOException $e) {
            throw new Exception("Error getting user ID: " . $e->getMessage());
        }
    }

    // get the username from the database
    public function getUsername($email){
        try{
            $query = 'SELECT username FROM users WHERE email = :email';
            $statement = $this->db->prepare($query);
            $statement->bindValue(':email', $email);
            $statement->execute();
            $result = $statement->fetch(PDO::FETCH_ASSOC);
            return $result;
        }
        catch (PDOException $e) {
            throw new Exception("Error getting username: " . $e->getMessage());
        }
    }

}