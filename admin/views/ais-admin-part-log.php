<li class="log">
    <?php echo wp_get_attachment_image( $result['id'] ); ?>
    <p class="log__title"><?php echo $result['name']; ?> (ID <?php echo $result['id']; ?>)</p>

    <?php if ( !empty( $result['results'] ) ) : ?>
        <ul class="log__results">
            <?php foreach( $result['results'] as $log_result ) : 
                $class = $log_result['success'] ? 'log__result--success' : 'log__result--failure';
            ?>
                <li class="log__result <?php echo $class; ?>">
                    <p><?php echo $log_result['message']; ?></p>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</li>