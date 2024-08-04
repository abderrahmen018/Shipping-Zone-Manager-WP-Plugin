<?php
/**
 * Plugin Name: Shipping Zones Manager
 * Description: Adds or updates shipping zones for Algerian wilayas and removes all shipping zones.
 * Version: 1.0
 * Author: Abderrahmene Boulouh
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// Fonction pour ajouter ou mettre à jour les zones de livraison
function add_or_update_shipping_zones_for_wilayas() {
    if (isset($_POST['custom_shipping_zones_action']) && $_POST['custom_shipping_zones_action'] === 'add_or_update_zones' && check_admin_referer('custom_shipping_zones_nonce', 'custom_shipping_zones_nonce_field')) {
        $wilayas_shipping = array(
            'DZ-01' => 500, // Adrar
            'DZ-02' => 700, // Chlef
            'DZ-03' => 800, // Laghouat
            'DZ-04' => 650, // Oum El Bouaghi
            'DZ-05' => 900, // Batna
        );

        foreach ($wilayas_shipping as $wilaya_code => $shipping_cost) {
            $zone_exists = false;
            $zones = WC_Shipping_Zones::get_zones();
            $zone_id = 0;
            foreach ($zones as $zone) {
                if ($zone['zone_name'] === $wilaya_code) {
                    $zone_exists = true;
                    $zone_id = $zone['zone_id'];
                    break;
                }
            }

            if ($zone_exists) {
                $zone = new WC_Shipping_Zone($zone_id);
            } else {
                $zone = new WC_Shipping_Zone();
                $zone->set_zone_name($wilaya_code);
                $zone->set_zone_order(1);
                $zone_id = $zone->save();
            }

            $zone->add_location($wilaya_code, 'state');

            $methods = $zone->get_shipping_methods(true);
            $flat_rate_method_exists = false;
            foreach ($methods as $method) {
                if ($method->id === 'flat_rate') {
                    $flat_rate_method_exists = true;
                    $instance_id = $method->get_instance_id();
                    $flat_rate_settings = get_option('woocommerce_flat_rate_' . $instance_id . '_settings');
                    $flat_rate_settings['cost'] = $shipping_cost;
                    update_option('woocommerce_flat_rate_' . $instance_id . '_settings', $flat_rate_settings);
                    break;
                }
            }

            if (!$flat_rate_method_exists) {
                $zone->add_shipping_method('flat_rate');
                $methods = $zone->get_shipping_methods(true);
                foreach ($methods as $method) {
                    if ($method->id === 'flat_rate') {
                        $instance_id = $method->get_instance_id();
                        $flat_rate_settings = get_option('woocommerce_flat_rate_' . $instance_id . '_settings');
                        $flat_rate_settings['cost'] = $shipping_cost;
                        update_option('woocommerce_flat_rate_' . $instance_id . '_settings', $flat_rate_settings);
                    }
                }
            }
        }

        wp_redirect(admin_url('admin.php?page=shipping-zones-manager&success=1'));
        exit;
    }
}
add_action('admin_init', 'add_or_update_shipping_zones_for_wilayas');

// Fonction pour supprimer toutes les zones de livraison
function remove_all_shipping_zones() {
    if (isset($_POST['remove_all_zones']) && check_admin_referer('remove_all_zones_nonce', 'remove_all_zones_nonce_field')) {
        $zones = WC_Shipping_Zones::get_zones();

        foreach ($zones as $zone) {
            $zone_id = $zone['zone_id'];
            $zone_obj = new WC_Shipping_Zone($zone_id);

            // Supprimer toutes les méthodes d'expédition de la zone
            $shipping_methods = $zone_obj->get_shipping_methods(true);
            foreach ($shipping_methods as $method) {
                $zone_obj->delete_shipping_method($method->instance_id);
            }

            // Supprimer la zone
            $zone_obj->delete();
        }

        wp_redirect(admin_url('admin.php?page=shipping-zones-manager&removed=1'));
        exit;
    }
}
add_action('admin_init', 'remove_all_shipping_zones');

// Ajouter un menu d'administration
function shipping_zones_manager_admin_menu() {
    add_menu_page(
        'Shipping Zones Manager',
        'Shipping Zones',
        'manage_options',
        'shipping-zones-manager',
        'shipping_zones_manager_page',
        'dashicons-admin-generic'
    );
}
add_action('admin_menu', 'shipping_zones_manager_admin_menu');

// Fonction pour afficher la page d'administration
function shipping_zones_manager_page() {
    include(plugin_dir_path(__FILE__) . 'views/admin-page.php');
}
