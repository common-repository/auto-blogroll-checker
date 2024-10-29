<?php 
        /*
        Plugin Name: Auto-Blogroll Checker
        Plugin URI: http://yourdailyguide.org/wp-auto-blogroll-checker/
        Description: Automatically checks all blogroll links if your link is still live. There are times that your link is removed by a webmaster without any notice so this plugin will come in handy especially for people that has a long list of blogroll which will be time consuming on their part if done manually.
        
        Author: Bryan Joseph King
        Version: 2.0
        Author URI: http://bryanjosephking.com/
        */
        
        /*
          Copyright 2010 Bryan Joseph King (email: bryanjosephking@gmail.com)
        
        */
        
        function getData($query) {
          while($row = mysql_fetch_array($query)) {
            $result[] = $row;
          }
          return $result;
        }
        
        function isValidURL($url) {
         return preg_match('|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i', $url);  
        }
        
        
        
        function alc_admin() {
        
          echo '<link rel="stylesheet" href="' . get_settings('siteurl') . '/wp-content/plugins/auto-blogroll-checker/link-checker.css" type="text/css" media="screen" />';
          
          echo '<div class="wrap">';
          
          $alc_add_column = mysql_query("ALTER TABLE wp_links ADD COLUMN link_located varchar(120) AFTER link_name");
          $alc_add_column2 = mysql_query("ALTER TABLE wp_links ADD COLUMN link_status varchar(50) AFTER link_located");
          $alc_add_column3 = mysql_query("ALTER TABLE wp_links ADD COLUMN link_note varchar(100) AFTER link_status");
          
          if($_POST['check_links'] == "Check Links") {
            set_time_limit(999999);
          
            $check_list = getData(mysql_query("SELECT * FROM wp_links"));
            
            for($y=0;$y<count($check_list);$y++) {
            
              if($check_list[$y]['link_located'] != "") {
                $check_url = $check_list[$y]['link_located'];
              } else {
                $check_url = $check_list[$y]['link_url'];
              }
              
              $ch = curl_init();
              $timeout = 5; // set to zero for no timeout
              curl_setopt ($ch, CURLOPT_URL, $check_url);
              curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
              curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
              $file = curl_exec($ch);
              curl_close($ch);
              
              $id = $check_list[$y]['link_id'];

              if(!strpos($file, get_settings('siteurl'))) {
                $query = mysql_query("UPDATE wp_links SET link_status='NOT FOUND' WHERE link_id=$id");
              } else {
                $query = mysql_query("UPDATE wp_links SET link_status='FOUND' WHERE link_id=$id");
              }
            }
            
            echo '<table><tr><td><b>Links Status Checked!</b></td></tr></table>';
          } elseif($_POST['update_location'] == "Update") {
            $id = $_POST['id'];
            $link_located = $_POST['link_located'];
            $link_note = $_POST['link_note'];
            $error_msg = "";
            
            for($x=0;$x<count($link_located);$x++)
            {
              if(isValidURL($link_located[$x]) || $link_located[$x] == "") {
                $query = mysql_query("UPDATE wp_links SET link_located='$link_located[$x]', link_note='$link_note[$x]' WHERE link_id=$id[$x]");
              } else {
                $error_msg .= '<p>' . $link_located[$x] . ' is not a valid URL. Please include http://</p>';
              }
            }
            
            if(empty($error_msg)) {
            
              echo '<table><tr><td><b>Updated Successfully!</b></td></tr></table>';
              
            } else {
            
              echo '<table><tr><td><b>Updated Successfully with errors:</b>' . $error_msg . '</td></tr></table>';
            
            }
          }

        
          $link_list = getData(mysql_query("SELECT * FROM wp_links"));
          
          echo '<table><tr><td>There are times that your link is not located in the Link URL so that\'s the purpose of Link Located that is why it\'s optional because if it\'s left blank then automatically it\'ll use Link URL instead. There are times that your link is not placed in the homepage but in the link page or other pages so you should input that in the Link Located field.</td></tr></table>';
          
          echo '<form method="POST"><table><tr><td>Your Site URL:</td><td><b>' . get_settings('siteurl') . '</b></td></tr></table><table><tr><td><b>Link Name</b></td><td><b>Link URL</b></td><td><b>Link Located URL (Optional)</b></td><td><b>Status</b></td><td><b>Note</b></td></tr>';
          
          for($x=0;$x<count($link_list);$x++) {
            echo '<tr><td>' . $link_list[$x]['link_name'] . '</td><td>' . $link_list[$x]['link_url'] . '</td><td><input type="hidden" name="id[]" value="' . $link_list[$x]['link_id'] . '" /><input type="input" name="link_located[]" value="' . $link_list[$x]['link_located'] . '" /></td><td>' . $link_list[$x]['link_status']  . '</td><td><textarea name="link_note[]" rows="1" cols="25">' . $link_list[$x]['link_note'] . '</textarea></tr>';
          }
          
          echo '</table><form method="POST"><table style="float: left;"><tr><td><input type="submit" name="check_links" value="Check Links" /></td></tr></table><table style="float: left;"><tr><td><input type="submit" name="update_location" value="Update" /></td></tr></table><div style="clear:both;"></div></form>';
          
          echo '<table><tr><td><b>NOTE: Loading Time will differ on how big is your blogroll.</b></td></tr></table>';
          
          echo '</div>';
?>





<?php
        }
        
        function alc_admin_actions() {
          add_options_page("Auto-Blogroll Checker", "Auto-Blogroll Checker", 1, "Auto-Blogroll-Checker", "alc_admin");  //add_options_page('My Plugin Options', 'My Plugin', 'capability_required', 'your-unique-identifier', 'my_plugin_options');

        }

        add_action('admin_menu', 'alc_admin_actions');  //adding of admin menu  (1st - admin menu, 2nd - function to call)
        
        
        

?>