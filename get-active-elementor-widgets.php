<?php

/**
 * Plugin Name: Get active elementor widgets
 * Description: Shows the widgets are currently used by elementor
 * Version: 0.1
 * Author: Konstantin
 * Author URI: https://kskonovalov.me
 * Text Domain: gaew
 */

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}
define( 'GAEW_URL', plugin_dir_url( __FILE__ ) );

add_action( 'admin_menu', 'gaew_get_page' );
function gaew_get_page() {
  add_submenu_page(
    'tools.php',
    'Get active elementor widgets',
    'Elementor widgets',
    'manage_options',
    'get-active-elementor-widgets',
    'gaew_main_func'
  );
}

//settings page
function gaew_main_func() {
  // output
  ?>
    <div class="wrap">
        <h2>Used / unused elementor widgets</h2>
        1. Get registered widgets
        2. Get used widgets
        ---
        3. Get unused widgets
        4. Get unused widgets by plugin name
      <?php
      $otherCategoryName = "other";

      // Get Registered widgets
      $registeredWidgetsData = \Elementor\Plugin::instance()->widgets_manager->get_widget_types_config( [] );
      //    VAR_DUMP($registeredWidgetsData);
      $registeredWidgets = [];
      foreach ( $registeredWidgetsData as $widgetID => $widgetFields ) {
        if ( isset( $widgetFields["categories"] ) && is_array( $widgetFields["categories"] ) ) {
          foreach ( $widgetFields["categories"] as $category ) {
            $registeredWidgets[ $category ][] = [
              "id"    => $widgetID,
              "title" => $widgetFields["title"]
            ];
          }
        } else {
          $registeredWidgets[ $otherCategoryName ][] = $widgetID;
        }
      }
      //      VAR_DUMP( $registeredWidgets );
      //    $registeredWidgets = array_keys($registeredWidgetsData);

      // get post meta
      $postID        = 15368;
      $elementorData = get_post_meta( $postID, '_elementor_data', true );
      $usedWidgets   = [];
      if ( ! empty( $elementorData ) ) {
        $elementorJson = json_decode( $elementorData, true );

        array_walk_recursive( $elementorJson, function ( $value, $key ) use ( &$usedWidgets ) {
          if ( $key === 'widgetType' ) {
            $usedWidgets[] = $value;
          }
        } );
      }

      //      VAR_DUMP($registeredWidgets, $usedWidgets);
      $usedColor = "#b35082";
      $unusedColor = "#71b350";
      $usedIcon = "&check;";
      $unusedIcon = "&cross;";
      echo '<table cellspacing="0" cellpadding="0">';
      foreach ( $registeredWidgets as $categoryName => $category ) {
        echo "<tr><th colspan='2'>{$categoryName}</th></tr>";
        foreach ( $category as $widget ) {
          if ( empty( $widget["id"] ) ) {
            // TODO: check skipped?
            continue;
          }

          $title = "";
          if ( ! empty( $widget["title"] ) ) {
            $title = $widget["title"];
          } else if ( ! empty( $widget["id"] ) ) {
            $title = $widget["id"];
          }
          if(in_array($widget["id"], $usedWidgets, true)) {
              $statusColor = $usedColor;
              $statusIcon = $usedIcon;
          } else {
            $statusColor = $unusedColor;
            $statusIcon = $unusedIcon;
          }

          echo "<tr><td style='color: {$statusColor};'>{$statusIcon}</td><td style='color: {$statusColor};'>{$title}</td></tr>";
        }
      }
      echo '</table>';

      //    VAR_DUMP($usedWidgets);
      //    $diff = array_diff($registeredWidgets, $usedWidgets);
      //    VAR_DUMP($diff);
      ?>
    </div>
  <?php
}
