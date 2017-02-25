<?php

namespace Drupal\br_migrations\Plugin\migrate_plus\data_fetcher;


use Drupal\migrate\MigrateException;
use Drupal\migrate_plus\Plugin\migrate_plus\data_fetcher\File;

/**
 * Retrieve data from a local path or general URL for migration.
 *
 * @DataFetcher(
 *   id = "xml_dir_concat",
 *   title = @Translation("Directory Concatenator")
 * )
 */
class XMLDirConcat extends File {

  /**
   * Return Http Response object for a given url.
   *
   * @param $url
   *   URL to retrieve from.
   *
   * @return \Psr\Http\Message\ResponseInterface
   */
  public function getResponse($url) {
    // Because we use file paths relative to the drupal root,
    // we add the drupal root here.
    $url = DRUPAL_ROOT . $url;
      $files = scandir($url);
    // This is a quick hack to combine a number of xml files in a directory
    // we merge them, then add one <?xml... tag on top, and wrap everything
    // inside <items> tag, so migrations will need to add this to their
    // item_selector!
    $output = [
      "<?xml version=\"1.0\" encoding=\"utf-8\"?>",
      "<items>",
    ];
    if ($files) {
      foreach ($files as $file_name) {
        $file_to_check = $url . DIRECTORY_SEPARATOR;
        if (preg_match("/.*xml$/", $file_name)) {
          $file_to_check .= $file_name;
        }
        else {
          continue;
        }

        // Make sure that only files make it here!
        if (is_file($file_to_check)) {
          $file_contents = file_get_contents($file_to_check);
          // NOTE: This is REALLY UGLY, but due to time limitations we have to
          // do the following:
          // data times don't have any mapping code that can be used for mapping
          // instead, the person who exported the products, thought it would be
          // nice to map the products on filename, so we have to add a Filename
          // element to each product. so that we can actually do the mapping.
          $file_contents = substr($file_contents, strpos($file_contents, "<Product>") + 9);
          $output[] = "<Product>";
          $output[] = "<FileName>". $file_name ."</FileName>";
          $output[] = $file_contents;
        }
      }
      $output[] = "</items>";

      return implode("", $output);
    }

    throw new MigrateException(sprintf('The Directory %s is empty or does not match given pattern', $url));
  }
}