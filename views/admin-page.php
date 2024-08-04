<div class="wrap">
    <h1>Shipping Zones Manager</h1>

    <!-- Formulaire pour ajouter ou mettre à jour les zones de livraison -->
    <form method="post" action="">
        <?php wp_nonce_field('custom_shipping_zones_nonce', 'custom_shipping_zones_nonce_field'); ?>
        <input type="hidden" name="custom_shipping_zones_action" value="add_or_update_zones">
        <p>
            <input type="submit" class="button button-primary" value="Add or Update Shipping Zones">
        </p>
        <?php
        if (isset($_GET['success']) && $_GET['success'] == 1) {
            echo '<div class="notice notice-success is-dismissible">
                <p>Shipping zones have been added or updated successfully.</p>
            </div>';
        }
        ?>
    </form>

    <!-- Formulaire pour supprimer toutes les zones de livraison -->
    <form method="post" action="">
        <?php wp_nonce_field('remove_all_zones_nonce', 'remove_all_zones_nonce_field'); ?>
        <input type="hidden" name="remove_all_zones" value="1">
        <p>
            <input type="submit" class="button button-primary" value="Remove All Shipping Zones">
        </p>
        <?php
        if (isset($_GET['removed']) && $_GET['removed'] == 1) {
            echo '<div class="notice notice-success is-dismissible">
                <p>All shipping zones have been removed successfully.</p>
            </div>';
        }
        ?>
    </form>
</div>
