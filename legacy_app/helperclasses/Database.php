<?php

/* The Database Class
 * An instance of this class should be globally accessible
 */

class Database {
	private $hostname;
	private $username;
	private $password;
	private $database;
	private $connection;

	function __construct($hostname, $username, $password, $database) {
			$this->hostname = $hostname;
			$this->username = $username;
			$this->password = $password;
			$this->database = $database;
    }

	public function connect() {
		$this->connection = mysqli_connect($hostname, $username, $password, $database);

		if ($this->connection->connect_error) {
    	die("Connection failed: " . $this->connection->connect_error);
		}
	}

	public function getConnection() {
		return $this->connection;
	}

	public function disconnect() {
		mysqli_close($this->connection);
	}
}
