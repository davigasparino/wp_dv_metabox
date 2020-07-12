<?php
    /**
     * WP Dv Metabox
     * Plugin Name: WP Dv Metabox
     * Description: Simples form builder usando metabox
     * Author: Davi Gasparino
     * Version: 1.0.0
     * Author URI: http://davigasparino.com.br
     * GitHub Plugin URI: https://github.com/wp-bootstrap/wp-bootstrap-navwalker
     * GitHub Branch: master
     * License: GPL-3.0+
     * License URI: http://www.gnu.org/licenses/gpl-3.0.txt
     */

    class WpDvMetabox{
        public $args = array(
            'id'        => '',
            'title'     => '',
            'screens'   => array('post'),
            'context'   => 'advanced',
            'priority'  => 'default',
            'fields'    => array(
                array(
                    'label' => '',
                    'id'    => '',
                    'type'  => '',
                )
            ),
        );

        function __construct($args = null){
            if(!empty($args)){
                $this->args = array_merge($this->args, $args);
            }

            add_action('add_meta_boxes', array($this, 'wp_dv_create_metabox'));
            add_action('save_post', array($this, 'wp_dv_save_options'));
        }

        function wp_dv_create_metabox(){
            add_meta_box(
                $this->args['id'],
                $this->args['title'],
                array( $this, 'wp_dv_form_builder' ),
                $this->args['screens'],
                $this->args['context'],
                $this->args['priority']
            );
        }

        function wp_dv_form_builder($post){
            wp_nonce_field( basename( __FILE__ ), 'dv_restaurant_fields' );
            ?>
            <div class="form-group">
                <?php if(!empty($this->args['fields'])): ?>
                    <?php foreach($this->args['fields'] as $fields): ?>
                        <?php $form_value = get_post_meta( $post->ID, $fields['id'], true ); ?>
                        <label for="<?php echo esc_attr($fields['id']); ?>"><?php echo esc_html($fields['label']); ?></label>
                        <input class="form-control" id="<?php echo esc_attr($fields['id']); ?>" name="<?php echo esc_attr($fields['id']); ?>" value="<?php echo esc_textarea($form_value); ?>" />
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <?php
        }

        function wp_dv_save_options($post_id, $post = null){
            foreach($this->args['fields'] as $fields):
                if ( ! current_user_can( 'edit_post', $post_id ) ) {
                    return $post_id;
                }

                if ( ! isset( $_POST[$fields['id']] ) || ! wp_verify_nonce( $_POST['dv_restaurant_fields'], basename(__FILE__) ) ) {
                    return $post_id;
                }

                $events_meta[$fields['id']] = esc_textarea( $_POST[$fields['id']] );

                foreach ( $events_meta as $key => $value ) :

                    if ( 'revision' === $post->post_type ) {
                        return;
                    }

                    if ( get_post_meta( $post_id, $key, false ) ) {
                        // If the custom field already has a value, update it.
                        update_post_meta( $post_id, $key, $value );
                    } else {
                        // If the custom field doesn't have a value, add it.
                        add_post_meta( $post_id, $key, $value);
                    }

                    if ( ! $value ) {
                        delete_post_meta( $post_id, $key );
                    }

                endforeach;
            endforeach;
        }
    }