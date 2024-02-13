<?php
if ( ! defined( 'ABSPATH' ) ) exit();

/**
 * Form Hooks Init
*/

class RB_UC_FormHooksInit
{
    private $forms = [];

    function __construct()
    {
        $this->get_forms();
        add_action('rb_uc_after_head', [$this, 'rb_uc_after_head_callback'], 10);
        add_action('rb_uc_after_body', [$this, 'rb_uc_after_body_callback'], 10);
        add_action('wp_footer', [$this, 'rb_uc_in_footer_callback'], 10);
    }

    function rb_uc_after_head_callback()
    {
        $form_after_head = $this->forms;
        echo $form_after_head['form_after_head'];
    }

    function rb_uc_after_body_callback()
    {
        $form_after_head = $this->forms;
        echo $form_after_head['form_after_body'];
    }

    function rb_uc_in_footer_callback()
    {
        $form_after_head = $this->forms;
        echo $form_after_head['form_in_footer'];
    }

    function get_forms()
    {
        $forms = RB_UC_AdminDashboardPages::rb_uc_get_forms_values();
        $this->forms = $forms;
        return $forms;
    }
}

new RB_UC_FormHooksInit();