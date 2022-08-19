<?php

class ParsedownForMembership extends ParsedownExtra {

  private $allHeaders;
  private $firstElementReturned;

  protected function inlineImage($excerpt) {
    $image = parent::inlineImage($excerpt);

    if (!isset($image)) {
      return null;
    }

    $image['element']['attributes']['src'] = $image['element']['attributes']['src'];

    return $image;
  }

  protected function blockHeader($Line) {
    $Block = parent::blockHeader($Line);
    if (isset($Block['element']['handler']['argument']) && preg_match('/[ #]*{('.$this->regexAttribute.'+)}[ ]*$/', $Block['element']['handler']['argument'], $matches, PREG_OFFSET_CAPTURE))
    {
      $attributeString = $matches[1][0];
      $Block['element']['attributes'] = $this->parseAttributeData($attributeString);
      $Block['element']['handler']['argument'] = substr($Block['element']['handler']['argument'], 0, $matches[0][1]);
    }

    $Block['element']['attributes']['id'] = mb_strtolower(preg_replace('@[^0-9a-z\.]+@i', '-', $Block['element']['text']));

    if ($this->allHeaders === null) {
      $this->allHeaders = [];
    }
    $this->allHeaders[] = $Block['element']['text'];

    if (sizeof($this->allHeaders) > 1) {
      return $Block;
    } else {
      $Block['element']['name'] = 'span';
      $Block['element']['text'] = 'Christopher Heppell, Chester-le-Street ASC and Swimming Club Data Systems';
      $Block['element']['attributes'] = [
        'class' => 'd-none vcard author',
        'rel' => 'author',
      ];
      return $Block;
    }
  }

  protected function blockSetextHeader($Line, array $Block = null) {
    return;
  }


  public function getHeadings() {
    return $this->allHeaders;
  }

}