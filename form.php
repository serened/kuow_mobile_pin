<?php

//HOLDS ANY SUCCESS/FAILURE MESSAGES TO DISPLAY
$status_message = "";

//IF THE FORM HAS BEEN SUBMIT, VALIDATE AND PROCESS INPUT
if (isset($_POST['posted']))
{
	/*
	 * VALIDATE FIELD FUNCTION
	 * PARAMS
	 *	$input = string input value (required)
	 *	$max_length = int max length of input (optional)
	 *	$pattern = string regex pattern to match against the input (recommended)
	 *	$required = bool check if empty $name is okay (optional)
	 * RETURNS bool
	 *	true if it passes validation (success)
	 *	false if it fails validation (failure)
	*/
	function valid_input ($input, $max_length=16, $pattern="any", $required=false)
	{
		$input = trim($input);
		$pat_lib = array("name"=>"/^(?:[a-z][-.,'\s]{0,2})+$/i", 
						 "name_num"=>"/^(?:[a-z][0-9]?[-.,'\s]{0,2})+$/i", 
						 "phone"=>"/\A[\(]?([2-9]\d{2})[\)]?[\-\.\s]?(\d{3})[\-\.\s]?(\d{4})\Z/i", 
						 "email"=>"/^[^@]{1,64}@[^@]{1,255}$/", //(this checks for proper length before and after the @)
						 "any"=>"/.*/i");
		if($required && empty($input))
		{
			return false;
		}
		else if(!$required && empty($input))
		{
			return true;
		}
		if($max_length < strlen($input))
		{
			return false;
		}
		if(!preg_match($pat_lib[strtolower($pattern)], $input))
		{
			return false;
		}
		return true;
	}

	//START WITH ALL FIELDS INVALID, MUST PASS TESTS TO BE VALID
	$valid_field = array("firstName"=>false, "lastName"=>false, "emailAddress"=>false, "phoneNumber"=>false, "feedbackText"=>false);

	//VALIDATE FIELD INPUT
	$valid_field["firstName"]	 = valid_input($_POST['firstName'], 75, "name", true);
	$valid_field["lastName"]	 = valid_input($_POST['lastName'], 75, "name", true);
	$valid_field["emailAddress"] = valid_input($_POST['emailAddress'], 150, "email", true);
	$valid_field["phoneNumber"]	 = valid_input($_POST['phoneNumber'], 16, "phone");
	$valid_field["feedbackText"] = valid_input($_POST['feedbackText'], 1000, "any", false);

	//FILTER OUT VALID FIELDS TO ONLY LEAVE FIELDS WITH PROBLEMS
	$bad_fields = array_filter($valid_field, create_function('$a','return !$a;')); //array should only contain fields with bad input
	
	if(empty($bad_fields)) //if all fields were valid
	{
		//REMOVE WHITESPACE FROM BEGINNING AND END OF INPUT
		foreach($_POST as &$p) {$p=trim($p); if($p=="") {$p=NULL;}}
		
		//CONNECT TO DATABASE
		require("../includes/odbc_connect.php");
		$link = db_connect();
		
		//INSERT INTO DATABASE
		$query = odbc_prepare($link, "INSERT INTO PIN_Contacts (First_Name, Last_Name, Email, Phone, Comment, Date) VALUES (?,?,?,?,?,?)");
		$success = odbc_execute($query, array($_POST['firstName'], $_POST['lastName'], $_POST['emailAddress'], $_POST['phoneNumber'], $_POST['feedbackText'], date("Y-m-d H:i:s")));
		
		//CHECK FOR INSERT SUCCESS
		if($success)
		{
			//TODO
			//send email confirmation
			//send to thank you page
			//unset form user input
			unset($_POST);
			$status_message = "<div class='error'>Form successfully submitted.</div>";
		}
		else
		{
			//insert failure - give them other contact options?
			$status_message = "<div class='error'>Failed to insert to database.</div>";
		}
	}
	else
	{
		//HANDLE FAILED FIELD VALIDATION AND DISPLAY MESSAGES TO USER
		//MAYBE: pass JSON output to jQuery and have jQuery display errors (maybe based on something like this) http://stackoverflow.com/questions/3358485/return-the-fields-that-do-not-pass-validation-in-php-to-jquery
		
		$status_message = "<div class='error'>Field validation failed.</div>";
	}

	//ENCODE SPECIAL CHARS FOR SAFE DISPLAY IN FORM FIELDS
	if(isset($_POST)){foreach ($_POST as &$p) {$p=htmlspecialchars($p);}}
}
		//CREATE VARIABLES FOR THE FORM TO DISPLAY DATA VALUES
		$val_fname    = (isset($_POST['firstName']) && !empty($_POST['firstName'])) 	  ? "value=\"".$_POST['firstName']."\"" 	: "";
		$val_lname    = (isset($_POST['lastName']) && !empty($_POST['lastName'])) 		  ? "value=\"".$_POST['lastName']."\"" 		: "";
		$val_email    = (isset($_POST['emailAddress']) && !empty($_POST['emailAddress'])) ? "value=\"".$_POST['emailAddress']."\"" 	: "";
		$val_phone    = (isset($_POST['phoneNumber']) && !empty($_POST['phoneNumber']))   ? "value=\"".$_POST['phoneNumber']."\"" 	: "";
		$val_feedback = (isset($_POST['feedbackText']) && !empty($_POST['feedbackText'])) ? $_POST['feedbackText'] 	: "";

?>

<!DOCTYPE html>
<!--[if lt IE 7 ]><html class="ie ie6" lang="en"> <![endif]-->
<!--[if IE 7 ]><html class="ie ie7" lang="en"> <![endif]-->
<!--[if IE 8 ]><html class="ie ie8" lang="en"> <![endif]-->
<!--[if (gte IE 9)|!(IE)]><!-->
<html lang="en"> 
<!--<![endif]-->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Talk to KUOW!</title>
<!-- Mobile Specific Metas
================================================== -->
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">

<!-- CSS
================================================== -->
<link rel="stylesheet" href="assets/css/base.css">
<link rel="stylesheet" href="assets/css/skeleton.css">
<link rel="stylesheet" href="assets/css/layout.css">
<link href='http://fonts.googleapis.com/css?family=Roboto:400,300,700' rel='stylesheet' type='text/css'>

<!--[if lt IE 9]>
<script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
<![endif]-->

<!-- Scripts
================================================== -->
<script src="assets/js/jquery-1.9.1.min.js" type="text/javascript"></script>
<script src="assets/js/jquery.validate.min.js" type="text/javascript"></script>
<script>
  $(document).ready(function(){
    $("#engage").validate();
  });
</script>
</head>

<body>
<!-- Top Nav
================================================== -->
<div class="sixteen columns">
	<div id="header">
		<div class="blue">
		</div>
		<div class="logo">
			<div class="container">
				<div class="six columns alpha">
					<a href="http://www.kuow.org"><img id="logo" class="scale-with-grid" src="/images/kuowlogo.png" width="293" height="48" alt="KUOW Logo"></a>
				</div>
				<div class="ten columns omega">
				</div>
			</div>
		</div>
		<div class="gray">
		</div>
	</div>
</div>

<!-- Form
================================================== -->
<div class="container">

<div class="sixteen columns">

<?php echo $status_message; ?>

<form class="" id="engage" action="" method="post" >
  
  <!-- Label and text input -->
<p><label for="firstName">First Name:</label></p>
<p><input type="text" name="firstName" id="firstName" class="required" <?=$val_fname;?> /></p>

<p><label for="lastName">Last Name:</label></p>
<p><input type="text" name="lastName" id="lastName" class="required" <?=$val_lname;?> /></p>

<p><label for="emailAddress">Email Address:</label></p>
<p><input type="email" name="emailAddress" id="emailAddress" class="required email" <?=$val_email;?> /></p>

<p><label for="phoneNumber">Phone Number:</label></p>
<p><input type="tel" name="phoneNumber" id="phoneNumber" <?=$val_phone;?>  /></p>

 
<!-- Label and textarea -->
<p><label for="feedbackText">Talk To Us:</label></p>
<p><textarea name="feedbackText" id="feedbackText"><?=$val_feedback;?></textarea></p>

<br />  

</form>

</div><!--sixteen columns-->

</div><!--container->

<!-- Footer
================================================== -->
<div class="sixteen columns">
<div class="black-top"></div>
</div><!--sixteen columns-->
</body>
</html>