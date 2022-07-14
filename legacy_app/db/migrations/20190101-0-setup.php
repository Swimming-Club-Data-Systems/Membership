<?php

$db->query(
  "CREATE TABLE IF NOT EXISTS `emergencyContacts` (
    `ID` int(11) NOT NULL AUTO_INCREMENT,
    `UserID` int(11) NOT NULL,
    `Name` mediumtext COLLATE utf8mb4_bin NOT NULL,
    `ContactNumber` mediumtext COLLATE utf8mb4_bin NOT NULL,
    PRIMARY KEY (`ID`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;"
);

$db->query(
  "CREATE TABLE IF NOT EXISTS `extras` (
    `ExtraID` int(11) NOT NULL AUTO_INCREMENT,
    `ExtraName` varchar(100) COLLATE utf8mb4_bin NOT NULL,
    `ExtraFee` decimal(6,2) NOT NULL,
    PRIMARY KEY (`ExtraID`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;"
);

$db->query(
  "CREATE TABLE IF NOT EXISTS `extrasRelations` (
    `RelationID` int(11) NOT NULL AUTO_INCREMENT,
    `ExtraID` int(11) NOT NULL,
    `MemberID` int(11) DEFAULT NULL,
    `UserID` int(11) DEFAULT NULL,
    PRIMARY KEY (`RelationID`),
    KEY `ExtraID` (`ExtraID`),
    KEY `MemberID` (`MemberID`),
    KEY `UserID` (`UserID`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;"
);

$db->query(
  "CREATE TABLE IF NOT EXISTS `familyIdentifiers` (
    `ID` int(11) NOT NULL AUTO_INCREMENT,
    `UID` mediumtext COLLATE utf8mb4_bin NOT NULL,
    `ACS` mediumtext COLLATE utf8mb4_bin NOT NULL,
    PRIMARY KEY (`ID`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;"
);

$db->query(
  "CREATE TABLE IF NOT EXISTS `familyMembers` (
    `ConnectionID` int(11) NOT NULL AUTO_INCREMENT,
    `FamilyID` int(11) NOT NULL,
    `MemberID` int(11) NOT NULL,
    PRIMARY KEY (`ConnectionID`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;"
);

$db->query(
  "CREATE TABLE IF NOT EXISTS `galaEntries` (
    `EntryID` int(11) NOT NULL AUTO_INCREMENT,
    `GalaID` int(11) NOT NULL,
    `MemberID` int(11) NOT NULL,
    `EntryProcessed` tinyint(1) NOT NULL,
    `TimesRequired` tinyint(1) DEFAULT NULL COMMENT 'If true, times required from coaches',
    `TimesProvided` tinyint(1) DEFAULT NULL COMMENT 'Set true if times provided for a non HyTek Gala',
    `FeeToPay` double DEFAULT NULL,
    `Charged` tinyint(1) NOT NULL DEFAULT 0,
    `50Free` tinyint(1) DEFAULT NULL,
    `100Free` tinyint(1) DEFAULT NULL,
    `200Free` tinyint(1) DEFAULT NULL,
    `400Free` tinyint(1) DEFAULT NULL,
    `800Free` tinyint(1) DEFAULT NULL,
    `1500Free` tinyint(1) DEFAULT NULL,
    `50Breast` tinyint(1) DEFAULT NULL,
    `100Breast` tinyint(1) DEFAULT NULL,
    `200Breast` tinyint(1) DEFAULT NULL,
    `50Fly` tinyint(1) DEFAULT NULL,
    `100Fly` tinyint(1) DEFAULT NULL,
    `200Fly` tinyint(1) DEFAULT NULL,
    `50Back` tinyint(1) DEFAULT NULL,
    `100Back` tinyint(1) DEFAULT NULL,
    `200Back` tinyint(1) DEFAULT NULL,
    `200IM` tinyint(1) DEFAULT NULL,
    `400IM` tinyint(1) DEFAULT NULL,
    `100IM` tinyint(1) DEFAULT NULL,
    `150IM` tinyint(1) DEFAULT NULL,
    `50FreeTime` text COLLATE utf8mb4_bin DEFAULT NULL,
    `100FreeTime` text COLLATE utf8mb4_bin DEFAULT NULL,
    `200FreeTime` text COLLATE utf8mb4_bin DEFAULT NULL,
    `400FreeTime` text COLLATE utf8mb4_bin DEFAULT NULL,
    `800FreeTime` text COLLATE utf8mb4_bin DEFAULT NULL,
    `1500FreeTime` text COLLATE utf8mb4_bin DEFAULT NULL,
    `50BreastTime` text COLLATE utf8mb4_bin DEFAULT NULL,
    `100BreastTime` text COLLATE utf8mb4_bin DEFAULT NULL,
    `200BreastTime` text COLLATE utf8mb4_bin DEFAULT NULL,
    `50FlyTime` text COLLATE utf8mb4_bin DEFAULT NULL,
    `100FlyTime` text COLLATE utf8mb4_bin DEFAULT NULL,
    `200FlyTime` text COLLATE utf8mb4_bin DEFAULT NULL,
    `50BackTime` text COLLATE utf8mb4_bin DEFAULT NULL,
    `100BackTime` text COLLATE utf8mb4_bin DEFAULT NULL,
    `200BackTime` text COLLATE utf8mb4_bin DEFAULT NULL,
    `200IMTime` text COLLATE utf8mb4_bin DEFAULT NULL,
    `400IMTime` text COLLATE utf8mb4_bin DEFAULT NULL,
    `100IMTime` text COLLATE utf8mb4_bin DEFAULT NULL,
    `150IMTime` text COLLATE utf8mb4_bin DEFAULT NULL,
    PRIMARY KEY (`EntryID`),
    KEY `GalaID` (`GalaID`),
    KEY `MemberID` (`MemberID`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;"
);

$db->query(
  "CREATE TABLE IF NOT EXISTS `galas` (
    `GalaID` int(11) NOT NULL AUTO_INCREMENT,
    `GalaName` varchar(255) COLLATE utf8mb4_bin DEFAULT NULL,
    `CourseLength` enum('SHORT','LONG','IRREGULAR') COLLATE utf8mb4_bin DEFAULT NULL,
    `GalaVenue` varchar(255) COLLATE utf8mb4_bin DEFAULT NULL,
    `GalaFee` decimal(8,2) DEFAULT NULL,
    `GalaFeeConstant` tinyint(1) NOT NULL DEFAULT 1,
    `ClosingDate` date NOT NULL COMMENT 'The closing date of the gala',
    `GalaDate` date NOT NULL COMMENT 'Last day of gala when the event will disappear from accounts',
    `HyTek` tinyint(1) NOT NULL,
    PRIMARY KEY (`GalaID`),
    UNIQUE KEY `GalaID` (`GalaID`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;"
);

$db->query(
  "CREATE TABLE IF NOT EXISTS `individualFeeTrack` (
    `ID` int(11) NOT NULL AUTO_INCREMENT,
    `MonthID` int(11) NOT NULL,
    `PaymentID` int(11) DEFAULT NULL,
    `MemberID` int(11) NOT NULL,
    `UserID` int(11) DEFAULT NULL,
    `Description` mediumtext COLLATE utf8mb4_bin NOT NULL,
    `Amount` int(11) NOT NULL,
    `Type` enum('SquadFee','ExtraFee') COLLATE utf8mb4_bin NOT NULL,
    `NC` tinyint(1) NOT NULL DEFAULT 0,
    PRIMARY KEY (`ID`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;"
);

$db->query(
  "CREATE TABLE IF NOT EXISTS `joinParents` (
    `Hash` char(40) COLLATE utf8mb4_bin NOT NULL,
    `First` varchar(30) COLLATE utf8mb4_bin NOT NULL,
    `Last` varchar(40) COLLATE utf8mb4_bin NOT NULL,
    `Email` varchar(70) COLLATE utf8mb4_bin NOT NULL,
    `Invited` tinyint(1) NOT NULL DEFAULT 0,
    UNIQUE KEY `Hash` (`Hash`),
    KEY `Hash_2` (`Hash`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;"
);

$db->query(
  "CREATE TABLE IF NOT EXISTS `joinSwimmers` (
    `ID` int(11) NOT NULL AUTO_INCREMENT,
    `Parent` char(40) COLLATE utf8mb4_bin NOT NULL,
    `First` varchar(30) COLLATE utf8mb4_bin NOT NULL,
    `Last` varchar(40) COLLATE utf8mb4_bin NOT NULL,
    `DoB` date NOT NULL,
    `XP` int(11) NOT NULL,
    `XPDetails` text COLLATE utf8mb4_bin DEFAULT NULL,
    `Medical` text COLLATE utf8mb4_bin DEFAULT NULL,
    `Questions` text COLLATE utf8mb4_bin DEFAULT NULL,
    `Club` varchar(30) COLLATE utf8mb4_bin DEFAULT NULL,
    `ASA` int(11) DEFAULT NULL,
    `TrialStart` datetime DEFAULT NULL,
    `TrialEnd` datetime DEFAULT NULL,
    `Comments` text COLLATE utf8mb4_bin DEFAULT NULL,
    `SquadSuggestion` int(11) DEFAULT NULL,
    `Sex` enum('Male','Female','Other') COLLATE utf8mb4_bin NOT NULL,
    PRIMARY KEY (`ID`),
    KEY `Parent` (`Parent`),
    KEY `SquadSuggestion` (`SquadSuggestion`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;"
);

$db->query(
  "CREATE TABLE IF NOT EXISTS `memberMedical` (
    `ID` int(11) NOT NULL AUTO_INCREMENT,
    `MemberID` int(11) NOT NULL,
    `Conditions` mediumtext COLLATE utf8mb4_bin DEFAULT NULL,
    `Allergies` mediumtext COLLATE utf8mb4_bin DEFAULT NULL,
    `Medication` mediumtext COLLATE utf8mb4_bin DEFAULT NULL,
    PRIMARY KEY (`ID`),
    UNIQUE KEY `MemberID_2` (`MemberID`),
    KEY `MemberID` (`MemberID`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;"
);

$db->query(
  "CREATE TABLE IF NOT EXISTS `memberPhotography` (
    `ID` int(11) NOT NULL AUTO_INCREMENT,
    `MemberID` int(11) NOT NULL,
    `Website` tinyint(1) NOT NULL,
    `Social` tinyint(1) NOT NULL,
    `Noticeboard` tinyint(1) NOT NULL,
    `FilmTraining` tinyint(1) NOT NULL,
    `ProPhoto` tinyint(1) NOT NULL,
    PRIMARY KEY (`ID`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;"
);

$db->query(
  "CREATE TABLE IF NOT EXISTS `members` (
    `MemberID` int(11) NOT NULL AUTO_INCREMENT,
    `SquadID` int(11) NOT NULL,
    `UserID` int(11) DEFAULT NULL,
    `Status` tinyint(1) NOT NULL DEFAULT 1,
    `RR` tinyint(1) NOT NULL DEFAULT 0,
    `AccessKey` varchar(20) COLLATE utf8mb4_bin NOT NULL,
    `MForename` varchar(255) COLLATE utf8mb4_bin DEFAULT NULL,
    `MSurname` varchar(255) COLLATE utf8mb4_bin DEFAULT NULL,
    `MMiddleNames` varchar(255) COLLATE utf8mb4_bin DEFAULT NULL,
    `ASANumber` varchar(255) COLLATE utf8mb4_bin DEFAULT NULL,
    `ASACategory` int(11) NOT NULL,
    `ClubPays` tinyint(1) NOT NULL,
    `DateOfBirth` date NOT NULL,
    `Gender` enum('Male','Female') COLLATE utf8mb4_bin NOT NULL,
    `OtherNotes` mediumtext COLLATE utf8mb4_bin NOT NULL,
    PRIMARY KEY (`MemberID`),
    UNIQUE KEY `MemberID` (`MemberID`),
    KEY `SquadID` (`SquadID`),
    KEY `UserID` (`UserID`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;"
);

$db->query(
  "CREATE TABLE IF NOT EXISTS `moves` (
    `MoveID` int(11) NOT NULL AUTO_INCREMENT,
    `MemberID` int(11) NOT NULL,
    `SquadID` int(11) NOT NULL,
    `MovingDate` date NOT NULL,
    PRIMARY KEY (`MoveID`),
    KEY `MemberID` (`MemberID`),
    KEY `SquadID` (`SquadID`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;"
);

$db->query(
  "CREATE TABLE IF NOT EXISTS `newUsers` (
    `ID` int(25) NOT NULL AUTO_INCREMENT,
    `AuthCode` mediumtext COLLATE utf8mb4_bin NOT NULL,
    `UserJSON` mediumtext COLLATE utf8mb4_bin NOT NULL,
    `Time` datetime NOT NULL DEFAULT current_timestamp(),
    `Type` mediumtext COLLATE utf8mb4_bin NOT NULL,
    PRIMARY KEY (`ID`),
    UNIQUE KEY `UserID` (`ID`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;"
);

$db->query(
  "CREATE TABLE IF NOT EXISTS `notify` (
    `EmailID` int(11) NOT NULL AUTO_INCREMENT,
    `MessageID` int(11) DEFAULT NULL,
    `UserID` int(11) NOT NULL,
    `Status` enum('Queued','Sent','No_Sub','Failed') COLLATE utf8mb4_bin NOT NULL,
    `Subject` mediumtext COLLATE utf8mb4_bin DEFAULT NULL,
    `Message` mediumtext COLLATE utf8mb4_bin DEFAULT NULL,
    `Sender` int(11) DEFAULT NULL,
    `ForceSend` tinyint(1) NOT NULL DEFAULT 0,
    `EmailType` mediumtext COLLATE utf8mb4_bin DEFAULT NULL,
    PRIMARY KEY (`EmailID`),
    KEY `MessageID` (`MessageID`),
    KEY `UserID` (`UserID`),
    KEY `Sender` (`Sender`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;"
);

$db->query(
  "CREATE TABLE IF NOT EXISTS `notifyAdditionalEmails` (
    `ID` int(11) NOT NULL AUTO_INCREMENT,
    `UserID` int(11) NOT NULL,
    `EmailAddress` varchar(100) COLLATE utf8mb4_bin NOT NULL,
    `Name` varchar(50) COLLATE utf8mb4_bin NOT NULL,
    PRIMARY KEY (`ID`),
    KEY `UserID` (`UserID`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;"
);

$db->query(
  "CREATE TABLE IF NOT EXISTS `notifyHistory` (
    `ID` int(11) NOT NULL AUTO_INCREMENT,
    `Sender` int(11) NOT NULL,
    `Subject` mediumtext COLLATE utf8mb4_bin NOT NULL,
    `Message` mediumtext COLLATE utf8mb4_bin NOT NULL,
    `ForceSend` tinyint(1) NOT NULL,
    `Date` datetime NOT NULL,
    `JSONData` mediumtext COLLATE utf8mb4_bin NOT NULL,
    PRIMARY KEY (`ID`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;"
);

$db->query(
  "CREATE TABLE IF NOT EXISTS `notifyOptions` (
    `ID` int(11) NOT NULL AUTO_INCREMENT,
    `UserID` int(11) NOT NULL,
    `EmailType` mediumtext COLLATE utf8mb4_bin NOT NULL,
    `Subscribed` tinyint(1) NOT NULL DEFAULT 0,
    PRIMARY KEY (`ID`),
    KEY `UserID` (`UserID`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;"
);

$db->query(
  "CREATE TABLE IF NOT EXISTS `passwordTokens` (
    `TokenID` int(11) NOT NULL AUTO_INCREMENT,
    `UserID` int(11) NOT NULL,
    `Token` mediumtext COLLATE utf8mb4_bin NOT NULL,
    `Date` date DEFAULT NULL,
    `Type` enum('Password_Reset','Account_Verification') COLLATE utf8mb4_bin NOT NULL,
    PRIMARY KEY (`TokenID`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;"
);

$db->query(
  "CREATE TABLE IF NOT EXISTS `paymentMandates` (
    `MandateID` int(11) NOT NULL AUTO_INCREMENT,
    `UserID` int(11) NOT NULL,
    `Name` varchar(20) COLLATE utf8mb4_bin NOT NULL,
    `Mandate` varchar(20) COLLATE utf8mb4_bin NOT NULL,
    `Customer` varchar(20) COLLATE utf8mb4_bin NOT NULL,
    `BankAccount` varchar(20) COLLATE utf8mb4_bin NOT NULL,
    `BankName` varchar(50) COLLATE utf8mb4_bin NOT NULL,
    `AccountHolderName` varchar(30) COLLATE utf8mb4_bin NOT NULL,
    `AccountNumEnd` varchar(2) COLLATE utf8mb4_bin NOT NULL,
    `InUse` tinyint(1) NOT NULL,
    PRIMARY KEY (`MandateID`),
    KEY `UserID` (`UserID`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;"
);

$db->query(
  "CREATE TABLE IF NOT EXISTS `paymentMonths` (
    `MonthID` int(11) NOT NULL AUTO_INCREMENT,
    `MonthStart` varchar(7) COLLATE utf8mb4_bin NOT NULL,
    `Date` date NOT NULL,
    PRIMARY KEY (`MonthID`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;"
);

$db->query(
  "CREATE TABLE IF NOT EXISTS `paymentPreferredMandate` (
    `PrefID` int(11) NOT NULL AUTO_INCREMENT,
    `UserID` int(11) NOT NULL,
    `MandateID` int(11) NOT NULL,
    PRIMARY KEY (`PrefID`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;"
);

$db->query(
  "CREATE TABLE IF NOT EXISTS `paymentRetries` (
    `UserID` int(11) NOT NULL,
    `Day` date NOT NULL,
    `PMKey` varchar(20) COLLATE utf8mb4_bin NOT NULL,
    `Tried` tinyint(1) NOT NULL DEFAULT 0,
    PRIMARY KEY (`UserID`,`Day`),
    KEY `PMKey` (`PMKey`),
    KEY `UserID` (`UserID`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;"
);

$db->query(
  "CREATE TABLE IF NOT EXISTS `payments` (
    `PaymentID` int(11) NOT NULL AUTO_INCREMENT,
    `Date` date NOT NULL,
    `Status` enum('pending_api_request','pending_customer_approval','pending_submission','submitted','confirmed','paid_out','cancelled','customer_approval_denied','failed','charged_back','cust_not_dd','paid_manually') COLLATE utf8mb4_bin NOT NULL,
    `UserID` int(11) NOT NULL,
    `MandateID` int(11) NOT NULL,
    `Name` varchar(50) COLLATE utf8mb4_bin DEFAULT NULL,
    `Amount` int(11) NOT NULL,
    `Currency` varchar(3) COLLATE utf8mb4_bin NOT NULL,
    `PMkey` varchar(20) COLLATE utf8mb4_bin NOT NULL,
    `Type` enum('Payment','Refund') COLLATE utf8mb4_bin NOT NULL,
    PRIMARY KEY (`PaymentID`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;"
);

$db->query(
  "CREATE TABLE IF NOT EXISTS `paymentSchedule` (
    `ID` int(11) NOT NULL AUTO_INCREMENT,
    `UserID` int(11) NOT NULL,
    `Day` int(11) NOT NULL,
    PRIMARY KEY (`ID`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;"
);

$db->query(
  "CREATE TABLE IF NOT EXISTS `paymentsPending` (
    `PaymentID` int(11) NOT NULL AUTO_INCREMENT,
    `Date` date NOT NULL,
    `Status` enum('Pending','Queued','Requested','Paid','Failed') COLLATE utf8mb4_bin NOT NULL,
    `UserID` int(11) NOT NULL,
    `Name` varchar(50) COLLATE utf8mb4_bin DEFAULT NULL,
    `Amount` int(11) NOT NULL,
    `Currency` varchar(3) COLLATE utf8mb4_bin NOT NULL,
    `PMkey` varchar(20) COLLATE utf8mb4_bin DEFAULT NULL,
    `Type` enum('Payment','Refund') COLLATE utf8mb4_bin NOT NULL,
    `MetadataJSON` mediumtext COLLATE utf8mb4_bin DEFAULT NULL,
    PRIMARY KEY (`PaymentID`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;"
);

$db->query(
  "CREATE TABLE IF NOT EXISTS `paymentSquadFees` (
    `SFID` int(11) NOT NULL AUTO_INCREMENT,
    `MonthID` int(11) NOT NULL,
    PRIMARY KEY (`SFID`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;"
);

$db->query(
  "CREATE TABLE IF NOT EXISTS `paymentTypes` (
    `PayTypeID` int(11) NOT NULL AUTO_INCREMENT,
    `PayTypeName` varchar(60) COLLATE utf8mb4_bin NOT NULL,
    `PayTypeDescription` varchar(200) COLLATE utf8mb4_bin DEFAULT NULL,
    `PayTypeEnabled` tinyint(1) NOT NULL,
    PRIMARY KEY (`PayTypeID`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;"
);

$db->query(
  "CREATE TABLE IF NOT EXISTS `paymentWebhookOps` (
    `ID` int(11) NOT NULL AUTO_INCREMENT,
    `EventID` varchar(20) COLLATE utf8mb4_bin NOT NULL,
    PRIMARY KEY (`ID`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;"
);

$db->query(
  "CREATE TABLE IF NOT EXISTS `posts` (
    `ID` int(11) NOT NULL AUTO_INCREMENT,
    `Author` int(11) DEFAULT NULL,
    `Date` timestamp NOT NULL DEFAULT current_timestamp(),
    `Content` mediumtext COLLATE utf8mb4_bin NOT NULL,
    `Title` mediumtext COLLATE utf8mb4_bin NOT NULL,
    `Excerpt` mediumtext COLLATE utf8mb4_bin NOT NULL,
    `Path` mediumtext COLLATE utf8mb4_bin NOT NULL,
    `Modified` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    `Type` mediumtext COLLATE utf8mb4_bin NOT NULL,
    `MIME` mediumtext COLLATE utf8mb4_bin NOT NULL,
    PRIMARY KEY (`ID`),
    KEY `Author` (`Author`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;"
);

$db->query(
  "CREATE TABLE IF NOT EXISTS `qualifications` (
    `ID` int(11) NOT NULL AUTO_INCREMENT,
    `UserID` int(11) NOT NULL,
    `Qualification` int(11) NOT NULL,
    `Info` mediumtext COLLATE utf8mb4_bin DEFAULT NULL,
    `From` date NOT NULL,
    `To` date DEFAULT NULL,
    PRIMARY KEY (`ID`),
    KEY `UserID` (`UserID`),
    KEY `Qualification` (`Qualification`),
    KEY `Qualification_2` (`Qualification`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;"
);

$db->query(
  "CREATE TABLE IF NOT EXISTS `qualificationsAvailable` (
    `ID` int(11) NOT NULL AUTO_INCREMENT,
    `Name` varchar(80) COLLATE utf8mb4_bin NOT NULL,
    PRIMARY KEY (`ID`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;"
);

$db->query(
  "CREATE TABLE IF NOT EXISTS `renewalMembers` (
    `ID` int(11) NOT NULL AUTO_INCREMENT,
    `PaymentID` int(11) NOT NULL,
    `MemberID` int(11) NOT NULL,
    `RenewalID` int(11) NOT NULL,
    `Date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE current_timestamp(),
    `CountRenewal` tinyint(1) NOT NULL DEFAULT 0,
    PRIMARY KEY (`ID`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;"
);

$db->query(
  "CREATE TABLE IF NOT EXISTS `renewalProgress` (
    `ID` int(11) NOT NULL AUTO_INCREMENT,
    `UserID` int(11) NOT NULL,
    `RenewalID` int(11) NOT NULL,
    `Date` date NOT NULL,
    `Stage` int(11) NOT NULL,
    `Substage` int(11) NOT NULL,
    `Part` int(11) NOT NULL,
    PRIMARY KEY (`ID`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;"
);

$db->query(
  "CREATE TABLE IF NOT EXISTS `renewals` (
    `ID` int(11) NOT NULL AUTO_INCREMENT,
    `Name` mediumtext COLLATE utf8mb4_bin NOT NULL,
    `Year` int(11) NOT NULL,
    `StartDate` date NOT NULL,
    `EndDate` date NOT NULL,
    PRIMARY KEY (`ID`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;"
);

$db->query(
  "CREATE TABLE IF NOT EXISTS `sessions` (
    `SessionID` int(11) NOT NULL AUTO_INCREMENT,
    `SquadID` int(11) NOT NULL,
    `VenueID` int(11) NOT NULL,
    `SessionName` varchar(100) COLLATE utf8mb4_bin NOT NULL,
    `SessionDay` int(11) NOT NULL,
    `MainSequence` tinyint(1) NOT NULL,
    `StartTime` time DEFAULT NULL,
    `EndTime` time DEFAULT NULL,
    `DisplayFrom` date DEFAULT NULL,
    `DisplayUntil` date DEFAULT NULL,
    PRIMARY KEY (`SessionID`),
    KEY `SquadID` (`SquadID`),
    KEY `VenueID` (`VenueID`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='Stores information about sessions for Squads';"
);

$db->query(
  "CREATE TABLE IF NOT EXISTS `sessionsAttendance` (
    `WeekID` int(11) NOT NULL,
    `SessionID` int(11) NOT NULL,
    `MemberID` int(11) NOT NULL,
    `AttendanceBoolean` int(11) NOT NULL,
    KEY `WeekID` (`WeekID`),
    KEY `SessionID` (`SessionID`),
    KEY `MemberID` (`MemberID`),
    KEY `MemberID_2` (`MemberID`),
    KEY `SessionID_2` (`SessionID`),
    KEY `WeekID_2` (`WeekID`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;"
);

$db->query(
  "CREATE TABLE IF NOT EXISTS `sessionsVenues` (
    `VenueID` int(11) NOT NULL AUTO_INCREMENT,
    `VenueName` varchar(100) COLLATE utf8mb4_bin NOT NULL,
    `Location` mediumtext COLLATE utf8mb4_bin DEFAULT NULL,
    PRIMARY KEY (`VenueID`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;"
);

$db->query(
  "CREATE TABLE IF NOT EXISTS `sessionsWeek` (
    `WeekID` int(11) NOT NULL AUTO_INCREMENT,
    `WeekDateBeginning` date NOT NULL,
    PRIMARY KEY (`WeekID`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;"
);

$db->query(
  "CREATE TABLE IF NOT EXISTS `squads` (
    `SquadID` int(11) NOT NULL AUTO_INCREMENT,
    `UserID` int(11) DEFAULT NULL,
    `SquadName` varchar(255) COLLATE utf8mb4_bin DEFAULT NULL,
    `SquadFee` decimal(8,2) DEFAULT NULL,
    `SquadCoach` varchar(100) COLLATE utf8mb4_bin NOT NULL,
    `SquadTimetable` varchar(100) COLLATE utf8mb4_bin NOT NULL,
    `SquadCoC` varchar(100) COLLATE utf8mb4_bin NOT NULL,
    `SquadKey` varchar(20) COLLATE utf8mb4_bin NOT NULL,
    PRIMARY KEY (`SquadID`),
    UNIQUE KEY `SquadID` (`SquadID`),
    KEY `UserID` (`UserID`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;"
);

$db->query(
  "CREATE TABLE IF NOT EXISTS `systemOptions` (
    `ID` int(11) NOT NULL AUTO_INCREMENT,
    `Option` varchar(30) COLLATE utf8mb4_bin NOT NULL,
    `Value` mediumtext COLLATE utf8mb4_bin NOT NULL,
    PRIMARY KEY (`ID`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;"
);

$db->query(
  "CREATE TABLE IF NOT EXISTS `targetedListMembers` (
    `ID` int(11) NOT NULL AUTO_INCREMENT,
    `ListID` int(11) NOT NULL,
    `ReferenceID` int(11) NOT NULL,
    `ReferenceType` enum('User','Member') COLLATE utf8mb4_bin NOT NULL,
    PRIMARY KEY (`ID`),
    KEY `ListID` (`ListID`),
    KEY `ReferenceID` (`ReferenceID`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;"
);

$db->query(
  "CREATE TABLE IF NOT EXISTS `targetedLists` (
    `ID` int(11) NOT NULL AUTO_INCREMENT,
    `Name` mediumtext COLLATE utf8mb4_bin NOT NULL,
    `Description` mediumtext COLLATE utf8mb4_bin NOT NULL,
    PRIMARY KEY (`ID`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;"
);

$db->query(
  "CREATE TABLE IF NOT EXISTS `times` (
    `MemberID` int(11) NOT NULL,
    `LastUpdate` date NOT NULL,
    `Type` enum('CY_SC','CY_LC','SCPB','LCPB') COLLATE utf8mb4_bin NOT NULL,
    `50Free` mediumtext COLLATE utf8mb4_bin DEFAULT NULL,
    `100Free` mediumtext COLLATE utf8mb4_bin DEFAULT NULL,
    `200Free` mediumtext COLLATE utf8mb4_bin DEFAULT NULL,
    `400Free` mediumtext COLLATE utf8mb4_bin DEFAULT NULL,
    `800Free` mediumtext COLLATE utf8mb4_bin DEFAULT NULL,
    `1500Free` mediumtext COLLATE utf8mb4_bin DEFAULT NULL,
    `50Breast` mediumtext COLLATE utf8mb4_bin DEFAULT NULL,
    `100Breast` mediumtext COLLATE utf8mb4_bin DEFAULT NULL,
    `200Breast` mediumtext COLLATE utf8mb4_bin DEFAULT NULL,
    `50Fly` mediumtext COLLATE utf8mb4_bin DEFAULT NULL,
    `100Fly` mediumtext COLLATE utf8mb4_bin DEFAULT NULL,
    `200Fly` mediumtext COLLATE utf8mb4_bin DEFAULT NULL,
    `50Back` mediumtext COLLATE utf8mb4_bin DEFAULT NULL,
    `100Back` mediumtext COLLATE utf8mb4_bin DEFAULT NULL,
    `200Back` mediumtext COLLATE utf8mb4_bin DEFAULT NULL,
    `100IM` mediumtext COLLATE utf8mb4_bin DEFAULT NULL,
    `200IM` mediumtext COLLATE utf8mb4_bin DEFAULT NULL,
    `400IM` mediumtext COLLATE utf8mb4_bin DEFAULT NULL,
    KEY `MemberID` (`MemberID`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;"
);

$db->query(
  "CREATE TABLE IF NOT EXISTS `userLogins` (
    `ID` int(11) NOT NULL AUTO_INCREMENT,
    `UserID` int(11) NOT NULL,
    `Time` timestamp NOT NULL DEFAULT current_timestamp(),
    `IPAddress` mediumtext COLLATE utf8mb4_bin NOT NULL,
    `GeoLocation` mediumtext COLLATE utf8mb4_bin DEFAULT NULL,
    `Browser` mediumtext COLLATE utf8mb4_bin NOT NULL,
    `Platform` mediumtext COLLATE utf8mb4_bin NOT NULL,
    `Mobile` tinyint(1) NOT NULL,
    `Hash` mediumtext COLLATE utf8mb4_bin NOT NULL,
    `HashActive` tinyint(1) NOT NULL DEFAULT 0,
    PRIMARY KEY (`ID`),
    KEY `UserID` (`UserID`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;"
);

$db->query(
  "CREATE TABLE IF NOT EXISTS `userOptions` (
    `ID` int(11) NOT NULL AUTO_INCREMENT,
    `User` int(11) NOT NULL,
    `Option` varchar(30) COLLATE utf8mb4_bin NOT NULL,
    `Value` mediumtext COLLATE utf8mb4_bin DEFAULT NULL,
    PRIMARY KEY (`ID`),
    KEY `User` (`User`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;"
);

$db->query(
  "CREATE TABLE IF NOT EXISTS `users` (
    `UserID` int(25) NOT NULL AUTO_INCREMENT,
    `Username` varchar(65) COLLATE utf8mb4_bin DEFAULT NULL,
    `GID` int(11) DEFAULT NULL COMMENT 'If G Suite User, store the ID here for automatic passwords',
    `Password` varchar(255) COLLATE utf8mb4_bin NOT NULL,
    `AccessLevel` enum('Parent','Galas','Coach','Committee','Admin') COLLATE utf8mb4_bin NOT NULL DEFAULT 'Parent',
    `EmailAddress` varchar(255) COLLATE utf8mb4_bin NOT NULL,
    `EmailComms` tinyint(1) NOT NULL,
    `Forename` mediumtext COLLATE utf8mb4_bin NOT NULL,
    `Surname` mediumtext COLLATE utf8mb4_bin NOT NULL,
    `Mobile` text COLLATE utf8mb4_bin NOT NULL,
    `MobileComms` tinyint(1) NOT NULL,
    `RR` tinyint(1) NOT NULL DEFAULT 0,
    `Edit` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    PRIMARY KEY (`UserID`),
    UNIQUE KEY `UserID` (`UserID`),
    UNIQUE KEY `EmailAddress` (`EmailAddress`),
    UNIQUE KEY `Username` (`Username`),
    UNIQUE KEY `Username_2` (`Username`,`EmailAddress`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;"
);

$db->query(
  "ALTER TABLE `extrasRelations`
  ADD CONSTRAINT `extrasRelations_ibfk_1` FOREIGN KEY (`ExtraID`) REFERENCES `extras` (`ExtraID`),
  ADD CONSTRAINT `extrasRelations_ibfk_2` FOREIGN KEY (`MemberID`) REFERENCES `members` (`MemberID`),
  ADD CONSTRAINT `extrasRelations_ibfk_3` FOREIGN KEY (`UserID`) REFERENCES `users` (`UserID`);"
);

$db->query(
  "ALTER TABLE `galaEntries`
  ADD CONSTRAINT `galaEntries_ibfk_1` FOREIGN KEY (`GalaID`) REFERENCES `galas` (`GalaID`),
  ADD CONSTRAINT `galaEntries_ibfk_2` FOREIGN KEY (`GalaID`) REFERENCES `galas` (`GalaID`),
  ADD CONSTRAINT `galaEntries_ibfk_3` FOREIGN KEY (`MemberID`) REFERENCES `members` (`MemberID`) ON DELETE CASCADE;"
);

$db->query(
  "ALTER TABLE `joinSwimmers`
  ADD CONSTRAINT `joinSwimmers_ibfk_1` FOREIGN KEY (`Parent`) REFERENCES `joinParents` (`Hash`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `joinSwimmers_ibfk_2` FOREIGN KEY (`SquadSuggestion`) REFERENCES `squads` (`SquadID`) ON DELETE CASCADE ON UPDATE CASCADE;"
);

$db->query(
  "ALTER TABLE `memberMedical`
  ADD CONSTRAINT `memberMedical_ibfk_1` FOREIGN KEY (`MemberID`) REFERENCES `members` (`MemberID`) ON DELETE CASCADE;"
);

$db->query(
  "ALTER TABLE `members`
  ADD CONSTRAINT `members_ibfk_1` FOREIGN KEY (`SquadID`) REFERENCES `squads` (`SquadID`),
  ADD CONSTRAINT `members_ibfk_3` FOREIGN KEY (`UserID`) REFERENCES `users` (`UserID`) ON DELETE SET NULL;"
);

$db->query(
  "ALTER TABLE `moves`
  ADD CONSTRAINT `moves_ibfk_1` FOREIGN KEY (`MemberID`) REFERENCES `members` (`MemberID`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `moves_ibfk_2` FOREIGN KEY (`SquadID`) REFERENCES `squads` (`SquadID`) ON DELETE CASCADE ON UPDATE NO ACTION;"
);

$db->query(
  "ALTER TABLE `notify`
  ADD CONSTRAINT `notify_ibfk_1` FOREIGN KEY (`MessageID`) REFERENCES `notifyHistory` (`ID`),
  ADD CONSTRAINT `notify_ibfk_2` FOREIGN KEY (`Sender`) REFERENCES `users` (`UserID`),
  ADD CONSTRAINT `notify_ibfk_3` FOREIGN KEY (`UserID`) REFERENCES `users` (`UserID`) ON DELETE CASCADE;"
);

$db->query(
  "ALTER TABLE `notifyAdditionalEmails`
  ADD CONSTRAINT `notifyAdditionalEmails_ibfk_1` FOREIGN KEY (`UserID`) REFERENCES `users` (`UserID`) ON DELETE CASCADE ON UPDATE CASCADE;"
);

$db->query(
  "ALTER TABLE `notifyOptions`
  ADD CONSTRAINT `notifyOptions_ibfk_1` FOREIGN KEY (`UserID`) REFERENCES `users` (`UserID`) ON DELETE CASCADE;"
);

$db->query(
  "ALTER TABLE `paymentMandates`
  ADD CONSTRAINT `paymentMandates_ibfk_1` FOREIGN KEY (`UserID`) REFERENCES `users` (`UserID`) ON DELETE CASCADE;"
);

$db->query(
  "ALTER TABLE `paymentRetries`
  ADD CONSTRAINT `paymentRetries_ibfk_1` FOREIGN KEY (`UserID`) REFERENCES `users` (`UserID`);"
);

$db->query(
  "ALTER TABLE `posts`
  ADD CONSTRAINT `posts_ibfk_3` FOREIGN KEY (`Author`) REFERENCES `users` (`UserID`) ON DELETE SET NULL;ALTER TABLE `notifyAdditionalEmails`
  ADD CONSTRAINT `notifyAdditionalEmails_ibfk_1` FOREIGN KEY (`UserID`) REFERENCES `users` (`UserID`) ON DELETE CASCADE ON UPDATE CASCADE;"
);

$db->query(
  "ALTER TABLE `qualifications`
  ADD CONSTRAINT `qualifications_ibfk_2` FOREIGN KEY (`UserID`) REFERENCES `users` (`UserID`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `qualifications_ibfk_3` FOREIGN KEY (`Qualification`) REFERENCES `qualificationsAvailable` (`ID`) ON DELETE CASCADE ON UPDATE NO ACTION;"
);

$db->query(
  "ALTER TABLE `sessions`
  ADD CONSTRAINT `sessions_ibfk_1` FOREIGN KEY (`SquadID`) REFERENCES `squads` (`SquadID`) ON DELETE CASCADE,
  ADD CONSTRAINT `sessions_ibfk_2` FOREIGN KEY (`VenueID`) REFERENCES `sessionsVenues` (`VenueID`) ON DELETE CASCADE;"
);

$db->query(
  "ALTER TABLE `sessionsAttendance`
  ADD CONSTRAINT `sessionsAttendance_ibfk_1` FOREIGN KEY (`WeekID`) REFERENCES `sessionsWeek` (`WeekID`) ON DELETE CASCADE,
  ADD CONSTRAINT `sessionsAttendance_ibfk_2` FOREIGN KEY (`SessionID`) REFERENCES `sessions` (`SessionID`) ON DELETE CASCADE,
  ADD CONSTRAINT `sessionsAttendance_ibfk_3` FOREIGN KEY (`MemberID`) REFERENCES `members` (`MemberID`) ON DELETE CASCADE;"
);

$db->query(
  "ALTER TABLE `squads`
  ADD CONSTRAINT `squads_ibfk_1` FOREIGN KEY (`UserID`) REFERENCES `users` (`UserID`);"
);

$db->query(
  "ALTER TABLE `targetedListMembers`
  ADD CONSTRAINT `targetedListMembers_ibfk_1` FOREIGN KEY (`ListID`) REFERENCES `targetedLists` (`ID`) ON DELETE CASCADE,
  ADD CONSTRAINT `targetedListMembers_ibfk_2` FOREIGN KEY (`ReferenceID`) REFERENCES `members` (`MemberID`) ON DELETE CASCADE;"
);

$db->query(
  "ALTER TABLE `times`
  ADD CONSTRAINT `times_ibfk_1` FOREIGN KEY (`MemberID`) REFERENCES `members` (`MemberID`) ON DELETE CASCADE;"
);

$db->query(
  "ALTER TABLE `userLogins`
  ADD CONSTRAINT `userLogins_ibfk_1` FOREIGN KEY (`UserID`) REFERENCES `users` (`UserID`) ON DELETE CASCADE;"
);

$db->query(
  "ALTER TABLE `userOptions`
  ADD CONSTRAINT `userOptions_ibfk_1` FOREIGN KEY (`User`) REFERENCES `users` (`UserID`) ON DELETE CASCADE;
COMMIT;"
);

$db->query(
  "ALTER TABLE galaEntries ADD Refunded BOOLEAN DEFAULT '0';"
);

$db->query(
  "ALTER TABLE galaEntries ADD AmountRefunded INT DEFAULT '0';"
);

$db->query(
  "CREATE TABLE IF NOT EXISTS `stripeCustomers` (
    `ID` int(11) NOT NULL AUTO_INCREMENT,
    `User` int(11) NOT NULL,
    `CustomerID` varchar(255) COLLATE utf8mb4_bin NOT NULL,
    PRIMARY KEY (`ID`),
    FOREIGN KEY (User) REFERENCES users(UserID)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;"
);

$db->query(
  "CREATE TABLE IF NOT EXISTS `stripePayMethods` (
    `ID` int(11) NOT NULL AUTO_INCREMENT,
    `Customer` varchar(255) COLLATE utf8mb4_bin NOT NULL,
    `MethodID` varchar(255) COLLATE utf8mb4_bin NOT NULL,
    `CardName` varchar(255) COLLATE utf8mb4_bin,
    `City` varchar(255) COLLATE utf8mb4_bin,
    `Country` varchar(255) COLLATE utf8mb4_bin,
    `Line1` varchar(255) COLLATE utf8mb4_bin,
    `Line2` varchar(255) COLLATE utf8mb4_bin,
    `PostCode` varchar(255) COLLATE utf8mb4_bin,
    `Brand` varchar(255) COLLATE utf8mb4_bin,
    `IssueCountry` varchar(255) COLLATE utf8mb4_bin,
    `ExpMonth` varchar(255) COLLATE utf8mb4_bin,
    `ExpYear` varchar(255) COLLATE utf8mb4_bin,
    `Funding` varchar(255) COLLATE utf8mb4_bin,
    `Last4` varchar(255) COLLATE utf8mb4_bin,
    PRIMARY KEY (`ID`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;"
);

$db->query(
  "ALTER TABLE stripePayMethods ADD `Name` varchar(255) COLLATE utf8mb4_bin;"
);

$db->query(
  "ALTER TABLE joinSwimmers MODIFY COLUMN ASA varchar(255) COLLATE utf8mb4_bin;"
);

$db->query(
  "CREATE TABLE IF NOT EXISTS `completedForms` (
    `ID` int(11) NOT NULL AUTO_INCREMENT,
    `Member` int(11) NULL,
    `User` int(11) NULL,
    `Form` varchar(255) COLLATE utf8mb4_bin DEFAULT NULL,
    `Date` date NOT NULL,
    `About` text COLLATE utf8mb4_bin DEFAULT NULL,
    PRIMARY KEY (`ID`),
    FOREIGN KEY (User) REFERENCES users(UserID),
    FOREIGN KEY (Member) REFERENCES members(MemberID)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;"
);

$db->query(
  "CREATE TABLE IF NOT EXISTS `stripePayments` (
    `ID` int(11) NOT NULL AUTO_INCREMENT,
    `User` int(11) NOT NULL,
    `DateTime` DATETIME NOT NULL,
    `Method` int(11) COLLATE utf8mb4_bin,
    `Intent` varchar(255) COLLATE utf8mb4_bin,
    `Amount` int(11) NOT NULL,
    `Currency` varchar(3) NOT NULL,
    `ServedBy` int(11),
    `Paid` BOOLEAN NOT NULL,
    PRIMARY KEY (`ID`),
    FOREIGN KEY (`User`) REFERENCES users(UserID),
    FOREIGN KEY (Method) REFERENCES stripePayMethods(ID),
    FOREIGN KEY (ServedBy) REFERENCES users(UserID)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;"
);

$db->query(
  "CREATE TABLE IF NOT EXISTS `stripePaymentItems` (
    `ID` int(11) NOT NULL AUTO_INCREMENT,
    `Payment` int(11) NOT NULL,
    `Name` varchar(255) COLLATE utf8mb4_bin,
    `Description` text COLLATE utf8mb4_bin,
    `Amount` int(11) NOT NULL,
    `Currency` varchar(3) NOT NULL,
    PRIMARY KEY (`ID`),
    FOREIGN KEY (Payment) REFERENCES stripePayments(ID)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;"
);