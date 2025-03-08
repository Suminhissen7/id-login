<?php

class DB {
    private $pdo;

    public function __construct() {
        require 'settings.php';

        try {
            $this->pdo = new PDO("mysql:host=$host;dbname=$db_name", $user, $pass);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }

    /**
     * Memeriksa apakah user_id ada di tabel players.
     *
     * @param string $user_id
     * @return bool
     */
    public function containsUserId($user_id) {
        $query = "SELECT COUNT(*) FROM players WHERE user_id = :user_id";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Menyimpan atau mengupdate session_key berdasarkan user_id.
     *
     * @param string $user_id
     * @param string $session_key
     * @return void
     */
    public function saveUserId($user_id, $session_key) {
        if ($this->containsUserId($user_id)) {
            // Update session_key jika user_id sudah ada
            $query = "UPDATE players SET session_key = :session_key, updated_at = NOW() WHERE user_id = :user_id";
        } else {
            // Insert data baru jika user_id belum ada
            $query = "INSERT INTO players (user_id, session_key, updated_at) VALUES (:user_id, :session_key, NOW())";
        }

        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_STR);
        $stmt->bindParam(':session_key', $session_key, PDO::PARAM_STR);
        $stmt->execute();
    }

    /**
     * Mendapatkan session_key berdasarkan user_id.
     *
     * @param string $user_id
     * @return string|null
     */
    public function getSessionKey($user_id) {
        $query = "SELECT session_key FROM players WHERE user_id = :user_id";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['session_key'] : null;
    }
}
