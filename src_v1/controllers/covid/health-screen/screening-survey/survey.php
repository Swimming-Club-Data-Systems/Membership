<?php

$db = app()->db;
$tenant = app()->tenant;
$user = app()->user;

$getMember = $db->prepare("SELECT MemberID, UserID, MForename, MSurname FROM members WHERE MemberID = ? AND Tenant = ?");
$getMember->execute([
  $id,
  $tenant->getId(),
]);

$member = $getMember->fetch(PDO::FETCH_ASSOC);

if (!$member) {
  halt(404);
}

$getCountRep = $db->prepare("SELECT COUNT(*) FROM squadMembers WHERE Member = ? AND Squad IN (SELECT Squad FROM squadReps WHERE User = ?)");
$getCountRep->execute([
  $id,
  $user->getId(),
]);
$rep = $getCountRep->fetchColumn() > 0;

if (!$rep && !$user->hasPermission('Admin') && !$user->hasPermission('Coach') && !$user->hasPermission('Galas')) {
  if ($member['UserID'] != $_SESSION['TENANT-' . app()->tenant->getId()]['UserID']) {
    halt(404);
  }
}

$pagetitle = htmlspecialchars(\SCDS\Formatting\Names::format($member['MForename'], $member['MSurname'])) . ' - COVID Health Screening';

include BASE_PATH . 'views/header.php';

?>

<style>
  .list-with-border {
    list-style-position: inside;
    display: block;
    padding-left: 0;
  }

  .list-with-border>li {
    padding: 1rem;
    background: var(--light);
    margin: 0 0 1rem 0;
    border-radius: .25rem;
  }

  .list-with-border>li::marker {
    text-align: left;
    font-weight: bold;
    padding: 0;
    margin: 0;
    display: block;
    margin: 0 0 0.5rem 0;
  }
</style>

<div class="bg-light mt-n3 py-3 mb-3">
  <div class="container-xl">

    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl('covid')) ?>">COVID</a></li>
        <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl('covid/health-screening')) ?>">Screening</a></li>
        <li class="breadcrumb-item active" aria-current="page">Survey</li>
      </ol>
    </nav>

    <div class="row align-items-center">
      <div class="col-lg-8">
        <h1>
          COVID-19 Health Screening <small class="text-muted"><?= htmlspecialchars(\SCDS\Formatting\Names::format($member['MForename'], $member['MSurname'])) ?></small>
        </h1>
        <p class="lead mb-0">
          The purpose of this screen is to inform and make you aware of the risks involved in returning to training.
        </p>
      </div>
      <div class="col">
        <img src="<?= htmlspecialchars(autoUrl('public/img/corporate/se.png')) ?>" class="w-50 ms-auto d-none d-lg-flex" alt="Swim England Logo">
      </div>
    </div>
  </div>
</div>

<div class="container-xl">

  <div class="row">

    <div class="col">

      <?php if (isset($_SESSION['CovidHealthSurveyError'])) { ?>
        <div class="alert alert-success">
          <p class="mb-0">
            <strong>There was a problem saving your COVID-19 health survey</strong>
          </p>
          <p class="mb-0">
            <?= htmlspecialchars($_SESSION['CovidHealthSurveyError']) ?>
          </p>
        </div>
      <?php unset($_SESSION['CovidHealthSurveyError']);
      } ?>

      <form method="post" class="needs-validation" novalidate>

        <ol class="list-with-border">
          <!-- QUESTION 1 -->
          <li>
            <div class="row">
              <div class="col-lg-8">
                <p>
                  Have you had confirmed Covid-19 infection or any symptoms (listed below) in keeping with Covid-19 in the last three months?
                </p>
                <ul class="mb-3">
                  <li>Fever</li>
                  <li>New, persistent, dry cough</li>
                  <li>Shortness of breath</li>
                  <li>Loss of taste or smell</li>
                  <li>Diarrhoea or vomiting</li>
                  <li>Muscle aches not related to sport/training</li>
                </ul>

                <div class="controls" data-group-name="confirmed-infection">
                  <div class="form-check">
                    <input type="radio" id="confirmed-infection-radio-yes" name="confirmed-infection-radio" class="form-check-input yes-requires-more-radio" value="1" required>
                    <label class="form-check-label" for="confirmed-infection-radio-yes">Yes</label>
                  </div>
                  <div class="form-check">
                    <input type="radio" id="confirmed-infection-radio-no" name="confirmed-infection-radio" class="form-check-input yes-requires-more-radio" value="0">
                    <label class="form-check-label" for="confirmed-infection-radio-no">No</label>
                  </div>
                  <div class="invalid-feedback">
                    Please select yes or no.
                  </div>
                </div>

                <div class="d-none pt-3" id="confirmed-infection-more">
                  <div class="mb-3 mb-0">
                    <label class="form-label" for="confirmed-infection-more-textarea">Please provide details:</label>
                    <textarea class="form-control" name="confirmed-infection-more-textarea" id="confirmed-infection-more-textarea" rows="4"></textarea>
                    <div class="invalid-feedback">
                      Please provide details.
                    </div>
                  </div>
                </div>
              </div>
              <div class="col">
                <aside class="card card-body">
                  <h3><i class="fa fa-info-circle" aria-hidden="true"></i> Help</h3>
                  <p class="mb-0">
                    If 7 days post recovery and no symptoms then a gradual return to exercise is permissible but should persistent symptoms of breathlessness on exertion then you should consult your usual medical practitioner.
                  </p>
                </aside>
              </div>
            </div>
          </li>

          <!-- Question 2 -->
          <li>
            <div class="row">
              <div class="col-lg-8">
                <p>
                  Have you had a known exposure to anyone with confirmed or suspected Covid-19 in the last two weeks? (e.g. close contact, household member)
                </p>

                <div class="controls" data-group-name="exposure">
                  <div class="form-check">
                    <input type="radio" id="exposure-radio-yes" name="exposure-radio" class="form-check-input yes-requires-more-radio" value="1" required>
                    <label class="form-check-label" for="exposure-radio-yes">Yes</label>
                  </div>
                  <div class="form-check">
                    <input type="radio" id="exposure-radio-no" name="exposure-radio" class="form-check-input yes-requires-more-radio" value="0">
                    <label class="form-check-label" for="exposure-radio-no">No</label>
                  </div>
                  <div class="invalid-feedback">
                    Please select yes or no.
                  </div>
                </div>

                <div class="d-none pt-3" id="exposure-more">
                  <div class="mb-3 mb-0">
                    <label class="form-label" for="exposure-more-textarea">Please provide details:</label>
                    <textarea class="form-control" name="exposure-more-textarea" id="exposure-more-textarea" rows="4"></textarea>
                    <div class="invalid-feedback">
                      Please provide details.
                    </div>
                  </div>
                </div>
              </div>
              <div class="col">
                <aside class="card card-body">
                  <h3><i class="fa fa-info-circle" aria-hidden="true"></i> Help</h3>
                  <p class="mb-0">
                    Not allowed to train until they have self-isolated for 10 days.
                  </p>
                </aside>
              </div>
            </div>
          </li>

          <!-- Question 3 -->
          <li>
            <div class="row">
              <div class="col-lg-8">
                <p>
                  Do you have any underlying medical conditions?
                </p>
                <p>
                  (Examples include: chronic respiratory conditions including asthma; chronic heart, kidney, liver or neurological conditions; diabetes mellitus; a spleen or immune system condition; currently taking medicines that affect your immune system such as steroid tablets)
                </p>

                <div class="controls" data-group-name="underlying-medical">
                  <div class="form-check">
                    <input type="radio" id="underlying-medical-radio-yes" name="underlying-medical-radio" class="form-check-input yes-requires-more-radio" value="1" required>
                    <label class="form-check-label" for="underlying-medical-radio-yes">Yes</label>
                  </div>
                  <div class="form-check">
                    <input type="radio" id="underlying-medical-radio-no" name="underlying-medical-radio" class="form-check-input yes-requires-more-radio" value="0">
                    <label class="form-check-label" for="underlying-medical-radio-no">No</label>
                  </div>
                  <div class="invalid-feedback">
                    Please select yes or no.
                  </div>
                </div>

                <div class="d-none pt-3" id="underlying-medical-more">
                  <div class="mb-3 mb-0">
                    <label class="form-label" for="underlying-medical-more-textarea">Please provide details:</label>
                    <textarea class="form-control" name="underlying-medical-more-textarea" id="underlying-medical-more-textarea" rows="4"></textarea>
                    <div class="invalid-feedback">
                      Please provide details.
                    </div>
                  </div>
                </div>
              </div>
              <div class="col">
                <aside class="card card-body">
                  <h3><i class="fa fa-info-circle" aria-hidden="true"></i> Help</h3>
                  <p class="mb-0">
                    If you have an underlying medical condition that makes you more susceptible to poor outcomes with COVID-19 (including age 65+) then you should consider the increased risk and may want to discuss this with you usual medical practitioner
                  </p>
                </aside>
              </div>
            </div>
          </li>

          <!-- Question 4  -->
          <li>
            <div class="row">
              <div class="col-lg-8">
                <p>
                  Do you or your child (if they are returning) live with or will you knowingly come into close contact with someone who is currently medically vulnerable if you return to the training environment?
                </p>

                <div class="controls" data-group-name="live-with-shielder">
                  <div class="form-check">
                    <input type="radio" id="live-with-shielder-radio-yes" name="live-with-shielder-radio" class="form-check-input yes-requires-more-radio" value="1" required>
                    <label class="form-check-label" for="live-with-shielder-radio-yes">Yes</label>
                  </div>
                  <div class="form-check">
                    <input type="radio" id="live-with-shielder-radio-no" name="live-with-shielder-radio" class="form-check-input yes-requires-more-radio" value="0">
                    <label class="form-check-label" for="live-with-shielder-radio-no">No</label>
                  </div>
                  <div class="invalid-feedback">
                    Please select yes or no.
                  </div>
                </div>

                <div class="d-none pt-3" id="live-with-shielder-more">
                  <div class="mb-3 mb-0">
                    <label class="form-label" for="live-with-shielder-more-textarea">Please provide details:</label>
                    <textarea class="form-control" name="live-with-shielder-more-textarea" id="live-with-shielder-more-textarea" rows="4"></textarea>
                    <div class="invalid-feedback">
                      Please provide details.
                    </div>
                  </div>
                </div>
              </div>
              <div class="col">
                <aside class="card card-body">
                  <h3><i class="fa fa-info-circle" aria-hidden="true"></i> Help</h3>
                  <p class="mb-0">
                    This is an individual call but awareness of risks and the appropriate precautions should be taken. One dose of the vaccine significantly reduces the risk to a vulnerable person.
                  </p>
                </aside>
              </div>
            </div>
          </li>

          <!-- Question 5 -->
          <li>
            <div class="row">
              <div class="col-lg-8">
                <p>
                  Do you fully understand the information presented in the Covid-19 Return To Training briefing and accept the risks associated with returning to the training environment in relation to the Covid-19 pandemic?
                </p>

                <div class="controls" data-group-name="understand-return">
                  <div class="form-check">
                    <input type="radio" id="understand-return-radio-yes" name="understand-return-radio" class="form-check-input no-requires-more-radio" value="1" required>
                    <label class="form-check-label" for="understand-return-radio-yes">Yes</label>
                  </div>
                  <div class="form-check">
                    <input type="radio" id="understand-return-radio-no" name="understand-return-radio" class="form-check-input no-requires-more-radio" value="0">
                    <label class="form-check-label" for="understand-return-radio-no">No</label>
                  </div>
                  <div class="invalid-feedback">
                    Please select yes or no.
                  </div>
                </div>

                <div class="d-none pt-3" id="understand-return-more">
                  <div class="mb-3 mb-0">
                    <label class="form-label" for="understand-return-more-textarea">Please provide details:</label>
                    <textarea class="form-control" name="understand-return-more-textarea" id="understand-return-more-textarea" rows="4"></textarea>
                    <div class="invalid-feedback">
                      Please provide details.
                    </div>
                  </div>
                </div>
              </div>
              <div class="col">
                <aside class="card card-body">
                  <h3><i class="fa fa-info-circle" aria-hidden="true"></i> Help</h3>
                  <p class="mb-0">
                    Additional explanation required in this circumstance and if understanding is not forthcoming they should be advised not to train.
                  </p>
                </aside>
              </div>
            </div>
          </li>

          <!-- Question 6 -->
          <li>
            <div class="row">
              <div class="col-lg-8">
                <p>
                  Able to train?
                </p>

                <div class="controls" data-group-name="able-to-train-return">
                  <div class="form-check">
                    <input type="radio" id="able-to-train-radio-yes" name="able-to-train-radio" class="form-check-input" value="1" required>
                    <label class="form-check-label" for="able-to-train-radio-yes">Yes</label>
                  </div>
                  <div class="form-check">
                    <input type="radio" id="able-to-train-radio-no" name="able-to-train-radio" class="form-check-input" value="0">
                    <label class="form-check-label" for="able-to-train-radio-no">No</label>
                  </div>
                  <div class="invalid-feedback">
                    Please select yes or no.
                  </div>
                </div>
              </div>
            </div>
          </li>

          <!-- Question 7 -->
          <li>
            <div class="row">
              <div class="col-lg-8">
                <p>
                  Sought medical advice?
                </p>

                <div class="controls" data-group-name="sought-advice">
                  <div class="form-check">
                    <input type="radio" id="sought-advice-radio-yes" name="sought-advice-radio" class="form-check-input" value="1" required>
                    <label class="form-check-label" for="sought-advice-radio-yes">Yes</label>
                  </div>
                  <div class="form-check">
                    <input type="radio" id="sought-advice-radio-no" name="sought-advice-radio" class="form-check-input" value="0">
                    <label class="form-check-label" for="sought-advice-radio-no">No</label>
                  </div>
                  <div class="invalid-feedback">
                    Please select yes or no.
                  </div>
                </div>
              </div>
            </div>
          </li>

          <!-- Question 8 -->
          <li class="d-none" id="sought-advice-more">
            <div class="row">
              <div class="col-lg-8">
                <p>
                  Medical advice received?
                </p>

                <div class="controls" data-group-name="advice-received">
                  <div class="form-check">
                    <input type="radio" id="advice-received-radio-yes" name="advice-received-radio" class="form-check-input yes-requires-more-radio" value="1">
                    <label class="form-check-label" for="advice-received-radio-yes">Yes</label>
                  </div>
                  <div class="form-check">
                    <input type="radio" id="advice-received-radio-no" name="advice-received-radio" class="form-check-input yes-requires-more-radio" value="0">
                    <label class="form-check-label" for="advice-received-radio-no">No</label>
                  </div>
                  <div class="invalid-feedback">
                    Please select yes or no.
                  </div>
                </div>

                <div class="d-none pt-3" id="advice-received-more">
                  <div class="mb-3 mb-0">
                    <label class="form-label" for="advice-received-more-textarea">Please provide details:</label>
                    <textarea class="form-control" name="advice-received-more-textarea" id="advice-received-more-textarea" rows="4"></textarea>
                    <div class="invalid-feedback">
                      Please provide details.
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </li>
        </ol>

        <p>
          By signing this form I consent to <?= htmlspecialchars($tenant->getName()) ?> using my/my child's personal data for the protection and safeguarding of my/my child's health as well as safeguarding wider public health in response to the impact of COVID-19 on club training activities. I understand that <?= htmlspecialchars($tenant->getName()) ?> may still have a lawful need to use this information for such purposes even if I later seek to withdraw this consent.
        </p>

        <p>
          For further details of how we process your personal data or your child’s personal data please view our <a href="<?= htmlspecialchars(autoUrl('privacy')) ?>" target="_blank">Privacy Policy</a>.
        </p>

        <p>
          <button type="submit" class="btn btn-success">
            Submit screening form
          </button>
        </p>
      </form>
    </div>

  </div>
</div>

<?php

$footer = new \SCDS\Footer();
$footer->addJs('public/js/covid-health-screen/form.js');
$footer->addJs('public/js/NeedsValidation.js');
$footer->render();
