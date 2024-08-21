<?php 
	include'includes/connection.php';
	foreach($_POST as $var=>$value)
	$$var = mysqli_real_escape_string($con, $value);

	$query_user=mysqli_query($con,"SELECT * from users where employee_id = '$id'");
	$row=mysqli_fetch_array($query_user);
	$num_rows_user = mysqli_num_rows($query_user);
	$sql = mysqli_query($con,"UPDATE employees SET employee_name = '$employee_name', position = '$position', department_id = '$department', access = '$access' WHERE employee_id = '$id'") or die(mysqli_error($con));
	if($access == 1){
		if($num_rows_user!=0){
			if($username=="" && $access!=0){
				echo "<script type='text/javascript'>alert('Username must not be empty!');</script>";
				echo "<script>document.location='edit_employee.php?id=".$id."';</script>";
			}else {
				$update = mysqli_query($con,"UPDATE users SET username='$username', password='$password' WHERE employee_id = '$id'") or die(mysqli_error($con));
			}
		}else{
			if ($username==$row['username'] && $access!=0 && $username!=''){
				echo "<script type='text/javascript'>alert('Sorry! This username is already taken! You may now login!');</script>";
				echo "<script>document.location='edit_employee.php?id=".$id."';</script>";
			}else if($username=="" && $access!=0){
				echo "<script type='text/javascript'>alert('Username must not be empty!');</script>";
				echo "<script>document.location='edit_employee.php?id=".$id."';</script>";
			}else {
				$insert = mysqli_query($con,"INSERT INTO users (employee_id, fullname,username, password, usertype_id) VALUES('$id','$employee_name','$username','$password','2')");
			}
		}
	}else if($access == 0){
		$delete = mysqli_query($con,"DELETE FROM users WHERE employee_id = '$id'");
	}
	echo "<script type='text/javascript'>alert('Successfully Updated!');</script>";
	echo "<script>window.location='edit_employee.php?id=".$id."'</script>";
?>