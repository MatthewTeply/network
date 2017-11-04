<!DOCTYPE html>
<html>
<head>
	<title></title>
	<link rel="stylesheet" type="text/css" href="CSS/user.css">
</head>
<body>

<div class="wrapper">
	<div class="container">
		<section id="user_main">
			<?php $img = inc_getInfo($_GET['usr'], "img"); ?>
			<div id="user_img_div">
				<div id="user_img_img" style='<?php echo "background-image: url(".$img.");"; ?>'>
					<div id="user_img_overlay">
						<h3>Choose<br><i class="fa fa-picture-o"></i></h3>
						<form style="position: absolute;" id="user_img_upload" method="POST" enctype="multipart/form-data">
							<input type="file" name="user_img" id="user_img_file">
							<button type="submit" name="setUserImg_subm" class="bttn-focus"><i class="fa fa-upload"></i> Upload</button>
						</form>
					</div>
				</div>
			</div>
			<ul>
				<li>
					<h1><?php echo inc_getInfo($_GET['usr'], "first")." ".inc_getInfo($_GET['usr'], "last"); ?></h1>
					<p id="user_email"><i class="fa fa-envelope-o"></i> <?php echo inc_getInfo($_GET['usr'], "em") ?></p>
				</li>
				<li>
					<b><i class="fa fa-user-o"></i> About me</b>
					<article>
						<p id="user_bio">
						 <?php 
						 if(inc_getInfo($_GET['usr'], "bio") == NULL)
						 	echo "This user has no bio!";
						 else
						 	echo inc_getInfo($_GET['usr'], "bio");
						 ?>
						 </p>
						<button id="user_acc_edit_bio_bttn" class="bttn-focus" style="display: none; margin-right: 20px; margin-top: 20px;"><i class="fa fa-cloud"></i> Update</button>
					</article>
				</li>
				<?php if ($_SESSION['netw_uid'] != inc_getInfo($_GET['usr'], "uid")): ?>
					<li style="position: relative;">
						<?php 
							$id = inc_getId($_SESSION['netw_uid']);
							$f_list_array = inc_getFriends($id, "list");

							$f_req_array = inc_getFriends($id, "req");
							
							$f_sent_array = inc_getFriends($id, "sent");

							$f_id = $_GET['usr'];
							$f_first = inc_getInfo($f_id, "first");
							$f_last = inc_getInfo($f_id, "last");
						?>

						<?php if(in_array($f_id, $f_req_array)): ?>

							<span id="profile_sender_req_span">
								<b style="position: absolute; top: -22px"><?php echo $f_first." ".$f_last." wants to be your friend!"; ?></b>
								<form class='f_add_form' id=<?php echo "_".$f_id; ?>>
									<input type='hidden' value=<?php echo $f_id; ?> id=<?php echo "f_add_f_id_".$f_id; ?>>
									<input type='hidden' value=<?php echo $id; ?> id=<?php echo "f_add_id_".$f_id; ?>>
									<button type='submit'><i class='fa fa-check'></i></button>
								</form>
								<form class='f_decline_form' id=<?php echo "_".$f_id; ?>>
									<input type='hidden' value=<?php echo $f_id; ?> id=<?php echo "f_decline_f_id_".$f_id; ?>>
									<input type='hidden' value=<?php echo $id; ?> id=<?php echo "f_decline_id_".$f_id; ?>>
									<button type='submit'><i class='fa fa-times'></i></button>
								</form>
							</span>

						<?php elseif(in_array($f_id, $f_sent_array)): ?>

							<button disabled="true"><i class="fa fa-check"></i> Sent</button>

						<?php elseif (!in_array($_GET['usr'], $f_list_array)): ?>
							<form class="f_req_form" id=<?php echo "_".$_GET['usr'] ?>>
								<input type="hidden" id=<?php echo "f_req_id_".$_GET['usr']; ?> value=<?php echo inc_getId($_SESSION['netw_uid']); ?>>
								<input type="hidden" id=<?php echo "f_req_f_id_".$_GET['usr']; ?> value=<?php echo $_GET['usr']; ?>>
								<button type="submit"><i class="fa fa-plus"></i> Add friend</button>
							</form>
						<?php else: ?>
							<button disabled="true"><i class="fa fa-check"></i> You are friends</button>
						<?php endif ?>

						<button><i class="fa fa-pencil"></i> Send Private Message</button>
					</li>
				<?php endif ?>
			</ul>
		</section>

		<?php if ($_SESSION['netw_uid'] == inc_getInfo($_GET['usr'], "uid")): ?>
			<section id="user_toolbar">
				<button id="user_acc_edit"><i class="fa fa-pencil"></i> Edit Profile</button>
				<button id="user_acc_settings"><i class="fa fa-cog"></i> Account settings</button>
			</section>
		<?php endif ?>

		<br>
		<section id="user_friends_list">
			<h2>Friends</h2>
			<span id="user_friends_list_span">
				<?php inc_showFriends($_GET['usr'], "list"); ?>
			</span>
		</section>

		<?php if ($_SESSION['netw_uid'] == inc_getInfo($_GET['usr'], "uid")): ?>
			<section style="display: inline-block; width: auto;" id="user_friends_requests">
				<?php inc_showFriends($_GET['usr'], "req"); ?>
			</section>
		<?php endif ?>
		
	</div>
</div>

<script src="JS/ajax/users.ajax.js"></script>
<script src="JS/ajax/acc_edit.ajax.js"></script>
<script src="JS/ajax/refresh.ajax.js"></script>
<script src="JS/user_edit.js"></script>
<script src="JS/buttons.js"></script>

</body>
</html>