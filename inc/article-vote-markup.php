
<?php
/**
 * Helpful article html markup
 */
?>

<div class="vp-helpful-wrapper">
   <div class="vp-helpfull-label" data-feedback-text="<?php _e('Thank you for your feedback.'); ?>" data-original-text="<?php _e('Was this article helpful?'); ?>" data-status-label><?php _e('Was this article helpful?'); ?></div>

   <div class="vp-helpfull-buttons" data-helpful-buttons-wrapper>
    <div class="vp-helpful-buttons-inner">
        <a href="#" class="vp-helpful-btn" data-vp-helpful-button data-action="yes">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 512 512">
                <path fill="#ccc" d="M414.39 97.61A224 224 0 1097.61 414.39 224 224 0 10414.39 97.61zM184 208a24 24 0 11-24 24 23.94 23.94 0 0124-24zm167.67 106.17c-12 40.3-50.2 69.83-95.62 69.83s-83.62-29.53-95.72-69.83a8 8 0 017.83-10.17h175.69a8 8 0 017.82 10.17zM328 256a24 24 0 1124-24 23.94 23.94 0 01-24 24z"/>
            </svg>
            <span class="vp-helpful-btn-text"><?php _e('YES'); ?></span>
            <span class="vp-helpful-btn-feedback" data-positive-percentage>0%</span>
        </a>

        <a href="#" class="vp-helpful-btn" data-vp-helpful-button data-action="no">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" class="ionicon" viewBox="0 0 512 512">
                <path fill="#ccc" d="M414.39 97.61A224 224 0 1097.61 414.39 224 224 0 10414.39 97.61zM184 208a24 24 0 11-24 24 23.94 23.94 0 0124-24zm-23.67 149.83c12-40.3 50.2-69.83 95.62-69.83s83.62 29.53 95.71 69.83a8 8 0 01-7.82 10.17H168.15a8 8 0 01-7.82-10.17zM328 256a24 24 0 1124-24 23.94 23.94 0 01-24 24z"/>
            </svg>
            <span class="vp-helpful-btn-text"><?php _e('NO'); ?></span>
            <span class="vp-helpful-btn-feedback" data-negative-percentage>0%</span>
        </a>
    </div>
   </div>
</div>
