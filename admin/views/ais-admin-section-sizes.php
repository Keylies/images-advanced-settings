<section>
    <p><?php _e( 'Disable default WordPress sizes', 'wpas' ); ?></p>

    <form id="default-form">
        <?php foreach ( $default_sizes as $default_size ) : ?>
            <div>
                <input type="checkbox" id="default-sizes-<?php echo $default_size; ?>" name="default_sizes_disabled[]" value="<?php echo $default_size; ?>" <?php checked( in_array( $default_size, $this->ais_sizes->data['default_sizes_disabled'] ), true ); ?>>
                <label for="default-sizes-<?php echo $default_size; ?>"><?php echo $default_size; ?></label>
            </div>
        <?php endforeach; ?>

        <button id="default-submit" class="button button-primary" type="submit" disabled><?php _e( 'Save', 'wpas' ); ?></button>
    </form>
</section> 

<section>
    <h3><?php _e( 'Add a custom size', 'wpas' ); ?></h2>

    <form id="add-form">
        <input type="hidden" name="new_size[disabled]" value="0">
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="name"><?php _e( 'Name', 'wpas' ); ?></label>
                </th>
                <td>
                    <input type="text" id="name" name="new_size[name]" required>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="width"><?php _e( 'Width', 'wpas' ); ?></label>
                </th>
                <td>
                    <input type="number" id="width" name="new_size[width]" class="small-text" required>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="height"><?php _e( 'Height', 'wpas' ); ?></label>
                </th>
                <td>
                    <input type="number" id="height" name="new_size[height]" class="small-text" required>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="crop"><?php _e( 'Crop', 'wpas' ); ?></label>
                </th>
                <td>
                    <input type="hidden" id="crop" name="new_size[crop]" value="0">
                    <input class="crop" type="checkbox" id="crop" name="new_size[crop]" value="1">
                    <select class="crop-position" name="new_size[crop_position]">
                        <?php foreach ( $crop_positions as $key => $name ) : ?>
                            <option value="<?php echo $key; ?>"><?php echo $name; ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
        </table>

        <button id="add-submit" class="button button-primary" type="submit" disabled><?php _e( 'Add', 'wpas' ); ?></button>
    </form>
</section>

<section>
    <h2><?php _e( 'Custom sizes', 'wpas' ); ?></h2>

    <div id="custom-sizes">
        <?php include AIS_Admin_Helpers::get_view('ais-admin-part-custom-sizes'); ?>
    </div>
</section>