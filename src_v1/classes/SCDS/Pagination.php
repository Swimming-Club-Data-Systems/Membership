<?php

namespace SCDS;

/**
 * Pagination class which provides pagination
 */
class Pagination extends \Zebra_Pagination
{

  private $recordsPerPage;


  /**
   * Create new SCDS Pagination object
   */
  public function __construct()
  {
    parent::__construct();
    $this->reset();
  }

  /**
   * Reset object to default SCDS settings
   */
  public function reset()
  {
    $this->selectable_pages(5);
    $this->labels('Previous', 'Next', 'Page %d of %d');
    $this->records_per_page(10);
    $this->padding(false);
  }

  public function get_limit_start()
  {
    return (($this->get_page() - 1) * $this->recordsPerPage);
  }

  public function get_records_per_page()
  {
    return $this->recordsPerPage;
  }

  public function records_per_page($recordsPerPage)
  {
    $this->recordsPerPage = $recordsPerPage;
    parent::records_per_page($recordsPerPage);
  }

  public function get_page_description() {
    return 'Page ' . $this->get_page() . ' of ' . $this->get_pages();
  }
}
