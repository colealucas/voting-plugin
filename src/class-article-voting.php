<?php
/**
 * Article_Voting class
 */

 class Article_Voting {

    public function __construct() {
        // Register hooks
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('wp_ajax_submit_vote', array($this, 'submit_vote'));
        add_action('wp_ajax_nopriv_submit_vote', array($this, 'submit_vote'));
        add_action('add_meta_boxes', array($this, 'add_meta_box'));
    }

    /**
     * Enqueue plugin scripts and styles
     */
    public function enqueue_scripts() {
        // Enqueue custom script
        wp_enqueue_script('article-voting-script', PLUGIN_DIR_URL . 'assets/js/article-voting-script.min.js', [], PLUGIN_VERSION, true);

        // Enqueue custom style
        wp_enqueue_style('article-voting-style', PLUGIN_DIR_URL . 'assets/css/article-voting-style.min.css', [], PLUGIN_VERSION);
    }

    /**
     * Handle Ajax request to submit votes
     */
    public function submit_vote() {

    }

    /**
     * Add a meta box for voting results in the admin area
     */
    public function add_meta_box() {

    }
}

// Instantiate the class on plugin initialization
$article_voting = new Article_Voting();
