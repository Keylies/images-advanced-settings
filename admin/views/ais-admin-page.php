<div class="wrap ais">
    <h1><?php echo get_admin_page_title(); ?></h1>

    <ul class="tabs" role="tablist">
        <?php $i = 0; foreach ( $sections as $section_key => $section_title ) : ?>
            <li class="tab" role="presentation">
                <a 
                    role="tab" 
                    href="#<?php echo $section_key; ?>" 
                    aria-controls="<?php echo $section_key; ?>" 
                    <?php echo $i === 0 ? ' aria-selected="true"' : ' tabindex="-1"'; ?>
                >
                    <?php echo $section_title; ?>
                </a>
            </li>
        <?php $i++; endforeach; ?>
    </ul>

    <p id="result-message"></p>

    <?php $i = 0; foreach ( $sections as $section_key => $section_title ) : ?>
        <div
            id="<?php echo $section_key; ?>" 
            class="tab-panel" 
            role="tabpanel" 
            aria-labelledby="<?php echo $section_key; ?>-tab" 
            <?php if ( $i !== 0 ) echo ' hidden'; ?>
        >
            <h2><?php echo $section_title; ?></h2>

            <?php include AIS_Admin_Helpers::get_view('ais-admin-section-' . $section_key); ?>
        </div>
    <?php $i++; endforeach; ?>

    <div id="logs-container">
        <progress id="logs-bar"></progress>
        <p id="logs-status"></p>
        <ol id="logs" class="logs"></ol>
    </div>

    <div id="remove-modal" class="remove__modal" aria-hidden="true">
        <p class="modal__title"><?php _e( 'Do you really want to remove this image size ?', 'wpas' ); ?></p>
        <div class="modal__input-container">
            <input type="checkbox" id="remove-images-checkbox" name="remove_images" checked>
            <label for="remove-images-checkbox"><?php _e( 'Remove generated images of this size too', 'wpas' ); ?></label>
        </div>
        <div class="modal__buttons">
            <button id="cancel-remove-button" class="button modal__button"><?php _e( 'Cancel', 'wpas' ); ?></button>
            <button id="confirm-remove-button" class="button button-primary modal__button"><?php _e( 'Remove', 'wpas' ); ?></button>
        </div>
    </div>
</div>

<?php
//$this->debug();
?>
