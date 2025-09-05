<?php
/**
 * Template de la liste des produits
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1 class="wp-heading-inline"><?php _e('Produits', 'block-traiteur'); ?></h1>
    <a href="<?php echo admin_url('admin.php?page=block-traiteur-products&action=add'); ?>" class="page-title-action">
        <?php _e('Ajouter un produit', 'block-traiteur'); ?>
    </a>
    <a href="<?php echo admin_url('admin.php?page=block-traiteur-products&action=categories'); ?>" class="page-title-action">
        <?php _e('Gérer les catégories', 'block-traiteur'); ?>
    </a>
    
    <hr class="wp-header-end">
    
    <!-- Filtres -->
    <div class="tablenav top">
        <div class="alignleft actions">
            <select id="category-filter">
                <option value=""><?php _e('Toutes les catégories', 'block-traiteur'); ?></option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?php echo esc_attr($category->id); ?>">
                        <?php echo esc_html($category->name); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            
            <select id="service-filter">
                <option value=""><?php _e('Tous les services', 'block-traiteur'); ?></option>
                <option value="restaurant"><?php _e('Restaurant', 'block-traiteur'); ?></option>
                <option value="remorque"><?php _e('Remorque', 'block-traiteur'); ?></option>
                <option value="both"><?php _e('Les deux', 'block-traiteur'); ?></option>
            </select>
            
            <button type="button" class="button" id="filter-products">
                <?php _e('Filtrer', 'block-traiteur'); ?>
            </button>
        </div>
    </div>
    
    <!-- Tableau des produits -->
    <?php if (!empty($products)): ?>
        <table class="wp-list-table widefat fixed striped products" id="products-table">
            <thead>
                <tr>
                    <th scope="col" class="manage-column column-image">
                        <?php _e('Image', 'block-traiteur'); ?>
                    </th>
                    <th scope="col" class="manage-column column-name">
                        <?php _e('Nom', 'block-traiteur'); ?>
                    </th>
                    <th scope="col" class="manage-column column-category">
                        <?php _e('Catégorie', 'block-traiteur'); ?>
                    </th>
                    <th scope="col" class="manage-column column-price">
                        <?php _e('Prix', 'block-traiteur'); ?>
                    </th>
                    <th scope="col" class="manage-column column-service">
                        <?php _e('Service', 'block-traiteur'); ?>
                    </th>
                    <th scope="col" class="manage-column column-status">
                        <?php _e('Statut', 'block-traiteur'); ?>
                    </th>
                    <th scope="col" class="manage-column column-actions">
                        <?php _e('Actions', 'block-traiteur'); ?>
                    </th>
                </tr>
            </thead>
            <tbody id="products-tbody">
                <?php foreach ($products as $product): ?>
                    <tr data-category="<?php echo esc_attr($product->category_id); ?>" 
                        data-service="<?php echo esc_attr($product->service_type); ?>">
                        <td class="column-image">
                            <?php if ($product->image_url): ?>
                                <img src="<?php echo esc_url($product->image_url); ?>" 
                                     alt="<?php echo esc_attr($product->name); ?>" 
                                     style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;">
                            <?php else: ?>
                                <div class="image-placeholder">
                                    <span class="dashicons dashicons-format-image"></span>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td class="column-name">
                            <strong>
                                <a href="<?php echo admin_url('admin.php?page=block-traiteur-products&action=edit&id=' . $product->id); ?>">
                                    <?php echo esc_html($product->name); ?>
                                </a>
                            </strong>
                            <?php if ($product->description): ?>
                                <div class="description">
                                    <?php echo esc_html(wp_trim_words($product->description, 10)); ?>
                                </div>
                            <?php endif; ?>
                            <?php if ($product->allergens): ?>
                                <div class="allergens">
                                    <small><strong><?php _e('Allergènes:', 'block-traiteur'); ?></strong> <?php echo esc_html($product->allergens); ?></small>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td class="column-category">
                            <span class="category-badge category-<?php echo esc_attr($product->category_slug ?? 'default'); ?>">
                                <?php echo esc_html($product->category_name ?? __('Sans catégorie', 'block-traiteur')); ?>
                            </span>
                        </td>
                        <td class="column-price">
                            <strong><?php echo number_format($product->price, 2); ?> €</strong>
                            <br><small>/ <?php echo esc_html($product->unit); ?></small>
                            <?php if ($product->min_quantity > 1): ?>
                                <br><small><?php printf(__('Min: %d', 'block-traiteur'), $product->min_quantity); ?></small>
                            <?php endif; ?>
                            <?php if ($product->max_quantity): ?>
                                <br><small><?php printf(__('Max: %d', 'block-traiteur'), $product->max_quantity); ?></small>
                            <?php endif; ?>
                        </td>
                        <td class="column-service">
                            <span class="service-badge service-<?php echo esc_attr($product->service_type); ?>">
                                <?php 
                                $service_labels = array(
                                    'restaurant' => __('Restaurant', 'block-traiteur'),
                                    'remorque' => __('Remorque', 'block-traiteur'),
                                    'both' => __('Les deux', 'block-traiteur')
                                );
                                echo esc_html($service_labels[$product->service_type]);
                                ?>
                            </span>
                        </td>
                        <td class="column-status">
                            <?php if ($product->is_active): ?>
                                <span class="status-active"><?php _e('Actif', 'block-traiteur'); ?></span>
                            <?php else: ?>
                                <span class="status-inactive"><?php _e('Inactif', 'block-traiteur'); ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="column-actions">
                            <div class="row-actions">
                                <span class="edit">
                                    <a href="<?php echo admin_url('admin.php?page=block-traiteur-products&action=edit&id=' . $product->id); ?>">
                                        <?php _e('Modifier', 'block-traiteur'); ?>
                                    </a>
                                </span>
                                |
                                <span class="duplicate">
                                    <a href="#" class="duplicate-product" data-product-id="<?php echo $product->id; ?>">
                                        <?php _e('Dupliquer', 'block-traiteur'); ?>
                                    </a>
                                </span>
                                |
                                <span class="delete">
                                    <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=block-traiteur-products&action=delete&id=' . $product->id), 'delete_product_' . $product->id); ?>" 
                                       onclick="return confirm('<?php _e('Êtes-vous sûr de vouloir supprimer ce produit ?', 'block-traiteur'); ?>')">
                                        <?php _e('Supprimer', 'block-traiteur'); ?>
                                    </a>
                                </span>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="no-products">
            <p><?php _e('Aucun produit trouvé.', 'block-traiteur'); ?></p>
            <a href="<?php echo admin_url('admin.php?page=block-traiteur-products&action=add'); ?>" class="button button-primary">
                <?php _e('Ajouter le premier produit', 'block-traiteur'); ?>
            </a>
        </div>
    <?php endif; ?>
</div>

<script>
jQuery(document).ready(function($) {
    // Filtrage des produits
    $('#filter-products').on('click', function() {
        var categoryFilter = $('#category-filter').val();
        var serviceFilter = $('#service-filter').val();
        
        $('#products-tbody tr').each(function() {
            var $row = $(this);
            var showRow = true;
            
            if (categoryFilter && $row.data('category') != categoryFilter) {
                showRow = false;
            }
            
            if (serviceFilter && $row.data('service') != serviceFilter) {
                showRow = false;
            }
            
            if (showRow) {
                $row.show();
            } else {
                $row.hide();
            }
        });
    });
    
    // Duplication de produit
    $('.duplicate-product').on('click', function(e) {
        e.preventDefault();
        
        var productId = $(this).data('product-id');
        
        if (confirm('<?php _e('Dupliquer ce produit ?', 'block-traiteur'); ?>')) {
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'block_traiteur_duplicate_product',
                    product_id: productId,
                    nonce: '<?php echo wp_create_nonce('block_traiteur_admin'); ?>'
                },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert('Erreur: ' + response.data);
                    }
                },
                error: function() {
                    alert('<?php _e('Erreur lors de la duplication', 'block-traiteur'); ?>');
                }
            });
        }
    });
});
</script>

<style>
.image-placeholder {
    width: 50px;
    height: 50px;
    background: #f0f0f1;
    border-radius: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #646970;
}

.description {
    margin-top: 5px;
    color: #646970;
    font-size: 13px;
}

.allergens {
    margin-top: 5px;
}

.allergens small {
    color: #d63384;
}

.category-badge {
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 500;
    text-transform: uppercase;
}

.category-signature {
    background: #e7f3ff;
    color: #0073aa;
}

.category-mini_boss {
    background: #fff3cd;
    color: #856404;
}

.category-accompagnement {
    background: #d1e7dd;
    color: #0f5132;
}

.category-buffet_sale {
    background: #f8d7da;
    color: #721c24;
}

.category-buffet_sucre {
    background: #e2e3e5;
    color: #383d41;
}

.service-badge {
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 500;
    text-transform: uppercase;
}

.service-restaurant {
    background: #e7f3ff;
    color: #0073aa;
}

.service-remorque {
    background: #f0f6fc;
    color: #2271b1;
}

.service-both {
    background: #d1e7dd;
    color: #0f5132;
}

.status-active {
    color: #0f5132;
    font-weight: 500;
}

.status-inactive {
    color: #721c24;
    font-weight: 500;
}

.no-products {
    padding: 40px;
    text-align: center;
    background: white;
    border: 1px solid #ccd0d4;
    margin-top: 20px;
}
</style>