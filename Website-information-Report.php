<?php
/**
 *  Plugin name: Website information Report
 *  Description: This Plugin Gives you infomation like wordpress Version,php Version,User active status(Away to online,online,offline),Installed Plugin(Active plugins,Inactive plugins),Comments,post.
 *  Author: Narola Infotech Solutions LLP
 *  Version: 1.3
 *  License:GPLv2
 */
/*This function used create menu on admin side*/

function wir_Report_panel()
{
    add_menu_page('Report page title', 'Website Report', 'manage_options', 'Website Reports', 'wir_Reports_func','');
}
add_action('admin_menu','wir_Report_panel');
/*This function used for display report*/	
register_activation_hook( __FILE__, 'plugin_activate' );//Add Column On plugin Activation
function plugin_activate() { 
      global $wpdb;
      $table_name = $wpdb->prefix . 'users';
      $db = $wpdb->dbname;
      $query = "SELECT COUNT( * ) as found FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME =  '$table_name' AND COLUMN_NAME = 'user_login_status' AND TABLE_SCHEMA = '$db'";
      $results = $wpdb->get_row($query);
      if ($results->found == "0") {
            $wpdb->query('ALTER TABLE ' . $table_name . '  ADD user_login_status varchar(5)');
      }
      if ( is_user_logged_in() ) {//check Admin is logged in or Not
            $user_id = get_current_user_id();
            $wpdb->query("UPDATE $table_name SET `user_login_status` = '3' WHERE $table_name.ID = $user_id");
            sleep(10);
            $wpdb->query("UPDATE $table_name SET `user_login_status` = '1' WHERE $table_name.ID = $user_id");
      }else{
            $user_id = get_current_user_id();
            $wpdb->query("UPDATE $table_name SET `user_login_status` = '0' WHERE $table_name.ID = $user_id");
      }
}
register_deactivation_hook(__FILE__,'Plugin_dectivate');//Add Column On plugin deactivation
function Plugin_dectivate()
{
      global $wpdb;
      $table_name = $wpdb->prefix . 'users';
      $db = $wpdb->dbname;
      $query = "SELECT COUNT( * ) as found FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME =  '$table_name' AND COLUMN_NAME = 'user_login_status' AND TABLE_SCHEMA = '$db'";
      $results = $wpdb->get_row($query);
      if ($results->found > "0") {
            $wpdb->query('ALTER TABLE ' . $table_name . '  DROP user_login_status');
      }
}
add_action('wp_login', 'custom_user_status_login', 10, 2);//login function	
function custom_user_status_login($user_login, $user){
      global $wpdb;
      $table_name = $wpdb->prefix . 'users';
      $wpdb->query("UPDATE $table_name SET `user_login_status` = '3' WHERE $table_name.ID = $user->ID");
      sleep(10);
      $wpdb->query("UPDATE $table_name SET `user_login_status` = '1' WHERE $table_name.ID = $user->ID");
}

add_action('wp_logout', 'custom_user_status_logout');//Logout function
function custom_user_status_logout($user_id){
      global $wpdb;
     // $user_id = get_current_user_id();
      $table_name = $wpdb->prefix . 'users';
      $wpdb->query("UPDATE $table_name SET `user_login_status` = '0' WHERE ID = $user_id");
}
function wir_Reports_func()
{
      _e('<div class="title"><h1>Website Report</h1></div>');//Page title
      $html='<br><table class="system_env">';
      $html.='<th colspan="2">Environment</th>';
      $html.='<tr><td>Current PHP Version &ratio;&emsp;&emsp;&emsp;&emsp;</td><td>'.phpversion().'</td></tr>';//php version
      function wir_site_version()//Wordpress version
       {
             global $wp_version;
             $v = substr($wp_version, 0, 3);
             return $v;
       }
       $html.='<tr><td>Current WordPress Version &ratio;</td><td>'.wir_site_version().'</td></tr>';
       $html.='</table>';
       _e($html);
       $total_user = count_users();
       $totaluser = $total_user['total_users'];//Total users
       _e('<br><table class="system_env">');
       _e('<th colspan="2">Users</th>');
       _e('<tr><td>Total Users &ratio;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;</td><td>'.$totaluser.'</td></tr>');
       _e('<tr><td><b>Status</b></td></tr>');
       _e('<tr><table class="user_status"><thead><tr><th>Name</th><th>Email</th><th>Date Registration</th><th>Status</th></tr></thead>');//User Detail
       _e('<tbody></tbody></table></tr></table>');

       $total_post = wp_count_posts()->publish;//Total Post
       _e('<br><table class="system_env">');
       _e('<th colspan="2">Post & Comments</th>');
       _e('<tr><td>Total Published Post &ratio;</td><td>'.$total_post.'</td></tr>');
       $total_comments = wp_count_comments()->all;//Total comments
       $approved_comments = wp_count_comments()->approved;//Total Approved comments
       $unapproved_comments = wp_count_comments()->moderated;//Total Approved comments
       $html='<tr><td>Total Published Comments &ratio;</td><td>'.$total_comments.'</td></tr>';
       $html.='<tr><td>Approved Comments &ratio;</td><td>'.$approved_comments.'</td></tr>';
       $html.='<tr><td>Pending Comments &ratio;</td><td>'.$unapproved_comments.'</td></tr>';
       $html.='</table>';
       _e($html);
       $allplugins = get_plugins();//Installed Plugins
       $all_plug=array();//Blank array for assign all installed plugins name
       $total_plug=count($allplugins);//count total installed plugins
       _e('<br><table class="system_env">');
       _e('<th colspan="2">Plugins</th>');
       $html='<tr><td>Current Intalled Plugins are &ratio;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;</td><td>'.$total_plug.'</td></tr>';
       _e($html);
       _e('<tr><td><ol>');
       foreach($allplugins as $plug)
       {
             _e('<li>');
             print_r($plug['Name']);
             array_push($all_plug,$plug['Name']);
             _e('</li></br>');
       }
       _e('</ol></td></tr><br>');
       $html='<tr><td>Current Active Plugins are &ratio;</td>';
       _e($html);//Active Plugins
       $active_plug=get_option('active_plugins');
       $activated_plugins=array();
       $act_plug=array();//Blank array veriable for active plugins name 
       _e('<td><ol>');
       foreach($active_plug as $actplug)
       {        
             if(isset($allplugins[$actplug]))
             {
                   array_push($activated_plugins, $allplugins[$actplug]);
             }         
       }
       foreach($activated_plugins as $active_p)
       {
             _e('<li>');
             print_r($active_p['Name']);
             array_push($act_plug,$active_p['Name']);
             _e('</li>');
       }
       _e('</ol></td></tr>');
       _e('<tr><td>Current Inactive Plugins are &ratio;</td>');
       $inactivated_plugins = array_diff($all_plug,$act_plug);
       _e('<td><ol>');
       foreach($inactivated_plugins as $inactive)//Inactive plugins
       {
             _e('<li>'.$inactive.'</li>'); 
       }
       _e('</ol></td></tr></table>');
}
function customadd_Css()//adding CSS
{
      $path = plugins_url();
      wp_enqueue_style('custom_css',$path.'/Website-information-Report/wordpress.css');   
}
add_action('admin_enqueue_scripts','customadd_Css');

add_action( 'wp_ajax_show_user_status','show_user_status' );
add_action( 'wp_ajax_nopriv_show_user_status','show_user_status' );

function show_user_status() {
      global $wpdb;
      $table_name = $wpdb->prefix . 'users';
      $query= "SELECT user_login,user_email,user_registered,user_login_status FROM `$table_name`";
      $result = $wpdb->get_results($query);
      $path =  plugins_url().'/Website-information-Report/image';//image folder path
      foreach($result as $res)
      {
            _e('<tr><td>'.$res->user_login.'</td><td>'.$res->user_email.'</td><td>'.$res->user_registered.'</td>');
            if($res->user_login_status == 1)
            {
                  echo "<td><img src='$path/online.png' alt='Online'></td></tr>";//online
            }elseif($res->user_login_status == 3)
            {
                  echo "<td><img src='$path/yellow.png' alt='Online'></td></tr>";//away
            }else{
                  echo "<td><img src='$path/red.png' alt='Offline'></td></tr>";//offline
            }
      }

      exit();
}

add_action('admin_footer', 'show_user_status_script');

function show_user_status_script() {
      $ajaxUrl = admin_url( 'admin-ajax.php' );
?>
<script>
      jQuery(document).ready(function(){
            loadUserStatus();
            setInterval(loadUserStatus, 5000);
      });

      function loadUserStatus() {
            jQuery.ajax({
                  type: 'POST',
                  dataType: 'html',
                  url: '<?php echo $ajaxUrl; ?>',
                  async: false,
                  data: {
                        action: 'show_user_status'
                  },
                  success: function(html) {
                        jQuery('.user_status tbody').html(html);
                  },
                  error: function(jqXHR, textStatus, errorThrown){
                        console.log(jqXHR + " :: " + textStatus + " :: " + errorThrown);
                  }
            });
      }
</script>
<?php
}
?>