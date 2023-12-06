<?php
/**
 * Article_Voting class
 */

 class Article_Voting {

    public function __construct() {
        // Register hooks
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_styles'));
        add_action('wp_ajax_submit_vote', array($this, 'submit_vote'));
        add_action('wp_ajax_nopriv_submit_vote', array($this, 'submit_vote'));
        add_action('add_meta_boxes', array($this, 'add_meta_box'));

        // Add filter to insert custom HTML at the end of the article
        add_filter('the_content', array($this, 'insert_article_vote'));

        // Register activation and deactivation hooks
        register_activation_hook(__FILE__, array($this, 'activate_plugin'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate_plugin'));

        // Add settings page
        add_action('admin_menu', array($this, 'add_settings_page'));

        // Register settings
        add_action('admin_init', array($this, 'register_settings'));

        add_filter( 'plugin_action_links_' . PLUGIN_NAME, array($this, 'settings_link') );

        // Output custom styles in the head
        add_action('wp_head', array($this, 'output_custom_styles'));
    }

    /**
     * Add settings links
     */
    public function settings_link( $links ) {
        $settings_link = '<a href="admin.php?page=article_voting_settings">Settings</a>';
        array_unshift($links, $settings_link);
        return $links;
    }

    /**
     * Enqueue plugin admin styles
     */
    public function enqueue_admin_styles() {
        wp_enqueue_style('article_voting_admin_styles', PLUGIN_DIR_URL . 'assets/css/article-voting-admin-style.min.css', [], PLUGIN_VERSION, 'all');
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
     * A metod to set a cookie for tracking already voted users
     */
    public function has_user_already_voted($post_id) {
        // Check if the user has already voted on this post using a cookie
        $cookie_key = 'article_voting_' . $post_id;

        if ( isset($_COOKIE[$cookie_key]) ) {
            $cookie_data_strip = stripslashes($_COOKIE[$cookie_key]);
            return json_decode($cookie_data_strip);
        }

        return false;
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

    /**
     * Process votes by validating them and update article post meta values
     */
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

        // Prepare cookie
        $cookie_key = 'article_voting_' . $post_id;

        // Serialize relevant information into a string
        $cookie_data = json_encode(array('post_id' => $post_id, 'vote_value' => $vote_value));

        // Set a cookie to indicate that the user has voted
        setcookie($cookie_key, $cookie_data, time() + 24 * 60 * 60, COOKIEPATH, COOKIE_DOMAIN);
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

    /**
     * A metod to calculate voting percentages
     */
    public function calculate_voting_percentage($post_id) {
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

    public function add_settings_page() {
        add_menu_page(
            'Article Voting Settings',
            'Article Voting',
            'manage_options',
            'article_voting_settings',
            array($this, 'render_settings_page'),
            'dashicons-thumbs-up',
            100
        );
    }

    public function render_settings_page() {
        ?>
        <div class="wrap">
            <h1>Article Voting Settings</h1>
            <?php settings_errors(); ?>

            <h2 class="nav-tab-wrapper">
                <a href="?page=article_voting_settings" class="nav-tab <?php echo (!isset($_GET['tab']) || $_GET['tab'] === 'general') ? 'nav-tab-active' : ''; ?>">General</a>
                <a href="?page=article_voting_settings&tab=appearance" class="nav-tab <?php echo (isset($_GET['tab']) && $_GET['tab'] === 'appearance') ? 'nav-tab-active' : ''; ?>">Appearance</a>
            </h2>

            <div class="article-voting-admin-wrapper">
                <div class="row">
                    <div class="col-md-8">
                        <div class="content-box">
                        <?php
                            if (!isset($_GET['tab']) || $_GET['tab'] === 'general') {
                                ?>
                                <form method="post" action="options.php">
                                    <?php
                                        // Output the settings fields for the General tab
                                        settings_fields('article_voting_settings');
                                        do_settings_sections('article_voting_settings_general');
                                        submit_button();
                                    ?>
                                </form>
                                <?php
                            } elseif ($_GET['tab'] === 'appearance') {
                                ?>
                                <form method="post" action="options.php">
                                    <?php
                                        // Output the settings fields for the Appearance tab
                                        settings_fields('article_voting_settings');
                                        do_settings_sections('article_voting_settings_appearance');
                                        submit_button();
                                    ?>
                                </form>
                                <?php
                            }
                            ?>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="content-box">
                            <div class="article-voting-sidebar text-content">
                                <h2 class="av-heading">Article Voting Plugin</h2>
                                <hr>

                                <p>
                                    This user-friendly and secure tool seamlessly integrates into WordPress, allowing readers to cast their votes with a simple click.
                                </p>

                                <p>
                                    <img src="<?php echo PLUGIN_DIR_URL; ?>assets/images/preview.png" alt="Article Voting Preview">
                                </p>

                                <h3 class="av-heading">Key Features</h3>

                                <ul>
                                    <li>Voting System: Implement a straightforward voting system with two actions - Yes and No.</li>
                                    <li>Real-time Results: Dynamically display voting results as an average percentage.</li>
                                    <li>Ajax Integration: Utilize Ajax requests for a smooth and seamless voting process.</li>
                                    <li>Admin Insights: View voting results directly from the WordPress admin area.</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    public function register_settings() {
        // General tab settings
        register_setting('article_voting_settings', 'article_voting_general_label');
        register_setting('article_voting_settings', 'article_voting_delete_on_uninstall');

        add_settings_section(
            'article_voting_general_section',
            'General Settings',
            array($this, 'render_general_section'),
            'article_voting_settings_general'
        );

        add_settings_field(
            'article_voting_general_label',
            'Widget Text Label',
            array($this, 'render_text_label_field'),
            'article_voting_settings_general',
            'article_voting_general_section'
        );

        // Appearance tab settings
        register_setting('article_voting_settings', 'article_voting_custom_css');
        register_setting('article_voting_settings', 'article_voting_yes_button_color');
        register_setting('article_voting_settings', 'article_voting_no_button_color');

        add_settings_section(
            'article_voting_appearance_section',
            'Appearance Settings',
            array($this, 'render_appearance_section'),
            'article_voting_settings_appearance'
        );

        add_settings_field(
            'article_voting_custom_css',
            'Custom CSS',
            array($this, 'render_custom_css_field'),
            'article_voting_settings_appearance',
            'article_voting_appearance_section'
        );
    }

    // Helper methods for rendering settings fields

    public function render_general_section() {
        echo '<p>General settings for the Article Voting plugin.</p>';
    }

    public function render_appearance_section() {
        echo '<p>Appearance settings for the Article Voting plugin.</p>';
    }

    public function render_text_label_field() {
        $label = get_option('article_voting_general_label', '');
        echo '<input type="text" name="article_voting_general_label" value="' . esc_attr($label) . '" placeholder="Was this article helpful?" />';
    }

    public function render_custom_css_field() {
        $custom_css = get_option('article_voting_custom_css', '');
        echo '<textarea name="article_voting_custom_css" rows="5" cols="50" placeholder=".vp-helpfull-label {color: red}">' . esc_textarea($custom_css) . '</textarea>';
    }

    public function output_custom_styles() {
        $custom_css = get_option('article_voting_custom_css', ''); // Retrieve saved custom CSS
        echo '<style id="article-voting-custom-styles">' . esc_html($custom_css) . '</style>';
    }

    public function activate_plugin() {
        // flush rewrite rules
        flush_rewrite_rules();
    }

    public function deactivate_plugin() {
        // flush rewrite rules
        flush_rewrite_rules();
    }
}
