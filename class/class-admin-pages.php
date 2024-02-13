<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Admin Dashboard Pages class
*/

class RB_UC_AdminDashboardPages
{
    function __construct()
    {
        add_action( 'admin_menu', [ $this, 'rb_uc_register_admin_menu_page'] ) ;
        
    }

    function rb_uc_register_admin_menu_page()
    {
        add_submenu_page( 'options-general.php', 'Tracking code', 'Tracking code', 'manage_options', 'tracking-code', [$this, 'rb_uc_tracking_code_callback'], 10 );
    }

    function rb_uc_tracking_code_callback()
    {
        $save_settings = $this->rb_uc_saving_form_settings();
        $forms_values = $this->rb_uc_get_forms_values();

        $html = '<div class="wrap">';
            $html .= '<h2>'. get_admin_page_title() .'</h2>';
            $html .= '<form action="'.htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'utf-8').'?page=tracking-code&update=true" method="POST" id="rb_uc_save_form_settings">';
                if ( function_exists('wp_nonce_field') ) {
                    $html .= wp_nonce_field('rb_uc_save_form_settings');
                }

                $html .= '<p>Code after<strong><code>'.htmlspecialchars('<head>').'</code></strong>tag:</br>';
                $html .= 'For using this field you need to edit your template files and add the following line just after the opening <strong><code>'.htmlspecialchars('<head>').'</code></strong> tag</br><code>&lt;?php do_action(\'rb_uc_after_head\'); ?&gt;</code></p>';
                $html .= '<p><textarea name="rb_uc_form_after_head">'.$forms_values['form_after_head'].'</textarea></p>';

                $html .= '<p>Code after<strong><code>'.htmlspecialchars('<body>').'</code></strong>tag:</br>';
                $html .= 'For using this field you need to edit your template files and add the following line just after the opening <strong><code>'.htmlspecialchars('<body>').'</code></strong> tag</br><code>&lt;?php do_action(\'rb_uc_after_body\'); ?&gt;</code></p>';
                $html .= '<p><textarea name="rb_uc_form_after_body">'.$forms_values['form_after_body'].'</textarea></p>';

                $html .= '<p>Code in <strong>'.htmlspecialchars('<footer>').'</strong> tag:</p>';
                $html .= '<p><textarea name="rb_uc_form_in_footer">'.$forms_values['form_in_footer'].'</textarea></p>';

                $html .= '<input name="rb_uc_save_form_settings" id="submit" class="button button-primary" value="Save Changes" type="submit">'; 
            $html .= '</form>';
        $html .= '</div>';


        echo $this->rb_uc_tracking_code_admin_styles();

        echo $html;
    }

    private function rb_uc_saving_form_settings()
    {
		if ( isset($_POST['rb_uc_save_form_settings']) ) {
			if ( function_exists('check_admin_referer') ) {
				check_admin_referer('rb_uc_save_form_settings');
			}
            
            $forms = [];

            if ( !empty($_POST['rb_uc_form_after_head']) )
                $forms['form_after_head'] = $_POST['rb_uc_form_after_head'];
            else
                $forms['form_after_head'] = '';

            if ( !empty($_POST['rb_uc_form_after_body']) )
                $forms['form_after_body'] = $_POST['rb_uc_form_after_body'];
            else
                $forms['form_after_body'] = '';

            if ( !empty($_POST['rb_uc_form_in_footer']) )
                $forms['form_in_footer'] = $_POST['rb_uc_form_in_footer'];
            else
                $forms['form_in_footer'] = '';

            update_option( 'rb_uc_forms_values', $forms );

			return true;
		}
    }

    private function rb_uc_tracking_code_admin_styles()
    {
        $styles = '
            <style>
                textarea {
                    min-width: 700px;
                    height: 220px;
                }
            </style>
        ';

        return $styles;
    }

    public static function rb_uc_get_forms_values()
    {
        $forms = [];
        $forms_values = get_option( 'rb_uc_forms_values' );
        $forms['form_after_head'] = ( isset($forms_values['form_after_head']) ) ? stripcslashes($forms_values['form_after_head']) : '';
        $forms['form_after_body'] = ( isset($forms_values['form_after_body']) ) ? stripcslashes($forms_values['form_after_body']) : '';
        $forms['form_in_footer']  = ( isset($forms_values['form_in_footer']) ) ? stripcslashes($forms_values['form_in_footer']) : '';

        return $forms;
    }
}

new RB_UC_AdminDashboardPages();