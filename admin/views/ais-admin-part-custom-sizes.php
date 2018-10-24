<?php if ( !empty( $this->data['sizes'] ) ) : ?>
    <form id="update-form">
        <table class="form-table">
            <tbody id="sizes-rows">
                <?php foreach ( $this->data['sizes'] as $i => $size ) : 
                    $name = esc_attr( $size['name'] );
                    $id_name = sanitize_title( $name );
                ?>
                    <tr>
                        <th scope="row">
                            <?php echo $name; ?>
                            <input type="hidden" name="updated_sizes[name][]" value="<?php echo esc_attr( $size['name'] ); ?>" required>
                        </th>
    
                        <td>
                            <fieldset>
                                <label for="<?php echo $id_name . '_size_width'; ?>"><?php _e( 'Width', 'wpas' ); ?></label>
                                <input type="number" id="<?php echo $id_name . '_size_width'; ?>" class="small-text" name="updated_sizes[width][]" value="<?php echo esc_attr( $size['width'] ); ?>" required>
                                <br>
                                <label for="<?php echo $id_name . '_size_height'; ?>"><?php _e( 'Height', 'wpas' ); ?></label>
                                <input type="number" id="<?php echo $id_name . '_size_height'; ?>" class="small-text" name="updated_sizes[height][]" value="<?php echo esc_attr( $size['height'] ); ?>" required>
                                <br>
                                <input type="hidden" name="updated_sizes[disabled][<?php echo $i; ?>]" value="0">
                                <input type="checkbox" id="<?php echo $id_name . '_size_disabled'; ?>" name="updated_sizes[disabled][<?php echo $i; ?>]" value="1" <?php checked( $size['disabled'], '1' ); ?>>
                                <label for="<?php echo $id_name . '_size_disabled'; ?>"><?php _e( 'Disabled', 'wpas' ); ?></label>
                                <br>
                                <input type="hidden" name="updated_sizes[crop][<?php echo $i; ?>]" value="0">
                                <input class="crop" id="<?php echo $id_name . '_size_crop'; ?>" type="checkbox" name="updated_sizes[crop][<?php echo $i; ?>]" value="1" <?php checked( $size['crop'], '1' ); ?>>
                                <label for="<?php echo $id_name . '_size_crop'; ?>"><?php _e( 'Crop', 'wpas' ); ?></label>
                                <select class="crop-position" class="small-text" name="updated_sizes[crop_position][]">
                                    <?php foreach ( $crop_positions as $key => $name ) : ?>
                                        <option value="<?php echo $key; ?>" <?php selected( $size['crop_position'], $key ); ?>><?php echo $name; ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <br>
                                <button data-index="<?php echo $i; ?>" class="remove-button"><?php _e( 'Delete', 'wpas' ); ?></button>
                            </fieldset>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <button id="update-submit" class="button button-primary" type="submit" disabled><?php _e( 'Save modifications', 'wpas' ); ?></button>
    </form>
<?php else : ?>
    <p><?php _e( 'No custom sizes', 'wpas' ); ?></p>
<?php endif; ?>