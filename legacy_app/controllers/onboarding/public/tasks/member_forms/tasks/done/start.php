<?php

if (isset($_SESSION['OnboardingSessionId'])) {
  header('location: ' . autoUrl('onboarding/go'));
} else {
  header('location: ' . autoUrl(''));
}