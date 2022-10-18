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

    /**
     * @param $base_url
     * @param $preserve_query_string
     * @return void
     *
     * Overrides base
     */
    public function base_url($base_url = '', $preserve_query_string = true) {

        // we'll need this in case "variable_name" is an empty string
        // (when "base_url" must be explicitly declared)
        $this->_properties['base_url_explicit'] = $base_url !== '';

        // set the base URL
        $base_url = ($base_url == '' ? '/v1' . $_SERVER['REQUEST_URI'] : $base_url);

        // parse the URL
        $parsed_url = parse_url($base_url);

        // cache the "path" part of the URL (that is, everything *before* the "?")
        $this->_properties['base_url'] = rtrim($parsed_url['path'], '/');

        // cache the "query" part of the URL (that is, everything *after* the "?")
        $this->_properties['base_url_query'] = isset($parsed_url['query']) ? $parsed_url['query'] : '';

        // store query string as an associative array
        parse_str($this->_properties['base_url_query'], $this->_properties['base_url_query']);

        // should query strings (other than those set in $base_url) be preserved?
        $this->_properties['preserve_query_string'] = $preserve_query_string;

    }
}
