<?php

/**
* Plugin Name: Pilllz Avatars
* Plugin URI: https://pilllz.com
* Description: Replace your Gravatar by Pilllz avatars !
* Author: meepha
* Text Domain: meepha-pilllz
* Domain Path: /languages
* Version: 0.0.8
* Author URI: https://meepha.com/
* License: GPL-2.0+
*/

/**
 * Class Pilllz
 * 
 * Represents the Pilllz plugin and its configuration settings.
 */
class Pilllz{

    /**
     * @var string $ver_num The version number of the Pilllz plugin.
     */
    public $ver_num = '0.0.8';

    /**
     * @var string $lang The language code used by the Pilllz plugin.
     */
    public $lang = 'fr';

    /**
     * @var string $text_domain The text domain for translating strings in the Pilllz plugin.
     */
    public $text_domain = 'meepha-pilllz';

    /**
     * @var bool $pilllz_api_key The API key for accessing the Pilllz service.
     */
    public $pilllz_api_key = false;

    /**
     * @var bool $pilllz_client_id The client ID for authenticating with the Pilllz service.
     */
    public $pilllz_client_id = false;

    /**
     * @var bool $pillz_active Indicates whether the Pilllz plugin is active or not.
     */
    public $pillz_active = false;

    
    /**
     * Class constructor for the Pilllz Avatars plugin.
     * Initializes the plugin by setting the language for API calls,
     * retrieving and setting plugin options, and registering necessary actions and filters.
     *
     * @since 1.0.0
     */
    public function __construct(){

        /* set the lang for the api call */
        $this->getLang();
        $this->pilllz_api_key = (get_option('pilllz_api_key')  !== "" ? get_option('pilllz_api_key') : false );
        $this->pilllz_client_id = (get_option('pilllz_client_id')  !== "" ? get_option('pilllz_client_id') : false );
        $this->pillz_active = (get_option('pilllz_activate')  === "on" ? true : false );

        /* LANGUAGES */
        add_action( 'init', [$this, 'textdomainloader']);
        add_filter( 'load_textdomain_mofile', [$this, 'load_languages'], 10, 2 );
        /* BOOTSTRAP */
        add_action('admin_init', [$this, 'pilllz_admin_init']);
        add_action('admin_menu', [$this, 'pilllz_add_admin_menu']);
        /* Add option link in plugin list */
        add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), [$this, 'pilllz_add_settings_link'] );
        
        if($this->pillz_active){
            /* REPLACE GRAVATARS */
            add_filter('get_avatar_url', [$this, 'pilllz_replace_url'], 10, 3);
            /* EDIT USER PROFILE */
            add_filter('user_profile_picture_description', [$this, 'user_profile_picture_description'], 10, 2);
            if($this->pilllz_client_id && $this->pilllz_api_key){
                /* ADD SHORTCODE [pilllz-editor user-id="{ID}"] */
                add_shortcode( 'pilllz-editor', [$this, 'pilllz_editor_shortcode']);
            }
        }
        
    }

    /**
     * Sets the language based on the current locale.
     */
    public function getLang(){
        switch (get_locale()) {
            case 'en_GB':
                $this->lang = 'en';
                break;
            case 'en_US':
                $this->lang = 'en';
                break;
            default:
                break;
        }
    }

    /**
     * Loads the plugin's text domain for localization.
     */
    public function textdomainloader() {
        load_plugin_textdomain( $this->text_domain , false, dirname( plugin_basename( __FILE__ ) ) . '/languages' ); 
    }
    
    /**
     * Loads the language file for the specified domain.
     *
     * @param string $mofile The path to the language file.
     * @param string $domain The text domain of the plugin.
     * @return string The updated path to the language file.
     */
    public function load_languages( $mofile, $domain ) {
        if ($this->text_domain === $domain && false !== strpos( $mofile, WP_LANG_DIR . '/plugins/' ) ) {
            $locale = apply_filters( 'plugin_locale', determine_locale(), $domain );
            $mofile = WP_PLUGIN_DIR . '/' . dirname( plugin_basename( __FILE__ ) ) . '/languages/' . $domain . '-' . $locale . '.mo';
        }
        return $mofile;
    }


    /**
     * Adds an admin menu for the Pilllz Avatars plugin.
     */
    public function pilllz_add_admin_menu(){

        $test = add_options_page(
            __('Pilllz Avatars', $this->text_domain),
            __('Pilllz Avatars', $this->text_domain),
            'manage_options',
            'pilllz',
            [$this, 'pilllz_render_options_page'],
        );
        
    }
    
    /**
     * Registers the settings for the Pilllz Avatars plugin.
     */
    public function pilllz_admin_init(){
        
        register_setting(
            'pilllz_options',
            'pilllz_api_key',
            array(
                'type' => 'string',
                'default' => null,
                'sanitize_callback' => 'sanitize_text_field'
            )
        );
        register_setting(
            'pilllz_options',
            'pilllz_client_id',
            array(
                'type' => 'string',
                'default' => null,
                'sanitize_callback' => 'sanitize_text_field'
            )
        );
        register_setting(
            'pilllz_options',
            'pilllz_activate',
            array(
                'type' => 'boolean',
                'default' => false,
                'sanitize_callback' => 'sanitize_text_field'
            )
        );
    }


    /**
     * Renders the options page for Pilllz Avatars plugin.
     *
     * This function displays the form for configuring the plugin options.
     * It includes fields for activating Pilllz Avatars, entering API key, and API client ID.
     *
     * @since 1.0.0
     */
    public function pilllz_render_options_page(){
        ?>
        <div class="wrap">
            <h2><?php _e('Pilllz Avatars', $this->text_domain); ?></h2>

            <?php
            if($this->pillz_active && (!$this->pilllz_api_key || !$this->pilllz_client_id)){
                ?>
                <div class="notice notice-error">
                    <p><?php _e('Pilllz is active, all gravatars will be replaced by random Pilllz avatars. If you wish to enable the avatar editor please register to obtain your API key and Client ID.', $this->text_domain); ?></p>
                </div>
                <?php
            }
            ?>

            <form method="post" action="options.php">
                <?php
                settings_fields('pilllz_options');
                do_settings_sections('pilllz_options');
                ?>
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row"><?php _e('Activate Pilllz Avatars', $this->text_domain); ?></th>
                        <td><input type="checkbox" name="pilllz_activate" <?php checked($this->pillz_active); ?> /></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php _e('API Key', $this->text_domain); ?> <small>(<?php _e('Coming soon : will unlock new features but will need registration on pilllz.com', $this->text_domain); ?>)</small></th>
                        <td><input type="text" name="pilllz_api_key" value="<?php echo esc_attr( get_option('pilllz_api_key') ); ?>" /></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php _e('API Client ID', $this->text_domain); ?> <small>(<?php _e('Coming soon : will unlock new features but will need registration on pilllz.com', $this->text_domain); ?>)</small></th>
                        <td><input type="text" name="pilllz_client_id" value="<?php echo esc_attr( get_option('pilllz_client_id') ); ?>" /></td>
                    </tr>
                </table>
                <?php
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }


    public function user_profile_picture_description($description, $user){
        if($this->pilllz_client_id && $this->pilllz_api_key){
            // Register and enqueue the admin script
            wp_register_script(
                'pilllz-admin',
                plugin_dir_url( __FILE__ ) . 'assets/admin/dist/main.js',
                array(), 
                filemtime( plugin_dir_path( __FILE__ ) . 'assets/admin/dist/main.js' ), 
                true
            );
            wp_enqueue_script( 'pilllz-admin' );
    
            // Enqueue the admin style wp-content/plugins/pilllz-avatars/assets/admin/dist/main.js
            wp_enqueue_style( 'pilllz-admin', plugin_dir_url( __FILE__ ).'assets/admin/dist/main.css', array(), $this->ver_num );
            $description = $this->getEditorBtn($user->ID);
        }else{
            // traduction de ce message en anglais
            $description = _e('Randomly generated profile picture by Pilllz.', $this->text_domain);
        }
        return $description;
    }
        
    /**
     * Replaces the URL with the avatar URL if the Pilllz plugin is active.
     *
     * @param string $url The original URL.
     * @param mixed $idOrEmail The ID or email of the user.
     * @param array $args Additional arguments.
     * @return string The modified URL or the original URL if the Pilllz plugin is not active.
     */
    public function pilllz_replace_url($url, $idOrEmail, $args){

        if($this->pillz_active){

            if(!is_object($idOrEmail) && is_email($idOrEmail)){
                $User = get_user_by('email', $idOrEmail);
                if($User){
                    $idOrEmail = $User->ID;
                }
            }else{
                if($idOrEmail){
                    if(is_object($idOrEmail) ){
                        switch (get_class($idOrEmail)) {
                            case 'WP_Comment':
                                $idOrEmail = $idOrEmail->user_id;
                                break;
                            default:
                                $idOrEmail = $idOrEmail->ID;
                                break;
                        }
                    }
                }
            }
            
            
            if($idOrEmail && strpos($url, 'gravatar.com') !== false){
                return $this->getAvatarUrl($idOrEmail);
            }else{
                return $url;
            }
            
        }else{
            return $url;
        }
    }

    /**
     * Generates the shortcode for the pilllz editor.
     *
     * @param array $atts The accepted parameters for the shortcode.
     * @return string The generated shortcode output.
     */
    public function pilllz_editor_shortcode($atts){
        $user_id = ($atts["user_id"] ?? false) ? intval($atts["user_id"]) : get_current_user_id();
        return $this->getEditor($user_id);
    }


    /**
     * Retrieves the avatar URL for a given user ID.
     *
     * @param int|bool $user_id The ID of the user. Defaults to false.
     * @return string|bool The URL of the avatar if successful, false otherwise.
     */
    public function getAvatarUrl($user_id = false){
        if(!$user_id)
            return false;
        return 'https://pilllz.com/avatar/'.$this->getHash($user_id).'/svg';
    }

    /**
     * Returns the hash value for a given user ID.
     *
     * @param int $user_id The ID of the user.
     * @return string The hash value generated using the user ID.
     */
    public function getHash($user_id){
        if(!$user_id)
            return '';
        return md5('wp-userid-'.$user_id);
    }

    /**
     * Retrieves the editor for a specific user.
     *
     * @param int $user_id The ID of the user.
     * @return string The HTML output of the editor.
     */
    public function getEditor($user_id){
        if(!$user_id)
            return '';

        // Enqueue the necessary JS and CSS files
        // wp_enqueue_script( 'pilllz-editor-script', 'https://pilllz.com/src/widget/pilllz.js', array( 'jquery' ), $this->ver_num, true );

        // Register and enqueue the admin script
        wp_register_script(
            'pilllz-editor-script',
            plugin_dir_url( __FILE__ ) . 'assets/public/pilllz.js',
            array('jquery'), 
            filemtime( plugin_dir_path( __FILE__ ) . 'assets/public/pilllz.js' ), 
            true
        );
        wp_enqueue_script( 'pilllz-editor-script' );

        wp_enqueue_style( 'pilllz-editor-style', 'https://pilllz.com/src/widget/pilllz.theme.css', array(), $this->ver_num );

        $displayed_user = get_user_by( 'ID', $user_id );
        if ( !$user_id && !$displayed_user) {
            return '';
        }
        // Start output buffering
        ob_start();
        ?>
        <div class="pilllz-editor">
            <div 
                id="pilllz_generator" 
                pilllz-apikey="<?= $this->pilllz_api_key; ?>" 
                pilllz-client_id="<?= $this->pilllz_client_id; ?>" 
                pilllz-user_id="<?= $this->getHash($user_id); ?>" 
                pilllz-lang="<?= $this->lang; ?>"
            >
                <?php _e('Loading...', $this->text_domain); ?> <?= $displayed_user->display_name; ?>
            </div>
        </div>
        <?php
        // End output buffering and return the output
        $output = ob_get_clean();
        return $output;
    }

    /**
     * Retrieves the editor button for a specific user.
     *
     * @param int $user_id The ID of the user.
     * @return string The HTML output of the editor button.
     */
    public function getEditorBtn($user_id){
        if(!$user_id)
            return '';

        // Enqueue the necessary JS and CSS files
        // wp_enqueue_script( 'pilllz-editor-script', 'https://pilllz.com/src/widget/pilllz.js', array( 'jquery' ), $this->ver_num, true );

        wp_register_script(
            'pilllz-editor-script',
            plugin_dir_url( __FILE__ ) . 'assets/public/pilllz.js',
            array('jquery'), 
            filemtime( plugin_dir_path( __FILE__ ) . 'assets/public/pilllz.js' ), 
            true
        );
        wp_enqueue_script( 'pilllz-editor-script' );

        wp_enqueue_style( 'pilllz-editor-style', 'https://pilllz.com/src/widget/pilllz.theme.css', 
        array(), $this->ver_num );

        $displayed_user = get_user_by( 'ID', $user_id );
        if ( !$user_id && !$displayed_user) {
            return '';
        }
        // Start output buffering
        ob_start();
        ?>
            <button 
                class="button pilllz-edit-avatar" 
                pilllz-apikey="<?= $this->pilllz_api_key; ?>" 
                pilllz-client_id="<?= $this->pilllz_client_id; ?>" 
                pilllz-user_id="<?= $this->getHash($user_id); ?>" 
                pilllz-lang="<?= $this->lang; ?>"
            >
                <?php _e('Edit your Pilllz', $this->text_domain); ?>
            </button>
        <?php
        // End output buffering and return the output
        $output = ob_get_clean();
        return $output;

    }

    public function pilllz_add_settings_link( $links ) {
        $settings_link = '<a href="options-general.php?page=pilllz">' . __( 'Settings' ) . '</a>';
        array_push( $links, $settings_link );
        return $links;
    }

}

new Pilllz;