<?php
    /**
     * WP Dv Metabox
     * Plugin Name: WP Dv Metabox
     * Description: Simples form builder usando metabox
     * Author: Davi Gasparino
     * Version: 1.0.0
     * Author URI: http://davigasparino.com.br
     * GitHub Plugin URI: https://github.com/davigasparino/wp_dv_metabox
     * GitHub Branch: master
     * License: GPL-3.0+
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

            add_action('admin_enqueue_scripts', array( $this, 'restaurant_admin_scripts'));
            add_action('add_meta_boxes', array($this, 'wp_dv_create_metabox'));
            add_action('save_post', array($this, 'wp_dv_save_options'));
        }

        function restaurant_admin_scripts(){
            wp_enqueue_style('wp_dv_metabox_style', get_template_directory_uri().'/plugins/wp_dv_metabox/css/style.css');
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

        function wp_dv_form_builder($post){
            wp_nonce_field( basename( __FILE__ ), 'dv_restaurant_fields' );
            if(!empty($this->args['fields'])):
                foreach($this->args['fields'] as $fields):
                    $form_value = get_post_meta( $post->ID, $fields['id'], true );
                    switch ($fields['type']):
                        case 'checkbox':
                            $this->wp_dv_form_builder_checkbox($fields, $form_value);
                            break;

                        case 'radio':
                            $this->wp_dv_form_builder_radio($fields, $form_value);
                            break;

                        default:
                            $this->wp_dv_form_buider_text($fields, $form_value);
                            break;
                    endswitch;
                endforeach;
            endif;
        }

        function wp_dv_form_buider_text($fields, $value){
            $html = '
                <div class="form-group">
                    <label for="'.esc_attr($fields['id']).'">'.esc_html($fields['label']).'</label>
                    <input class="form-control" type="'.esc_attr($fields['type']).'" id="'.esc_attr($fields['id']).'" name="'.esc_attr($fields['id']).'" value="'.esc_textarea($value).'" />
                </div>
            ';
            echo $html;
        }

        function wp_dv_form_builder_radio($fields, $value){
            $html = '<div class="form-group"><label>'.esc_html($fields['label']).'</label><div class="container-radio">';
            foreach ($fields['options'] as $option_key => $option_value){
                $checked = ($value == $option_value) ? 'checked' : '';
                $html .= '<label><input name="'.esc_attr($fields['id']).'" type="'.esc_attr($fields['type']).'" value="'.esc_attr($option_value).'" '.esc_attr($checked).'> '.esc_html($option_key).'</label>';
            }
            $html .= '</div></div>';
            echo $html;
        }

        function wp_dv_form_builder_checkbox($fields, $value){
            $html = '<div class="form-group"><label>'.esc_html($fields['label']).'</label><div class="container-radio">';
            foreach ($fields['options'] as $option_key => $option_value){
                $checked = ($value == $option_value) ? 'checked' : '';
                $html .= '<label><input name="'.esc_attr($fields['id']).'" type="'.esc_attr($fields['type']).'" value="'.esc_attr($option_value).'" '.esc_attr($checked).'> '.esc_html($option_key).'</label>';
            }
            $html .= '</div></div>';
            echo $html;
        }
    }