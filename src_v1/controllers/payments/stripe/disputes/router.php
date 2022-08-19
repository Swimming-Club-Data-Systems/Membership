<?php

/**
 * Stripe Dispute Pages
 * 
 * Note - We don't handle evidence submission for disputes via our system
 * Instead we only display their status and use that to keep our info right
 */

$this->get('/', function() {
  include 'home.php';
});