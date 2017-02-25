<?php

namespace Drupal\br_migrations\Plugin\migrate_plus\data_parser;

use Drupal\migrate_plus\Plugin\migrate_plus\data_parser\SimpleXml;


/**
 * Obtain XML data for migration using the SimpleXML API.
 *
 * @DataParser(
 *   id = "br_simple_xml",
 *   title = @Translation("Br Simple XML")
 * )
 */
class BrSimpleXml extends  SimpleXml {
  protected function fetchNextRow() {
    $target_element = array_shift($this->matches);

    // If we've found the desired element, populate the currentItem and
    // currentId with its data.
    if ($target_element !== FALSE && !is_null($target_element)) {
      foreach ($this->fieldSelectors() as $field_name => $xpath) {
        foreach ($target_element->xpath($xpath) as $value) {
          if ($value->count() < 1) {
            $this->currentItem[$field_name][] = (string) $value;
          }
          else {
            // we want to maintain the literal expression as it appers on the
            // XML document
            foreach ($value->children() as $child) {
              /** @var \SimpleXMLElement $child */
              $parts = [];
              $parts[] = $child->asXML();
            }
            $this->currentItem[$field_name][] = implode("\n", $parts);
          }
        }
      }
      // Reduce single-value results to scalars.
      foreach ($this->currentItem as $field_name => $values) {
        if (count($values) == 1) {
          $this->currentItem[$field_name] = reset($values);
        }
      }
    }
  }
}