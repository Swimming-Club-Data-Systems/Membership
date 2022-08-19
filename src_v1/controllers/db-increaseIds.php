<?php

$db = app()->db;

$amount = 300000;
$tenant = 9;

$db->query("SET FOREIGN_KEY_CHECKS=0;");

$db->beginTransaction();

try {

  $query = $db->prepare("UPDATE coaches SET ID = ID + :amount, User = User + :amount, Squad = Squad + :amount");
  $query->execute([
    'amount' => $amount
  ]);

  $query = $db->prepare("UPDATE completedForms SET ID = ID + :amount, Member = Member + :amount, User = User + :amount");
  $query->execute([
    'amount' => $amount
  ]);

  $query = $db->prepare("UPDATE emergencyContacts SET ID = ID + :amount, UserID = UserID + :amount");
  $query->execute([
    'amount' => $amount
  ]);

  $query = $db->prepare("UPDATE extras SET ExtraID = ExtraID + :amount, Tenant = :tenant");
  $query->execute([
    'amount' => $amount,
    'tenant' => $tenant,
  ]);

  $query = $db->prepare("UPDATE extrasRelations SET RelationID = RelationID + :amount, ExtraID = ExtraID + :amount, MemberID = MemberID + :amount, UserID = UserID + :amount");
  $query->execute([
    'amount' => $amount
  ]);

  $query = $db->prepare("UPDATE galaData SET Gala = Gala + :amount");
  $query->execute([
    'amount' => $amount
  ]);

  $query = $db->prepare("UPDATE galaEntries SET EntryID = EntryID + :amount, GalaID = GalaID + :amount, MemberID = MemberID + :amount, StripePayment = StripePayment + :amount, PaymentID = PaymentID + :amount");
  $query->execute([
    'amount' => $amount,
  ]);

  $query = $db->prepare("UPDATE galas SET GalaID = GalaID + :amount, Tenant = :tenant");
  $query->execute([
    'amount' => $amount,
    'tenant' => $tenant,
  ]);

  $query = $db->prepare("UPDATE galaSessions SET ID = ID + :amount, Gala = Gala + :amount");
  $query->execute([
    'amount' => $amount,
  ]);

  $query = $db->prepare("UPDATE galaSessionsCanEnter SET ID = ID + :amount, Member = Member + :amount, `Session` = `Session` + :amount");
  $query->execute([
    'amount' => $amount,
  ]);

  $query = $db->prepare("UPDATE individualFeeTrack SET ID = ID + :amount, MonthID = MonthID + :amount, PaymentID = PaymentID + :amount, MemberID = MemberID + :amount, UserID = UserID + :amount");
  $query->execute([
    'amount' => $amount,
  ]);

  $query = $db->prepare("UPDATE joinParents SET Tenant = :tenant");
  $query->execute([
    'tenant' => $tenant,
  ]);

  $query = $db->prepare("UPDATE joinSwimmers SET ID = ID + :amount, Tenant = :tenant");
  $query->execute([
    'amount' => $amount,
    'tenant' => $tenant,
  ]);

  $query = $db->prepare("UPDATE listSenders SET User = User + :amount, List = List + :amount");
  $query->execute([
    'amount' => $amount,
  ]);

  $query = $db->prepare("UPDATE meetResults SET Result = Result + :amount, Meet = Meet + :amount, Member = Member + :amount");
  $query->execute([
    'amount' => $amount,
  ]);

  $query = $db->prepare("UPDATE meetsWithResults SET Meet = Meet + :amount, Gala = Gala + :amount, Tenant = :tenant");
  $query->execute([
    'amount' => $amount,
    'tenant' => $tenant,
  ]);

  $query = $db->prepare("UPDATE memberEmailAddresses SET ID = ID + :amount, Member = Member + :amount");
  $query->execute([
    'amount' => $amount,
  ]);

  $query = $db->prepare("UPDATE memberMedical SET ID = ID + :amount, MemberID = MemberID + :amount");
  $query->execute([
    'amount' => $amount,
  ]);

  $query = $db->prepare("UPDATE memberPhotography SET ID = ID + :amount, MemberID = MemberID + :amount");
  $query->execute([
    'amount' => $amount,
  ]);

  $query = $db->prepare("UPDATE members SET MemberID = MemberID + :amount, UserID = UserID + :amount, Tenant = :tenant");
  $query->execute([
    'amount' => $amount,
    'tenant' => $tenant,
  ]);

  $query = $db->prepare("UPDATE newUsers SET ID = ID + :amount");
  $query->execute([
    'amount' => $amount,
  ]);

  $query = $db->prepare("UPDATE notify SET EmailID = EmailID + :amount, MessageID = MessageID + :amount, UserID = UserID + :amount, Sender = Sender + :amount");
  $query->execute([
    'amount' => $amount,
  ]);

  $query = $db->prepare("UPDATE notifyAdditionalEmails SET ID = ID + :amount, UserID = UserID + :amount");
  $query->execute([
    'amount' => $amount,
  ]);

  $query = $db->prepare("UPDATE notifyHistory SET ID = ID + :amount, Sender = Sender + :amount, Tenant = :tenant");
  $query->execute([
    'amount' => $amount,
    'tenant' => $tenant,
  ]);

  $query = $db->prepare("UPDATE notifyOptions SET ID = ID + :amount, UserID = UserID + :amount");
  $query->execute([
    'amount' => $amount,
  ]);

  $query = $db->prepare("UPDATE passwordTokens SET TokenID = TokenID + :amount, UserID = UserID + :amount");
  $query->execute([
    'amount' => $amount,
  ]);

  $query = $db->prepare("UPDATE paymentCategories SET ID = ID + :amount, Tenant = :tenant");
  $query->execute([
    'amount' => $amount,
    'tenant' => $tenant,
  ]);

  $query = $db->prepare("UPDATE paymentMandates SET MandateID = MandateID + :amount, UserID = UserID + :amount");
  $query->execute([
    'amount' => $amount,
  ]);

  $query = $db->prepare("UPDATE paymentMonths SET MonthID = MonthID + :amount, Tenant = :tenant");
  $query->execute([
    'amount' => $amount,
    'tenant' => $tenant,
  ]);

  $query = $db->prepare("UPDATE paymentPreferredMandate SET PrefID = PrefID + :amount, UserID = UserID + :amount, MandateID = MandateID + :amount");
  $query->execute([
    'amount' => $amount,
  ]);

  $query = $db->prepare("UPDATE paymentRetries SET UserID = UserID + :amount");
  $query->execute([
    'amount' => $amount,
  ]);

  $query = $db->prepare("UPDATE payments SET PaymentID = PaymentID + :amount, UserID = UserID + :amount, MandateID = MandateID + :amount");
  $query->execute([
    'amount' => $amount,
  ]);

  $query = $db->prepare("UPDATE paymentSchedule SET ID = ID + :amount, UserID = UserID + :amount");
  $query->execute([
    'amount' => $amount,
  ]);

  $query = $db->prepare("UPDATE paymentsPayouts SET Tenant = :tenant");
  $query->execute([
    'tenant' => $tenant,
  ]);

  $query = $db->prepare("UPDATE paymentsPending SET PaymentID = PaymentID + :amount, UserID = UserID + :amount, Category = Category + :amount, Payment = Payment + :amount");
  $query->execute([
    'amount' => $amount,
  ]);

  $query = $db->prepare("UPDATE paymentSquadFees SET SFID = SFID + :amount, MonthID = MonthID + :amount, Tenant = :tenant");
  $query->execute([
    'amount' => $amount,
    'tenant' => $tenant,
  ]);

  $query = $db->prepare("UPDATE paymentWebhookOps SET ID = ID + :amount");
  $query->execute([
    'amount' => $amount,
  ]);

  $query = $db->prepare("UPDATE `permissions` SET ID = ID + :amount, `User` = `User` + :amount");
  $query->execute([
    'amount' => $amount,
  ]);

  $query = $db->prepare("UPDATE posts SET ID = ID + :amount, Author = Author + :amount, Tenant = :tenant");
  $query->execute([
    'amount' => $amount,
    'tenant' => $tenant,
  ]);

  $query = $db->prepare("UPDATE qualifications SET ID = ID + :amount, UserID = UserID + :amount, Qualification = Qualification + :amount, Tenant = :tenant");
  $query->execute([
    'amount' => $amount,
    'tenant' => $tenant,
  ]);

  $query = $db->prepare("UPDATE qualificationsAvailable SET ID = ID + :amount");
  $query->execute([
    'amount' => $amount,
  ]);

  $query = $db->prepare("UPDATE renewalMembers SET ID = ID + :amount, PaymentID = PaymentID + :amount, MemberID = MemberID + :amount, RenewalID = RenewalID + :amount");
  $query->execute([
    'amount' => $amount,
  ]);

  $query = $db->prepare("UPDATE renewalProgress SET ID = ID + :amount, UserID = UserID + :amount, RenewalID = RenewalID + :amount");
  $query->execute([
    'amount' => $amount,
  ]);

  $query = $db->prepare("UPDATE renewals SET ID = ID + :amount, Tenant = :tenant");
  $query->execute([
    'amount' => $amount,
    'tenant' => $tenant,
  ]);

  $query = $db->prepare("UPDATE `sessions` SET SessionID = SessionID + :amount, SquadID = SquadID + :amount, VenueID = VenueID + :amount, Tenant = :tenant");
  $query->execute([
    'amount' => $amount,
    'tenant' => $tenant,
  ]);

  $query = $db->prepare("UPDATE sessionsAttendance SET WeekID = WeekID + :amount, SessionID = SessionID + :amount, MemberID = MemberID + :amount");
  $query->execute([
    'amount' => $amount,
  ]);

  $query = $db->prepare("UPDATE sessionsVenues SET VenueID = VenueID + :amount, Tenant = :tenant");
  $query->execute([
    'amount' => $amount,
    'tenant' => $tenant,
  ]);

  $query = $db->prepare("UPDATE sessionsWeek SET WeekID = WeekID + :amount, Tenant = :tenant");
  $query->execute([
    'amount' => $amount,
    'tenant' => $tenant,
  ]);

  $query = $db->prepare("UPDATE squadMembers SET Member = Member + :amount, Squad = Squad + :amount");
  $query->execute([
    'amount' => $amount,
  ]);

  $query = $db->prepare("UPDATE squadMoves SET ID = ID + :amount, Member = Member + :amount, Old = Old + :amount, New = New + :amount");
  $query->execute([
    'amount' => $amount,
  ]);

  $query = $db->prepare("UPDATE squadReps SET `User` = `User` + :amount, Squad = Squad + :amount");
  $query->execute([
    'amount' => $amount,
  ]);

  $query = $db->prepare("UPDATE squads SET SquadID = SquadID + :amount, Tenant = :tenant");
  $query->execute([
    'amount' => $amount,
    'tenant' => $tenant,
  ]);

  $query = $db->prepare("UPDATE stripeCustomers SET ID = ID + :amount, `User` = `User` + :amount");
  $query->execute([
    'amount' => $amount,
  ]);

  $query = $db->prepare("UPDATE stripePaymentItems SET ID = ID + :amount, Payment = Payment + :amount, Category = Category + :amount");
  $query->execute([
    'amount' => $amount,
  ]);

  $query = $db->prepare("UPDATE stripePayments SET ID = ID + :amount, `User` = `User` + :amount, Method = Method + :amount, ServedBy = ServedBy + :amount");
  $query->execute([
    'amount' => $amount,
  ]);

  $query = $db->prepare("UPDATE stripePayMethods SET ID = ID + :amount");
  $query->execute([
    'amount' => $amount,
  ]);

  $query = $db->prepare("UPDATE stripePayouts SET Tenant = :tenant");
  $query->execute([
    'tenant' => $tenant,
  ]);

  $query = $db->prepare("UPDATE targetedListMembers SET ID = ID + :amount, ListID = ListID + :amount, ReferenceID = ReferenceID + :amount");
  $query->execute([
    'amount' => $amount,
  ]);

  $query = $db->prepare("UPDATE targetedLists SET ID = ID + :amount, Tenant = :tenant");
  $query->execute([
    'amount' => $amount,
    'tenant' => $tenant,
  ]);

  $query = $db->prepare("UPDATE teamManagers SET `User` = `User` + :amount, Gala = Gala + :amount");
  $query->execute([
    'amount' => $amount,
  ]);

  $query = $db->prepare("UPDATE tenantOptions SET ID = ID + :amount, Tenant = :tenant");
  $query->execute([
    'amount' => $amount,
    'tenant' => $tenant,
  ]);

  $query = $db->prepare("UPDATE tenants SET ID = :tenant");
  $query->execute([
    'tenant' => $tenant,
  ]);

  // $query = $db->prepare("UPDATE timesIndividual SET ID = ID + :amount, MemberID = MemberID + :amount");
  // $query->execute([
  //   'amount' => $amount,
  // ]);

  $query = $db->prepare("UPDATE trainingLogs SET ID = ID + :amount, Member = Member + :amount");
  $query->execute([
    'amount' => $amount,
  ]);

  $query = $db->prepare("UPDATE userLogins SET ID = ID + :amount, UserID = UserID + :amount");
  $query->execute([
    'amount' => $amount,
  ]);

  $query = $db->prepare("UPDATE `userOptions` SET ID = ID + :amount, `User` = `User` + :amount");
  $query->execute([
    'amount' => $amount,
  ]);

  $query = $db->prepare("UPDATE users SET UserID = UserID + :amount, Tenant = :tenant");
  $query->execute([
    'amount' => $amount,
    'tenant' => $tenant,
  ]);

  $db->commit();

} catch (Exception $e) {
  $db->rollBack();
  pre($e);
}

$db->query("SET FOREIGN_KEY_CHECKS=1;");