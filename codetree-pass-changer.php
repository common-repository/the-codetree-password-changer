<?php
/*
Plugin Name: CodeTree Pass Changer
Version: 2.0
Plugin URI: http://www.mycodetree.com
Donate link: http://mycodetree.com/donations/
Description: Works with an active http://mycodetree.com subscription to remind users to change account passowrd at specified intervals
Author: Mycodetree
Author URI: http://www.mycodetree.com/

Copyright 2010 mycodetree.com.  (email: support@mycodetree.com)

While this software is free of charge, it is designed to work with an active
subscription from http://mycodetree.com.

*/
add_action('admin_menu', 'codetree_pass_changer');

add_filter( 'plugin_action_links', 'codetree_pass_changer_add_action_link', 10, 2 );

/*** CRON SETUP ***/                                                                                                         
register_activation_hook(__FILE__, 'my_activation');
add_action('pass_compare', 'do_compare');

function my_activation() {
    wp_schedule_event(time(), 'daily', 'pass_compare');
}

register_deactivation_hook(__FILE__, 'my_deactivation');

function my_deactivation() {
    wp_clear_scheduled_hook('pass_compare');
}

function do_compare() {
    global $wpdb;
    $users = $wpdb->get_col( $wpdb->prepare("SELECT $wpdb->users.ID,$wpdb->users.user_pass FROM $wpdb->users")); 
    foreach ($users as $use) {
        $user = get_userdata($use); 
        if (get_user_meta($user->ID, 'codetree_pass_monitor', true) != 'off') {
            $time = explode('|:|', get_user_meta($user->ID, 'codetree_pass_monitor', true));
            if (calculator($time[1]) < 1) {
                //compare the passwords and notify or save new         
                if ($time[0] == $user->user_pass) {
                    //FIRE ALERT SYSTEM
                    alertSystem($user->user_email,get_user_meta($user->ID, 'first_name', true),get_bloginfo('name'),get_bloginfo('admin_email'), get_bloginfo('url'));
                }
                else {
                    //RESAVE NEW PASSWORD AND RESET MONITOR
                    update_user_meta($user->ID, 'codetree_pass_monitor', $user->user_pass . "|:|" . time());
                }
            }
        }
    }
}
/*** END CRON SETUP ***/

function codetree_pass_changer() {
    add_options_page('Codetree Pass Changer Options', 'Codetree Pass', 'administrator', 'codetree_pass_changer_options', 'codetree_pass_changer_options');  
}

function codetree_pass_changer_add_action_link( $links, $file ) {
    static $this_plugin;
     if( ! $this_plugin ) $this_plugin = plugin_basename(__FILE__);
    if ( $file == $this_plugin ) {
        $settings_link = '<a href="' . admin_url( 'options-general.php?page=codetree_pass_changer_options' ) . '">' . __('Settings') . '</a>';
        array_unshift( $links, $settings_link ); // before other links
    }
    return $links;
}

function alertSystem($alertAddress, $firstname, $blogname, $blogemail, $blogurl) {
    $subject = "Password Change Alert";
    $headers = "From: $blogname <$blogemail>\r\n";
    $headers .= "Reply-To: <$blogemail>\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
    $message = "
    <html>
    <head>
    <title>Password Change Alert</title>
    </head>
    <body>
    <center>$blogname is protected by:<br /><a href='http://mycodetree.com' title='http://mycodetree.com'><img src='http://mycodetree.com/wp-content/uploads/2010/06/vsmall_MyCodetree.jpg' border='0' width='150px;' alt='The CodeTree | http://mycodetree.com'></a><br /><a href='http://mycodetree.com' title='http://mycodetree.com'>http://mycodetree.com</a></center>
    <br />
    <div style='padding: 5px; color: white; background-color: #125F99; width: 95%; -webkit-border-radius: 7px; -moz-border-radius: 7px; border-radius: 7px; text-align: left; font-family: helvetica;'>Hey $firstname, it is time to change your account password at <a href='$blogurl' target='_blank' style='color: white;'>$blogname</a>. Please login to your account as soon as possible and change your password. You will receive this same email daily until your account password has been changed.</div>
    <br />
    <div style='padding: 5px; color: white; background-color: #F36525; width: 95%; -webkit-border-radius: 7px; -moz-border-radius: 7px; border-radius: 7px; text-align: right; font-family: helvetica;'>Feel free to use The <a href='http://mycodetree.com/password-generator/' target='_blank' style='color: white;'>CodeTree Password Generator</a> for password ideas :)</div>
    <br />
    <div style='text-align: center; font-family: Helvetica; font-size: 10px; color: #B0B0B0;'>This email was sent to you because you have an active account with $blogurl and because our records indicate that it is time for you to change your account password.</div>
    </body>
    </html>
    ";
    mail($alertAddress, $subject, $message, $headers);
}

function codetree_pass_changer_postbox($id, $title, $content, $donate = true) {
    ?>
        <div id="<?php echo $id; ?>" class="postbox">
            <div class="handlediv" title="Click to toggle"><br /></div>
            <h3 class="hndle"><span><?php echo $title; ?></span></h3>
            <div class="inside">
                <?php echo $content; ?>
            </div>
<?php 
if ($donate) {
$donerTest = codetree_pass_changer_apiBinder(get_option('codetree-pass-changer-api')); 
if (!$donerTest[0]) {
?>
<div style="text-align: right;margin-right: 3px;">
Consider a small donation :)
<br />
<a href='http://mycodetree.com/donations' target='_blank'><img src="https://www.paypal.com/en_US/i/btn/btn_donateCC_LG.gif"></a>
<br /><br />
</div>
<?php } }?>
        </div>
    <?php
}

function codetree_pass_changer_form_table($rows) {
    $content = '<table class="form-table" width="100%">';
    foreach ($rows as $row) {
        $content .= '<tr><th valign="top" scope="row" style="width:50%">';
        if (isset($row['id']) && $row['id'] != '')
            $content .= '<label for="'.$row['id'].'" style="font-weight:bold;">'.$row['label'].':</label>';
        else
            $content .= $row['label'];
        if (isset($row['desc']) && $row['desc'] != '')
            $content .= '<br/><small>'.$row['desc'].'</small>';
        $content .= '</th><td valign="top">';
        $content .= $row['content'];
        $content .= '</td></tr>'; 
    }
    $content .= '</table>';
    return $content;
}

function codetree_pass_changer_form_checks($rows) {
    $content = '<table class="form-table" width="100%">';
    foreach ($rows as $row) {
        $content .= '<tr><th valign="top" scope="row" style="width:50%;">';
        if (isset($row['id']) && $row['id'] != '')
            $content .= '<label for="'.$row['id'].'" style="font-weight:bold;">'.$row['label'].'</label>';
        else
            $content .= $row['label'];
        if (isset($row['desc']) && $row['desc'] != '')
            $content .= '<br/><small><em>'.$row['desc'].'</em></small>';
        $content .= '</th><td valign="middle">';
        $content .= $row['content'];
        $content .= '</td></tr>'; 
        $content .= '<tr><td><hr /></td></tr>';
    }
    $content .= '</table>';
    return $content;
}

function codetree_pass_changer_get_target($string, $start, $end){ 
    $string = " ".$string; 
    $ini = strpos($string,$start); 
    if ($ini == 0) return ""; 
    $ini += strlen($start); 
    $len = strpos($string,$end,$ini) - $ini; 
    return substr($string,$ini,$len); 
} 

function calculator($userSaveTime) {
    $secondsToAlert = NULL;
    $distance = NULL;
    $result = NULL;
    $secondsToAlert = get_option('codetree-pass-changer-duration') * 86400;
    $distance = time() - $userSaveTime;
    $result = $secondsToAlert - $distance;
    $final = round($result / 86400);
    if (round($result / 86400) < 0) {
        return(0);
    }    
    return (round($result / 86400));
}

function codetree_pass_changer_apiBinder($api) {   
  $errmsg = array();   
  $response = @file_get_contents("http://mycodetree.com/backup-manager/authentication.php?motion=bind&api=$api");
  $error = codetree_pass_changer_get_target($response, "<error>", "</error>");
  if (!empty($error)) {
      $errmsg = array(false, "<div style='background-color: Gold; color: Maroon; padding: 5px; -webkit-border-radius: 7px; -moz-border-radius: 7px; border-radius: 7px;'>" . $error . "</div>");
  }
  else {
      $fn = codetree_pass_changer_get_target($response, "<firstname>", "</firstname>");
      $dn = codetree_pass_changer_get_target($response, "<domain>", "</domain>");
      $site = str_replace('http://', '', get_option('siteurl'));
      if (substr($site,0,4) == 'www.' OR substr($site,0,4) == 'WWW.') {
          $site = str_replace('www.', '', $site);
          $site = str_replace('WWW.', '', $site);
      }
      if ($site == $dn) {
         
         $errmsg = array(true, "<div style='background-color: Green; color: White; padding: 5px; -webkit-border-radius: 7px; -moz-border-radius: 7px; border-radius: 7px;'>Hey " . ucfirst(strtolower($fn)) . ", we think it is pretty awesome that you are using the Codetree Pass Changer from <a href='http://mycodetree.com' style='color: white; font-weight: bold;' target='_blank'>http://mycodetree.com</a>! Thanks !!!</div>");
      }
      else {
          $errmsg = array(false, "<div style='background-color: Gold; color: Maroon; padding: 5px; -webkit-border-radius: 7px; -moz-border-radius: 7px; border-radius: 7px;'>The API key does not match the domain</div>");
      }
  }
  return $errmsg;      
}
function codetree_pass_changer_options() { 
global $wpdb;
$szSort = "user_nicename";
$aUsersID = $wpdb->get_col( $wpdb->prepare("SELECT $wpdb->users.ID FROM $wpdb->users ORDER BY %s ASC", $szSort )); 
?>
 <div class="wrap">
    <h2>Codetree Pass Changer for WordPress</h2>
    <div class="postbox-container" style="width:70%;">
        <div class="metabox-holder">    
            <div class="meta-box-sortables">
            <form id='apiKey' action="#" method="post">
            <?php 

                if ( function_exists('wp_nonce_field') ) wp_nonce_field('codetree-pass-changer-update-options');
                //API Key
                if (isset($_POST['codetree-pass-changer-api']) && !empty($_POST['codetree-pass-changer-api'])) {
                  $apikey = trim(stripslashes($_POST['codetree-pass-changer-api'])); 
                  update_option('codetree-pass-changer-api', $apikey);             
                }
                //Duration
                if (isset($_POST['codetree-pass-changer-duration']) && ! empty($_POST['codetree-pass-changer-duration'])) {
                    update_option('codetree-pass-changer-duration', trim(stripslashes($_POST['codetree-pass-changer-duration'])));
                }

                //Sort the checks  
                if (isset($_POST['codetree-pass-changer-duration'])) {
                    foreach ($_POST as $k => $v) {
                        $test = explode('_', $k);
                        if ($test[0] == 'codetree-pass-changer') {
                            $getpass =  $wpdb->get_col( $wpdb->prepare("SELECT $wpdb->users.user_pass FROM $wpdb->users WHERE $wpdb->users.ID='" . $test[1]. "'"));
                            $currentStatus = get_user_meta($test[1], 'codetree_pass_monitor', true);
                            if ($v[0] == 'on') {
                                if ($currentStatus == 'off') {
                                    update_user_meta($test[1], 'codetree_pass_monitor', $getpass[0] . "|:|" . time());
                                }
                            }
                            else {
                                update_user_meta($test[1], 'codetree_pass_monitor', 'off');
                            }
                        }
                    }
                }  
                
                $isvalid = codetree_pass_changer_apiBinder(get_option('codetree-pass-changer-api'));
                $getAPI = NULL;
                if (!$isvalid[0]) {
                    $getAPI = "&nbsp;&nbsp;&nbsp;&nbsp;(<a href='http://mycodetree.com' target='_blank'>Get API Key</a>)";
                }
                $rows[] = array(
                        'id' => 'codetree-pass-changer-api',
                        'label' => 'Codetree Pass Changer API Key',
                        'desc' => 'Your API key is found in the subscription profile area at http://mycodetree.com',
                        'content' => "<input type='text' name='codetree-pass-changer-api' value='" . get_option('codetree-pass-changer-api') . "'/>$getAPI"
                    );
                if (!$isvalid[0]) {
                 $rows[] = array(
                        'id' => 'codetree-pass-changer-duration',
                        'label' => 'Codetree Monitor Duration',
                        'desc' => 'How long The CodeTree Pass Changer should wait before reminding a user to change passwords. This is relative to each individual user and when that user last changed passwords.',
                        'content' => "<select name='codetree-pass-changer-duration'/><option>" . get_option('codetree-pass-changer-duration') . "</option><option>30</option><option>45</option><option>60</option></select>&nbsp;days (a valid API key will unrestrict the duration)"
                    ); 
                }
                else {
                $rows[] = array(
                        'id' => 'codetree-pass-changer-duration',
                        'label' => 'Codetree Monitor Duration',
                        'desc' => 'How long The CodeTree Pass Changer should wait before reminding a user to change passwords. This is relative to each individual user and when that user last changed passwords.',
                        'content' => "<input type='text' name='codetree-pass-changer-duration' size='3' value='" . get_option('codetree-pass-changer-duration') . "'/>&nbsp;days"
                    );    
                }
                foreach ( $aUsersID as $iUserID ) :
                $user = get_userdata( $iUserID );
                $curr = array();
                $oChk = NULL;
                $offChk = NULL;
                $timeLeft = NULL;
                $gettime = NULL;
                $curr = get_user_meta($user->ID, 'codetree_pass_monitor', true);
                if (empty($curr) OR $curr == '') {
                    update_user_meta($user->ID, 'codetree_pass_monitor', 'off');
                    $curr = 'off';
                }
                if ($curr == 'off') {
                    $offChk = "checked='checked'";
                }
                else {
                    $oChk = "checked='checked'";
                    $gettime = explode('|:|', $curr);
                    $tl = calculator($gettime[1]);
                    $timeLeft = " " . ($tl == 0 ? "<span style='color: maroon;'>($tl days left)</span>" : "<span style='color: green;'>($tl days left)</span>") . " [<a href='http://mycodetree.com/password-generator/' target='_blank' title='MyCodeTree Password Generator'>Password Generator</a>]";
                    
                }
                $checks[] = array (
                    'id' => 'codetree-pass-changer[]',
                    'label' => '<img src=\'http://www.gravatar.com/avatar/' . md5(trim(strtolower($user->user_email))). '\' border=\'0\' align=\'bottom\' style=\'padding: 8px;\' width=\'32px\' border=\'0\'><br /><a href=\'/wp-admin/user-edit.php?user_id=' . $user->ID . '&wp_http_referer=/wp-admin/users.php\' title=\'Click to edit the profile for ' . $user->first_name . ' ' . $user->last_name . '\'>' . $user->first_name . ' ' . $user->last_name . '</a>',
                    'desc' => 'Monitor password change for ' . $user->first_name . ' ' . $user->last_name,
                    'content' => "On: <input type='radio' name='codetree-pass-changer_" . $user->ID . "[]' value='on' $oChk> Off: <input type='radio' name='codetree-pass-changer_" . $user->ID . "[]' value='off' $offChk>$timeLeft"
                );
                endforeach;

                ?>        
                <input type="hidden" name="action" value="update" />
                <input type="hidden" name="action" value="update" />
                <?php codetree_pass_changer_postbox('codetreepasschangersettings','Codetree Pass Changer Settings (<a href=\'http://wordpress.org/extend/plugins/search.php?q=the+codetree\' target=\'_blank\'>see all CodeTree plugins</a>)', codetree_pass_changer_form_table($rows)); ?>
                <?php codetree_pass_changer_postbox('codetreepasschangersettings_2','Users To Monitor', codetree_pass_changer_form_checks($checks), false); ?>
                <?=$isvalid[1];?>

                <p class="submit">
                <input type="submit" class="button-primary" name="save" value="<?php _e('Save Changes') ?>" />
                </p>
            </form>

            </div>
        </div>
    </div>
</div>
<?php } ?>