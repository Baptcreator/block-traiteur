<?php
/**
 * Template du formulaire de produit
 */

if (!defined('ABSPATH')) {
    exit;
}

$is_edit = !empty($product);
$page_title = $is_edit ? __('Modifier le produit', 'block-traiteur') : __('Ajouter un produit', 'block-traiteur');
?>

<div class="wrap">
    <h1><?php echo esc_html($page_title); ?></h1>
    
    <form method="post" class="product-form">
        <?php 
        if ($is_edit) {
            wp_nonce_field('update_product_' . $product->id);
        } else {
            wp_nonce_field('add_product');
        }
        ?>
        
        <table class="form-table">
            <tbody>
                <tr>
                    <th scope="row">
                        <label for="name"><?php _e('Nom du produit', 'block-traiteur'); ?> <span class="required">*</span></label>
                    </th>
                    <td>
                        <input type="text" id="name" name="name" class="regular-text" 
                               value="<?php echo $is_edit ? esc_attr($product->name) : ''; ?>" required>
                        <p class="description"><?php _e('Nom affiché dans le formulaire de commande', 'block-traiteur'); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="category_id"><?php _e('Catégorie', 'block-traiteur'); ?> <span class="required">*</span></label>
                    </th>
                    <td>
                        <select id="category_id" name="category_id" required>
                            <option value=""><?php _e('Sélectionner une catégorie', 'block-traiteur'); ?></option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo esc_attr($category->id); ?>" 
                                        <?php selected($is_edit ? $product->category_id : '', $category->id); ?>>
                                    <?php echo esc_html($category->name); ?> (<?php echo esc_html($category->slug ?? 'food'); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="price"><?php _e('Prix', 'block-traiteur'); ?> <span class="required">*</span></label>
                    </th>
                    <td>
                        <input type="number" id="price" name="price" step="0.01" min="0" class="small-text" 
                               value="<?php echo $is_edit ? esc_attr($product->price) : ''; ?>" required>
                        <span class="currency">€ TTC</span>
                        <p class="description"><?php _e('Prix unitaire toutes taxes comprises', 'block-traiteur'); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="unit"><?php _e('Unité', 'block-traiteur'); ?></label>
                    </th>
                    <td>
                        <select id="unit" name="unit">
                            <option value="piece" <?php selected($is_edit ? $product->unit : 'piece', 'piece'); ?>><?php _e('Pièce', 'block-traiteur'); ?></option>
                            <option value="gramme" <?php selected($is_edit ? $product->unit : '', 'gramme'); ?>><?php _e('Gramme', 'block-traiteur'); ?></option>
                            <option value="portion" <?php selected($is_edit ? $product->unit : '', 'portion'); ?>><?php _e('Portion', 'block-traiteur'); ?></option>
                            <option value="personne" <?php selected($is_edit ? $product->unit : '', 'personne'); ?>><?php _e('Personne', 'block-traiteur'); ?></option>
                        </select>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="min_quantity"><?php _e('Quantité minimum', 'block-traiteur'); ?></label>
                    </th>
                    <td>
                        <input type="number" id="min_quantity" name="min_quantity" min="1" class="small-text" 
                               value="<?php echo $is_edit ? esc_attr($product->min_quantity) : '1'; ?>">
                        <p class="description"><?php _e('Quantité minimum pouvant être commandée', 'block-traiteur'); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="max_quantity"><?php _e('Quantité maximum', 'block-traiteur'); ?></label>
                    </th>
                    <td>
                        <input type="number" id="max_quantity" name="max_quantity" min="1" class="small-text" 
                               value="<?php echo $is_edit ? esc_attr($product->max_quantity) : ''; ?>">
                        <p class="description"><?php _e('Laisser vide pour aucune limite', 'block-traiteur'); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="description"><?php _e('Description', 'block-traiteur'); ?></label>
                    </th>
                    <td>
                        <textarea id="description" name="description" rows="4" class="large-text"><?php echo $is_edit ? esc_textarea($product->description) : ''; ?></textarea>
                        <p class="description"><?php _e('Description affichée dans le formulaire', 'block-traiteur'); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="ingredients"><?php _e('Ingrédients', 'block-traiteur'); ?></label>
                    </th>
                    <td>
                        <textarea id="ingredients" name="ingredients" rows="3" class="large-text"><?php echo $is_edit ? esc_textarea($product->ingredients) : ''; ?></textarea>
                        <p class="description"><?php _e('Liste des ingrédients principaux', 'block-traiteur'); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="allergens"><?php _e('Allergènes', 'block-traiteur'); ?></label>
                    </th>
                    <td>
                        <input type="text" id="allergens" name="allergens" class="regular-text" 
                               value="<?php echo $is_edit ? esc_attr($product->allergens) : ''; ?>">
                        <p class="description"><?php _e('Ex: Gluten, Lait, Œuf, Fruits à coque', 'block-traiteur'); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="image_url"><?php _e('Image', 'block-traiteur'); ?></label>
                    </th>
                    <td>
                        <div class="image-upload-container">
                            <input type="url" id="image_url" name="image_url" class="regular-text" 
                                   value="<?php echo $is_edit ? esc_url($product->image_url) : ''; ?>" 
                                   placeholder="https://exemple.com/image.jpg">
                            <button type="button" class="button upload-image-button">
                                <?php _e('Choisir une image', 'block-traiteur'); ?>
                            </button>
                            <div class="image-preview">
                                <?php if ($is_edit && $product->image_url): ?>
                                    <img src="<?php echo esc_url($product->image_url); ?>" 
                                         alt="<?php echo esc_attr($product->name); ?>" 
                                         style="max-width: 150px; height: auto;">
                                <?php endif; ?>
                            </div>
                        </div>
                        <p class="description"><?php _e('URL de l\'image du produit (optionnel)', 'block-traiteur'); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="service_type"><?php _e('Disponible pour', 'block-traiteur'); ?></label>
                    </th>
                    <td>
                        <select id="service_type" name="service_type">
                            <option value="both" <?php selected($is_edit ? $product->service_type : 'both', 'both'); ?>><?php _e('Restaurant et Remorque', 'block-traiteur'); ?></option>
                            <option value="restaurant" <?php selected($is_edit ? $product->service_type : '', 'restaurant'); ?>><?php _e('Restaurant uniquement', 'block-traiteur'); ?></option>
                            <option value="remorque" <?php selected($is_edit ? $product->service_type : '', 'remorque'); ?>><?php _e('Remorque uniquement', 'block-traiteur'); ?></option>
                        </select>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="sort_order"><?php _e('Ordre d\'affichage', 'block-traiteur'); ?></label>
                    </th>
                    <td>
                        <input type="number" id="sort_order" name="sort_order" min="0" class="small-text" 
                               value="<?php echo $is_edit ? esc_attr($product->sort_order) : '0'; ?>">
                        <p class="description"><?php _e('Plus le nombre est petit, plus le produit apparaît en premier', 'block-traiteur'); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="is_active"><?php _e('Statut', 'block-traiteur'); ?></label>
                    </th>
                    <td>
                        <fieldset>
                            <label>
                                <input type="checkbox" id="is_active" name="is_active" value="1" 
                                       <?php checked($is_edit ? $product->is_active : 1, 1); ?>>
                                <?php _e('Produit actif (visible dans le formulaire)', 'block-traiteur'); ?>
                            </label>
                        </fieldset>
                    </td>
                </tr>
            </tbody>
        </table>
        
        <p class="submit">
            <?php if ($is_edit): ?>
                <input type="submit" name="update_product" class="button-primary" 
                       value="<?php _e('Mettre à jour le produit', 'block-traiteur'); ?>">
            <?php else: ?>
                <input type="submit" name="add_product" class="button-primary" 
                       value="<?php _e('Ajouter le produit', 'block-traiteur'); ?>">
            <?php endif; ?>
            
            <a href="<?php echo admin_url('admin.php?page=block-traiteur-products'); ?>" class="button">
                <?php _e('Annuler', 'block-traiteur'); ?>
            </a>
        </p>
    </form>
</div>

<script>
jQuery(document).ready(function($) {
    // Upload d'image avec media library WordPress
    $('.upload-image-button').on('click', function(e) {
        e.preventDefault();
        
        var frame = wp.media({
            title: '<?php _e('Sélectionner une image', 'block-traiteur'); ?>',
            button: {
                text: '<?php _e('Utiliser cette image', 'block-traiteur'); ?>'
            },
            multiple: false
        });
        
        frame.on('select', function() {
            var attachment = frame.state().get('selection').first().toJSON();
            $('#image_url').val(attachment.url);
            $('.image-preview').html('<img src="' + attachment.url + '" style="max-width: 150px; height: auto;">');
        });
        
        frame.open();
    });
    
    // Prévisualisation de l'image URL
    $('#image_url').on('blur', function() {
        var url = $(this).val();
        if (url) {
            $('.image-preview').html('<img src="' + url + '" style="max-width: 150px; height: auto;" onerror="this.style.display=\'none\'">');
        } else {
            $('.image-preview').empty();
        }
    });
});
</script>

<style>
.required {
    color: #d63384;
}

.currency {
    margin-left: 5px;
    font-weight: 500;
}

.image-upload-container {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    flex-wrap: wrap;
}

.image-preview {
    margin-top: 10px;
}

.image-preview img {
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 4px;
}

.form-table th {
    width: 200px;
}

.product-form .form-table td {
    vertical-align: top;
}

.description {
    margin-top: 5px !important;
    font-style: italic;
    color: #646970;
}
</style>