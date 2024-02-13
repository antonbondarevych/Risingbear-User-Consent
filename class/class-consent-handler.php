<?php
if ( ! defined( 'ABSPATH' ) ) exit('access denied');

/**
 * Consent Handler Class
*/

class RB_UC_ConsentHandler
{

    private $COOCKIE_POLICY_DOMAIN = '/';

    function __construct()
    {
        add_shortcode('gdpr_selects', [$this, 'rb_uc_generate_gdpr_shortcode']);
        add_action( 'rb_uc_after_head', [$this, 'rb_uc_header_consent_scripts'], 1 );
        add_action( 'wp_footer', [$this, 'rb_uc_footer_consent_scripts'], 1 );
        add_action( 'wp_enqueue_scripts', [$this, 'rb_uc_consent_styles'] );
    }

    public function rb_uc_generate_gdpr_shortcode()
    {
        $html = '<div>';
        $html .='<p><label for="" class="chslider"><span class="chslider__off"></span> <span class="chslider__circle"></span> <span class="chslider__on"></span></label><u>Nødvendige cookies</u></p>

            <p><input id="audience_cookies" class="ch-slider" name="audience_cookies" type="checkbox"><u>Informasjonskapsler for publikumsmåling:</u></p>

            <ul class="list-clasic">
                <li><a href="https://developers.google.com/analytics/devguides/collection/analyticsjs/cookie-usage">Google Analytics</a></li>
                    <li><a href="https://help.hotjar.com/hc/nb-NO/articles/115011789248-Hotjar-Cookies">Hotjar</a></li>
                    <li><a href="https://snap.com/nb-NO/cookie-policy">Snap</a></li>
                    <li><a href="https://www.tiktok.com/legal/cookie-policy">TikTok</a></li>
                </ul>

        <p><input id="targeted_cookies" class="ch-slider" name="targeted_cookies" type="checkbox"><u>Målrettede informasjonskapsler:</u></p>
            <ul class="list-clasic">
                <li><a href="https://www.facebook.com/policy/cookies/">Facebook</a></li>
                <li><a href="https://policy.pinterest.com/en/cookies/">Pinterest</a></li>
                <li><a href="https://snap.com/nb-NO/cookie-policy">Snap</a></li>
                <li><a href="https://www.tiktok.com/legal/cookie-policy">TikTok</a></li>
            </ul>';
        $html .='</div>';

        return $html;
    }

    function rb_uc_header_consent_scripts()
    {?>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}

            let consentStatusAudience = ( rbuc_getCookie('cookieconsent_status_audience') == 'allow' ) ? 'granted' : 'denied';
            let consentStatusTarget   = ( rbuc_getCookie('cookieconsent_status_targeted') == 'allow' ) ? 'grant' : 'revoke';

            // console.log('consentStatusAudience', consentStatusAudience);
            // console.log('consentStatusTarget', consentStatusTarget);

            setGoogleConsent(consentStatusAudience, 'default');
            setFacebookConsent(consentStatusTarget);

            function rbuc_getCookie(name) {
                const value = `; ${document.cookie}`;
                const parts = value.split(`; ${name}=`);
                if (parts.length === 2) return parts.pop().split(';').shift();
            }

            function setGoogleConsent(consentStatus, action) {
                gtag("consent", action, {
                    ad_storage: consentStatus,
                    analytics_storage: consentStatus,
                    ad_user_data: consentStatus,
                    ad_personalization: consentStatus,
                    functionality_storage: consentStatus,
                    personalization_storage: consentStatus,
                    security_storage: consentStatus,
                    wait_for_update: 500
                });
            }

            function setFacebookConsent(consentStatus) {
                if (typeof fbq == "undefined") { 
                    return;
                }
                fbq('consent', consentStatus);
            }

            function enableAllCookie() {
                setGoogleConsent('granted', 'update');
                setFacebookConsent('grant');
            }

        </script>
    <?php
    }

    function rb_uc_footer_consent_scripts()
    {?>
        <script>
            jQuery(document).ready(function($){
        
                /*
                * GDPR cookies checkbox
                */
        
                /*
                * add html and cookies to the "ch-slider" checkboxes in a cycle
                * added Timeout because in some cases the script did not have time to work
                */
                setTimeout(function(){
                    $(".ch-slider").each(function(index) {
                        $('<label for="' + $(this).attr("id") + '" class="chslider"><span class="chslider__off"></span> <span class="chslider__circle"></span> <span class="chslider__on"></span></label>').insertAfter( $(this) );
        
                        var cookie_name = $(this).attr("name");
                        var el_id = $(this).attr("id");

                        if(cookie_name == 'audience_cookies') {
                            if ( rbuc_getCookie('cookieconsent_status_audience') == 'allow' ) {
                                if (el_id == 'audience_cookies') {
                                    $("#audience_cookies").prop("checked", true);
                                }
                                else {
                                    $("#audience_cookies").prop("checked", false);
                                }
                            }
                            else {
                                $("#audience_cookies").prop("checked", false);
                            }
                        }
                        else if(cookie_name == 'targeted_cookies') {
                            if ( rbuc_getCookie('cookieconsent_status_targeted') == 'allow' ) {
                                if (el_id == 'targeted_cookies') {
                                    $("#targeted_cookies").prop("checked", true);
                                }
                                else {
                                    $("#targeted_cookies").prop("checked", false);
                                }
                            }
                            else {
                                $("#targeted_cookies").prop("checked", false);
                            }
                        }
        
                    })
                }, 400);
        
                /*
                * when the value of the checkbox has been changed, change the cookies
                */
                $('.ch-slider').on('change', function(){
                    let cookie_name = $(this).attr("name");
        
                    if(cookie_name == 'audience_cookies') {
                        // set cookie value
                        if ( $(this).is(":checked") ) {
                            document.cookie = "cookieconsent_status_audience=allow; path=/";
                            document.cookie = "cookieconsent_status=savesettings; path=/";
                            setGoogleConsent('granted', 'update');

                            $('#nsc_bar_input_switchaudience').prop('checked', true);
                        }else {
                            document.cookie = "cookieconsent_status_audience=denied; path=/";
                            setGoogleConsent('denied', 'update');
                            $('#nsc_bar_input_switchaudience').prop('checked', false);
                        }
                    }
                    else if(cookie_name == 'targeted_cookies') {
                        // set cookie value
                        if ( $(this).is(":checked") ) {
                            // rb_setCookie(cookie_name, false, days);
        
                            //delete when off
                            // rb_setCookie('targeted_cookies',"",-1);
                            document.cookie = "cookieconsent_status_targeted=allow; path=/";
                            document.cookie = "cookieconsent_status=savesettings; path=/";
                            // setOptIn();
                            setFacebookConsent('grant');
                            $('#nsc_bar_input_switchtargeted').prop('checked', true);
                        }
                        else {
                            // rb_setCookie(cookie_name, true, days);
                            document.cookie = "cookieconsent_status_targeted=denied; path=/";
                            setFacebookConsent('revoke');
                            $('#nsc_bar_input_switchtargeted').prop('checked', false);
                        }
                    }
                });

                $('body').on('change', '.cc-switch-element input', function(){
                    let elID = $(this).attr('id');
                    if ( elID == 'nsc_bar_input_switchaudience' ) {
                        if ( $(this).is(":checked") )
                            $('#audience_cookies').prop('checked', true);
                        else
                            $('#audience_cookies').prop('checked', false);
                    }

                    if ( elID == 'nsc_bar_input_switchtargeted' ) {
                        if ( $(this).is(":checked") )
                            $('#targeted_cookies').prop('checked', true);
                        else
                            $('#targeted_cookies').prop('checked', false);
                    }
                });
        
                /*
                * if clicked on button "Ok, forstått" on cookie popup on page
                * "Våre retningslinjer for informasjonskapsler" - uncheck checkboxes
                */
                $('body').on('click', '.cc-btn.cc-allowall', function(){
                    enableAllCookie();
                    $('.ch-slider').each(function(index) {
                        $(this).prop( 'checked', true );
                    });
                });
            });
    
         </script>
    <?php
    }

    function rb_uc_consent_styles()
    {
        wp_enqueue_style( 'rb_uc_consent-styles', RB_UC_PLUGIN_URL . '/assets/css/consent-styles.css', false, false, 'all' );
    }
}

new RB_UC_ConsentHandler();