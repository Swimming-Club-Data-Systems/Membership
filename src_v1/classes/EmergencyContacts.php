<?php

class EmergencyContacts {
	private $contacts;

	public function __construct(private $dbconn) {
		$this->contacts = [];
	}

	public function byParent(int $id): void {
    $sql = $this->dbconn->prepare("SELECT ID, UserID, `Name`, ContactNumber, `Relation` FROM `emergencyContacts` WHERE `UserID` = ?");
    $sql->execute([
			$id
		]);
		while ($row = $sql->fetch(PDO::FETCH_ASSOC)) {
			$new = new EmergencyContact();
			$new->existing(
				$row['ID'],
				$row['UserID'],
				$row['Name'],
				$row['ContactNumber'],
				$row['Relation']
			);
			$this->contacts[] = $new;
		}
	}

	public function bySwimmer(int $id): void {
		$sql = $this->dbconn->prepare("SELECT ID, UserID, `Name`, ContactNumber FROM `members` LEFT JOIN `emergencyContacts` ON members.UserID = emergencyContacts.UserID WHERE `MemberID` = ?");
    $sql->execute([$id]);
    while ($row = $sql->fetch(PDO::FETCH_ASSOC)) {
			$new = new EmergencyContact();
			$new->existing(
				$row['ID'],
				$row['UserID'],
				$row['Name'],
				$row['ContactNumber']
			);
			$this->contacts[] = $new;
		}
	}

	public function getContacts() {
		return $this->contacts;
	}

	public function getContact($i) {
		return $this->contacts[$i];
	}

}
