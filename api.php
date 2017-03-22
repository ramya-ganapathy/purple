<?php
header('Access-Control-Allow-Origin: *'); 
//header('Access-Control-Allow-Headers: Content-Type')
$conn = mysqli_connect("localhost", "root", "", "purple");
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
error_reporting(0);


function defvalue($val)
{
	return $val ? $val : 0;
}
	
if(isset($_GET['action']) && $_GET['action'] == 'users')
{

		$result = mysqli_query($conn,"select * from users");
		$res = ['data' => array(), 'status' => 'Success'];
		if(mysqli_num_rows($result))
			{
				while($r = mysqli_fetch_assoc($result))
					{
							$res['data'][] = $r;
					}
					echo json_encode($res);
					exit;
			}
			 $res['status'] = 'No Results found';
			 echo json_encode($res);
			exit;
	
}
elseif(isset($_GET['action']) && $_GET['action'] == 'products')
{

		$result = mysqli_query($conn,"select * from products");
		$res = ['data' => array(), 'status' => 'Success'];
		if(mysqli_num_rows($result))
			{
				while($r = mysqli_fetch_assoc($result))
					{
							$res['data'][] = $r;
					}
					echo json_encode($res);
					exit;
			}
			 $res['status'] = 'No Results found';
			 echo json_encode($res);
			exit;
	
}
elseif(isset($_GET['action']) && $_GET['action'] == 'product_details' && $_GET['product_id'])
{
		$result = mysqli_query($conn,"select * from products where id = ".$_GET['product_id']);
		$res = ['data' => array(), 'status' => 'Success'];
		if(mysqli_num_rows($result))
			{
				while($r = mysqli_fetch_assoc($result))
					{
							$res['data'][] = $r;
					}
		
				 	$res['status'] = "Success";
				 	//echo "<pre>";
				 	//print_r($res);
					echo json_encode($res);
					exit;
			}
			 $res['data'] = [];
			 $res['status'] = 'No Results found';
			exit;
	
}
elseif(isset($_GET['action']) && $_GET['action'] == 'signup')
{
	if(!isset($_POST['name']))
	$_POST = (array)json_decode(file_get_contents("php://input"));

	$name = $_POST['name'];
	$email = $_POST['email'];
	$password = md5($_POST['password']);
	$username = $_POST['username'];
	$userrole = $_POST['userrole'];
	$status = $_POST['status'];

	$result = mysqli_query($conn,"select * from users where username= '".$username."'");
	$res = ['data' => array(), 'status' => 'Success','code' => 200];
		if(!mysqli_num_rows($result))
		{

			$sql = "INSERT INTO users (name, email, password, username, status, userrole)
					VALUES ('$name', '$email', '$password', '$username', $status,'$userrole')";
			$result = mysqli_query($conn, $sql);
			$res['data']['user_id'] = mysqli_insert_id($conn);
			echo json_encode($res);
			exit;
		}
		else
		{
			$res['status'] = 'Username already exists';
			$res['code'] = 100;
			echo json_encode($res);
			exit;
		}


}
elseif(isset($_GET['action']) && $_GET['action'] == 'login')
{

	if(!isset($_POST['email']))
	$_POST = (array)json_decode(file_get_contents("php://input"));

	$email = $_POST['email'];
	$password = md5($_POST['password']);

	$result = mysqli_query($conn,"select * from users where email = '".$email."' and password = '".$password."'");
	$res = ['data' => array(), 'status' => 'Success','code' => 200];
		if(mysqli_num_rows($result))
		{
			$res['data'] = mysqli_fetch_assoc($result);
			echo json_encode($res);
			exit;
		}
		else
		{
			$res['status'] = 'Username or Password incorrect';
			$res['code'] = 100;
			echo json_encode($res);
			exit;
		}
}
elseif(isset($_GET['action']) && $_GET['action'] == 'add_product')
{
	if(!isset($_POST['name']))
	$_POST = (array)json_decode(file_get_contents("php://input"));

	$name = $_POST['name'];
	$description = $_POST['description'];
	$price = $_POST['price'];
	
	$status = $_POST['status'];

	$result = mysqli_query($conn,"select * from products where name= '".$name."'");
	$res = ['data' => array(), 'status' => 'Success','code' => 200];
		if(!mysqli_num_rows($result))
		{

			$sql = "INSERT INTO products (name, description, price,  status)
					VALUES ('$name', '$description', '$price',  $status)";
			$result = mysqli_query($conn, $sql);
			$res['data']['product_id'] = mysqli_insert_id($conn);
			echo json_encode($res);
			exit;
		}
		else
		{
			$res['status'] = 'Product name already exists';
			$res['code'] = 100;
			echo json_encode($res);
			exit;
		}


}
elseif(isset($_GET['action']) && $_GET['action'] == 'addbooking')
{
	if(count($_POST) == 0)
	$_POST = (array)json_decode(file_get_contents("php://input"),TRUE);
	//print_r(file_get_contents("php://input"));
	$user_id = $_POST['user_id'];
	$date = date('Y-m-d');
	$status = 'booked';
	$total_amt = $_POST['total_amt'];
	$currency = $_POST['currency'];

	$sql = "INSERT INTO booking (user_id, status, total_amt, booking_date, currency) VALUES ('$user_id', '$status', '$total_amt', '$date', '$currency')";
	$result = mysqli_query($conn, $sql);
	$booking_id = mysqli_insert_id($conn);
	$res = ['data' => array(), 'status' => 'Success',"code" => 200];

	$booking_details[] = $_POST['booking_details'];
	
	// user mail process start
	$rnu = mysqli_fetch_assoc(mysqli_query($conn,"select * from users where id= '".$user_id."'"));
	
	$uto = $rnu['email'];
	$usubject = 'Your booking order';
	
	$headers = "From: krishc@zenstill.com \r\n";
	$headers .= "MIME-Version: 1.0\r\n";
	$headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";

	$umessage = '<html><body>';
	$umessage .=	'<h6>Dear '.$rnu['name'].', </h6>';
	$umessage .=	'<p> Thank you for booking our venue (Venue Name). Your booking details are as follows </p>';
	$umessage .=	'<p> </p>';
	$umessage .= '<table rules="all" style="border-color: #666;" cellpadding="10">';
	$umessage .= "<tr><th>Venue Name</th><th>Date of Booking</th><th>Time of Booking</th></tr>";
	// user mail process partial end


	$i = 0;
	
	$msubject = 'Your venue has been booked';
			$message = '<html><body>';
			$message .=	'<h6>Dear Venue Owner, </h6>';
			$message .=	'<p> Congratulations. Your venue has been booked by one of our members. Please see details below </p>';
			$message .= '<table rules="all" style="border-color: #666;" cellpadding="10">';
	foreach($booking_details as $booking1)
	{
		foreach($booking1 as $booking)
		{
		//print_r($booking);
		//echo $booking['venue_id'];
			$sql1 = "INSERT INTO booking_details (booking_id, facility_id, venue_id, booking_start_time, booking_end_time, booking_session, booking_date,quantity, sport_type) VALUES ('".$booking_id."', '".$booking['facility_id']."', '".$booking['venue_id']."', '".$booking['booking_start_time']."', '".$booking['booking_end_time']."', '".$booking['booking_session']."', '".$booking['booking_date']."','".$booking['quantity']."','".$booking['sport_type']."')";
			$result = mysqli_query($conn, $sql1);
			
			// woner mail process start
			$r = mysqli_fetch_assoc(mysqli_query($conn,"select * from venues where id= '".$booking['venue_id']."'"));
			
			$ru = mysqli_fetch_assoc(mysqli_query($conn,"select * from users where id= '".$r['user_id']."'"));
			
			$to = $ru['email'];
			
			if($booking['facility_id'] != 0 )
			$rf = mysqli_fetch_assoc(mysqli_query($conn,"select facility_name from facilities where id= '".$booking['facility_id']."'"));
			$message .= "<tr><td><strong>Venue Name:</strong> </td><td>" . $r['venue_name']. "</td></tr>";
			if(isset($rf['facility_name']))
			$message .= "<tr><td><strong>Facility:</strong> </td><td>" . $rf['facility_name']. "</td></tr>";
			$message .= "<tr><td><strong>Date of Booking:</strong> </td><td>" . $booking['booking_date'] . "</td></tr>";
			
			if(!empty($booking['booking_start_time'])){
			$message .= "<tr><td><strong>Time of Booking:</strong> </td><td>".$booking['booking_start_time']." to ".$booking['booking_end_time']."</td></tr>"; }
			$message .= "<tr><td><strong>Booked By :</strong> </td><td>" . $rnu['name']." ( ".$rnu['mobile']." ) " . "</td></tr>";
			
			
			
			// woner mail process end
			
			// normal user order
			$umessage .= "<tr><td>".$r['venue_name']."</td><td>".$booking['booking_date']."</td>";
			
			
			if(!empty($booking['booking_start_time'])){
			$umessage .= "<td>".$booking['booking_start_time']." to ".$booking['booking_end_time']."</td></tr>";
			}else{
				$umessage .= "<td> - </td></tr>";
			}
			

			$i++;
		}
	}
	$message .= "</table>";
	$message .=	'<p> For more information, please login to your account.</p>';
			$message .=	'<h6> Thanks </h6>';
			$message .=	'<h6> FindASportVenue Admin </h6>';
			$message .= "</body></html>";
			
			mail($to, $msubject, $message,$headers);
	
	// user mail process partial start
	$umessage .= "<tr><td>If you need to contact the venue owner please call at </td><td>".$ru['mobile']."</td>";
	$umessage .= "</table>";
	$umessage .=	'<p> If you choose to cancel the venue, do so before 1 week of the event to get a refund. Actual refund given will vary between 
venues. You can email cancellation@findasportvenue.com for details. </p>';
	$umessage .=	'<h6> Thanks </h6>';
	$umessage .=	'<h6> FindASportVenue Admin </h6>';
	$umessage .= "</body></html>";
	
	mail($uto, $usubject, $umessage,$headers);
	// normal user mail process end
	
	
	$res['data']['booking_id'] = $booking_id;
	echo json_encode($res);
	exit;

	$res['code'] = 100;
			echo json_encode($res);
			exit;

}
elseif(isset($_GET['action']) && $_GET['action'] == 'checkavailability')
{
	
	$date = date('Y-m-d');
	$start_date = $_GET['start_date'];
	$end_date = $_GET['end_date'];
	$venue_id = $_GET['venue_id'];
	//$facility_id = $_GET['facility_id'];

	date_default_timezone_set('UTC');

	if($start_date != '' && $end_date != '' && $venue_id != '')
	{ 
		$res = ['data' => array(), 'status' => 'Success',"code" => 200];
		$sql = "select a.booking_start_time,a.booking_end_time,a.booking_date,a.sport_type from booking_details a, booking b where a.booking_date BETWEEN '".$start_date."' and '".$end_date."' and a.venue_id = $venue_id and b.status != 'Cancelled' group by a.booking_date, a.booking_start_time, a.booking_end_time";
		
		$result = mysqli_query($conn, $sql);

		if(mysqli_num_rows($result))
		{
			while($r2 = mysqli_fetch_assoc($result))
			{
			
				$start_time = $r2['booking_start_time'];
				$end_time = $r2['booking_end_time'];
				$r3 = array();
				if($r2['sport_type']!=1)
				{
					if($start_time != $end_time){
						for($i=(int)$start_time; $i< (int)$end_time;$i++)
						{
							$r3[] = $i.":00:00";
						}
					}
				}
				else
				{
					for($i=6; $i< 24;$i++)
					{
						$r3[] = $i.":00:00";
					}
				}
				$r2['booked_time'] = $r3;
				
				if(!isset($res['data'][$r2['booking_date']]))
					$res['data'][$r2['booking_date']] = array();

				$res['data'][$r2['booking_date']] = array_values(array_unique(array_merge($res['data'][$r2['booking_date']], $r2['booked_time'])));
			}	
			
			$new_res = array();
			foreach($res['data'] as $k => $v) 
			{
			    $new_res[] = array("booked_time" => $v, "booking_date" => $k);
			}
			//echo json_encode($new_res);exit;
			if(isset($_GET['is_mobile']))
			$res['data'] = ['booked_dates' => $new_res];
			else
			$res['data'] = $new_res;
	
		}
		else
		{
			//$res['status'] = "No Results Found";
			//$res['code'] = 100;
		}
		$sql = "select date from availability where date BETWEEN '".$start_date."' and '".$end_date."' and venue_id = $venue_id group by date";
		
		$result1 = mysqli_query($conn, $sql);

		if(mysqli_num_rows($result1))
		{
			while($r2 = mysqli_fetch_assoc($result1))
			{
				$res['data']['blocked_dates'][] = $r2['date'];
			}
		}
		else
		{
			//$res['status'] = "No Results Found";
			//$res['code'] = 100;
		}
				if(mysqli_num_rows($result) == 0 && mysqli_num_rows($result1) == 0)
		{
		$res['status'] = "No Results Found";
			$res['code'] = 100;
			}
		
		echo json_encode($res);
		exit;
	}
	else
	{
		$res['status'] = 'Input not Adequate';
		$res['code'] = 100;
		echo json_encode($res);
		exit;
	}
}
elseif(isset($_GET['action']) && $_GET['action'] == 'myorders')
{
	$user_id = $_GET['user_id'];
	if($user_id != '')
	{
	$result = mysqli_query($conn,"select * from booking where user_id = ".$user_id." order by id desc");
	$res = ['data' => array(), 'status' => 'Success'];
		if(mysqli_num_rows($result))
		{
			while($r = mysqli_fetch_assoc($result))
			{
			$r['order_date'] = $r['booking_date'];
				$result1 = mysqli_query($conn,"select a.*,b.* from booking_details a, venues b where booking_id = ".$r['id']." and a.venue_id = b.id order by booking_date asc");
				$r1 = mysqli_fetch_assoc($result1);
				
				
				$r['venue_name'] = $r1['venue_name'];
				$r['address_door_no'] = $r1['address_door_no'];
				$r['address_street'] = $r1['address_street'];
				$r['address_location'] = $r1['address_location'];
				$r['withinsevday'] = (strtotime($r1['booking_date']) - strtotime(date("Y-m-d")) < 7*86400);
				
				$res['data'][] = $r;
			} 
			echo json_encode($res);
			exit;
		}
		else
		{
			$res['status'] = 'No results found';
			echo json_encode($res);
			exit;
		}
	}
	else
	{
		$res['status'] = 'Input not Adequate';
		$res['code'] = 100;
		echo json_encode($res);
		exit;
	}
}
elseif(isset($_GET['action']) && $_GET['action'] == 'venueorders')
{
	$user_id = $_GET['user_id'];
	if($user_id != '')
	{
	$result = mysqli_query($conn,"SELECT * FROM booking where id in (select a.booking_id from booking_details a, venues b where a.venue_id = b.id and b.user_id = ".$user_id.") order by id desc");
	$res = ['data' => array(), 'status' => 'Success'];
		if(mysqli_num_rows($result))
		{
			while($r = mysqli_fetch_assoc($result))
			{
			
				$res['data'][] = $r;
			} 
			echo json_encode($res);
			exit;
		}
		else
		{
			$res['status'] = 'No results found';
			echo json_encode($res);
			exit;
		}
	}
	else
	{
		$res['status'] = 'Input not Adequate';
		$res['code'] = 100;
		echo json_encode($res);
		exit;
	}
}
elseif(isset($_GET['action']) && $_GET['action'] == 'venueorderscount')
{
	$user_id = $_GET['user_id'];
	if($user_id != '')
	{
	$result = mysqli_query($conn,"SELECT * FROM booking where id in (select a.booking_id from booking_details a, venues b where a.venue_id = b.id and b.user_id = ".$user_id." ) and booking_date = '".date("Y-m-d")."'");
	$res = ['data' => mysqli_num_rows($result), 'status' => 'Success'];
		
		echo json_encode($res);
		exit;
	}
	else
	{
		$res['status'] = 'Input not Adequate';
		$res['code'] = 100;
		echo json_encode($res);
		exit;
	}
}
elseif(isset($_GET['action']) && $_GET['action'] == 'orderdetails')
{
	$order_id = $_GET['order_id'];
	if($order_id != '')
	{
	$result = mysqli_query($conn,"select a.*,b.venue_name from booking_details a, venues b where a.venue_id = b.id and a.booking_id = ".$order_id);
	$res = ['data' => array(), 'status' => 'Success'];
		if(mysqli_num_rows($result))
		{
			while($r = mysqli_fetch_assoc($result))
			{
				if($r['facility_id'] != 0)
				{
				//echo 'select d.facility_name from booking_details a, venues b,venue_facilities c, facilities d where a.venue_id = b.id and c.facility_id = d.id and a.facility_id = c.facility_id and a.booking_id = '.$order_id .' and a.facility_id = '.$r['facility_id'].' group by a.id';
				$res_gal = mysqli_query($conn,'select d.facility_name from booking_details a, venues b,venue_facilities c, facilities d where a.venue_id = b.id and c.facility_id = d.id and a.facility_id = c.facility_id and a.booking_id = '.$order_id .'  and a.facility_id = '.$r['facility_id'].' group by a.id');
				$res2 = [];
				$r1 = mysqli_fetch_assoc($res_gal);
				$r['facility_name'] = $r1['facility_name'];
				}
				else
					$r['facility_name'] = '--';
				///
				if($r['sport_type'] != 0)
				{
					$res_sport = mysqli_query($conn, 'select sport_name from sports where id = '.$r['sport_type']);
					$r5 = mysqli_fetch_assoc($res_sport);
					$r['sport_name'] = $r5['sport_name'];
				}
				
				$res['data'][] = $r;
			} 
			echo json_encode($res);
			exit;
		}
		else
		{
			$res['status'] = 'No results found';
			echo json_encode($res);
			exit;
		}
	}
	else
	{
		$res['status'] = 'Input not Adequate';
		$res['code'] = 100;
		echo json_encode($res);
		exit;
	}
}
elseif(isset($_GET['action']) && $_GET['action'] == 'profileupdate')
{
	if(!isset($_POST['id']))
	$_POST = (array)json_decode(file_get_contents("php://input"));

	$name = $_POST['name'];
	$last_name = $_POST['last_name'];
	$email = $_POST['email'];
	$mobile = $_POST['mobile'];
	$mobile_country_code = $_POST['mobile_country_code'];
	$address= $_POST['address']; 
	$location= $_POST['location']; 
	$city = $_POST['city'];
	$state = $_POST['state'];
	$postcode = $_POST['postcode'];
	$user_id = $_POST['id'];
	$account_number = $_POST['account_number'];
	$password = isset($_POST['new_password']) ? md5($_POST['new_password']) : '';
	
	if($user_id != ''){
	if($password != '')
	{
	//echo "update users set password='" .$password."' where id = ".$user_id;
	$result = mysqli_query($conn,"update users set password='" .$password."' where id = ".$user_id);
	}
	else
		$result = mysqli_query($conn,"update users set name='".$name."',last_name = '".$last_name."' , email = '".$email."' , mobile = '".$mobile."' , mobile_country_code = '".$mobile_country_code."' , address= '".$address."', location= '".$location."',city = '".$city."' , state = '".$state."', postcode = '".$postcode."', account_number = '".$account_number."' where id = ".$user_id);
		
		if($result)
		$res = ['data' => array(), 'status' => 'Success',"code" => 200];
	
		$result1 = mysqli_query($conn,"select * from users where id = '".$user_id."'");
		if(mysqli_num_rows($result1))
		{
			$res['data'] = mysqli_fetch_assoc($result1);
			echo json_encode($res);
			exit;
		}
	}
	else{
		$res['status'] = 'Input not Adequate';
		$res['code'] = 100;
		echo json_encode($res);
		exit;
	}

}
elseif(isset($_GET['action']) && $_GET['action'] == 'verfied_user')
{
	$user_id = $_GET['user_id'];
	if($user_id != '')
	{
		//echo "update users set status = 1 where user_id = ".$user_id;
		$result = mysqli_query($conn,"update users set status = 1 where id = ".$user_id);
		$res = ['status' => 'Success', 'code' => 200];
		echo json_encode($res);
	}
	else
	{
		$res['status'] = 'Input not Adequate';
		$res['code'] = 100;
		echo json_encode($res);
		exit;
	}
}
elseif(isset($_GET['action']) && $_GET['action'] == 'getvenuecost')
{
	$venue_id  = $_GET['venue_id'];
	if($venue_id != '')
	{
		$res = ['data' => array(), 'status' => 'Success'];
		$result1 = mysqli_query($conn,'select * from venue_facilities  where  venue_id ='.$_GET['venue_id'].' and facility_id = 0');

		if(mysqli_num_rows($result1))
		{
			while($r1 = mysqli_fetch_assoc($result1))
			{
				$res['data'][] = $r1;
			}
			echo json_encode($res);
			exit;
		}
		else{
		$res['status'] = 'No results found';
		$res['code'] = 100;
		echo json_encode($res);
		exit;
		}
	}
	else{
		$res['status'] = 'Input not Adequate';
		$res['code'] = 100;
		echo json_encode($res);
		exit;
	}
}
elseif(isset($_GET['action']) && $_GET['action'] == 'get_images_by_venue_id')
{
	$venue_id  = $_GET['venue_id'];

	if($venue_id != '')
	{
		$res = ['data' => array(), 'status' => 'Success'];
		$result1 = mysqli_query($conn,'select * from venue_media  where  venue_id ='.$_GET['venue_id'].' order by id desc');

		if(mysqli_num_rows($result1))
		{
			while($r1 = mysqli_fetch_assoc($result1))
			{
				$res2[] = $r1;
			}
			$res['data']['images'] = $res2;
			echo json_encode($res);
			exit;
		}
		else{
		$res['status'] = 'No Results Found';
		$res['code'] = 100;
		echo json_encode($res);
		exit;
	}
	}
	else{
		$res['status'] = 'Input not Adequate';
		$res['code'] = 100;
		echo json_encode($res);
		exit;
	}
}
elseif(isset($_GET['action']) && $_GET['action'] == 'delete_facility_by_id')
{
	$facility_id  = $_GET['facility_id'];
	$venue_id = $_GET['venue_id'];

	if($venue_id != '' && $facility_id != '')
	{
		$res = ['data' => array(), 'status' => 'Success','code' => 200];
		$result1 = mysqli_query($conn,'delete from venue_facilities where facility_id ='.$_GET['facility_id'].' and venue_id = '.$venue_id);
		echo json_encode($res);
		exit;
	}
	else {
		$res['status'] = 'Input not Adequate';
		$res['code'] = 100;
		echo json_encode($res);
		exit;
	}
}
elseif(isset($_GET['action']) && $_GET['action'] == 'delete_image_by_id')
{
	$image_id  = $_GET['image_id'];

	if($image_id != '')
	{
		$res = ['data' => array(), 'status' => 'Success'];
		$result1 = mysqli_query($conn,'delete from venue_media where id ='.$_GET['image_id']);
		echo json_encode($res);
		exit;
	}
	else {
		$res['status'] = 'Input not Adequate';
		$res['code'] = 100;
		echo json_encode($res);
		exit;
	}
}
elseif(isset($_GET['action']) && $_GET['action'] == 'getvenuefacilities')
{
	$venue_id  = $_GET['venue_id'];

	if($venue_id != '')
	{
		$res = ['data' => array('facilities' => []), 'status' => 'Success','code' => 200];
		$result1 = mysqli_query($conn,'select b.facility_name,b.facility_logo,a.* from venue_facilities a, facilities b where  a.venue_id ='.$_GET['venue_id'].' and a.facility_id = b.id ');

		if(mysqli_num_rows($result1))
		{
			while($r1 = mysqli_fetch_assoc($result1))
			{
				if(!isset($res['data']['facilities'][$r1['facility_id']])) $res['data']['facilities'][$r1['facility_id']] = array();
				$res['data']['facilities'][$r1['facility_id']][] = $r1;
			}

			
		}
		
			echo json_encode($res);
			exit;		
	}
	else {
		$res['status'] = 'Input not Adequate';
		$res['code'] = 100;
		echo json_encode($res);
		exit;
	}
}
elseif(isset($_GET['action']) && $_GET['action'] == 'updatevenue')
{
	if(count($_POST == 0))
	$_POST = (array)json_decode(file_get_contents("php://input"), TRUE);

	$venue_id =$_POST['id'];
	if($venue_id != '')
	{
		$venue_name = $_POST['venue_name'];
		$venue_description = $_POST['venue_description'];
		$address_door_no = $_POST['address_door_no'];
		$address_street = $_POST['address_street'];
		$address_postcode = $_POST['address_postcode'];
		$address_location = $_POST['address_location'];
		$contact_name = $_POST['contact_name'];
		$contact_email = $_POST['contact_email'];
		$contact_landline = $_POST['contact_landline'];
		$contact_mobile = $_POST['contact_mobile'];
		$contact_mobile_country_code = $_POST['contact_mobile_country_code'];
		$contact_landline_country_code = $_POST['contact_landline_country_code'];
		$zipcode = $_POST['zipcode'];
		$venue_id = $_POST['id'];
		$latitude= $_POST['latitude'];
		$longitude = $_POST['longitude'];
		$currency = $_POST['currency'];
		$avdays = $_POST['available_days'];
		$avmonths = $_POST['available_months'];


		$result1 = mysqli_query($conn,'update venues set venue_name = "'.$venue_name.'",venue_description ="'.$venue_description.'", 
		address_door_no ="'.$address_door_no.'",address_street ="'.$address_street.'",address_postcode="'.$address_postcode.'",
address_location="'.$address_location.'",contact_name="'.$contact_name.'",contact_email="'.$contact_email.'",contact_landline="'.$contact_landline.'",contact_mobile="'.$contact_mobile.'",contact_mobile_country_code="'.$contact_mobile_country_code.'",contact_landline_country_code="'.$contact_landline_country_code.'",zipcode="'.$zipcode.'", currency = "'.$currency.'" , latitude= "'.$latitude.'" , longitude= "'.$longitude.'" where id='.$venue_id);
		
		
		
		$result = mysqli_query($conn,"select * from venue_availability where venue_id=".$venue_id);
		if(mysqli_num_rows($result))
{
  
  $avilable = mysqli_query($conn,'update venue_availability set sun = '.defvalue($avdays["sun"]).',mon= '.defvalue($avdays["mon"]).',tue= '.defvalue($avdays["tue"]).', wed= '.defvalue($avdays["wed"]).',thu= '.defvalue($avdays["thu"]).',fri= '.defvalue($avdays["fri"]).', sat= '.defvalue($avdays["sat"]).' where venue_id='.$venue_id);
}
else
{
  $avilable = mysqli_query($conn,'INSERT INTO venue_availability ( sun,mon,tue,wed,thu,fri,sat,venue_id ) VALUES( '.defvalue($avdays["sun"]).', '.defvalue($avdays["mon"]).','.defvalue($avdays["tue"]).', '.defvalue($avdays["wed"]).','.defvalue($avdays["thu"]).','.defvalue($avdays["fri"]).', '.defvalue($avdays["sat"]).' ,'.$venue_id.')');
}
	$result = mysqli_query($conn,"select * from venue_availabe_month where venue_id=".$venue_id);	
		if(mysqli_num_rows($result))
{
  
  $avilablemonth = mysqli_query($conn,'update venue_availabe_month set allf = '.defvalue($avmonths["allf"]).',jan= '.defvalue($avmonths["jan"]).', 
    feb= '.defvalue($avmonths["feb"]).',mar= '.defvalue($avmonths["mar"]).',apr= '.defvalue($avmonths["apr"]).',may= '.defvalue($avmonths["may"]).', june= '.defvalue($avmonths["june"]).', 
    july= '.defvalue($avmonths["july"]).', aug= '.defvalue($avmonths["aug"]).', sept= '.defvalue($avmonths["sept"]).', oct= '.defvalue($avmonths["oct"]).', nov= '.defvalue($avmonths["nov"]).', 
    dece= '.defvalue($avmonths["dece"]).' where venue_id='.$venue_id);
}
else
{
echo 'INSERT INTO venue_availabe_month ( allf,jan,feb,mar,apr,may,june,july,aug,sept,oct,nov,dece,venue_id ) VALUES( '.defvalue($avmonths["alf"]).', '.defvalue($avdays["jan"]).','.defvalue($avdays["feb"]).', '.defvalue($avdays["mar"]).','.defvalue($avdays["apr"]).','.defvalue($avdays["may"]).', '.defvalue($avdays["june"]).', '.defvalue($avdays["july"]).', '.defvalue($avdays["aug"]).', '.defvalue($avdays["sept"]).', '.defvalue($avdays["oct"]).', '.defvalue($avdays["nov"]).', '.defvalue($avdays["dece"]).' ,'.$venue_id.')';
  $avilable = mysqli_query($conn,'INSERT INTO venue_availabe_month ( allf,jan,feb,mar,apr,may,june,july,aug,sept,oct,nov,dece,venue_id ) VALUES( '.defvalue($avmonths["alf"]).', '.defvalue($avdays["jan"]).','.defvalue($avdays["feb"]).', '.defvalue($avdays["mar"]).','.defvalue($avdays["apr"]).','.defvalue($avdays["may"]).', '.defvalue($avdays["june"]).', '.defvalue($avdays["july"]).', '.defvalue($avdays["aug"]).', '.defvalue($avdays["sept"]).', '.defvalue($avdays["oct"]).', '.defvalue($avdays["nov"]).', '.defvalue($avdays["dece"]).' ,'.$venue_id.')');
}
		
		$result2 = mysqli_query($conn,'delete from venue_facilities where venue_id = '.$venue_id.' and facility_id = 0' );
		$res = ['data' => [], 'status' => 'Success','code' => 200];
		
		if(count($_POST['cost_details']))
		{
			for($i=0;$i<count($_POST['cost_details']);$i++)
			{
				$venue_id=$venue_id;
				$facility_id= 0;
				$cost=$_POST['cost_details'][$i]['cost'];
				$cost_per_day=$_POST['cost_details'][$i]['cost_per_day'];
				$from_month=$_POST['cost_details'][$i]['from_month'];
				$to_month=$_POST['cost_details'][$i]['to_month'];
				$facility_description = $_POST['cost_details'][$i]['facility_description'];
				$result3 = mysqli_query($conn,"INSERT INTO venue_facilities ( facility_id, venue_id, from_month, to_month, cost, cost_per_day, facility_description) VALUES ('".$facility_id."', '".$venue_id."', '".$from_month."', '".$to_month."', '".$cost."', '".$cost_per_day."', '".$facility_description."')");
			}		

			
		}	
		
		
		$result3 = mysqli_query($conn,'delete from venue_sports where venue_id = '.$venue_id );
		$res = ['data' => [], 'status' => 'Success','code' => 200];
		
		if(count($_POST['sport_details']))
		{
			for($i=0;$i<count($_POST['sport_details']);$i++)
			{
				$venue_id=$venue_id;
				$sports_id=$_POST['sport_details'][$i];
				$result3 = mysqli_query($conn,"INSERT INTO venue_sports ( sports_id, venue_id ) VALUES ('".$sports_id."', '".$venue_id."')");
			}		

			
		}	
		echo json_encode($res);
		exit;	
	}
	else {
		$res['status'] = 'Input not Adequate';
		$res['code'] = 100;
		echo json_encode($res);
		exit;
	}
}
elseif(isset($_GET['action']) && $_GET['action'] == 'add_availability')
{
	$date= $_GET['date'];
	$venue_id = $_GET['venue_id'];
	if(isset($date) && isset($venue_id))
	{
		$res = ['data' => array(), 'status' => 'Success','code' => 200];
		
		if($date)
		{
			$cnt_conn = mysqli_num_rows(mysqli_query($conn,"select * from availability where date = '$date' and venue_id = '$venue_id'"));
			if($cnt_conn <= 0)
			$sql1 = mysqli_query($conn,"insert into availability(venue_id, date) values ($venue_id , '$date')");
			else
			$sql1 = mysqli_query($conn,"delete from availability where date = '$date' and venue_id = '$venue_id'");
		}
	
		echo json_encode($res);
		exit;
	}
	else {
		$res['status'] = 'Input not Adequate';
		$res['code'] = 100;
		echo json_encode($res);
		exit;
	}
}elseif(isset($_GET['action']) && $_GET['action'] == 'add_availability2')
{
	$day1 = explode(',',$_GET['day']);
	$startdate = $_GET['startdate'];
	$enddate = $_GET['enddate'];
	$venue_id = $_GET['venue_id'];
	if(isset($day1) && isset($enddate))
	{
		$res = ['data' => array('facilities' => []), 'status' => 'Success','code' => 200];
		foreach($day1 as $day)
		{
		$start_date = $startdate;
		$loopdate = $start_date;
		if($start_date != $enddate)
		{
			while(strtotime($loopdate) <= strtotime($enddate))
			{
				//echo "sds";
				if($day == "0")
				echo $loopdate = date('Y-m-d', strtotime('next sunday', strtotime($loopdate)));
				if($day == "1")
				echo $loopdate = date('Y-m-d', strtotime('next monday', strtotime($loopdate)));
				if($day == "2")
				echo $loopdate = date('Y-m-d',strtotime('next tuesday', strtotime($loopdate)));
				if($day == "3")
				echo $loopdate = date('Y-m-d',strtotime('next wednesday', strtotime($loopdate)));
				if($day == "4")
				echo $loopdate = date('Y-m-d',strtotime('next thursday', strtotime($loopdate)));
				if($day == "5")
				echo $loopdate = date('Y-m-d',strtotime('next friday', strtotime($loopdate)));
				if($day == "6")
				echo $loopdate = date('Y-m-d',strtotime('next saturday', strtotime($loopdate)));

				//echo "<br/>";
				if($loopdate <= $enddate)
				{
					$sql1 = mysqli_query($conn,"insert into availability(venue_id, date) values ($venue_id , '$loopdate')");
				}
				//exit;
			}
		}
		}
		echo json_encode($res);
		exit;
	}
	else {
		$res['status'] = 'Input not Adequate';
		$res['code'] = 100;
		echo json_encode($res);
		exit;
	}
}
elseif(isset($_GET['action']) && $_GET['action'] == 'add_availability1')
{
	$day1 = explode(',',$_GET['day']);
	$enddate = $_GET['enddate'];
	$venue_id = $_GET['venue_id'];
	if(isset($day1) && isset($enddate))
	{
		$res = ['data' => array('facilities' => []), 'status' => 'Success','code' => 200];
		foreach($day1 as $day)
		{
			$month = date('m'); 
			$loopdate = date('Y-m-d',strtotime(date('Y').'-'.date('m').'-'.$day));
			for($n=1; $n<=$enddate; $n++)
			{
			//echo "insert into availability(venue_id, date) values ($venue_id , '$loopdate')";
			$sql1 = mysqli_query($conn,"insert into availability(venue_id, date) values ($venue_id , '$loopdate')");
			$loopdate = date('Y-m-d', strtotime('+1 month',strtotime($loopdate)));
			}				
		}
		echo json_encode($res);
		exit;
	}
	else {
		$res['status'] = 'Input not Adequate';
		$res['code'] = 100;
		echo json_encode($res);
		exit;
	}
}
elseif(isset($_GET['action']) && $_GET['action'] == 'getsports')
{
		$res = ['data' => array('sports' => []), 'status' => 'Success','code' => 200];
		$result = mysqli_query($conn,"select * from sports where status = 1");
		$res = ['data' => array(), 'status' => 'Success'];
		if(mysqli_num_rows($result))
			{
				while($r = mysqli_fetch_assoc($result))
					{
						$res['data'][] = $r;
					}
					echo json_encode($res);
					exit;
			}
			else {
			 $res['status'] = 'No Results found';
			 echo json_encode($res);
			exit;
	
			}
}
elseif(isset($_GET['action']) && $_GET['action'] == 'getrecentlyadded')
{

		$user_id = isset($_GET['user_id']) ? $_GET['user_id'] : '';
		
 			//print_r($dates);
		//echo "select a.* from venues a, booking_details b where a.status = 1 and b.venue_id = a.id and b.facility_id = 0 and b.booking_date between $date and $date_end";
		if($user_id != '')
		$result = mysqli_query($conn,"select * from venues where  user_id =".$user_id);
		else
		$result = mysqli_query($conn,"select * from venues  order by  id desc limit 10");
		$res = ['data' => array(), 'status' => 'Success'];
		if(mysqli_num_rows($result))
			{
				while($r = mysqli_fetch_assoc($result))
					{
					$res_cost = mysqli_query($conn,'select a.* from venue_facilities a where  a.venue_id ='.$r["id"].' and a.facility_id = 0 limit 1');
	
	 				 	$cos = mysqli_fetch_assoc($res_cost);
	 				 	$r['cost'] = $cos['cost'];
	 				 	
	 				 	$res_img = mysqli_query($conn,'select * from venue_media where  venue_id ='.$r["id"].' and facility_id = 0 limit 1');
	
	 				 	$img = mysqli_fetch_assoc($res_img );
	 				 	
	 				 	$r['image'] = $img['media_location'];
	 				 	$exp = explode("/upload/",$img['media_location']); //print_r($exp);exit;
	 				 	$r['cimage'] = '/upload/compressed/'.$exp[1]; //echo $r['image']; echo $r['cimage'] ; exit;
	 				 	
	 				 	$res_sports = mysqli_query($conn,'select b.sport_name from venue_sports a, sports b where a.venue_id ='.$r["id"].' and a.sports_id = b.id ');
				 $res4 = [];
				 while($r3 = mysqli_fetch_assoc($res_sports))
					{
						$res4[] = $r3['sport_name'];
					}
				 $r['sports']= implode(",", $res4);
						
						$res['data'][] = $r;
					}
					//print_r($res);exit;
					echo json_encode($res);
					exit;
			}
			 $res['status'] = 'No Results found';
			 echo json_encode($res);
			 exit;
	
}
elseif(isset($_GET['action']) && $_GET['action'] == 'searchvenues')
{

		$search = $_GET['search'];
		//$search_items = explode(',',$q);
		//$name = $search_items[0];
		//$location = $search_items[1];
		
		$sport= mysqli_query($conn,"SELECT venue_sports.venue_id FROM venue_sports INNER JOIN sports ON venue_sports.sports_id = sports.id where sport_name like '%".$search ."%'");
		$resvenuid = array();
		while($r = mysqli_fetch_assoc($sport))
		{
		  if($r['venue_id'] > 0 ) $resvenuid[] = $r['venue_id'];
		}
		
		$venuid = (count($resvenuid))? implode(',',$resvenuid) : $venuid = '0';
		
		$result = mysqli_query($conn,"select * from venues where venue_name like '%".$search ."%' OR address_location like '%".$search ."%' OR address_street like '%".$search ."%' OR id IN (".$venuid.")");
		
		$res = ['data' => array(), 'status' => 'Success'];
		
		if(mysqli_num_rows($result))
		{ 
			while($r = mysqli_fetch_assoc($result))
			{
			   $res_img = mysqli_query($conn,'select * from venue_media where  venue_id ='.$r["id"].' and facility_id = 0 limit 1');
			   $img = mysqli_fetch_assoc($res_img );
			   $r['image'] = $img['media_location'];
			   $exp = explode("/upload/",$img['media_location']); //print_r($exp);exit;
			   $r['cimage'] = '/upload/compressed/'.$exp[1]; //echo $r['image']; echo $r['cimage'] ; exit;
				
			   $res['data'][] = $r;
			   
			}
			//echo '<pre>'; print_r($res);exit;
			echo json_encode($res);
			exit;
		}
		 //echo count($res); echo '<pre>'; print_r($res);exit;
		 $res['status'] = 'No Results found';
		 echo json_encode($res);
		 exit;
	
}
elseif(isset($_GET['action']) && $_GET['action'] == 'searchbydate')
{

		$date = $_GET['date'];
		
		$result = mysqli_query($conn,"select * from venues where status = 1  and  venue_name like '%".$name."%' OR venue_location like '%".$location."%'");
		$res = ['data' => array(), 'status' => 'Success'];
		if(mysqli_num_rows($result))
			{
				while($r = mysqli_fetch_assoc($result))
					{
						$sql2 = mysql_query("select venue_id,SUM(quantity) as cnt from booking details where venue_id = ".$r['id']." and facility_id = 0 and booking_date = ".$date." group by venue_id");
						 	$res2 = mysql_fetch_assoc($sql2);
						 	if($res2['cnt'] < 11)
						 	{
						 		$res['data'][] = $r;
						 	}
						
					}
					echo json_encode($res);
					exit;
			}
			 $res['status'] = 'No Results found';
			 echo json_encode($res);
			 exit;
	
}
elseif(isset($_GET['action']) && $_GET['action'] == 'searchbydate')
{

		$date = $_GET['date'];
		
		$result = mysqli_query($conn,"select * from venues where status = 1  and  venue_name like '%".$name."%' OR venue_location like '%".$location."%'");
		$res = ['data' => array(), 'status' => 'Success'];
		if(mysqli_num_rows($result))
			{
				while($r = mysqli_fetch_assoc($result))
					{
						$sql2 = mysql_query("select venue_id,SUM(quantity) as cnt from booking details where venue_id = ".$r['id']." and facility_id = 0 and booking_date = ".$date." group by venue_id");
						 	$res2 = mysql_fetch_assoc($sql2);
						 	if($res2['cnt'] < 11)
						 	{
						 		$res['data'][] = $r;
						 	}
						
					}
					echo json_encode($res);
					exit;
			}
			 $res['status'] = 'No Results found';
			 echo json_encode($res);
			 exit;
	
}
elseif(isset($_GET['action']) && $_GET['action'] == 'block_dates')
{
	if(count($_POST) == 0)
	$_POST = (array)json_decode(file_get_contents("php://input"),TRUE);

	//echo "<pre>";print_r($_POST);
	$no_of_records = array();
	if(isset($_POST['date']) && isset($_GET['venue_id']))
	{
		$wk_day = array('Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday');
		
		$venue_id = $_GET['venue_id'];
		$startdate = $_POST['date'];
		$reason = $_POST['reason'];
		$res = ['status' => 'Success','code' => 200];
		$no_of_records[] = $startdate;
		if(isset($_POST['recurrence']) && $_POST['recurrence'])
		{
			$enddate = $_POST['recurrence_end_by']['end_date'];
			
			$tt = array('daily' => 'days', 'weekly' => 'weeks', 'monthly' => 'months');

			if(!$enddate)
			{
				if($_POST['recurring_type'] == 'monthly')
				$no_of_day = $_POST[$_POST['recurring_type'].'_recurrence']['recurrence_month'] * $_POST['recurrence_end_by']['no_of_occurence'];
				else
				$no_of_day = $_POST[$_POST['recurring_type'].'_recurrence']['recurrence_by'] * $_POST['recurrence_end_by']['no_of_occurence'];
				$enddate = date("Y-m-d", strtotime("+ ".$no_of_day.$tt[$_POST['recurring_type']], strtotime($startdate)));
			}

			if($_POST['recurring_type'] == 'daily')
			{
				$startdate = date("Y-m-d", strtotime("+ ".$_POST['daily_recurrence']['recurrence_by']."days", strtotime($startdate)));

				while(strtotime($startdate) <= strtotime($enddate))
				{
					$no_of_records[] = $startdate;

					$startdate = date("Y-m-d", strtotime("+ ".$_POST['daily_recurrence']['recurrence_by']."days", strtotime($startdate)));
				}
			}
			elseif($_POST['recurring_type'] == 'weekly')
			{
				
				foreach ($_POST['weekly_recurrence']['recurrence_on'] as $key => $value) {
					if($value)
					{
						$nndate = date("Y-m-d", strtotime($wk_day[$key]." this week", strtotime($startdate)));
						if(strtotime($nndate) >= strtotime($startdate))
							$no_of_records[] = $nndate;
					}
				}
				
				$startdate = date("Y-m-d", strtotime("+ ".$_POST['weekly_recurrence']['recurrence_by']."weeks", strtotime($startdate)));

				while(strtotime($startdate) <= strtotime($enddate))
				{
					foreach ($_POST['weekly_recurrence']['recurrence_on'] as $key => $value) {
						if($value)
						{
							$nndate = date("Y-m-d", strtotime($wk_day[$key]." this week", strtotime($startdate)));
							if(strtotime($nndate) <= strtotime($enddate))
								$no_of_records[] = $nndate;
						}
					}

					$startdate = date("Y-m-d", strtotime("+ ".$_POST['weekly_recurrence']['recurrence_by']."weeks", strtotime($startdate)));
				}
			}
			elseif($_POST['recurring_type'] == 'monthly')
			{
				$startdate = date("Y-m-d", strtotime("+ ".$_POST['monthly_recurrence']['recurrence_month']."months", strtotime($startdate)));

				while(strtotime($startdate) <= strtotime($enddate))
				{
					$no_of_records[] = date("Y-m-", strtotime($startdate)).$_POST['monthly_recurrence']['recurrence_day'];

					$startdate = date("Y-m-d", strtotime("+ ".$_POST['monthly_recurrence']['recurrence_month']."months", strtotime($startdate)));
				}
			}
		}
		
		if (mysqli_query($conn, "insert into blocking_reason(reason) values ('$reason')")) 
		{
    		$last_id = mysqli_insert_id($conn);
			foreach($no_of_records as $datee)
			{
				$sql1 = mysqli_query($conn,"insert into availability(venue_id,group_id,date) values ($venue_id,$last_id, '$datee')");	
			}
    
		}
		
		echo json_encode($res);
		exit;
	}
	else {
		$res['status'] = 'Input not Adequate';
		$res['code'] = 100;
		echo json_encode($res);
		exit;
	}
}
elseif(isset($_GET['action']) && $_GET['action'] == 'check_day_availability')
{
	if(count($_POST) == 0)
	$_POST = (array)json_decode(file_get_contents("php://input"),TRUE);
	
	$res = array();
	if(isset($_POST['date']) && isset($_GET['venue_id']))
	{
		$venue_id = $_GET['venue_id'];
		$cdate = $_POST['date'];
		
		$result = mysqli_query($conn,"select * from availability where venue_id = ".$venue_id." and date = '".$cdate."'");
		$row = mysqli_fetch_assoc($result);
		if(mysqli_num_rows($result))
		{
			$res1 = mysqli_fetch_assoc(mysqli_query($conn,"select * from blocking_reason where id = ".$row['group_id']));
			$res2 = mysqli_query($conn,"select * from availability where group_id = ".$row['group_id']);
			
			$res['blocked'] = 'yes';
			if(mysqli_num_rows($res2) > 1){ $res['type'] = 'grouped'; } else { $res['type'] = 'single'; }
			$b_dates = array();
			while($d = mysqli_fetch_assoc($res2))
			{
				$b_dates[] = $d['date'];
			}
			$res['reason'] = $res1['reason'];
			$res['venue_id'] = $venue_id;
			$res['group_id'] = $row['group_id'];
			$res['dates'] = $b_dates;
		
		}
		else
		{ 
			$res['blocked'] = 'no'; 
		}
		
		echo json_encode($res);
		exit;
	}
	else {
		$res['status'] = 'Input not Adequate';
		$res['code'] = 100;
		echo json_encode($res);
		exit;
	}
}
elseif(isset($_GET['action']) && $_GET['action'] == 'unblock_the_date')
{
	if(count($_POST) == 0)
	$_POST = (array)json_decode(file_get_contents("php://input"),TRUE);

	if(isset($_GET['type']))
	{
		$type = $_GET['type'];
		$venue_id = $_POST['venue_id'];
		$group_id = $_POST['group_id'];
		$date = $_POST['date'];
		//print_r($_POST); print_r($_GET);exit;
		if($type == 'single'){
		$result = mysqli_query($conn,"delete from availability where venue_id = ".$venue_id." and date = '".$date."'");
		}
		
		if($type == 'grouped'){
		$result = mysqli_query($conn,"delete from availability where venue_id = ".$venue_id." and group_id = ".$group_id);
		}
			
		echo json_encode('deleted');
		exit;
	}
	else {
		$res['status'] = 'Input not Adequate';
		$res['code'] = 100;
		echo json_encode($res);
		exit;
	}
}
elseif(isset($_GET['action']) && $_GET['action'] == 'add_viewed')
{
	if(count($_POST) == 0)
	$_POST = (array)json_decode(file_get_contents("php://input"),TRUE);

	if(isset($_GET['venue_id']))
	{
		
		$venue_id = $_GET['venue_id'];
		$date = date("Y-m-d H:i:s");
		if($venue_id != ''){
		$result = mysqli_query($conn,"update venues set viewed = '".$date."' where id = ".$venue_id);
		}
			
		echo json_encode('Updated');
		exit;
	}
	else {
		$res['status'] = 'Input not Adequate';
		$res['code'] = 100;
		echo json_encode($res);
		exit;
	}
}
elseif(isset($_GET['action']) && $_GET['action'] == 'getrecentlyviewed')
{

		
		$result = mysqli_query($conn,"select * from venues  order by  viewed desc limit 5");
		$res = ['data' => array(), 'status' => 'Success'];
		if(mysqli_num_rows($result))
			{
				while($r = mysqli_fetch_assoc($result))
					{
					$res_cost = mysqli_query($conn,'select a.* from venue_facilities a where  a.venue_id ='.$r["id"].' and a.facility_id = 0 limit 1');
	
	 				 	$cos = mysqli_fetch_assoc($res_cost);
	 				 	$r['cost'] = $cos['cost'];
	 				 	
	 				 	$res_img = mysqli_query($conn,'select * from venue_media where  venue_id ='.$r["id"].' and facility_id = 0 limit 1');
	
	 				 	$img = mysqli_fetch_assoc($res_img );
	 				 	
	 				 	$r['image'] = $img['media_location'];
	 				 	$exp = explode("/upload/",$img['media_location']); //print_r($exp);exit;
	 				 	$r['cimage'] = '/upload/compressed/'.$exp[1]; //echo $r['image']; echo $r['cimage'] ; exit;
	 				 	
	 				 	$res_sports = mysqli_query($conn,'select b.sport_name from venue_sports a, sports b where a.venue_id ='.$r["id"].' and a.sports_id = b.id ');
				 $res4 = [];
				 while($r3 = mysqli_fetch_assoc($res_sports))
					{
						$res4[] = $r3['sport_name'];
					}
				 $r['sports']= implode(",", $res4);
						
						$res['data'][] = $r;
					}
					//print_r($res);exit;
					echo json_encode($res);
					exit;
			}
			 $res['status'] = 'No Results found';
			 echo json_encode($res);
			 exit;
	
}
elseif(isset($_GET['action']) && $_GET['action'] == 'change_status')
{
	if(count($_POST) == 0)
	$_POST = (array)json_decode(file_get_contents("php://input"),TRUE);

	if(isset($_GET['order_id']))
	{
		
		$order_id = $_GET['order_id'];
		$date = date("Y-m-d H:i:s");
		if($order_id != ''){
		$result = mysqli_query($conn,"update booking set status= 'Cancelled' where id = ".$order_id);
		$result_get = mysqli_fetch_assoc(mysqli_query($conn,"select user_id from booking where id = ".$order_id));
		
		$rnu = mysqli_fetch_assoc(mysqli_query($conn,"select * from users where id= '".$result_get['user_id']."'"));
	
	$uto = $rnu['email'];
	$usubject = 'Cencelling Order';
	
	$headers = "From: krishc@zenstill.com \r\n";
	$headers .= "MIME-Version: 1.0\r\n";
	$headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";

	$umessage = '<html><body>';
	$umessage .=	'<h6>Dear '.$rnu['name'].', </h6>';
	$umessage .=	'<p> Dear member </p>';
	$umessage .=	'<p> Looking forward to seeing you again  </p>';
	// user mail process partial end


	$i = 0;
	$booking1  = mysqli_fetch_assoc(mysqli_query($conn,"select * from booking_details where booking_id = ".$order_id));
	foreach($booking1 as $booking)
		{
		//print_r($booking);
		//echo $booking['venue_id'];
			
			
			// woner mail process start
			$r = mysqli_fetch_assoc(mysqli_query($conn,"select * from venues where id= '".$booking['venue_id']."'"));
			
			$ru = mysqli_fetch_assoc(mysqli_query($conn,"select * from users where id= '".$r['user_id']."'"));
			
			$to = $ru['email'];
			$subject = 'Your venue has been Cancelled';
			$message = '<html><body>';
			$message .=	'<h6>Dear Venue Owner, </h6>';
			$message .=	'<p> CPlease note that your venues booking has been cancelled by the member. Contact FindASportVenue Admin to discuss cancellation charges and associated refund. </p>';
			$message .= '<table rules="all" style="border-color: #666;" cellpadding="10">';
			$message .= "<tr><td><strong>Venue Name:</strong> </td><td>" . $r['venue_name']. "</td></tr>";
			$message .= "<tr><td><strong>Date of Booking:</strong> </td><td>" . $booking['booking_date'] . "</td></tr>";
			
			if(!empty($booking['booking_start_time'])){
			$message .= "<tr><td><strong>Time of Booking:</strong> </td><td>".$booking['booking_start_time']." to ".$booking['booking_end_time']."</td></tr>"; }
			$message .= "<tr><td><strong>Booked By :</strong> </td><td>" . $rnu['name']." ( ".$rnu['mobile']." ) " . "</td></tr>";
			$message .= "</table>";
			$message .=	'<p> For more information, please login to your account.</p>';
			$message .=	'<h6> Thanks </h6>';
			$message .=	'<h6> FindASportVenue Admin </h6>';
			$message .= "</body></html>";
			mail($to, $subject, $message,$headers);
			// woner mail process end
			

			$i++;
		}
	
	
	// user mail process partial start
	
	
	mail($uto, $usubject, $umessage,$headers);
	// normal user mail process end
		$res['code'] = 200;
			$res['status'] = "Success";
		}
			
		echo json_encode($res);
		exit;
	}
	else {
		$res['status'] = 'Input not Adequate';
		$res['code'] = 100;
		echo json_encode($res);
		exit;
	}
}