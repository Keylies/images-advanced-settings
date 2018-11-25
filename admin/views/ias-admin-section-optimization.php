<section>
    <h3><?php _e( 'Lazy loading', 'images-advanced-settings' ); ?></h3>
    <p><?php _e( 'Apply LazyLoad script to load images when they enter the viewport.', 'images-advanced-settings' ); ?></p>

    <form id="lazy-form">
        <div>
            <input type="hidden" name="lazy_enabling" value="0">
            <input type="checkbox" id="lazy-enabling" name="lazy_enabling" value="1" <?php checked( $this->data['lazy_loading'], '1' ); ?>>
            <label for="lazy-enabling"><?php _e( 'Enable', 'images-advanced-settings' ); ?></label>
        </div>

        <button id="lazy-button" class="button button-primary" type="submit" disabled>
            <?php _e( 'Save', 'images-advanced-settings' ); ?>
            <div class="ias-spinner"></div>
        </button>
    </form>

    <p id="lazy-message" class="info-message"></p>
</section>