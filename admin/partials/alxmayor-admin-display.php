<div class="wrap">
    <h2><?php echo esc_html(get_admin_page_title()); ?></h2>
    <form method="post" name="alxmayor_options" action="options.php">
    <?php
        settings_fields($this->plugin_name);
        do_settings_sections($this->plugin_name);
        $options = get_option($this->plugin_name);
    ?>
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="<?php echo $this->plugin_name; ?>-ocultar_precio">
                        <?php _e('Ocultar precio para usuarios no registrados', 'alxmayor'); ?>
                    </label>
                </th>
                <td>
                    <input type="checkbox" id="<?php echo $this->plugin_name; ?>-ocultar_precio" name="<?php echo $this->plugin_name; ?>[ocultar_precio]" value="1" <?php checked(isset($options['ocultar_precio']) ? $options['ocultar_precio'] : 0); ?> />
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="<?php echo $this->plugin_name; ?>-mensaje_precio_oculto">
                        <?php _e('Mensaje para precio oculto', 'alxmayor'); ?>
                    </label>
                </th>
                <td>
                    <input type="text" id="<?php echo $this->plugin_name; ?>-mensaje_precio_oculto" name="<?php echo $this->plugin_name; ?>[mensaje_precio_oculto]" value="<?php echo isset($options['mensaje_precio_oculto']) ? esc_attr($options['mensaje_precio_oculto']) : ''; ?>" class="regular-text" />
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="<?php echo $this->plugin_name; ?>-habilitar_monto_minimo">
                        <?php _e('Habilitar monto mínimo de compra', 'alxmayor'); ?>
                    </label>
                </th>
                <td>
                    <input type="checkbox" id="<?php echo $this->plugin_name; ?>-habilitar_monto_minimo" name="<?php echo $this->plugin_name; ?>[habilitar_monto_minimo]" value="1" <?php checked(isset($options['habilitar_monto_minimo']) ? $options['habilitar_monto_minimo'] : 0); ?> />
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="<?php echo $this->plugin_name; ?>-monto_minimo">
                        <?php _e('Monto mínimo de compra', 'alxmayor'); ?>
                    </label>
                </th>
                <td>
                    <input type="number" id="<?php echo $this->plugin_name; ?>-monto_minimo" name="<?php echo $this->plugin_name; ?>[monto_minimo]" value="<?php echo isset($options['monto_minimo']) ? esc_attr($options['monto_minimo']) : ''; ?>" class="regular-text" min="0" step="1" />
                </td>
            </tr>
        </table>
        <?php submit_button('Guardar cambios', 'primary', 'submit', true); ?>
    </form>
</div>
