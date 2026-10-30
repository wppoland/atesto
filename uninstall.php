<?php
/**
 * Uninstall cleanup for Proof.
 *
 * Removes the plugin's own options and cached feed when it is deleted from
 * wp-admin. Proof stores no custom tables and never touches WooCommerce data.
 *
 * @package Proof
 */

declare(strict_types=1);

defined('WP_UNINSTALL_PLUGIN') || exit;

/**
 * Remove this plugin's options and cached feed from the current site.
 */
function proof_uninstall_site(): void
{
    delete_option('proof_settings');
    delete_option('proof_db_version');
    delete_transient('proof_feed_cache');
}

// Options are per site, so a network uninstall has to visit every site.
if (is_multisite()) {
    foreach (get_sites(['fields' => 'ids', 'number' => 0]) as $proof_site_id) {
        switch_to_blog((int) $proof_site_id);
        proof_uninstall_site();
        restore_current_blog();
    }
    unset($proof_site_id);
} else {
    proof_uninstall_site();
}

// The PRO banner's dismissal is stored per user, so it belongs to the
// plugin rather than to the site content. User meta is global, not
// per-site, which is why this uses delete_metadata's \$delete_all rather
// than a loop over the users of one blog.
delete_metadata('user', 0, 'proof_pro_banner_dismissed', '', true);
