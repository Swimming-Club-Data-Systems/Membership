<?php

/* The Database Class
 * An instance of this class should be globally accessible
 */

class Database {
	private $connection;

	function __construct(private $hostname, private $username, private $password, private $database)
 {
 }

	public function connect(): void {
		$this->connection = mysqli_connect($hostname, $username, $password, $database);

		if ($this->connection->connect_error) {
    	die("Connection failed: " . $this->connection->connect_error);
		}
	}

	public function getConnection() {
		return $this->connection;
	}

	public function disconnect(): void {
		mysqli_close($this->connection);
	}
}
