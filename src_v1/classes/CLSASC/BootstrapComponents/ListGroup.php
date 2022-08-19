<?php

namespace CLSASC\BootstrapComponents;

/**
 * 
 *
 * @copyright Chester-le-Street ASC https://github.com/Chester-le-Street-ASC
 * @author Chris Heppell https://github.com/clheppell
 */
class ListGroup {
  private $items;

  public function __construct($json) {
    $this->items = json_decode($json);
  }

  public function render($current = false) {
    $output = '';
    $listGroupClass = '';

    if (strlen($this->items->title) > 0) {
      $output .= '<div class="position-sticky top-3 card mb-3">
        <div class="card-header">' . $this->items->title . '</div>';
      $listGroupClass = ' list-group-flush ';
    }

    $output .= '<div class="list-group ' . $listGroupClass . '">';

    foreach ($this->items->links as $link) {
      if ((!isset($link->exclude) && !isset($link->include)) || (isset($link->exclude) && !in_array($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'], $link->exclude)) || (isset($link->include) && in_array($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'], $link->include))) {
        $active = '';
        if ($link->id == $current) {
          $active = ' active ';
        }

        $target = '';
        if (isset($link->target) && strlen($link->target) > 0) {
          $target = 'target="' . $link->target . '"';
        } else {
          $target = 'target="_self"';
        }

        $title = '';
        if (isset($link->title) && strlen($link->title) > 0) {
          $target = 'title="' . $link->title . '"';
        } else {
          $target = 'title="' . $link->name . '"';
        }

        $url = $link->link;
        if (!isset($link->external) || !$link->external) {
          $url = autoUrl($link->link);
        }

        $output .= '<a href="' . $url . '" ' . $target . ' ' . $title . ' class="list-group-item list-group-item-action ' . $active . '">' . $link->name . '</a>';
      }
    }

    $output .= '</div>';

    if (strlen($this->items->title) > 0) {
      $output .= '</div>';
    }

    return $output;
  }
}
