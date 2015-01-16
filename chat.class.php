<?php 
require_once('config.php');
require_once('my_error_handler');

class Chat
{

	private $mysqli; 
	
	//constructor open database connection
	function __construct() {
	$this->mysqli=new mysqli(DB_HOST,DB_USER,DB_PASSWORD,DB_DATABASE);
	}

	//destructor closes database connection
	function __destruct() {
	$this->mysqli->close();
	}

	//delete all the messages from the database

	public function deleteAllMessages()
	{
		query='TRUNCATE TABLE chat';
		$result=$this->mysqli->query($query);
	}


	//post new message
	public function postNewMessage($user_name, $message,$color)
	{
		$user_name=$this->mysqli->real_escape_string($user_name);
		$message=$this->mysqli->real_escape_string($message);
		$color=$this->mysqli->real_escape_string($color);
		$query='INSERT INTO chat(posted_on,user_name,message,color)' . 
		'VALUES(
		NOW(),
		"' . $user_name . '",
		"' . $message . '",
		"' . $color . '" ) ';
		$result=$this->mysqli->query($query);
	  }


	//get new messages
	public function getNewMessages($id=0)
	{
		$id=$this->mysqli->real_escape_string($id);
		//for exisiting users
		if($id>0)
		{
		  $query='SELECT message_id,user_name,message,color,DATE_FORMAT(posted_on, "%H:%i:%s") AS posted_on FROM chat where message_id >'
		  . $id . ' ORDER BY message_id ASC ';

		}

		//for new users
		else 
		{
		  $query='SELECT message_id,user_name,message,color,posted_on 
		  FROM (SELECT message_id,user_name,message,color,DATE_FORMAT(posted_on, "%H:%i:%s") AS posted_on FROM chat ORDER BY message_id 
			DESC LIMIT 50) AS Last50 ORDER BY message_id ASC';
		}
		$result=$this->mysqli->query($query);
		

		//XML RESPONSE
		$response='<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
		$response .='<response>';
		$response .=$this->isDatabaseCleared($id);
		if(result->num_rows) //if database has some messages
		{
		while($row=$result->fetch_array(MYSQLI_ASSOC)) //fetches result set as a list or array
		{
			$id=$row['message_id'];
			$color=$row['color'];
			$userName=$row['user_name'];
			$time=$row['posted_on'];
			$message=$row['message'];
			$response .='<id>' . $id . '</id>' .
				    '<color>' . $color . '</color> '.
				    '<time>' . $time . '</time> '.
				    '<name>' . $userName . '</name> '.
				    '<message>' . $message . '</message> '.
			
		}
		result->close();
		}
		$response .='</response>';
		return $response;
		

	}

	private isDatabaseCleared($id) //id is the last message received by user
	{
		if($id>0)
		{
			$check_clear ='Select count(*) old From chat where message_id<=' .$id ;
			$result=$this->mysqli->query($check_query);
			$row=$result->fetch_array(MYSQLI_ASSOC);
			if($row['old']==0)
			return '<clear>true</clear>' ;
		}
	return '<clear>false</clear>' ;

	}
}

?>
















	
