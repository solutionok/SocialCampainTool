<?php
/**
 * @package Social Ninja
 * @version 1.7
 * @author InspiredDev <iamrock68@gmail.com>
 * @copyright 2015
 */
 
class admin
{
	public function count_users($email = '', $uid = '', $show_admin = 0, $banned = 0)
	{
		$q = ' 1 ';
		if(!empty($email))$q .= " AND users.email LIKE '%$email%' ";
		if(!empty($uid))$q .= " AND users.user_id = '$uid' ";
		if(!empty($show_admin))$q .= " AND users.is_admin != 0 ";
		if(!empty($banned))$q .= " AND users.account_status != 1 ";
		return sql_num_rows(sql_query("SELECT NULL FROM users WHERE $q"));
	}
	
	public function list_users($from = 1, $rows = 100, $email = '', $uid = '', $show_admin = 0, $banned = 0)
	{
		$from--;
		$q = ' 1 ';
		if(!empty($email))$q .= " AND users.email LIKE '%$email%' ";
		if(!empty($uid))$q .= " AND users.user_id = '$uid' ";
		if(!empty($show_admin))$q .= " AND users.is_admin != 0 ";
		if(!empty($banned))$q .= " AND users.account_status != 1 ";
		
		$q = sql_query("SELECT * FROM users LEFT JOIN membership_plans ON membership_plans.plan_id = users.plan_id WHERE $q ORDER BY users.user_id ASC LIMIT $from, $rows");
		
		$users = array();
		while($res = sql_fetch_assoc($q))$users[] = $res;
		return $users;
	}	
	
	public function count_payments($email = '', $uid = '', $txn_id = '', $from_date = '', $to_date = '')
	{
		$q = ' 1 ';
		if(!empty($email))$q .= " AND (payments.buyer_email LIKE '%$email%' OR payments.social_email LIKE '%$email%') ";
		if(!empty($uid))$q .= " AND payments.user_id = '$uid' ";
		if(!empty($txn_id))$q .= " AND payments.txn_id = '$txn_id' ";
		if(!empty($from_date))$q .= " AND payments.date_paid >= '$from_date' ";
		if(!empty($to_date))$q .= " AND payments.date_paid <= '$to_date' ";
		
		list($count) = sql_fetch_row(sql_query("SELECT COUNT(*) FROM payments WHERE $q"));
		return $count;
	}	
	
	public function list_payments($from = 1, $rows = 100, $email = '', $uid = '', $txn_id = '', $from_date = '', $to_date = '')
	{
		$from--;
		$q = ' 1 ';
		if(!empty($email))$q .= " AND (payments.buyer_email LIKE '%$email%' OR payments.social_email LIKE '%$email%') ";
		if(!empty($uid))$q .= " AND payments.user_id = '$uid' ";
		if(!empty($txn_id))$q .= " AND payments.txn_id = '$txn_id' ";
		if(!empty($from_date))$q .= " AND payments.date_paid >= '$from_date' ";
		if(!empty($to_date))$q .= " AND payments.date_paid <= '$to_date' ";
		
		$q = sql_query("SELECT payments.*, membership_plans.plan_name FROM payments LEFT JOIN membership_plans ON membership_plans.plan_id = payments.plan_id WHERE $q ORDER BY payments.date_paid DESC LIMIT $from, $rows");
		
		$users = array();
		while($res = sql_fetch_assoc($q))$users[] = $res;
		return $users;
	}
	
	public function count_folders($uid = '', $name = '')
	{
		$q = ' 1 ';
		$q2 = ' 1 ';
		if(!empty($uid))$q = " users.user_id = '$uid' ";
		if(!empty($name))$q2 = " folders.folder_name LIKE '%$name%' ";
		
		return sql_num_rows(sql_query("SELECT NULL FROM folders LEFT JOIN users ON users.user_id = folders.user_id AND $q WHERE users.user_id IS NOT NULL AND $q2"));
	}
	
	public function list_folders($from = 1, $rows = 100, $uid = '', $name = '')
	{
		$from--;
		$q = ' 1 ';
		$q2 = ' 1 ';
		if(!empty($uid))$q = " users.user_id = '$uid' ";
		if(!empty($name))$q2 = " folders.folder_name LIKE '%$name%' ";
		$q = sql_query("SELECT folders.*, users.email FROM folders LEFT JOIN users ON users.user_id = folders.user_id AND $q WHERE users.user_id IS NOT NULL AND $q2 LIMIT $from, $rows");
		
		$folders = array();
		while($res = sql_fetch_assoc($q))$folders[] = $res;
		return $folders;
	}	
	
	public function count_files($uid = '', $folder_id = '', $name = '')
	{
		$q = ' 1 ';
		$q2 = ' 1 ';
		$q3 = ' 1 ';
		if(!empty($uid))$q .= " AND users.user_id = '$uid' ";
		if(!empty($folder_id))$q2 .= " AND files.folder_id = '$folder_id' ";
		if(!empty($name))$q3 .= " AND folders.folder_name LIKE '%$name%' ";
		
		return sql_num_rows(sql_query("SELECT NULL FROM files LEFT JOIN users ON users.user_id = files.user_id AND $q LEFT JOIN folders ON folders.folder_id = files.folder_id AND $q3 WHERE users.user_id IS NOT NULL AND folders.folder_id IS NOT NULL AND $q2"));
	}
	
	public function list_files($from = 1, $rows = 100, $uid = '', $folder_id = '', $name = '')
	{
		$from--;
		
		$q = ' 1 ';
		$q2 = ' 1 ';
		$q3 = ' 1 ';
		if(!empty($uid))$q .= " AND users.user_id = '$uid' ";
		if(!empty($folder_id))$q2 .= " AND files.folder_id = '$folder_id' ";
		if(!empty($name))$q3 .= " AND folders.folder_name LIKE '%$name%' ";
		
		$q = sql_query("SELECT files.*, users.email, users.storage, folders.folder_name FROM files LEFT JOIN users ON users.user_id = files.user_id AND $q LEFT JOIN folders ON folders.folder_id = files.folder_id AND $q3 WHERE users.user_id IS NOT NULL AND folders.folder_id IS NOT NULL AND $q2 LIMIT $from, $rows");
		
		$files = array();
		while($res = sql_fetch_assoc($q))$files[] = $res;
		return $files;
	}
	
	public function count_accounts($site, $id = '', $uid = '', $name = '', $banned = 0)
	{
		list($table, $col, $uname, $name_col) = get_site_params($site);
		if(empty($table))return false;
		
		$q = ' 1 ';
		$q2 = ' 1 ';
		if(!empty($id))$q .= " AND $table.$col = '$id' ";
		if(!empty($uid))$q2 .= " AND users.user_id = '$uid' ";
		if(!empty($name)){
			if(in_array($site , array('fbpage', 'fbgroup', 'fbevent'))){
				$q .= " AND $table.$name_col LIKE '%$name%' ";
			}
			else $q .= " AND ($table.first_name LIKE '%$name%' OR $table.last_name LIKE '%$name%' ".($site != 'fbprofile' ? " OR $table.$uname LIKE '%$name%' " : '').") ";
		}
		if(!empty($banned))$q .= " AND $table.account_status != 1 ";
		
		return sql_num_rows(sql_query("SELECT NULL FROM $table LEFT JOIN users ON users.user_id = $table.user_id AND $q2 WHERE $q AND users.user_id IS NOT NULL"));
	}
	
	public function list_accounts($from = 1, $rows = 100, $site, $id = '', $uid = '', $name = '', $banned = 0)
	{
		$from--;
		
		list($table, $col, $uname, $name_col) = get_site_params($site);
		if(empty($table))return false;
		
		$q = ' 1 ';
		$q2 = ' 1 ';
		if(!empty($id))$q .= " AND $table.$col = '$id' ";
		if(!empty($uid))$q2 .= " AND users.user_id = '$uid' ";
		if(!empty($name)){
			if(in_array($site , array('fbpage', 'fbgroup', 'fbevent'))){
				$q .= " AND $table.$name_col LIKE '%$name%' ";
			}
			else $q .= " AND ($table.first_name LIKE '%$name%' OR $table.last_name LIKE '%$name%' ".($site != 'fbprofile' ? " OR $table.$uname LIKE '%$name%' " : '').") ";
		}
		if(!empty($banned))$q .= " AND $table.account_status != 1 ";
		
		$q = sql_query("SELECT $table.*, users.email FROM $table LEFT JOIN users ON users.user_id = $table.user_id AND $q2 WHERE $q AND users.user_id IS NOT NULL LIMIT $from, $rows");
		
		$accs = array();
		while($res = sql_fetch_assoc($q))$accs[] = $res;
		return $accs;
	}
	
	public function count_schedules($site = '', $uid = '', $name = '')
	{
		$q = ' 1 ';
		$q2 = ' 1 ';
		if(!empty($uid))$q .= " AND schedules.user_id = '$uid' ";
		if(!empty($site))$q .= " AND schedules.site = '$site' ";
		if(!empty($name))$q2 .= " AND schedule_groups.schedule_group_name LIKE '%$name%' ";
		
		return sql_num_rows(sql_query("SELECT NULL FROM schedules LEFT JOIN schedule_groups ON schedule_groups.schedule_group_id = schedules.schedule_group_id AND $q2 WHERE $q AND schedule_groups.schedule_group_id IS NOT NULL"));
	}
	
	public function list_schedules($from = 1, $rows = 100, $site = '', $uid = '', $name = '')
	{
		$from--;
		
		list($table, $col, $idcol, $uname) = get_site_params($site);
		
		$q = ' 1 ';
		$q2 = ' 1 ';
		if(!empty($uid))$q .= " AND schedules.user_id = '$uid' ";
		if(!empty($site))$q .= " AND schedules.site = '$site' ";
		if(!empty($name))$q2 .= " AND schedule_groups.schedule_group_name LIKE '%$name%' ";
		
		$q = sql_query("SELECT 
							schedule_groups.*,
							schedules.*  
							FROM schedules 
							LEFT JOIN schedule_groups ON 
							schedule_groups.schedule_group_id = schedules.schedule_group_id  AND $q2 
							WHERE $q AND schedule_groups.schedule_group_id IS NOT NULL 
						LIMIT $from, $rows");
		
		$sch = array();
		while($res = sql_fetch_assoc($q))$sch[] = $res;
		return $sch;
	}	
	
	public function count_queued_video($uid = '')
	{
		$q = ' 1 ';
		if(!empty($uid))$q .= " AND user_id = '$uid' ";
		return sql_num_rows(sql_query("SELECT NULL FROM video_editor_queue WHERE $q"));
	}
	
	public function list_queued_video($from = 1, $rows = 100, $uid = '')
	{
		$from--;
		$q = ' 1 ';
		if(!empty($uid))$q .= " AND video_editor_queue.user_id = '$uid' ";
		
		$q = sql_query("SELECT video_editor_queue.*, users.storage, users.email FROM video_editor_queue LEFT JOIN users ON users.user_id = video_editor_queue.user_id WHERE $q LIMIT $from, $rows");
		
		$videos = array();
		while($res = sql_fetch_assoc($q))$videos[] = $res;
		return $videos;
	}	
}

?>