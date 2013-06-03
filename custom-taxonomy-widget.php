<?php

/*
Plugin Name: Custom Taxonomy Widget
Plugin URI: http://wordpress.org/extend/plugins/custom-taxonomy-widget/
Description: This plugin allows you to create a new Sidebar widget to display terms from custom taxonomies!
Author: David Jenkins
Version: 1.0
Author URI: http://dakanndesigns.com/
License: GPLv2 or later
*/

/*
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

class CustomTaxonomyWidget extends WP_Widget
{
    public function __construct()
    {
        parent::__construct(
            'custom_taxonomy_widget',
            'Custom Taxonomy Widget',
            array('description' => 'Allows you to create a new Sidebar widget to display terms from custom taxonomies!')
        );
    }

    public function widget($args, $instance)
    {
        extract($args);
        $title = apply_filters('widget_title', $instance['title']);
        $hide_empty = (isset($instance['hide_empty'])) ? true : false;
        $order_options = (isset($instance['order_options'])) ? explode('/', $instance['order_options']) : array('', '');

        $get_terms_args = array(
            'hide_empty' => $hide_empty,
            'orderby'    => (isset($order_options[0])) ? $order_options[0] : 'name',
            'order'      => (isset($order_options[1])) ? $order_options[1] : 'ASC',
            'number'     => (isset($instance['max_terms'])) ? $instance['max_terms'] : '',
            'exclude'    => (isset($instance['exclude'])) ? $instance['exclude'] : '',
            'include'    => (isset($instance['include'])) ? $instance['include'] : '',          
            'pad_counts' => true
        );

        $terms = get_terms($instance['custom_taxonomies'], $get_terms_args);

        if (empty($terms) && isset($instance['hide_widget_empty']))
            return;

        echo $before_widget;
            if (! empty($title))
                echo $before_title . $title . $after_title;
            ?>
                <ul>
                    <?php foreach ($terms as $term): ?>
                        <li class="<?php echo ($term->parent != "0") ? 'taxonomy-has-parent' : null; ?>">
                            <a href="<?php echo get_term_link($term); ?>"><?php echo $term->name; ?></a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php
        echo $after_widget;
    }

    public function form($instance)
    {
        $field_data = array(
            'title' => array(
                'id'    => $this->get_field_id('title'),
                'name'  => $this->get_field_name('title'),
                'value' => (isset($instance['title'])) ? $instance['title'] : __('New Title')
            ),
            'taxonomies' => array(
                'name'   => $this->get_field_name('custom_taxonomies'),
                'value'  => (isset($instance['custom_taxonomies'])) ? $instance['custom_taxonomies'] : ''
            ),
            'max_terms' => array(
                'id'    => $this->get_field_id('max_terms'),
                'name'  => $this->get_field_name('max_terms'),
                'value' => (isset($instance['max_terms'])) ? $instance['max_terms'] : ''
            ),
            'hide_widget_empty' => array(
                'id'    => $this->get_field_id('hide_widget_empty'),
                'name'  => $this->get_field_name('hide_widget_empty'),
                'value' => (isset($instance['hide_widget_empty'])) ? 'true' : ''
            ),
            'hide_empty' => array(
                'id'    => $this->get_field_id('hide_empty'),
                'name'  => $this->get_field_name('hide_empty'),
                'value' => (isset($instance['hide_empty'])) ? 'true' : ''
            ),
            'order_options' => array(
                'id'    => $this->get_field_id('order_options'),
                'name'  => $this->get_field_name('order_options'),
                'value' => (isset($instance['order_options'])) ? $instance['order_options'] : 'name'
            ),
            'exclude' => array(
                'id'    => $this->get_field_id('exclude'),
                'name'  => $this->get_field_name('exclude'),
                'value' => (isset($instance['exclude'])) ? $instance['exclude'] : ''
            ),
            'include' => array(
                'id'    => $this->get_field_id('include'),
                'name'  => $this->get_field_name('include'),
                'value' => (isset($instance['include'])) ? $instance['include'] : ''
            )
        );

        $taxonomies = get_taxonomies(array('_builtin' => false), 'objects');

        ?>
            <p>
                <label for="<?php echo $field_data['title']['id']; ?>"><?php _e('Title:'); ?></label>
                <input class="widefat" id="<?php echo $field_data['title']['id']; ?>" name="<?php echo $field_data['title']['name']; ?>" type="text" value="<?php echo esc_attr($field_data['title']['value']); ?>">
            </p>


            <p style='font-weight: bold;'><?php _e('Options:'); ?></p>

            <p>
                <input id="<?php echo $field_data['hide_widget_empty']['id']; ?>" name="<?php echo $field_data['hide_widget_empty']['name']; ?>" type="checkbox" value="true" <?php checked($field_data['hide_widget_empty']['value'], 'true'); ?>>
                <label for="<?php echo $field_data['hide_widget_empty']['id']; ?>"><?php _e('Hide Widget If There Are No Terms To Be Displayd?'); ?></label>
            </p>

            <p>
                <input id="<?php echo $field_data['hide_empty']['id']; ?>" name="<?php echo $field_data['hide_empty']['name']; ?>" type="checkbox" value="true" <?php checked($field_data['hide_empty']['value'], 'true'); ?>>
                <label for="<?php echo $field_data['hide_empty']['id']; ?>"><?php _e('Hide Terms That Have No Related Posts?'); ?></label>
            </p>

            <p>
                <label for="<?php echo $field_data['order_options']['id']; ?>"><?php _e('Order Terms By:'); ?></label><br>
                <select id="<?php echo $field_data['order_options']['id']; ?>" name="<?php echo $field_data['order_options']['name']; ?>">
                    <option value="id/ASC" <?php selected($field_data['order_options']['value'], 'id/ASC'); ?>>ID Ascending</option>
                    <option value="id/DESC" <?php selected($field_data['order_options']['value'], 'id/DESC'); ?>>ID Descending</option>
                    <option value="count/ASC" <?php selected($field_data['order_options']['value'], 'count/ASC'); ?>>Count Ascending</option>
                    <option value="count/DESC" <?php selected($field_data['order_options']['value'], 'count/DESC'); ?>>Count Descending</option>
                    <option value="name/ASC" <?php selected($field_data['order_options']['value'], 'name/ASC'); ?>>Name Ascending</option>
                    <option value="name/DESC" <?php selected($field_data['order_options']['value'], 'name/DESC'); ?>>Name Descending</option>               
                    <option value="slug/ASC" <?php selected($field_data['order_options']['value'], 'slug/ASC'); ?>>Slug Ascending</option>
                    <option value="slug/DESC" <?php selected($field_data['order_options']['value'], 'slug/DESC'); ?>>Slug Descending</option>
                </select>
            </p>

            <p>
                <label for="<?php echo $field_data['max_terms']['id']; ?>"><?php _e('Maximum Number Of Terms To Return:'); ?></label>
                <input class="widefat" id="<?php echo $field_data['max_terms']['id']; ?>" name="<?php echo $field_data['max_terms']['name']; ?>" type="text" value="<?php echo esc_attr($field_data['max_terms']['value']); ?>" placeholder="Keep Empty To Display All">
            </p>

            <p>
                <label for="<?php echo $field_data['exclude']['id']; ?>"><?php _e('Ids To Exclude From Being Displayed:'); ?></label>
                <input class="widefat" id="<?php echo $field_data['exclude']['id']; ?>" name="<?php echo $field_data['exclude']['name']; ?>" type="text" value="<?php echo esc_attr($field_data['exclude']['value']); ?>" placeholder="Separate ids with a comma ','">
            </p>

            <p>
                <label for="<?php echo $field_data['include']['id']; ?>"><?php _e('Only Display Terms With The Following Ids:'); ?></label>
                <input class="widefat" id="<?php echo $field_data['include']['id']; ?>" name="<?php echo $field_data['include']['name']; ?>" type="text" value="<?php echo esc_attr($field_data['include']['value']); ?>" placeholder="Separate ids with a comma ','">
            </p>


            <p style='font-weight: bold;'><?php _e('Custom Taxonomies:'); ?></p>

            <?php foreach($taxonomies as $taxonomy): ?>
                <p>
                    <input id="<?php echo $taxonomy->name; ?>" name="<?php echo $field_data['taxonomies']['name']; ?>[]" type="checkbox" value="<?php echo $taxonomy->name; ?>" <?php echo $this->is_taxonomy_checked($field_data['taxonomies']['value'], $taxonomy->name); ?>>
                    <label for="<?php echo $taxonomy->name; ?>"><?php echo $taxonomy->labels->name; ?></label>
                </p>
            <?php endforeach; ?>
        <?php
    }

    public function update($new_instance, $old_instance)
    {
        $instance['title'] = strip_tags($new_instance['title']);
        $instance['hide_widget_empty'] = $new_instance['hide_widget_empty'];
        $instance['hide_empty']        = $new_instance['hide_empty'];
        $instance['order_options']     = $new_instance['order_options'];
        $instance['max_terms']         = $new_instance['max_terms'];
        $instance['exclude']           = $new_instance['exclude'];
        $instance['include']           = $new_instance['include'];
        $instance['custom_taxonomies'] = $new_instance['custom_taxonomies'];

        return $instance;
    }

    public function is_taxonomy_checked($custom_taxonomies_checked, $taxonomy_name)
    {
        if (! is_array($custom_taxonomies_checked))
            return checked($custom_taxonomies_checked, $taxonomy_name);

        if (in_array($taxonomy_name, $custom_taxonomies_checked))
            return 'checked="checked"';
    }
}

add_action('widgets_init', 'init_custom_taxonomy_widget');
function init_custom_taxonomy_widget()
{
    register_widget('CustomTaxonomyWidget');
}