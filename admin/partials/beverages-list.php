<?php
/**
 * Vue d'administration pour la liste des boissons
 * 
 * @package Block_Traiteur
 * @since 1.0.0
 */

// Empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;

// Récupérer les boissons avec leurs catégories
$beverages = $wpdb->get_results("
    SELECT b.*, c.name as category_name, COALESCE(c.slug, 'beverage') as category_slug
    FROM {$wpdb->prefix}block_beverages b
    LEFT JOIN {$wpdb->prefix}block_beverage_categories c ON b.category_id = c.id
    ORDER BY COALESCE(c.sort_order, 0), b.sort_order
");

$categories = $wpdb->get_results("
    SELECT * FROM {$wpdb->prefix}block_beverage_categories 
    ORDER BY sort_order
");
?>

<div class="wrap">
    <h1>
        <?php _e('Gestion des Boissons', 'block-traiteur'); ?>
        <button type="button" class="page-title-action" id="add-beverage-btn">
            <?php _e('Ajouter une boisson', 'block-traiteur'); ?>
        </button>
    </h1>

    <?php if (isset($_GET['message'])): ?>
        <div class="notice notice-success is-dismissible">
            <p>
                <?php 
                switch($_GET['message']) {
                    case 'added':
                        _e('Boisson ajoutée avec succès.', 'block-traiteur');
                        break;
                    case 'updated':
                        _e('Boisson mise à jour avec succès.', 'block-traiteur');
                        break;
                    case 'deleted':
                        _e('Boisson supprimée avec succès.', 'block-traiteur');
                        break;
                }
                ?>
            </p>
        </div>
    <?php endif; ?>

    <!-- Filtres -->
    <div class="tablenav top">
        <div class="alignleft actions">
            <select name="filter_category" id="filter-category">
                <option value=""><?php _e('Toutes les catégories', 'block-traiteur'); ?></option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?php echo esc_attr($category->id); ?>"
                            <?php selected(isset($_GET['filter_category']) ? $_GET['filter_category'] : '', $category->id); ?>>
                        <?php echo esc_html($category->name); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="button" class="button" id="filter-btn"><?php _e('Filtrer', 'block-traiteur'); ?></button>
        </div>
    </div>

    <!-- Tableau des boissons -->
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th scope="col" class="manage-column column-cb check-column">
                    <input type="checkbox" id="cb-select-all-1" />
                </th>
                <th scope="col" class="manage-column"><?php _e('Nom', 'block-traiteur'); ?></th>
                <th scope="col" class="manage-column"><?php _e('Catégorie', 'block-traiteur'); ?></th>
                <th scope="col" class="manage-column"><?php _e('Prix', 'block-traiteur'); ?></th>
                <th scope="col" class="manage-column"><?php _e('Statut', 'block-traiteur'); ?></th>
                <th scope="col" class="manage-column"><?php _e('Actions', 'block-traiteur'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($beverages)): ?>
                <tr>
                    <td colspan="6" class="no-items">
                        <?php _e('Aucune boisson trouvée.', 'block-traiteur'); ?>
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($beverages as $beverage): ?>
                    <tr data-beverage-id="<?php echo esc_attr($beverage->id); ?>">
                        <th scope="row" class="check-column">
                            <input type="checkbox" name="beverage[]" value="<?php echo esc_attr($beverage->id); ?>" />
                        </th>
                        <td class="beverage-name">
                            <strong><?php echo esc_html($beverage->name); ?></strong>
                            <?php if (!empty($beverage->description)): ?>
                                <br><small class="description"><?php echo esc_html($beverage->description); ?></small>
                            <?php endif; ?>
                        </td>
                        <td class="category-name">
                            <?php echo esc_html($beverage->category_name ?: __('Non catégorisé', 'block-traiteur')); ?>
                        </td>
                        <td class="price">
                            <?php echo number_format($beverage->price, 2, ',', ' '); ?> €
                        </td>
                        <td class="status">
                            <?php if ($beverage->is_active): ?>
                                <span class="status-active">✓ <?php _e('Actif', 'block-traiteur'); ?></span>
                            <?php else: ?>
                                <span class="status-inactive">✗ <?php _e('Inactif', 'block-traiteur'); ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="actions">
                            <button type="button" class="button button-small edit-beverage" 
                                    data-beverage-id="<?php echo esc_attr($beverage->id); ?>">
                                <?php _e('Modifier', 'block-traiteur'); ?>
                            </button>
                            <button type="button" class="button button-small button-link-delete delete-beverage" 
                                    data-beverage-id="<?php echo esc_attr($beverage->id); ?>">
                                <?php _e('Supprimer', 'block-traiteur'); ?>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Actions groupées -->
    <div class="tablenav bottom">
        <div class="alignleft actions bulkactions">
            <select name="action2" id="bulk-action-selector-bottom">
                <option value="-1"><?php _e('Actions groupées', 'block-traiteur'); ?></option>
                <option value="activate"><?php _e('Activer', 'block-traiteur'); ?></option>
                <option value="deactivate"><?php _e('Désactiver', 'block-traiteur'); ?></option>
                <option value="delete"><?php _e('Supprimer', 'block-traiteur'); ?></option>
            </select>
            <button type="button" class="button action" id="bulk-action-btn">
                <?php _e('Appliquer', 'block-traiteur'); ?>
            </button>
        </div>
    </div>
</div>

<!-- Modal d'ajout/modification -->
<div id="beverage-modal" class="block-traiteur-modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="modal-title"><?php _e('Ajouter une boisson', 'block-traiteur'); ?></h2>
            <span class="close">&times;</span>
        </div>
        <div class="modal-body">
            <form id="beverage-form">
                <input type="hidden" name="action" value="save_beverage">
                <input type="hidden" name="beverage_id" id="beverage-id" value="">
                <input type="hidden" name="security" value="<?php echo wp_create_nonce('block_traiteur_beverage_nonce'); ?>">
                
                <table class="form-table">
                    <tr>
                        <th><label for="beverage-name"><?php _e('Nom', 'block-traiteur'); ?> *</label></th>
                        <td><input type="text" id="beverage-name" name="name" class="regular-text" required></td>
                    </tr>
                    <tr>
                        <th><label for="beverage-description"><?php _e('Description', 'block-traiteur'); ?></label></th>
                        <td><textarea id="beverage-description" name="description" class="large-text" rows="3"></textarea></td>
                    </tr>
                    <tr>
                        <th><label for="beverage-category"><?php _e('Catégorie', 'block-traiteur'); ?> *</label></th>
                        <td>
                            <select id="beverage-category" name="category_id" required>
                                <option value=""><?php _e('Sélectionner une catégorie', 'block-traiteur'); ?></option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo esc_attr($category->id); ?>">
                                        <?php echo esc_html($category->name); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="beverage-price"><?php _e('Prix (€)', 'block-traiteur'); ?> *</label></th>
                        <td><input type="number" id="beverage-price" name="price" step="0.01" min="0" class="small-text" required></td>
                    </tr>
                    <tr>
                        <th><label for="beverage-sort-order"><?php _e('Ordre d\'affichage', 'block-traiteur'); ?></label></th>
                        <td><input type="number" id="beverage-sort-order" name="sort_order" class="small-text" value="0"></td>
                    </tr>
                    <tr>
                        <th><label for="beverage-active"><?php _e('Statut', 'block-traiteur'); ?></label></th>
                        <td>
                            <label>
                                <input type="checkbox" id="beverage-active" name="is_active" value="1" checked>
                                <?php _e('Actif', 'block-traiteur'); ?>
                            </label>
                        </td>
                    </tr>
                </table>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="button" id="cancel-beverage"><?php _e('Annuler', 'block-traiteur'); ?></button>
            <button type="button" class="button button-primary" id="save-beverage"><?php _e('Enregistrer', 'block-traiteur'); ?></button>
        </div>
    </div>
</div>

<style>
.block-traiteur-modal {
    position: fixed;
    z-index: 100000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5);
}

.modal-content {
    background-color: #fefefe;
    margin: 5% auto;
    padding: 0;
    border: 1px solid #888;
    width: 600px;
    max-width: 90%;
    border-radius: 4px;
}

.modal-header {
    padding: 20px;
    background-color: #f1f1f1;
    border-bottom: 1px solid #ddd;
    position: relative;
}

.modal-header h2 {
    margin: 0;
    font-size: 1.3em;
}

.close {
    position: absolute;
    right: 20px;
    top: 20px;
    color: #aaa;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
}

.close:hover {
    color: #000;
}

.modal-body {
    padding: 20px;
}

.modal-footer {
    padding: 20px;
    background-color: #f1f1f1;
    border-top: 1px solid #ddd;
    text-align: right;
}

.status-active {
    color: #46b450;
}

.status-inactive {
    color: #dc3232;
}
</style>

<script>
jQuery(document).ready(function($) {
    // Modal controls
    $('#add-beverage-btn').on('click', function() {
        $('#modal-title').text('<?php _e('Ajouter une boisson', 'block-traiteur'); ?>');
        $('#beverage-form')[0].reset();
        $('#beverage-id').val('');
        $('#beverage-modal').show();
    });

    $('.edit-beverage').on('click', function() {
        var beverageId = $(this).data('beverage-id');
        // Charger les données de la boisson via AJAX
        loadBeverageData(beverageId);
    });

    $('.close, #cancel-beverage').on('click', function() {
        $('#beverage-modal').hide();
    });

    // Fermer modal en cliquant à l'extérieur
    $(window).on('click', function(event) {
        if (event.target.id === 'beverage-modal') {
            $('#beverage-modal').hide();
        }
    });

    // Sauvegarder la boisson
    $('#save-beverage').on('click', function() {
        var formData = $('#beverage-form').serialize();
        
        $.post(ajaxurl, formData, function(response) {
            if (response.success) {
                location.reload();
            } else {
                alert('Erreur: ' + response.data);
            }
        }).fail(function() {
            alert('Erreur de communication avec le serveur.');
        });
    });

    // Fonctions utilitaires
    function loadBeverageData(beverageId) {
        $.post(ajaxurl, {
            action: 'get_beverage_data',
            beverage_id: beverageId,
            security: '<?php echo wp_create_nonce('block_traiteur_beverage_nonce'); ?>'
        }, function(response) {
            if (response.success) {
                var data = response.data;
                $('#modal-title').text('<?php _e('Modifier la boisson', 'block-traiteur'); ?>');
                $('#beverage-id').val(data.id);
                $('#beverage-name').val(data.name);
                $('#beverage-description').val(data.description);
                $('#beverage-category').val(data.category_id);
                $('#beverage-price').val(data.price);
                $('#beverage-sort-order').val(data.sort_order);
                $('#beverage-active').prop('checked', data.is_active == 1);
                $('#beverage-modal').show();
            }
        });
    }
});
</script>
