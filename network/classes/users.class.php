<?php

session_start();
include 'db.inc.php';

class User {

	private $conn;

	function __construct() {

		$this->conn = DBConnection::getDB();
	}

	//getUid, getEm, getId

	public function getInfo($id, $opt) {

		$stmnt = $this->conn->prepare("SELECT * FROM users WHERE id=?");
		$stmnt->bind_param("i", $id);

		$stmnt->execute();
		$results = $stmnt->get_result();

		$row = $results->fetch_assoc();

		if($opt == "uid")
			return $row['uid'];
		if($opt == "em")
			return $row['em'];
		if($opt == "first")
			return $row['first'];
		if($opt == "last")
			return $row['last'];
		if($opt == "img")
			return $row['img'];
		if($opt == "bio")
			return $row['bio'];
	}

	public function getId($username) {

		$stmnt = $this->conn->prepare("SELECT * FROM users WHERE uid=?");
		$stmnt->bind_param("s", $username);

		$stmnt->execute();
		$results = $stmnt->get_result();

		$row = $results->fetch_assoc();

		return $row['id'];
	}

	//Sign in, Sign up

 	public function setUser($username, $first, $last, $password, $email) { //Sign up

		$hash_password = password_hash($password, PASSWORD_DEFAULT);

		$stmnt = $this->conn->prepare("SELECT * FROM users WHERE uid=? OR em=?");
		$stmnt->bind_param("ss", $username, $email);

		$stmnt->execute();

		$results = $stmnt->get_result();

		$numRows = $results->num_rows;

		if ($numRows != 0) {

			exit("This username or email is already taken!");
			return false;
		}

		else {

			$stmnt = $this->conn->prepare("INSERT INTO users (uid, first, last, pwd, em) VALUES (?, ?, ?, ?, ?)");
			$stmnt->bind_param("sssss", $username, $first, $last, $hash_password, $email);

			$stmnt->execute();

			exit("Signed up successfuly!");
			return true;
		}
	}

	function getUser($username, $password) { //Log in

		$stmnt = $this->conn->prepare("SELECT * FROM users WHERE uid=?");
		$stmnt->bind_param("s", $st_uid);

		$st_uid = $username;

		$stmnt->execute();
		
		$results = $stmnt->get_result();

		$row = $results->fetch_assoc();
		if(password_verify($password, $row['pwd']) === false) {

			exit("Wrong username or password!");
		}
		else {
			$_SESSION['netw_uid'] = $row['uid'];
			header("Location: index.php?usr=".$row['id']."");
			return true;
		}
	}

	function un_getUser() {

		session_unset();
		session_destroy();
		header("Location: index.php?loggedout");
	}

	//Friends

	public function getFriends($id, $opt) {

		$stmnt = $this->conn->prepare("SELECT * FROM users WHERE id=?");
		$stmnt->bind_param("i", $id);

		$stmnt->execute();
		$results = $stmnt->get_result();

		$row = $results->fetch_assoc();

		if($opt == "list") {
			$f_array = explode(",", $row['f_list']);
		}
		if($opt == "req") {
			if($row['f_req'] != NULL)
				$f_array = explode(",", $row['f_req']);
			else
				$f_array = array();
		}
		if($opt == "sent") {
			if($row['f_sent'] != NULL)
				$f_array = explode(",", $row['f_sent']);
			else
				$f_array = array();
		}
	
		return $f_array;
	}

	public function showFriends($id, $opt) {

		if($opt == "list")
			$f_array = $this->getFriends($id, "list");
		else if($opt == "req")
			$f_array = $this->getFriends($id, "req");
		else
			exit("Wrong parameters!");

		foreach ($f_array as $f_id) {

			$stmnt = $this->conn->prepare("SELECT * FROM users WHERE id=?");
			$stmnt->bind_param("i", $f_id);

			$stmnt->execute();
			$results = $stmnt->get_result();

			$row = $results->fetch_assoc();

			if($opt == "list")
				echo "<a href='index.php?usr=".$row['id']."'>".$row['first']." ".$row['last']."</a>";
			if($opt == "req") {
				if(empty($row['f_req'])) {
					echo 
					"
					<form class='f_add_form' id='_".$row['id']."'>
						<span><a href='index.php?usr=".$row['id']."'>".$row['first']." ".$row['last']."</a> wants to be your friend!</span>
						<input type='hidden' value='".$row['id']."' id='f_add_f_id_".$row['id']."'>
						<input type='hidden' value='".$this->getId($_SESSION['netw_uid'])."' id='f_add_id_".$row['id']."'>
						<button type='submit'><i class='fa fa-check'></i> Accept request</button>
					</form><br>
					";
				}
				else {
					echo "You have no friend requests!";
				}
			}
		}
	}

	public function sendReq($id, $f_id) {

		$f_list_array = $this->getFriends($f_id, "list");

		if(in_array($id, $f_list_array))
			exit("This users is already in your friends list!");

		//Check if request is not already sent
		$f_sent_array = $this->getFriends($id, "sent");

		if($f_sent_array != NULL) {

			foreach ($f_sent_array as $r_sent_id) {
				if($r_sent_id == $f_id) {
					exit("A friend request to this user has already been sent!");
				}
			}
		}

		//Add request to SENDER's f_sent
		array_push($f_sent_array, $f_id);
		$f_sent_string = implode(",", $f_sent_array);
	
		$stmnt = $this->conn->prepare("UPDATE users SET f_sent=? WHERE id=?");
		$stmnt->bind_param("si", $f_sent_string, $id);

		$stmnt->execute();

		//Add request to RECIEVER's f_req
		$f_req_array = $this->getFriends($f_id, "req");

		array_push($f_req_array, $id);

		if(sizeof($f_req_array) != 1) {
			$f_req_string = implode(",", $f_req_array);
		}
		else {			
			$f_req_string = $f_req_array[0];
		}

		$stmnt = $this->conn->prepare("UPDATE users SET f_req=? WHERE id=?");
		$stmnt->bind_param("si", $f_req_string, $f_id);

		$stmnt->execute();

		echo "Friend request sent to ".$this->getInfo($f_id, "first")."!";
	}

	public function addFriend($id, $f_id) {

		$f_list_array = $this->getFriends($id, "list");
		
		if(in_array($f_id, $f_list_array))
			exit("This users is already in your friends list!");

		//ADD SENDER TO RECIEVER'S F_LIST
		$f_req_array = $this->getFriends($id, "req");

		$f_id_key = array_search($f_id, $f_req_array);
		
		array_push($f_list_array, $f_req_array[$f_id_key]);

		if(empty($f_list_array[0]))
			unset($f_list_array[0]);

		$f_list_string = implode(",", $f_list_array);

		$stmnt = $this->conn->prepare("UPDATE users SET f_list=? WHERE id=?");
		$stmnt->bind_param("si", $f_list_string, $id);

		$stmnt->execute();

		//REMOVER SENDER FROM RECIEVER'S F_REQ
		unset($f_req_array[$f_id_key]);
		$f_req_array = array_values($f_req_array);

		if(sizeof($f_req_array) == 0)
			$f_req_string = NULL;
		else if(sizeof($f_req_array) == 1) {
			$f_req_string = $f_req_array[0];
		}
		else
			$f_req_string = implode(",", $f_req_array);

		$stmnt = $this->conn->prepare("UPDATE users SET f_req=? WHERE id=?");
		$stmnt->bind_param("si", $f_req_string, $id);

		$stmnt->execute();

		//REMOVE RECIEVER FROM SENDER'S F_SENT
		$f_sent_array = $this->getFriends($f_id, "sent");

		$f_id_key = array_search($id, $f_sent_array);

		unset($f_sent_array[$f_id_key]);
		$f_sent_array = array_values($f_sent_array);

		if(sizeof($f_sent_array) == 0)
			$f_sent_string = NULL;
		else if(sizeof($f_sent_array) == 1)
			$f_sent_string = $f_sent_array[0];
		else
			$f_sent_string = implode(",", $f_sent_array);

		$stmnt = $this->conn->prepare("UPDATE users SET f_sent=? WHERE id=?");
		$stmnt->bind_param("si", $f_sent_string, $f_id);

		$stmnt->execute();

		//ADD RECIEVER TO SENDER'S F_LIST
		$f_list_array = $this->getFriends($f_id, "list");

		array_push($f_list_array, $id);

		if(empty($f_list_array[0]))
			unset($f_list_array[0]);

		$f_list_string = implode(",", $f_list_array);

		$stmnt = $this->conn->prepare("UPDATE users SET f_list=? WHERE id=?");
		$stmnt->bind_param("si", $f_list_string, $f_id);

		$stmnt->execute();
	}

	//Account edits
	public function editBio($id, $content) {

		if($id != $this->getId($_SESSION['netw_uid']))
			exit("You are not allowed to edit this user's bio!");

		$content = str_replace("<br>", "", $content);
		$content = str_replace("<div>", "", $content);
		$content = preg_replace("/\s+/", " ", $content);

		$stmnt = $this->conn->prepare("UPDATE users SET bio=? WHERE id=?");
		$stmnt->bind_param("si", htmlspecialchars($content), $id);

		$stmnt->execute();

		echo htmlspecialchars($content);//"Bio updated!";
	}
}