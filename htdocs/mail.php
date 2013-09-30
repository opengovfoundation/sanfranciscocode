<?php

	/*
	 * Include the PHP declarations that drive this page.
	 */
	require $_SERVER['DOCUMENT_ROOT'].'/../includes/page-head.inc.php';

	//Use SendGrid for mailing
	require_once INCLUDE_PATH . "/sendgrid-php/SendGrid_loader.php";

	function email($em, $subject, $message = '')
	{

		try{
			$sendgrid = new SendGrid(SENDGRID_USERNAME, SENDGRID_PASSWORD);

			$mail = new SendGrid\Mail();
			$mail->addTo($em)
				->setFrom(EMAIL_ADDRESS)
				->setSubject($subject)
				->setText($message);

			$result = $sendgrid->smtp->send($mail);
		}
		catch(Exception $e){
			error_log($e);
			return false;
		}

		return true;
	}

	//declare our assets
	$name = stripcslashes($_POST['name']);
	$emailAddr = stripcslashes($_POST['email']);
	$comment = stripcslashes($_POST['message']);
	$subject = stripcslashes($_POST['subject']);
	$contactMessage =
		"Message:
		$comment

		Name: $name
		E-mail: $emailAddr

		Sending IP:$_SERVER[REMOTE_ADDR]
		Sending Script: $_SERVER[HTTP_HOST]$_SERVER[PHP_SELF]";



	//send the email
	$result = email(CONTACT_EMAIL, $subject, $contactMessage);

	if($result)
	{
		$response = 'success';
	}
	else
	{
		$response = 'failure';
	}

	header('content-type: application/json; charset=utf-8');
	print json_encode(array("response" => $response));




?>