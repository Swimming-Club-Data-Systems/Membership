<?php

namespace SCDS\Checkout;

class Assets {

  public static function cardLogos() {

    $html = '<img class="apple-pay-row" src="' . htmlspecialchars(autoUrl("img/stripe/apple-pay-mark.svg")) . '" aria-hidden="true">';
    $html .= '<img class="google-pay-row" src="' . htmlspecialchars(autoUrl("img/stripe/google-pay-mark.svg")) . '" aria-hidden="true">';
    
    $html .= '<img class="visa-row d-dark-none" src="' . htmlspecialchars(autoUrl("img/stripe/brand-checkout/visa_light.svg")) . '" aria-hidden="true">';
    $html .= '<img class="visa-row d-light-none" src="' . htmlspecialchars(autoUrl("img/stripe/brand-checkout/visa_dark.svg")) . '" aria-hidden="true">';
    
    $html .= '<img class="mastercard-row" src="' . htmlspecialchars(autoUrl("img/stripe/brand-checkout/mastercard_light.svg")) . '" aria-hidden="true">';
    $html .= '<img class="mastercard-row d-dark-none" src="' . htmlspecialchars(autoUrl("img/stripe/brand-checkout/maestro_light.svg")) . '" aria-hidden="true">';
    
    $html .= '<img class="mastercard-row d-light-none" src="' . htmlspecialchars(autoUrl("img/stripe/brand-checkout/maestro_dark.svg")) . '" aria-hidden="true">';

    $html .= '<img class="amex-row" src="' . htmlspecialchars(autoUrl("img/stripe/brand-checkout/amex_light.svg")) . '" aria-hidden="true">';
    
    $html .= '<img class="amex-row" src="' . htmlspecialchars(autoUrl("img/stripe/brand-checkout/discover_light.svg")) . '" aria-hidden="true">';
    
    $html .= '<img class="amex-row" src="' . htmlspecialchars(autoUrl("img/stripe/brand-checkout/diners_light.svg")) . '" aria-hidden="true">';

    $html .= '<img class="amex-row" src="' . htmlspecialchars(autoUrl("img/stripe/brand-checkout/union.svg")) . '" aria-hidden="true">';

    return $html;

  }

  public static function networkLogos() {
    
    $html = '<img class="visa-row d-dark-none" src="' . htmlspecialchars(autoUrl("img/stripe/brand-checkout/visa_light.svg")) . '" aria-hidden="true">';
    $html .= '<img class="visa-row d-light-none" src="' . htmlspecialchars(autoUrl("img/stripe/brand-checkout/visa_dark.svg")) . '" aria-hidden="true">';
    
    $html .= '<img class="mastercard-row" src="' . htmlspecialchars(autoUrl("img/stripe/brand-checkout/mastercard_light.svg")) . '" aria-hidden="true">';
    $html .= '<img class="mastercard-row d-dark-none" src="' . htmlspecialchars(autoUrl("img/stripe/brand-checkout/maestro_light.svg")) . '" aria-hidden="true">';
    
    $html .= '<img class="mastercard-row d-light-none" src="' . htmlspecialchars(autoUrl("img/stripe/brand-checkout/maestro_dark.svg")) . '" aria-hidden="true">';

    $html .= '<img class="amex-row" src="' . htmlspecialchars(autoUrl("img/stripe/brand-checkout/amex_light.svg")) . '" aria-hidden="true">';
    
    $html .= '<img class="amex-row" src="' . htmlspecialchars(autoUrl("img/stripe/brand-checkout/discover_light.svg")) . '" aria-hidden="true">';
    
    $html .= '<img class="amex-row" src="' . htmlspecialchars(autoUrl("img/stripe/brand-checkout/diners_light.svg")) . '" aria-hidden="true">';

    $html .= '<img class="amex-row" src="' . htmlspecialchars(autoUrl("img/stripe/brand-checkout/union.svg")) . '" aria-hidden="true">';

    return $html;

  }

}