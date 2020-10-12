<?php
/**
*@package LMSReports
*/
/*
Plugin Name: LMS Reports
Description: Custom user reports for LMS staff, with site and group computations.
Version: 0.4.201902dev
Author: Jared Meidal
Aurthor URI: https://github.com/kahu5
License: GPLv2 or later
 */
 add_action('admin_menu', 'lmsreports_plugin_setup_menu');
 function lmsreports_plugin_setup_menu(){
         add_menu_page( 'LMS Reports', 'Reports', 'manage_options', 'lmsreports-plugin', 'lmsreports_init','dashicons-feedback' );
 }
 function lmsreports_init(){
         echo "<h1>Minstry Understanding Course Reports</h1>";
         //Database Access
         global $wpdb;

       //variable set
       $page = "lmsreports-plugin";
       $location = NULL;
       $location .= $_REQUEST['location'];
       $manager  = NULL;
       $manager .= $_REQUEST['manager'];
       $course  = NULL;
       $course .= $_REQUEST['course'];
       //$course  = array("106","160");

wp_register_script('prefix_bootstrap', '//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js');
wp_enqueue_script('prefix_bootstrap');

wp_register_style('prefix_bootstrap', '//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css');
wp_enqueue_style('prefix_bootstrap');
echo '<div class="row">';
//LOCATION SELECTION
global $wpdb;
  $sql="SELECT DISTINCT B_location.meta_value as location
  FROM {$wpdb->prefix}usermeta as A_manager
  JOIN {$wpdb->prefix}usermeta as B_location ON A_manager.`user_id`= B_location.`user_id`
  AND A_manager.`meta_key` = 'manager'
  AND B_location.`meta_key` = 'location'
  INNER JOIN {$wpdb->prefix}users as C_Student ON A_manager.meta_value = C_Student.ID ";

  if(empty($_REQUEST['manager'])){
    $sql .= "WHERE A_manager.meta_value != 'null' ";
  }
  elseif(!empty($_REQUEST['manager'])){
    $sql .= "WHERE A_manager.meta_value = '".$manager."' ";
  }
    $sql .= "AND B_location.meta_value != 'null' ORDER BY C_Student.display_name ASC";
          //echo "location:<code>$sql</code>";
             $result = $wpdb->get_results($sql, ARRAY_A);
           if(!$result){
           }
           else{
             echo '<div class="col-md-4">
            <div class="dropdown">
               <button class="btn btn-primary dropdown-toggle" type="button" data-toggle="dropdown">Select Location
               <span class="caret"></span></button> '.$location.'';
               if ($wpdb->num_rows=='1' && empty($location)){ echo $result['0']['location'];}
               echo '<ul class="dropdown-menu">';
             if ($location!=NULL){
                       echo "<a class='btn btn-default btn-sm' href='?page=".$page."&course=".$course."&location=&manager=".$manager."'><i class=\"fa fa-eraser\"></i> CLEAR LOCATION</a>";
             }
             else{
               //echo "<small><i class=\"fa fa-filter\"></i> SELECT LOCATION</a></small><br>";
             }
             foreach($result as $row){
               $location_set1 = NULL;
               $location_set2 = NULL;
               if (isset($location) && $location==$row['location']){
                 $location_set1 = "<span style='font-weight:800;color:black'>";
                 $location_set2 = "</span>";
               }
             echo "<li><a href='?page=".$page."&course=".$course."&location=".$row['location']."&manager=".$manager."'>".$location_set1.$row['location'].$location_set2."</a></li>";
               }
         echo '
               </ul>
               </div></div>';

           }

$wpdb->flush();

//MANAGER ARRAY
global $wpdb;
  $sql = "SELECT ID,display_name FROM `{$wpdb->prefix}users`";
    $result = $wpdb->get_results($sql, ARRAY_A);
    if(!$result){
    }
    else{
    $managerList = array();
    foreach($result as $row){
  $managerList[$row['display_name']] = $row['ID'];
    }
  }

//MANAGER SELECTION
global $wpdb;
  $sql="SELECT DISTINCT C_Student.display_name as manager,C_Student.ID
  FROM {$wpdb->prefix}usermeta as A_manager
  JOIN {$wpdb->prefix}usermeta as B_location ON A_manager.`user_id`= B_location.`user_id`
  AND A_manager.`meta_key` = 'manager'
  AND B_location.`meta_key` = 'location'
  INNER JOIN {$wpdb->prefix}users as C_Student ON A_manager.meta_value = C_Student.ID ";

  if(empty($_REQUEST['location'])){
  $sql .= "WHERE B_location.meta_value != 'null' ";
  }
  elseif(!empty($_REQUEST['location'])){
    $sql .= "WHERE B_location.meta_value = '".$location."' ";
  }
  $sql .= "AND A_manager.meta_value != 'null' ORDER BY C_Student.display_name ASC";

      //echo "<br> manager:<code>$sql</code>";
         $result = $wpdb->get_results($sql, ARRAY_A);
       if(!$result){
       }
       else{
         $ManagerName = array_search($_REQUEST["manager"], $managerList);
         echo '<div class="col-md-4">
        <div class="dropdown">
           <button class="btn btn-primary dropdown-toggle" type="button" data-toggle="dropdown">Select Manager
           <span class="caret"></span></button> '.$ManagerName.'';
            if ($wpdb->num_rows=='1' && empty($manager)){ echo $result['0']['manager'];}
           echo '<ul class="dropdown-menu">';
         if ($manager!=NULL){
           echo "<a class='btn btn-default btn-sm' href='?page=".$page."&course=".$course."&location=".$location."&manager='><i class=\"fa fa-eraser\"></i> CLEAR MANAGER</a> <br>";
         }
         else{
           //echo "<small><i class=\"fa fa-filter\"></i> SELECT MANAGER</a></small><br>";
         }
         foreach($result as $row){
           $manager_set1 = NULL;
           $manager_set2 = NULL;
           if ($manager==$row['ID']){
             $manager_set1 = "<span style='font-weight:800;color:black'>";
             $manager_set2 = "</span>";
           }
       echo "<li><a href='?page=".$page."&course=".$course."&location=".$location."&manager=".$row['ID']."'>".$manager_set1.$row['manager'].$manager_set2."</a></li>";
         }
           echo '</ul>
                 </div></div>';
       }
$wpdb->flush();

      //COURSE ARRAY
       $sql = "SELECT ID,post_title FROM `{$wpdb->prefix}posts` WHERE post_type = 'sfwd-courses' AND post_status = 'publish' AND (post_title NOT LIKE '%ITNA%') ORDER BY post_title ASC";
         $result = $wpdb->get_results($sql, ARRAY_A);
         if(!$result){
         }
         else{
         $coursesList[] = array();
         foreach($result as $row){
       $coursesList[$row['post_title']] = $row['ID'];
         }
       }
//COURSE SELECTION
        echo '<div class="col-md-4">
        <div class="dropdown">
          <button class="btn btn-primary dropdown-toggle" type="button" data-toggle="dropdown">Select Course
          <span class="caret"></span></button> ';
    if (empty($course)){
         $sql = "SELECT DISTINCT meta_course.meta_value as course_progress
         FROM `{$wpdb->prefix}users`
         LEFT JOIN {$wpdb->prefix}usermeta as meta_location ON {$wpdb->prefix}users.ID = meta_location.user_id and meta_location.meta_key = 'location'
         LEFT JOIN {$wpdb->prefix}usermeta as meta_manager ON {$wpdb->prefix}users.ID = meta_manager.user_id and meta_manager.meta_key = 'manager'
         LEFT JOIN {$wpdb->prefix}usermeta as meta_course ON {$wpdb->prefix}users.ID = meta_course.user_id AND meta_course.meta_key = '_sfwd-course_progress'
         LEFT JOIN {$wpdb->prefix}posts as meta_posts ON meta_course.meta_value LIKE CONCAT('%',meta_posts.ID,'%') AND meta_course.meta_key = '_sfwd-course_progress' ";
         if(!empty($_REQUEST['location']) && !empty($_REQUEST['manager'])){
         $sql .= "WHERE meta_location.meta_value = '".$location."' AND meta_manager.meta_value = '".$manager."' ";
         }
         elseif(empty($_REQUEST['location']) && !empty($_REQUEST['manager'])){
         $sql .= "WHERE meta_location.meta_value != 'null' AND meta_manager.meta_value = '".$manager."' ";
         }
         elseif(!empty($_REQUEST['location']) && empty($_REQUEST['manager'])){
         $sql .= "WHERE meta_location.meta_value != '".$location."' AND meta_manager.meta_value != 'null' ";
         }
         else{
         $sql .= "WHERE meta_location.meta_value != 'null' AND meta_manager.meta_value != 'null' ";
         }
         $sql .= "ORDER BY course_progress ASC;";

           $result = $wpdb->get_results($sql, ARRAY_A);
           if(!$result){
           }
           else{

         }
      echo $CourseName.'
      <ul class="dropdown-menu">';
                      $i = "0";
                      $courseIDcheck = array();
             foreach($result as $row){
               if (empty($course)){
               $course_progress = unserialize($row["course_progress"]);
             if (!empty($course_progress)){
               foreach($course_progress as $course_id => $coursep){
                 $course_id = get_post($course_id);
                 $course_ids = $course_id->ID;
                 $course_title = $course_id->post_title;

    if (!in_array($course_ids, $courseIDcheck) || $i == "0"){
                   $course_set1 = NULL;
                   $course_set2 = NULL;
                   if ($course==$course_id){
                     $course_set1 = "<span style='font-weight:800;color:black'>";
                     $course_set2 = "</span>";
                   }
                         echo "<li><a href='?page=".$page."&course=".$course_ids."&location=".$location."&manager=".$manager."'>".$course_set1.$course_title.$course_set2."</a></li>";
             //echo "<option value=\"".$row['ID']."\">".$row['post_title']."</option>";
           }
           $i++;
           array_push($courseIDcheck, $course_ids);
               }
             }
             }
             }
           }
           else{
             $sql = "SELECT ID,post_title FROM `{$wpdb->prefix}posts` WHERE post_type = 'sfwd-courses' AND post_status = 'publish' AND (post_title NOT LIKE '%ITNA%') ORDER BY post_title ASC";
               $result = $wpdb->get_results($sql, ARRAY_A);
               if(!$result){
               }
               else{
                 $CourseName = array_search($_REQUEST["course"], $coursesList);
               $coursesList[] = array();
               foreach($result as $row){
             $coursesList[$row['post_title']] = $row['ID'];
               }
               echo $CourseName.'
               <ul class="dropdown-menu">';
             }
             echo "<a class='btn btn-default btn-sm' href='?page=".$page."&course=&location=".$location."&manager=".$manager."'><i class=\"fa fa-eraser\"></i> CLEAR COURSE</a>
             <!--:".array_search($course, $coursesList)."-->
             ";
                 foreach($result as $row){
                   $course_set1 = NULL;
                   $course_set2 = NULL;
                   if ($course==$row['ID']){
                     $course_set1 = "<span style='font-weight:800;color:black'>";
                     $course_set2 = "</span>";
                   }
                         echo "<li><a href='?page=".$page."&course=".$row['ID']."&location=".$location."&manager=".$manager."'>".$course_set1.$row['post_title'].$course_set2."</a></li>";
             //echo "<option value=\"".$row['ID']."\">".$row['post_title']."</option>";
               }
           }
           echo '</ul>
                 </div></div></div>
                 <br>';
                        //  echo "<code>course: $sql</code>";
$wpdb->flush();

//Report Generation
echo '<div class="row">';
         global $wpdb;

         //SELECT FILTER
         $wheresql = array();
         $sql = "SELECT ID, display_name, user_email
             ,meta_location.meta_value as location
             ,meta_manager.meta_value as manager_id
             /*,meta_manager_nickname.meta_value as manager_nickname*/
             ,meta_course.meta_value as course_progress
         FROM `{$wpdb->prefix}users`
         LEFT JOIN {$wpdb->prefix}usermeta as meta_location ON {$wpdb->prefix}users.ID = meta_location.user_id and meta_location.meta_key = 'location'
         LEFT JOIN {$wpdb->prefix}usermeta as meta_manager ON {$wpdb->prefix}users.ID = meta_manager.user_id and meta_manager.meta_key = 'manager'
         /*LEFT JOIN {$wpdb->prefix}usermeta as meta_manager_nickname ON meta_manager.meta_value = meta_manager_nickname.user_id and meta_manager_nickname.meta_key = 'nickname' */
         LEFT JOIN {$wpdb->prefix}usermeta as meta_course ON {$wpdb->prefix}users.ID = meta_course.user_id AND meta_course.meta_key = '_sfwd-course_progress'
         ";
         if (!empty($course)){        array_push($wheresql," meta_course.meta_value LIKE '%$course%'");}
         //if (!empty($course)){        array_push($wheresql," meta_course.meta_value LIKE '%$course[0]%' OR meta_course.meta_value LIKE '%$course[1]%'");}
         if (!empty($manager)){            array_push($wheresql," meta_manager.meta_value = '$manager'");}
         if (!empty($location)){            array_push($wheresql," meta_location.meta_value = '$location'");}
         if (count($wheresql)>0){
             $i = 0;
             foreach($wheresql as $wherestatement){
                 if ($i==0){  $sql .= " WHERE"; }
                 else {  $sql .= " AND"; }
                 $sql .= " $wherestatement";
                 $i++;
             }
         }
         $sql .= " ORDER BY user_login ASC";
         //echo "<br>records:<code>$sql</code>";
         $result = $wpdb->get_results($sql, ARRAY_A);
         $row_cnt = ($wpdb->num_rows);

         if(!$result){
             echo "No progress reported in this course.";
         }
         else{
         echo '<div class="col-md-8">';
             $CourseName = array_search($course, $coursesList);
             echo "<h3>$CourseName</h3>";
        // echo '<div><a href="#" onclick="window.location("/wp-content/plugins/back2back-reports/export-csv.php"); return false"><i class="fa fa-file-excel-o fa-2x"></i> Download CSV </a>[coming soon]</div>';
         echo "<table class=\"table table-striped\" cell-padding=\"6px\">";
         echo "<tr><th>ID</th><th>Student</th><th>Email</th><th>Location</th><th>Manager</th>";
    if (!empty($_REQUEST['course'])){
             echo "<th width=\"20px\">Progress</th>";
    }
         echo "</tr>";
         $UsersPercTotal = 0;
         $countES = 0;
         $percES = 0;

         foreach($result as $row){
         if (!empty($course)){
         $course_progress = unserialize($row["course_progress"]);
         $return = NULL;
         }
         $maxEN = 0;
         if (!empty($course_progress)){

         foreach($course_progress as $course_id => $coursep){
         					$course_id = get_post($course_id);
                   if ($course_id->ID == $course){
                 //if ($course_id->ID == $course){
         					$return .= "<b>".$course_id->post_title."</b>: ";
                   $courseCompletedDate = $coursep['time'];
                   $course_completed = $coursep['completed'];
                   $course_total = $coursep['total'];
                     $UserPercentage = number_format(($course_completed / $course_total)* 100, 1);
                     // $UsersPercTotal = $UsersPercTotal + $UserPercentage;
                   $return .= "" .$course_completed. " / " .$course_total. "<br>".$UserPercentage;
                   //$return .= $UserPercentage;
                   $maxEN = max($maxEN, $UserPercentage);
                             $UsersPercTotal = $UsersPercTotal + $maxEN;
         			}
               }
             }
         				//$return .=   "<br>";
                $ManagerName = array_search($row["manager_id"], $managerList);
           echo "<tr><td><small>".$row["ID"]."</small></td><td>".$row["display_name"]."</td><td>".$row["user_email"]."</td><td>".$row["location"]."</td><td>".$ManagerName."</td>";
           if (!empty($_REQUEST['course'])){
                      if (is_null($maxEN)){
                        $maxEN = "<td>not started</td>";
                      }
                      else{
                        echo "<td>".$maxEN.$maxES." %</td>";
                      }
           }
         }
         //$fp = fopen('wp-content/uploads/file.csv', 'w');
         //foreach ($row as $val) {
         //    fputcsv($fp, $val);
         //  }
         //fclose($fp);
         echo '</table>';

         echo '</div>
           <div class="col-md-4"><br><br>';
           if ($row_cnt>0 && $courseLoop == "106"){
             printf("This course has %d record(s).\n", $row_cnt);
             echo "<br><strong>".number_format(($UsersPercTotal / $row_cnt)* 1, 1)."% COURSE AVERAGE</strong>";
             $countEN = $row_cnt;
             $percEN = $UsersPercTotal;
           }
           elseif ($row_cnt>0 && $courseLoop == "160"){
             printf("This course has %d record(s).\n", $row_cnt);
             echo "<br><strong>".number_format(($UsersPercTotal / $row_cnt)* 1, 1)."% COURSE AVERAGE</strong>";
             $countES = $row_cnt;
             $percES = $UsersPercTotal;
           }
           else{
             printf("This course has %d record(s).\n", $row_cnt);
             echo "<br><strong>".number_format(($UsersPercTotal / $row_cnt)* 1, 1)."% COURSE AVERAGE</strong>";
             $countES = $row_cnt;
             $percES = $UsersPercTotal;
           }
         echo "</div>
         </div>
         <br><br>";
         //echo "TOTAL: ".($countES+$countEN)." staff at ".number_format((($percEN + $percES) / ($countES+$countEN)),1)."%";
         echo "<br><br><br>";
          //echo "SQL= <code>$sql</code>";
         }
 }
function display_courseAVG(){
  wp_register_script('prefix_bootstrap', '//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js');
  wp_enqueue_script('prefix_bootstrap');

  wp_register_style('prefix_bootstrap', '//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css');
  wp_enqueue_style('prefix_bootstrap');
//  echo do_shortcode("[ld_course_list mycourses='true' show_content='false' progress_bar='true']");
echo '<div class="text-right">';

$language = $_REQUEST['lang'];
echo __('<a class="btn btn-primary btn-small" href="../courses/ministry-understanding-2019">Return to Course</a>','back2back-reports');

echo '<br></div>';

  global $current_user;
wp_get_current_user();
if ($current_user->ID == '') {
    //show nothing to user
}
else {
    //write code to show  here

$manager = $current_user->ID;

//Database Access
global $wpdb;

if (in_array("administrator", $current_user->roles)){
  echo "<p>Hi ".$current_user->display_name."! [".$current_user->ID.'] '.$current_user->roles[0]."</p>";

  //MANAGER SELECTION
  if ($_REQUEST['manager']!=NULL){
    $manager = $_REQUEST['manager'];
  }
  $sql = "SELECT DISTINCT ID,user_login, meta_value, display_name
  FROM `{$wpdb->prefix}users`
  LEFT JOIN {$wpdb->prefix}usermeta
  ON {$wpdb->prefix}users.ID = {$wpdb->prefix}usermeta.meta_value
  WHERE meta_key = 'manager'
  ORDER BY user_login ASC";

    $result = $wpdb->get_results($sql, ARRAY_A);
  if(!$result){
  }
  else{
    if ($manager!=NULL){
      echo "<a class='btn btn-default btn-sm' href='?lang=".$language."&course=".$course."&location=".$location."&manager='><i class=\"fa fa-eraser\"></i> CLEAR MANAGER</a> <br>";
    }
    else{
      echo "<small><i class=\"fa fa-filter\"></i> SELECT MANAGER</a></small><br>";
    }
      foreach($result as $row){
        $manager_set1 = NULL;
        $manager_set2 = NULL;
        if ($manager==$row['ID']){
          $manager_set1 = "<span style='font-weight:800;color:black'>";
          $manager_set2 = "</span>";
        }
    echo "<a class='btn btn-primary btn-sm' href='?lang=".$language."&course=".$course."&location=".$location."&manager=".$row['ID']."'>".$manager_set1.$row['display_name'].$manager_set2."</a> ";
      }
  }
    $wpdb->flush();
}

//Manager's List
$sql = "SELECT DISTINCT user_email FROM `{$wpdb->prefix}users`
LEFT JOIN {$wpdb->prefix}usermeta ON {$wpdb->prefix}users.ID = user_id
WHERE meta_key = 'manager' AND meta_value = $manager";

$result = $wpdb->get_results($sql, ARRAY_A);
$row_cnt = ($wpdb->num_rows);
  //echo ('<p>You are managing '.$row_cnt.' staff: ','edumodo-child');
  echo "<p>".$current_user->display_name." ";
  echo __('is managing','back2back-reports');
  echo ' <small>('.$row_cnt.')</small>: <code style="color:#000;background:#FFF;">';
if(!$result){
}
else{
      foreach($result as $row){
echo '<small>'.$row['user_email'].'</small> ';
      }
}
echo '</code></p><br>';
//variable set
$location = NULL;
//$manager = $current_user->ID;
$course  = array("106","160");

//COURSE ARRAY

  $sql = "SELECT ID,post_title FROM `{$wpdb->prefix}posts` WHERE post_type = 'sfwd-courses' AND post_status = 'publish' AND (post_title NOT LIKE '%ITNA%') ORDER BY post_title ASC";
    $result = $wpdb->get_results($sql, ARRAY_A);
    if(!$result){
    }
    else{
    $coursesList[] = array();
    foreach($result as $row){
  $coursesList[$row['post_title']] = $row['ID'];
    }
  }

  //Report Generation
  echo '<div class="row">';
 foreach($course as $courseLoop) {

//SELECT FILTER
$wheresql = array();


$sql = "SELECT ID, display_name
    ,meta_location.meta_value as location
    ,meta_manager.meta_value as manager_id
    /*,meta_manager_nickname.meta_value as manager_nickname*/
    ,meta_course.meta_value as course_progress
FROM `{$wpdb->prefix}users`
LEFT JOIN {$wpdb->prefix}usermeta as meta_location ON {$wpdb->prefix}users.ID = meta_location.user_id and meta_location.meta_key = 'location'
LEFT JOIN {$wpdb->prefix}usermeta as meta_manager ON {$wpdb->prefix}users.ID = meta_manager.user_id and meta_manager.meta_key = 'manager'
/*LEFT JOIN {$wpdb->prefix}usermeta as meta_manager_nickname ON meta_manager.meta_value = meta_manager_nickname.user_id and meta_manager_nickname.meta_key = 'nickname' */
LEFT JOIN {$wpdb->prefix}usermeta as meta_course ON {$wpdb->prefix}users.ID = meta_course.user_id AND meta_course.meta_key = '_sfwd-course_progress'
";
if (!empty($course)){        array_push($wheresql," meta_course.meta_value LIKE '%$courseLoop%'");}
//if (!empty($course)){        array_push($wheresql," meta_course.meta_value LIKE '%$course[0]%' OR meta_course.meta_value LIKE '%$course[1]%'");}
if (!empty($manager)){            array_push($wheresql," meta_manager.meta_value = '$manager'");}
if (!empty($location)){            array_push($wheresql," meta_location.meta_value = '$location'");}
if (count($wheresql)>0){
    $i = 0;
    foreach($wheresql as $wherestatement){
        if ($i==0){  $sql .= " WHERE"; }
        else {  $sql .= " AND"; }
        $sql .= " $wherestatement";
        $i++;
    }
}
$sql .= " ORDER BY user_login ASC";
//echo "<code>$sql</code>";
$result = $wpdb->get_results($sql, ARRAY_A);
$row_cnt = ($wpdb->num_rows);

$CourseName = array_search($courseLoop, $coursesList);
echo "<h4>$CourseName</h4>";
  echo '<div class="col-md-8">';
if(!$result){
  echo __('No progress reported in this course.<br>','back2back-reports');
}
else{
  echo '<table class="table table-striped"><tr><th>';
  echo __('Student','back2back-reports');
  echo '</th><th>';
  echo __('Progress','back2back-reports');
  echo '</th></tr>';
  $UsersPercTotal = 0;

foreach($result as $row){
if (!empty($course)){
$course_progress = unserialize($row["course_progress"]);
$return = NULL;
}
$max = 0;

if (!empty($course_progress)){

foreach($course_progress as $course_id => $coursep){
					$course_id = get_post($course_id);
          if ($course_id->ID == $courseLoop){
        //if ($course_id->ID == $course){
					$return .= "<b>".$course_id->post_title."</b>: ";
          $courseCompletedDate = $coursep['time'];
          $course_completed = $coursep['completed'];
          $course_total = $coursep['total'];
            $UserPercentage = number_format(($course_completed / $course_total)* 100, 1);
            // $UsersPercTotal = $UsersPercTotal + $UserPercentage;
          //$return .= "" .$course_completed. " / " .$course_total. "<br>".$UserPercentage;
          //$return .= $UserPercentage;

  $max = max($max, $UserPercentage);
            $UsersPercTotal = $UsersPercTotal + $max;
if($max>0){
  //if(isset(array_search($allUserProgress, $row['ID']))){
    //$max = max($max, $allUserProgress[$row['ID']];
  //}
            //$allUserProgress[] = array();
          $allUserProgress[$row['ID']] = $max;
          if(in_array($row['ID'],$allUserProgress)){
            echo "~!";
          }
        }
			}
      }
    }
				//$return .=   "<br>";
  echo "<tr><td>".$row["display_name"]."</td><td>";
  //echo $return;
    //echo "<tr><td>".$row["display_name"]."</td><td>".$maxEN.$maxES." %</td>";
if (is_null($max)){
  $max = "not started";
}
else{
  echo "".$max." %";
}
}
}
 echo '</td></table>';

if ($row_cnt>0){
   echo __('This course has ','back2back-reports');
   echo $row_cnt;
   echo __(' records','back2back-reports');
   echo "<br><strong>".number_format(($UsersPercTotal / $row_cnt)* 1, 1)."% ";
   echo __('COURSE AVERAGE','back2back-reports');
   echo "</strong>";
 }
   $countEN = $row_cnt;
   $percEN = $UsersPercTotal;
echo '<hr></div>
  <div class="col-md-4">';

echo "</div><br>";
echo '<div class="row"></div>';
 }
echo "</div><br><br>";
echo __('If you have questions about your staff reports, please contact Employee Relations at <a href="mailto:employee.relations@back2back.org">employee.relations@back2back.org</a>.','back2back-reports');
//print_r ($allUserProgress);
//echo "TOTAL: ".($countES+$countEN)." staff at ".number_format((array_sum($allUserProgress)),1)." ".number_format((array_sum($allUserProgress))/($countES+$countEN),1)."%".count($allUserProgress);
echo "<br><br>";
// echo "SQL= <code>$sql</code>";
  $wpdb->flush();
}
}
add_shortcode('lmsreports','display_courseAVG');
