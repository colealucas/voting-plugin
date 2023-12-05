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

        // Add filter to insert custom HTML at the end of the article
        add_filter('the_content', array($this, 'insert_article_vote'));
    }

    /**
     * Enqueue plugin scripts and styles
     */
    public function enqueue_scripts() {
        // Enqueue custom script
        wp_enqueue_script('article-voting-script', PLUGIN_DIR_URL . 'assets/js/article-voting-script.min.js', [], PLUGIN_VERSION, true);

        // Localize script variables
        wp_localize_script('article-voting-script', 'article_voting_vars', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('article-voting-nonce'),
            'post_id' => get_the_ID(),
        ));

        // Enqueue custom style
        wp_enqueue_style('article-voting-style', PLUGIN_DIR_URL . 'assets/css/article-voting-style.min.css', [], PLUGIN_VERSION);
    }

    /**
     * Handle Ajax request to submit votes
     */
    public function submit_vote() {
        // Verify nonce to ensure the request is legitimate
        $nonce = isset($_POST['nonce']) ? sanitize_text_field($_POST['nonce']) : '';

        if (!wp_verify_nonce($nonce, 'article-voting-nonce')) {
            die('Invalid nonce');
        }

        // Get the post ID and vote value from the Ajax request
        $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
        $vote_value = isset($_POST['vote_value']) ? sanitize_text_field($_POST['vote_value']) : '';

         // Check if the user has already voted on this post using localStorage
        $localStorageKey = 'article_voting_' . $post_id;
        $lastVoteTimestamp = isset($_COOKIE[$localStorageKey]) ? intval($_COOKIE[$localStorageKey]) : 0;

        // Define a cooldown period (e.g., 24 hours)
        $cooldownPeriod = 24 * 60 * 60; // 24 hours in seconds

        if (time() - $lastVoteTimestamp < $cooldownPeriod) {
            die(json_encode(array(
                'status' => 'error',
                'message' => 'User already voted!',
            )));
        }

        // Process the vote (example: update post meta)
        $this->process_vote($post_id, $vote_value);

        $percentages = $this->calculate_voting_percentage($_POST['post_id']);
        // Return a response (you can customize the response based on your needs)
        echo json_encode(array(
            'status' => 'success',
            'message' => 'Thank you for your feedback.',
            'positive_votes' => $percentages['positive'],
            'negative_votes' => $percentages['negative'],
        ));

        // Always exit to prevent further execution
        exit();
    }

    private function process_vote($post_id, $vote_value) {
        // exit if invalid vote provided
         if (!isset($vote_value)) {
            return false;
        }

        // Example: Update post meta based on vote value
        $current_votes = get_post_meta($post_id, 'article_votes_data', true);

        // set defaults if not defined
        if (!$current_votes) {
            $current_votes = [];
            $current_votes['yes'] = 0;
            $current_votes['no'] = 0;
        }

        $votes = array(
            'yes' => intval($current_votes['yes']), // Number of positive votes
            'no' => intval($current_votes['no']),   // Number of negative votes
        );

        if ($vote_value === 'yes') {
            $votes['yes'] += 1;
        } elseif ($vote_value === 'no') {
            $votes['no'] += 1;
        }

        // Update post meta with the new vote counts
        update_post_meta($post_id, 'article_votes_data', $votes);
    }

    /**
     * Add a meta box for voting results in the admin area
     */
    public function add_meta_box() {
        // Add a meta box for voting results in the admin area
        add_meta_box(
            'article_voting_meta_box',
            'Article Voting Results',
            array($this, 'render_meta_box_content'),
            'post',
            'side', // Adjust the position and context based on your preference
            'high'  // Adjust the priority based on your preference
        );
    }

    /**
     * Render metabox in admin (article edit)
     */
    public function render_meta_box_content($post) {
        // Get voting results for the current post
        $votes = get_post_meta($post->ID, 'article_votes_data', true);

        // Display voting results
        ?>
        <p>
            <strong>Positive Votes:</strong> <?php echo isset($votes['yes']) ? $votes['yes'] : 0; ?>
        </p>

        <p>
            <strong>Negative Votes:</strong> <?php echo isset($votes['no']) ? $votes['no'] : 0; ?>
        </p>
        <?php
    }

    private function calculate_voting_percentage($post_id) {
        $votes = get_post_meta($post_id, 'article_votes_data', true);

        $result = array(
            'positive' => 0,
            'negative' => 0
        );

        if ($votes) {
            // Calculate the total number of votes
            $total_votes = isset($votes['yes']) ? $votes['yes'] + (isset($votes['no']) ? $votes['no'] : 0) : 0;

            // Calculate the percentages
            $result['positive'] = $total_votes > 0 ? intval(round(($votes['yes'] / $total_votes) * 100, 2)) : 0;
            $result['negative'] = $total_votes > 0 ? intval(round(($votes['no'] / $total_votes) * 100, 2)) : 0;
        }

        return $result;
    }

    /**
     * Insert the vote markup
     */
    public function insert_article_vote($content) {
        // Add it only for single posts
        if (is_single()) {
            ob_start();
            include(PLUGIN_DIR_PATH . '/inc/article-vote-markup.php');
            $markup = ob_get_clean();

            // Append custom HTML to the end of the content
            $content .= $markup;
        }

        return $content;
    }
}
