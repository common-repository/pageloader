<?php
/*
  Plugin Name: Page Loader
  Plugin URI: http://www.brizgo.net/
  Description: Used to access urls for a specified amount of time . users can view the content of website for specified period of time.
  Version: 1.0
  Author: Brizgo Technology Solutions
  Author URI: www.brizgo.net
 */
require_once(ABSPATH . 'wp-admin/upgrade-functions.php');
global $wpdb;
define('PL_PLUGIN_URL', WP_PLUGIN_URL . "/pageloader/");
define('PL_EMAIL_TB', $wpdb->prefix . 'pl_emails');
define('PL_URLS_TB', $wpdb->prefix . 'pl_urls');

register_activation_hook(__FILE__, 'on_url_install');
add_action('admin_menu', 'pl_adminmenu');
add_action('admin_enqueue_scripts', 'pl_adminscripts_methods');
add_action('init', 'pl_frontscript');
add_action('admin_init', 'pl_frontscript');
add_action('wp_print_styles', 'pl_stylesheets');
add_action('admin_init', 'pl_admin_register_init');

function pl_admin_register_init() {
    wp_enqueue_style('pl_adminstyle', PL_PLUGIN_URL . 'pladminstyle.css');
}

function pl_stylesheets() {
    //wp_enqueue_style('wppm_stylecustom', WPPM_URL.'custom-style.css');
    wp_enqueue_style('wppm_stylecustom', PL_PLUGIN_URL . 'plcustom.css');
}

function pl_adminmenu() {
    add_menu_page('Page Loader', 'Page Loader', 'publish_posts', 'wp_url_selector', 'wp_url_selector_settings');
}

//load admin js files
function pl_adminscripts_methods() {
    wp_enqueue_script(
            'newscript', plugins_url('/pl_javascript.js', __FILE__), array('scriptaculous')
    );
}

function pl_frontscript() {
    if (!is_admin()) {
        wp_enqueue_script('jquery');
    }
}

function on_url_install() {
    global $wpdb;
    if ($wpdb->get_var("show tables like '" . PL_EMAIL_TB . "'") != PL_EMAIL_TB) {
        $sql = "CREATE TABLE " . PL_EMAIL_TB . "(id int NOT NULL AUTO_INCREMENT PRIMARY KEY, email varchar(250))";
        dbDelta($sql);
    }
    if ($wpdb->get_var("show tables like '" . PL_URLS_TB . "'") != PL_URLS_TB) {
        $sql = "CREATE TABLE " . PL_URLS_TB . "(id INT( 30 ) NOT NULL AUTO_INCREMENT ,
url LONGTEXT  NOT NULL ,
expire VARCHAR( 50 ) NOT NULL ,
PRIMARY KEY ( id ))";
        dbDelta($sql);
    }
}

function wp_url_selector_settings() {
    ?>
    <script type="text/javascript">
                                 
        jQuery(document).ready(function ()
        {
            jQuery("#optSelection1").attr('checked', true);
                                
                             
            jQuery(".radioBtn").click(function() {
                var v= jQuery(this).val();
                if(1==v)
                {
                    jQuery('#cmbPages').show();
                    jQuery('#customPage').hide();
                    jQuery('#customPage').val('');
                }
                else if(2==v){
                    jQuery('#customPage').show();
                    jQuery('#cmbPages').hide();
                    jQuery('#cmbPages').val('');
                }

            });

        });
        function selectAll()
        {
            var content=eval(document.getElementById('txtPermalink'));
            content.focus();
            content.select();
        }
    </script>

    <?php
    if (isset($_POST['hiddensave'])) {

        $urlpage = $_POST['urlpage'];
        update_option('pl_pageurl', $urlpage);
    }
    ?>
    <div id="dashboard_right_now" class="post_box">
        <form name="generalsettings" id="generalsettings"  method="post"  enctype="multipart/form-data" action="<?php echo get_bloginfo('url'); ?>/wp-admin/admin.php?page=wp_url_selector" >
            <h3>Page-Loader Settings</h3>  
            <table class="form-postmembers"  cellspacing="8" width="50%">
                <tr>
                    <th>Select a page for the URL</th>
                    <th>
                        <?php
                        $selectedpage = get_option('pl_pageurl');
                        $pages = wp_dropdown_pages(
                                array('post_type' => 'page',
                                    'selected' => $selectedpage,
                                    'name' => 'urlpage',
                                    'show_option_none' => __('Select a page', 'pageloader'),
                                    'sort_column' => 'menu_order, post_title', 'echo' => 0));
                        echo $pages;
                        ?>
                    </th></tr>
                <tr>   

                    <th>
                        <input type="hidden" value="save" name="hiddensave"/>
                        <input class="button-primary"  type="submit" value="Save" name="save" style="margin-left:11px;"  >
                    </th><th></th>
                </tr>
        </form>

        <tr valign="top" style="background-color:#ECECEC;">
            <th scope="row"><b>Get Your Page</b></th> <th></th>
        </tr>
        <tr><th>
                <input type="radio" name="optSelection"  id="optSelection1" class="radioBtn" value="1"  style="float: left; margin-right: 15px;"/>
                Select Page for the content</th><th>
                <?php
                $selectedpage = get_option('pl_pageurl');
                $pages = wp_dropdown_pages(
                        array('post_type' => 'page',
                            'selected' => $selectedpage,
                            'name' => 'cmbPages',
                            'show_option_none' => __('Select a page', 'pageloader'),
                            'sort_column' => 'menu_order, post_title', 'echo' => 0));
                echo $pages;
                ?>
            </th></tr>

        <tr><th>

            <input type="radio" name="optSelection" value="2" id="optSelection2" class="radioBtn" style="float: left; margin-right: 15px;"/>Enter Custom Page</td><td> <input style="display:none;" type="text" name="customPage" id="customPage"  /></td></tr>
    </th> 
    </tr>
    <tr ><th>Duration</th><th><input type="text" name="duration" id="duration"  />
            <select name="cmbDuration" id="cmbDuration" style="height: 25px; width: 145px;">
                <option value="1">Seconds</option>
                <option value="2">Minutes</option>
                <option value="3">Hours</option>
                <option value="4">Days</option>
            </select></th></tr>

    <input type="hidden" value="" id="hidurl" />
    
    <tr> 
        <th>
            <?php
            $pageforurl = get_option('pl_pageurl');
            if ($pageforurl == '') {
                $pageforurl = 0;
            }
            ?>
            <input type="button" class="button-primary"  name="goBtn" id="goBtn"  value="Go" onclick="createmyurl(<?php echo $pageforurl; ?>)"/>
            click go button and use the url.
        </th>
        <th>
            <textarea id="txtPermalink"  name="txtPermalink" cols="66" rows="4" onclick="selectAll();" style="width:300px;  display:none"/>
        </th>
    </tr>

    </table>

    </div>
    <?php
}

function pl_filtercontent($content) {
    global $wpdb;

    $showurlid = get_option('pl_pageurl');
    $plurl = $_REQUEST['plurl'];

    if ((is_page($showurlid)) && ($plurl != '')) {

        // $myresult = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".PL_URLS_TB." WHERE id ='$d'",$plurl));
        $myresult = $wpdb->get_row("SELECT * FROM ".PL_URLS_TB . " WHERE id =".$plurl);
        $weburl = $myresult->url;
        $expiretime = $myresult->expire;

        $currenttime = time();
           
        if ($currenttime <= $expiretime) {

            ?>
            <div id="back">
                <div class="className" id="test">
                    <div class="signup1">
                        <div class="headingforsignup">
                            <h3>Enter email address</h3>
                        </div>
                        <div class="form_signup">
                            <form action="" method="post" id="subscribe_form" name="subscribe_form">
                                
                                <input type="text" id="nl_email" name="nl_email" value="Email" class="text2"   />
                        </div>
                        <div class="submit1"><input onclick="saveemail();" type="button" name="submit_button" class="submit_btn" value="submit" /></div>
                        </form>
                    </div>
                </div>
            </div>
            <script type="text/javascript">

                function validation()
                {
                    var x=document.forms["subscribe_form"]["nl_email"].value;
                    var atpos=x.indexOf("@");
                    var dotpos=x.lastIndexOf(".");
                    if (atpos<1 || dotpos<atpos+2 || dotpos+2>=x.length)
                    {
                        alert("enter a valid e-mail address");
                        return false;
                    }
                    else{
                        return true;
                    }
                }

              
            </script>

            <?php
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL,$weburl);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
            curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

            $contents = curl_exec($ch);
         
            echo $contents;
            curl_close($ch);
        } else {

            $content = "You can't view the content. content expired";
            echo $content;
        }
        exit();
    } else {
        return ($content);
    }
}

add_action('wp_head', 'pl_filtercontent');


add_action('wp_ajax_save_url', 'save_url_callback');

function save_url_callback() {
    global $wpdb; // this is how you get access to the database

    $page = $_POST['page'];
    $expire = $_POST['duration'];
    $interval = $_POST['cmbDuration'];
    $exturl = $_POST['exturl'];

    switch ($interval) {
        case 1:
            $extratime = $expire;
            break;

        case 2:
            $extratime = $expire * 60;
            break;

        case 3:
            $extratime = $expire * 3600;
            break;

        case 4:
            $extratime = $expire * 86400;
            break;
    }
     
    if (0 == $exturl) {
        $href = get_permalink($page);
    } else {
        $href = $page;
    }

    $current_time = time();
    $expiretime = $current_time + $extratime;

//$query = "INSERT INTO ".PL_URLS_TB." (url,expire) VALUES (%s, %s)";  
//$lastid =$wpdb->query($wpdb->prepare($query,$href,$expiretime));  

    $wpdb->insert(
            PL_URLS_TB, array(
        'url' => $href,
        'expire' => $expiretime
            ), array(
        '%s',
        '%s'
            )
    );
    $lid = $wpdb->insert_id;

    $showurlid = get_option('pl_pageurl');
    $showurl = get_permalink($showurlid);

    $rest = substr($showurl, -1);
    if ($rest == '/') {
        $result = $showurl . '?plurl=' . $lid;
    } else {
        $result = $showurl . '&plurl=' . $lid;
    }
    $result = array('permalink' => $result);
    $output = json_encode($result);
    echo $output;
    die(); // this is required to return a proper result
}

add_action('admin_print_scripts', 'save_url_javascript');

function save_url_javascript() {
    ?>
    <script type="text/javascript" >

        function createmyurl(pageforurl){
            
            var innerpage = jQuery('#cmbPages').val();
            var duration =  jQuery('#duration').val();
            var customUrl = jQuery('#customPage').val();
            var cmbDuration = jQuery('#cmbDuration').val();
               
                                   
            var data = {};
            data['duration']= duration;
            data['cmbDuration'] = cmbDuration;
            data['action'] = 'save_url';
               
           if(jQuery('#optSelection1').is(':checked')){
                data['page'] = innerpage;
                data['exturl'] = 0;
               
           }
                
            if(customUrl =='')
            {
                data['page'] = innerpage;
                data['exturl'] = 0;
            }
            else if(innerpage=='')
            {
                data['page'] = customUrl;       
                data['exturl'] = 1;        
            }
                                                       
            if(data['page']=='')
            {
                alert('Enter a url or page');
                false;
            }
            else if(duration=='')
            {
                alert('Enter duration');
                false;
            }
            else if(pageforurl==''||pageforurl==0)    {
                alert('Save a page for url');
                false;
            }
           
            else{
                // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
                jQuery.post(ajaxurl, data, function(response) {
                                                           
                    //var plink = JSON.parse(response);
                    jQuery('#txtPermalink').show();
                    jQuery('#txtPermalink').val(response.permalink);
                           
                }, "json");
            }

        }
    </script>
<?php
}

add_action('wp_ajax_save_email', 'save_email_callback');

function save_email_callback() {
    global $wpdb;
    $mail = $_POST['email'];
    $id = $_POST['action'];
//echo $mail;
    $wpdb->insert(
            PL_EMAIL_TB, array(
        'email' => $mail
            ), array(
        '%s'
            )
    );
//$wpdb->insert('wp_email', array('email'=>$mail) );
    $la_id = $wpdb->insert_id;
    $result = array('result' => $la_id);
    $output = json_encode($result);
    echo $output;
}

add_action('wp_enqueue_scripts', 'ajax_save_email');

function ajax_save_email() {
    ?>
    <script type="text/javascript" >
        function saveemail(){
            var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
            if(validation())
            {
                var email = jQuery("#nl_email").val();
                              
                var data = {};
                data['email'] = email ;
                data['action'] = 'save_email';
                              
                jQuery.post(ajaxurl, data, function(response) {
                                  
                                                                           
                    jQuery("#back").fadeOut("slow");
                    jQuery("#back").hide();
                           
                });
                            
                                                   
            }
                 
                 
        }
              
    </script>
    <?php
}
?>