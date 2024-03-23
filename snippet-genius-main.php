<?php
/*
Plugin Name: Snippet Genius
Plugin URI: https://github.com/thimiraperera/snippet-genius
Description: Manage custom code snippets for different parts of your WordPress site with ease.
Author URI: https://github.com/thimiraperera
Version: 1.0
Author: Thimira Perera
License: GPL-3.0-or-later
License URI: https://www.gnu.org/licenses/gpl-3.0.html
*/

// Add link to settings page in plugin action links
add_filter('plugin_action_links', 'snippet_genius_settings_link', 10, 2);

function snippet_genius_settings_link($links, $file) {
    if (plugin_basename(__FILE__) === $file) {
        $settings_link = '<a href="' . admin_url('options-general.php?page=snippet-genius-info') . '">Settings</a>';
        array_unshift($links, $settings_link);
    }
    return $links;
}

// Register activation hook
register_activation_hook(__FILE__, 'snippet_genius_activate');

function snippet_genius_activate() {
    // Get path to wp-content directory
    $wp_content_dir = WP_CONTENT_DIR;

    // Define directory paths relative to wp-content directory
    $snippets_dir = $wp_content_dir . '/snippets/';
    $admin_dir = $snippets_dir . 'admin/';
    $frontend_dir = $snippets_dir . 'frontend/';
    $site_dir = $snippets_dir . 'global/';

    // Create snippets directory
    if (!file_exists($snippets_dir)) {
        mkdir($snippets_dir);
    }

    // Create admin directory
    if (!file_exists($admin_dir)) {
        mkdir($admin_dir);
    }

    // Create frontend directory
    if (!file_exists($frontend_dir)) {
        mkdir($frontend_dir);
    }

    // Create global directory
    if (!file_exists($site_dir)) {
        mkdir($site_dir);
    }
}

// Enqueue styles for the WordPress admin dashboard
function enqueue_admin_styles() {
    wp_enqueue_style('admin-styles', plugins_url('css/style.css', __FILE__));
}
add_action('admin_enqueue_scripts', 'enqueue_admin_styles');

// Enqueue all CSS files within the 'css' folder for the whole WordPress site
function enqueue_snippet_genius_styles() {
    // Get path to the 'css' folder within the plugin directory
    $css_folder_path = plugin_dir_path(__FILE__) . 'css/';

    // Get an array of all CSS files in the 'css' folder
    $css_files = glob($css_folder_path . '*.css');

    // Enqueue each CSS file for the whole WordPress site
    foreach ($css_files as $css_file) {
        // Generate a unique handle for each stylesheet based on the file name
        $handle = 'snippet-genius-' . basename($css_file, '.css');

        // Enqueue the stylesheet for the whole WordPress site
        wp_enqueue_style($handle, plugins_url('css/' . basename($css_file), __FILE__));
    }
}
add_action('wp_enqueue_scripts', 'enqueue_snippet_genius_styles');


// Include Snippets from the 'admin' directory if in admin area
if (is_admin()) {
    $admin_files = glob(WP_CONTENT_DIR . '/snippets/admin/*.php');
    if ($admin_files !== false) {
        foreach ($admin_files as $file) {
            if (file_exists($file)) {
                include_once $file;
            } else {
                error_log("Snippet Genius: Admin file '$file' not found.");
            }
        }
    } else {
        error_log("Snippet Genius: Admin directory not found.");
    }
}

// Include Snippets from the 'global' directory
$global_files = glob(WP_CONTENT_DIR . '/snippets/global/*.php');
if ($global_files !== false) {
    foreach ($global_files as $file) {
        if (file_exists($file)) {
            include_once $file;
        } else {
            error_log("Snippet Genius: Global file '$file' not found.");
        }
    }
} else {
    error_log("Snippet Genius: Global directory not found.");
}

// Include Snippets from the 'frontend' directory if not in admin area
if (!is_admin()) {
    $frontend_files = glob(WP_CONTENT_DIR . '/snippets/frontend/*.php');
    if ($frontend_files !== false) {
        foreach ($frontend_files as $file) {
            if (file_exists($file)) {
                include_once $file;
            } else {
                error_log("Snippet Genius: Frontend file '$file' not found.");
            }
        }
    } else {
        error_log("Snippet Genius: Frontend directory not found.");
    }
}

// Register settings page
function snippet_genius_settings_page() {
    add_options_page(
        'Snippet Genius Info', // Page title
        'Snippet Genius Info', // Menu title
        'manage_options',           // Capability required
        'snippet-genius-info',  // Menu slug
        'snippet_genius_render_settings_page' // Callback function to render the settings page
    );
}
add_action('admin_menu', 'snippet_genius_settings_page');

// Render settings page
function snippet_genius_render_settings_page() {
    ?>
    <div class="wrap">
        <h2>Snippet Genius Info</h2>
        <div style="margin-top:20px; margin-left: 20px;">
            <div style="display: flex;">
                <div class="color-circle" style="background-color: #ff5733;"></div>
                <div style="margin-bottom: 20px;"><strong>Global</strong> - Designed to impact the entire WordPress site, affecting both frontend and backend functionalities universally.</div>
            </div>
            <div style="display: flex;">
                <div class="color-circle" style="background-color: #007bff;"></div>
                <div style="margin-bottom: 20px;"><strong>Frontend</strong> - Designed for modifications visible to visitors and users accessing the site's frontend.</div>
            </div>
            <div style="display: flex;">
                <div class="color-circle" style="background-color: #28a745;"></div>
                <div style="margin-bottom: 20px;"><strong>Admin</strong> - Tailored to enhance or modify functionalities within the WordPress admin dashboard.</div>
            </div>
        </div>

        <button id="delete-snippets-btn" class="button button-danger">Delete Snippets Folder</button>
        <p>Please be aware that the plugin will leave the <code>/snippets/*.*</code> folder behind after you <strong>delete</strong> it. You must first manually delete the Snippets folder by clicking the provided button in order to guarantee the plugin is <strong>completely</strong> removed.</p>
		
        <div class="table-responsive">
			<table class="snippet-genius widefat">
				<thead>
					<tr>
						<th><strong>Snippet Name</strong></th>
						<th><strong>Snippet Description</strong></th>
					</tr>
				</thead>
				<tbody>
					<?php
					// Include Snippets from the 'global' directory
					$global_files = glob(WP_CONTENT_DIR . '/snippets/global/*.php');
					foreach ($global_files as $file) {
						if (file_exists($file)) {
							include_once $file;
							// Get snippet name and description from comments
							$snippet_name = get_snippet_name($file);
							$snippet_description = get_snippet_description($file);
							echo '<tr>';
							echo '<td><div class="color-circle-tbl" style="background-color: #ff5733;"></div><strong>' . $snippet_name . '</strong></td>';
							echo '<td>' . $snippet_description . '</td>';
							echo '</tr>';
						}
					}

					// Include Snippets from the 'frontend' directory
					$frontend_files = glob(WP_CONTENT_DIR . '/snippets/frontend/*.php');
					foreach ($frontend_files as $file) {
						if (file_exists($file)) {
							include_once $file;
							// Get snippet name and description from comments
							$snippet_name = get_snippet_name($file);
							$snippet_description = get_snippet_description($file);
							echo '<tr>';
							echo '<td><div class="color-circle-tbl" style="background-color: #007bff;"></div><strong>' . $snippet_name . '</strong></td>';
							echo '<td>' . $snippet_description . '</td>';
							echo '</tr>';
						}
					}

					// Include Snippets from the 'admin' directory
					$admin_files = glob(WP_CONTENT_DIR . '/snippets/admin/*.php');
					foreach ($admin_files as $file) {
						if (file_exists($file)) {
							include_once $file;
							// Get snippet name and description from comments
							$snippet_name = get_snippet_name($file);
							$snippet_description = get_snippet_description($file);
							echo '<tr>';
							echo '<td><div class="color-circle-tbl" style="background-color: #28a745;"></div><strong>' . $snippet_name . '</strong></td>';
							echo '<td>' . $snippet_description . '</td>';
							echo '</tr>';
						}
					}
					?>
				</tbody>
			</table>
		</div>

    </div>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('delete-snippets-btn').addEventListener('click', function() {
            if (confirm('Are you sure you want to delete the snippet folders and deactivate the plugin?')) {
                // Send an AJAX request to delete snippet folders and deactivate the plugin
                var xhr = new XMLHttpRequest();
                xhr.open('GET', '<?php echo admin_url('admin-ajax.php'); ?>?action=delete_snippet_folders', true);
                xhr.onload = function() {
                    if (xhr.status === 200 && xhr.responseText === 'success') {
                        // Display success message
                        alert('Snippet folders deleted successfully. The plugin has been deactivated.');

                        // Redirect to plugins page
                        window.location.href = '<?php echo admin_url('plugins.php'); ?>';
                    } else {
                        // Display error message
                        alert('Error: Unable to delete snippet folders or deactivate the plugin.');
                    }
                };
                xhr.send();
            }
        });
    });
    </script>
    <?php
}

// Helper function to extract snippet name from file comments
function get_snippet_name($file_path) {
    $file_content = file_get_contents($file_path);
    preg_match('/Snippet Name: (.*)/', $file_content, $matches);
    return isset($matches[1]) ? $matches[1] : 'Unnamed Snippet';
}

// Helper function to extract snippet description from file comments
function get_snippet_description($file_path) {
    $file_content = file_get_contents($file_path);
    preg_match('/Snippet Description: (.*)/', $file_content, $matches);
    return isset($matches[1]) ? $matches[1] : 'No description available';
}

// Register an AJAX action to handle the deletion of snippet folders and deactivation of the plugin
add_action('wp_ajax_delete_snippet_folders', 'delete_snippet_folders');
function delete_snippet_folders() {
    // Define the path to the snippets root folder
    $snippets_root_folder = WP_CONTENT_DIR . '/snippets';

    // Delete the snippet folders and their contents
    $success = true;
    if (!rrmdir($snippets_root_folder)) {
        $success = false;
    }

    // Deactivate the plugin
    deactivate_plugins(plugin_basename(__FILE__));

    // Send the response
    if ($success) {
        echo 'success';
    } else {
        echo 'error';
    }
    wp_die();
}

// Helper function to recursively delete a directory and its contents
function rrmdir($dir) {
    if (is_dir($dir)) {
        $objects = scandir($dir);
        foreach ($objects as $object) {
            if ($object != '.' && $object != '..') {
                if (is_dir($dir.'/'.$object)) {
                    rrmdir($dir.'/'.$object);
                } else {
                    unlink($dir.'/'.$object);
                }
            }
        }
        reset($objects);
        return rmdir($dir);
    }
    return false;
}