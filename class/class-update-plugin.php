<?php
if ( ! defined( 'ABSPATH' ) ) exit();

/**
 * Update Plugin Class
*/

class RB_UC_PluginUpdate {

    private $transient_name = RB_UC_PLUGIN_BASENAME_DIR . '_key';
    private $remote_json = 'https://devtemp.no/repository/risingbear-user-consent/info.json';

    function __construct()
    {
        add_filter( 'plugins_api', [$this, 'rb_uc_plugin_info'], 10, 3);
        add_filter( 'site_transient_update_plugins', [$this, 'rb_uc_plugin_push_update']);
        add_action( 'upgrader_process_complete', [$this, 'rb_uc_plugin_upgrade_completed'], 10, 2 );
        register_deactivation_hook( RB_UC_PLUGIN_BASENAME_FILE, [$this, 'rb_uc_plugin_activation'] );
    }

    function rb_uc_plugin_info($res, $action, $args)
    {
        // do nothing if this is not about getting plugin information
        if( 'plugin_information' !== $action ) {
            return $res;
        }

        // do nothing if it is not our plugin
        if( RB_UC_PLUGIN_BASENAME_DIR !== $args->slug ) {
            return $res;
        }

        // info.json is the file with the actual plugin information on your server
        $remote = $this->get_remote_json();

        // do nothing if we don't get the correct response from the server
        if( is_wp_error( $remote ) || 200 !== wp_remote_retrieve_response_code( $remote )|| empty(wp_remote_retrieve_body( $remote )) ) {
            return $res;
        }

        $remote = json_decode( wp_remote_retrieve_body( $remote ) );
        
        $res = new stdClass();
        $res->name = $remote->name;
        $res->slug = $remote->slug;
        $res->author = $remote->author;
        $res->author_profile = $remote->author_profile;
        $res->version = $remote->version;
        $res->tested = $remote->tested;
        $res->requires = $remote->requires;
        $res->requires_php = $remote->requires_php;
        $res->download_link = $remote->download_url;
        $res->trunk = $remote->download_url;
        $res->last_updated = $remote->last_updated;
        $res->sections = array(
            'description' => $remote->sections->description,
            'installation' => $remote->sections->installation,
            'changelog' => $remote->sections->changelog
        );

        if( ! empty( $remote->sections->screenshots ) ) {
            $res->sections[ 'screenshots' ] = $remote->sections->screenshots;
        }

        $res->banners = array(
            'low' => $remote->banners->low,
            'high' => $remote->banners->high
        );
        
        return $res;

    }

    function rb_uc_plugin_push_update($transient)
    {
        if ( empty( $transient->checked ) ) {
            return $transient;
        }

        $remote = $this->get_remote_json();
    
        if( 
            is_wp_error( $remote )
            || 200 !== wp_remote_retrieve_response_code( $remote )
            || empty(wp_remote_retrieve_body( $remote ))
        ) {
            return $transient;	
        }
        
        $remote = json_decode( wp_remote_retrieve_body( $remote ) );

        if(
            $remote
            && version_compare( RB_UC_VERSION, $remote->version, '<' )
            && version_compare( $remote->requires, get_bloginfo( 'version' ), '<' )
            && version_compare( $remote->requires_php, PHP_VERSION, '<' )
        ) {
            
            $res = new stdClass();
            $res->slug = $remote->slug;
            $res->plugin = RB_UC_PLUGIN_BASENAME_FILE; // it could be just YOUR_PLUGIN_SLUG.php if your plugin doesn't have its own directory
            $res->new_version = $remote->version;
            $res->tested = $remote->tested;
            $res->package = $remote->download_url;
            $transient->response[ $res->plugin ] = $res;
            
            //$transient->checked[$res->plugin] = $remote->version;
        }
     
        return $transient;
    }

    private function get_remote_json()
    {
        $transient = get_transient($this->transient_name);

        if ( $transient )
            return $transient;
        
        $remote = wp_remote_get(
            $this->remote_json,
            [
                'timeout' => 5,
                'headers' => [
                    'Accept' => 'application/json'
                ]
            ]
        );

        $transient = set_transient(
            $this->transient_name,
            $remote,
            15 * 60 // 15 mins
        );

        return $remote;
    }

    function rb_uc_plugin_upgrade_completed($upgrader_object, $options)
    {
        if( $options['action'] === 'update' && $options['type'] === 'plugin' && isset( $options['plugins'] ) ) {
            foreach( $options['plugins'] as $plugin ) {
                if( $plugin == RB_UC_PLUGIN_BASENAME_FILE ) {
                    delete_transient( $this->transient_name );
                }
            }
        }
    }

    function rb_uc_plugin_activation()
    {
        delete_transient( $this->transient_name );
    }
}

new RB_UC_PluginUpdate();