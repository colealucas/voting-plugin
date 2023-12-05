<?php
/**
 * Plugin Name: Votin Plugin
 * Description: Allow visitors to vote on articles with yes or no if the article was helpful.
 * Version: 1.0
 * Author: Nicolae Lucas
 */

 // Keep track of plugin version
 define('PLUGIN_VERSION', '1.0.0');
 define('PLUGIN_DIR_URL', plugin_dir_url(__FILE__));
 define('PLUGIN_DIR_PATH', plugin_dir_path(__FILE__));

// Include the main class file
require_once PLUGIN_DIR_PATH . '/src/class-article-voting.php';

// Instantiate the main class
$article_voting = new Article_Voting();
