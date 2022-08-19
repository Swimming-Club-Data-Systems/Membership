<?php

namespace CLSASC\BootstrapComponents;

/**
 * 
 *
 * @copyright Chester-le-Street ASC https://github.com/Chester-le-Street-ASC
 * @author Chris Heppell https://github.com/clheppell
 */
class RenewalProgressListGroup
{
  private $renewal;
  private $orderedPossibleValues = [];

  public function __construct(\Renewal $renewal)
  {
    $this->renewal = $renewal;
    $this->orderedPossibleValues = [
      'account_review' => [
        'url' => 'account-review',
        'id' => 'account-review',
        'progress_name' => 'account-review',
        'name' => 'Account Review',
        'title' => null,
      ],
      'member_review' => [
        'url' => 'member-review',
        'id' => 'member-review',
        'progress_name' => 'member-review',
        'name' => 'Member Review',
        'title' => null,
      ],
      'fee_review' => [
        'url' => 'fee-review',
        'id' => 'fee-review',
        'progress_name' => 'fee-review',
        'name' => 'Fee Review',
        'title' => null,
      ],
      'address_review' => [
        'url' => 'address-review',
        'id' => 'address-review',
        'progress_name' => 'address-review',
        'name' => 'Address Review',
        'title' => null,
      ],
      'emergency_contacts' => [
        'url' => 'emergency-contacts',
        'id' => 'emergency-contacts',
        'progress_name' => 'emergency-contacts',
        'name' => 'Emergency Contacts',
        'title' => null,
      ],
      'medical_forms' => [
        'url' => 'medical-forms',
        'id' => 'medical-forms',
        'progress_name' => 'medical-forms',
        'name' => 'Medical Forms',
        'title' => null,
      ],
      'code_of_conduct' => [
        'url' => 'conduct-forms',
        'id' => 'conduct-forms',
        'progress_name' => 'conduct-forms',
        'name' => 'Conduct Forms',
        'title' => null,
      ],
      'data_protection_and_privacy' => [
        'url' => 'data-protection-and-privacy',
        'id' => 'data-protection-and-privacy',
        'progress_name' => 'data-protection-and-privacy',
        'name' => 'Data Protection and Privacy',
        'title' => null,
      ],
      'terms_and_conditions' => [
        'url' => 'terms-and-conditions',
        'id' => 'terms-and-conditions',
        'progress_name' => 'terms-and-conditions',
        'name' => 'Terms and Conditions',
        'title' => null,
      ],
      'photography_permissions' => [
        'url' => 'photography-permissions',
        'id' => 'photography-permissions',
        'progress_name' => 'photography-permissions',
        'name' => 'Photography Permissions',
        'title' => null,
      ],
      'admin_form' => [
        'url' => 'administration-form',
        'id' => 'administration-form',
        'progress_name' => 'administration-form',
        'name' => 'Administration Form',
        'title' => null,
      ],
      'direct_debit' => [
        'url' => 'direct-debit',
        'id' => 'direct-debit',
        'progress_name' => 'direct-debit',
        'name' => 'Direct Debit',
        'title' => null,
      ],
      'renewal_fee' => [
        'url' => $this->renewal->getTypeName(false) . '-fees',
        'id' => $this->renewal->getTypeName(false) . '-fees',
        'progress_name' => 'renewal-fees',
        'name' => $this->renewal->getTypeName() . ' Fees',
        'title' => null,
      ],
    ];
  }

  public function render($current = null)
  {
    $output = '';
    $listGroupClass = '';

    // if (strlen($this->items->title) > 0) {
    //   $output .= '<div class="position-sticky top-3 card mb-3">
    //     <div class="card-header">' . $this->items->title . '</div>';
    //   $listGroupClass = ' list-group-flush ';
    // }

    $output .= '<div class="list-group ' . $listGroupClass . ' position-sticky top-3">';

    foreach ($this->renewal->getProgress() as $progressObject) {
      if (isset($this->orderedPossibleValues[$progressObject['object']])) {
        $link = $this->orderedPossibleValues[$progressObject['object']];
        $active = '';
        if ($link['id'] == $current) {
          $active = ' active ';
        }

        $target = '';
        // if (isset($link->target) && strlen($link->target) > 0) {
        //   $target = 'target="' . $link->target . '"';
        // } else {
        //   $target = 'target="_self"';
        // }
        $target = 'target="_self"';

        $title = '';
        if ($link['title']) {
          $title = $link['title'];
        } else {
          $title = $link['name'];
        }

        $url = autoUrl('registration-and-renewal/' . $this->renewal->getId() . '/' . $link['url']);

        $status = '<span class="text-warning"><i class="fa fa-fw fa-minus-circle" aria-hidden="true"></i></span>';
        if (isset($progressObject['completed']) && bool($progressObject['completed'])) {
          $status = '<span class="text-success"><i class="fa fa-fw fa-check-circle" aria-hidden="true"></i></span>';
        }

        $output .= '<a href="' . htmlspecialchars($url) . '" ' . $target . ' ' . htmlspecialchars($title) . ' class="list-group-item list-group-item-action d-flex justify-content-between align-items-center ' . $active . '">' . htmlspecialchars($link['name']) . $status . '</a>';
      }
    }

    $output .= '</div>';

    // if (strlen($this->items->title) > 0) {
    //   $output .= '</div>';
    // }

    return $output;
  }

  public static function renderLinks(\Renewal $renewal, $current = null) {
    $group = new RenewalProgressListGroup($renewal);
    return $group->render($current);
  }
}
