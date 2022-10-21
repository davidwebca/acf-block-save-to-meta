<?php 

namespace davidwebca\WordPress;

class ACF_SaveToMeta {
    private $metas_to_save = [];
    private $processed_blocks = [];
    private $meta_cache = [];

    public function __construct() {
        add_filter('acf/pre_save_block', [$this, 'pre_save_block'], 10, 1);
        add_action('save_post', [$this, 'save_post_meta'], 10, 1);
        add_filter('acf/load_field', [$this, 'load_meta_field'], 10, 1);
        add_filter('acf/load_value', [$this, 'load_meta_value'], 10, 3);
        add_action('acf/render_field_settings', [$this, 'add_setting'], 10, 1);
    }

    public function add_setting($field) {
        \acf_render_field_setting( $field, array(
            'label'         => __('Save to meta?', 'acf_savetometa'),
            'instructions'  => '',
            'name'          => 'save_to_meta',
            'type'          => 'true_false',
            'ui'            => 1,
        ), true);
    }

    /**
     * Add meta as class attributes to later save in meta.
     * This is required becuase new posts don't have post_id in blocks.
     *
     * This hook can weirdly be called multiple times in the lifecycle,
     * so we make sure to save unique data per block id
     * 
     * @param  Array    $attrs  Array of attributes we're currently trying to save for the block
     * @return Array
     */
    public function pre_save_block($attrs) {
        // Bail early if already processed
        if(in_array($attrs['id'], $this->processed_blocks)) {
            return $attrs;
        }
        // Bail early if empty
        if(empty($attrs['data'])){
            return $attrs;
        }

        global $post;
        $block_name = $attrs['name'];
        $block_fields = \acf_get_block_fields(['name' => $block_name]);

        // Key them by field name for easy access
        $block_fields_save_to_meta = [];
        foreach ($block_fields as $key => $fields) {
            if(isset($fields['save_to_meta']) && $fields['save_to_meta'] == 1) {
                $block_fields_save_to_meta[$fields['name']] = 1;
            }
        }

        /**
         * We save this as an array of arrays because there might be multiple
         * of the same blocks on the same page, hence multiple of the same field
         *
         * BEWARE: This means that your field's names MUST be unique per block,
         * otherwise you might get colliding saves. Ex.: a field named "title" 
         * used in multiple blocks will override another field named "title"
         * in a different block added later in the page.
         *
         * This means, when getting back the values, it will always be an array,
         * even if you have a single block in your page.
         */
        foreach ($attrs['data'] as $key => $value) {
            if(str_starts_with($key, '_') && isset($block_fields_save_to_meta[substr($key, 1)])) {
                // We save a single value of the ACF meta key
                $this->metas_to_save[$key] = $value;
            }else if( isset($block_fields_save_to_meta[$key]) ){
                // Warning: We can't have multiple of the same value, the latest one will always prevail
                $this->metas_to_save[$key] = $value;
                /**
                 * To avoid any complexities, we make sure the block saves
                 * without any content in its markup, but we can't "unset" otherwise
                 * acf won't load the fields at all when trying to display the block later
                 */
                // $attrs['data'][$key] = '';
            }

        }
        array_push($this->processed_blocks, $attrs['id']);


        return $attrs;
    }

    /**
     * Save meta
     * 
     * @param  string   $post_id    Real post id
     * @return void
     */
    public function save_post_meta($post_id) {
        // If this is a revision, bail early
        if ( wp_is_post_revision( $post_id ) ) {
            return;
        }
        // Avoid infinite loop
        remove_action('save_post', [$this, 'save_post_meta'], 10, 1);

        // acf_update_values
        wp_update_post(array(
            'ID'        => $post_id,
            'meta_input'=> $this->metas_to_save,
        ));

        // Should not be called again
        // add_action('save_post', [$this, 'save_post_meta'], 10, 1);
    }

    /**
     * Loads the values from metadata and discards the block's values
     * 
     * @param  mixed        $value      Existing value of the block's field
     * @param  string|int   $post_id    Post ID, either block_5938429 or real post ID from database
     * @param  array        $field      ACF field definition
     * @return mixed                    Edited value
     */
    // public function load_meta($field) {
    public function load_meta_value($value, $post_id, $field) {
        // $post_id = get_the_ID();
        // Bail early if not a block or if save_to_meta setting doesn't exist / is false
        if(!isset($field['save_to_meta']) || $field['save_to_meta'] == 0) {
            return $value;
        }

        $meta_name = $field['name'];
        if(!isset($this->meta_cache[$meta_name])) {
            $this->meta_cache[$meta_name] = get_post_meta( $post_id, $meta_name, true );
        }
        $value = $this->meta_cache[$meta_name];

        return $value;
    }
    /**
     * Loads the value from the field settings which is require in some editing situations
     * 
     * @param  mixed        $value      Existing value of the block's field
     * @param  string|int   $post_id    Post ID, either block_5938429 or real post ID from database
     * @param  array        $field      ACF field definition
     * @return mixed                    Edited value
     */
    // public function load_meta($field) {
    public function load_meta_field($field) {
        $post_id = get_the_ID();
        
        // Bail early if not a block or if save_to_meta setting doesn't exist / is false
        if(!isset($field['save_to_meta']) || $field['save_to_meta'] == 0) {
            return $field;
        }

        $meta_name = $field['name'];
        if(!isset($this->meta_cache[$meta_name])) {
            $this->meta_cache[$meta_name] = get_post_meta( $post_id, $meta_name, true );
        }
        $field['value'] = $this->meta_cache[$meta_name];

        return $field;
    }
}

// phpcs:disable
if (function_exists('add_action')) {
    new ACF_SaveToMeta();
}
