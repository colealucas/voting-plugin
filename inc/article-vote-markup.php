
<?php
/**
 * Helpful article html markup
 */

 // Instantiate the main class
$article_voting = new Article_Voting();
$percentage = $article_voting->calculate_voting_percentage(get_the_ID());
$already_voted = $article_voting->has_user_already_voted(get_the_ID());
$voted_class = ($already_voted ? 'voted' : '');
$default_label = ( get_option('article_voting_general_label') ?  get_option('article_voting_general_label') : _('Was this article helpful?'));
$label_text = ($already_voted ? _('Thank you for your feedback.') : $default_label);
$user_response = (is_object($already_voted) && $already_voted->vote_value ? $already_voted->vote_value : '');

?>

<div class="vp-helpful-wrapper">
   <div class="vp-helpfull-label" data-status-label><?php echo $label_text; ?></div>

   <div class="vp-helpfull-buttons <?php echo $voted_class; ?>" data-helpful-buttons-wrapper>
    <div class="vp-helpful-buttons-inner">
        <a href="#" class="vp-helpful-btn <?php echo ($user_response === 'yes' ? 'active' : ''); ?>" data-vp-helpful-button data-action="yes">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 512 512">
                <path fill="#ccc" d="M414.39 97.61A224 224 0 1097.61 414.39 224 224 0 10414.39 97.61zM184 208a24 24 0 11-24 24 23.94 23.94 0 0124-24zm167.67 106.17c-12 40.3-50.2 69.83-95.62 69.83s-83.62-29.53-95.72-69.83a8 8 0 017.83-10.17h175.69a8 8 0 017.82 10.17zM328 256a24 24 0 1124-24 23.94 23.94 0 01-24 24z"/>
            </svg>
            <span class="vp-helpful-btn-text"><?php _e('YES'); ?></span>
            <span class="vp-helpful-btn-feedback" data-positive-percentage><?php echo $percentage['positive']; ?>%</span>
        </a>

        <a href="#" class="vp-helpful-btn <?php echo ($user_response === 'no' ? 'active' : ''); ?>" data-vp-helpful-button data-action="no">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" class="ionicon" viewBox="0 0 512 512">
                <path fill="#ccc" d="M414.39 97.61A224 224 0 1097.61 414.39 224 224 0 10414.39 97.61zM184 208a24 24 0 11-24 24 23.94 23.94 0 0124-24zm-23.67 149.83c12-40.3 50.2-69.83 95.62-69.83s83.62 29.53 95.71 69.83a8 8 0 01-7.82 10.17H168.15a8 8 0 01-7.82-10.17zM328 256a24 24 0 1124-24 23.94 23.94 0 01-24 24z"/>
            </svg>
            <span class="vp-helpful-btn-text"><?php _e('NO'); ?></span>
            <span class="vp-helpful-btn-feedback" data-negative-percentage><?php echo $percentage['negative']; ?>%</span>
        </a>
    </div>
   </div>
</div>
