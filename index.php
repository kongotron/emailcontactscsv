<?php
ini_set('max_execution_time', 30000); //sets the execution time for the php script, this can take ages to run depending on the size of your inbox.

$login = "webdesign@asgard.ie"; //imap username
$password = "ASGmodubuild2";	//imap password
$server = '{imap.gmail.com:993/ssl}';	//gmail server settings


$contacts = array();			//List of all Contacts
$contact = array();				//Variable for one contact
$tempcontactlist = array();		//Temporary list of all contacts

$connection = imap_open($server, $login, $password);	//open the connection
$mailboxes = imap_list($connection, $server, '*');	//get the list of all mailboxes in the account



foreach($mailboxes as $mailbox) {	//loop through all mailboxes
    $shortname = str_replace($server, '', $mailbox); 	//stip the name out of the list
	imap_reopen($connection, $server.$shortname);	//open that mailbox
	$count = imap_num_msg($connection);	//count the number of messages in that mailbox
	for($i = 1; $i <= $count; $i++) {	//loop through that mailbox
		$header = imap_headerinfo($connection, $i); //get the header info for that email
		//$raw_body = imap_body($connection, $i);	//get the body of that email
		$tempcontactlist = parseHeader($header);	//call the parse header function
		$contacts = array_merge($contacts, $tempcontactlist); //merge the returned array of contacts into the master array of contacts
	}	
}
$contacts = array_map("unserialize", array_unique(array_map("serialize", $contacts)));	//remove duplicates from array
outputCSV($contacts, $login);	//call the output function
echo "Finished";	

function parseHeader($headerdata){ 
	$contacts = array();	//list of contacts for this function
	$contact = array();		//variable for one contact
	
	$toArray = $headerdata -> to;			//get the to array
	$fromArray = $headerdata -> from;		//get the from array
	$replyArray = $headerdata -> reply_to;	//get the reply_to array
	$senderArray = $headerdata -> sender;	//get the sender array
	
	foreach($toArray as $address){			//loop to get the details of the to array and add it to the contact variable and then to the list of contacts
		if(isset($address -> personal)){
			$contact[0] = $address -> personal;
		}else{
			$contact[0] = "";
		}
		$contact[1] = $address -> mailbox."@".$address -> host;
		array_push($contacts, $contact);
	}
	
	foreach($fromArray as $address){		//loop to get the details of the from array and add it to the contact variable and then to the list of contacts
		if(isset($address -> personal)){
			$contact[0] = $address -> personal;
		}else{
			$contact[0] = "";
		}
		$contact[1] = $address -> mailbox."@".$address -> host;
		array_push($contacts, $contact);
	}
	
	foreach($replyArray as $address){		//loop to get the details of the reply array and add it to the contact variable and then to the list of contacts
		if(isset($address -> personal)){
			$contact[0] = $address -> personal;
		}else{
			$contact[0] = "";
		}
		$contact[1] = $address -> mailbox."@".$address -> host;
		array_push($contacts, $contact);
	}
	
	foreach($senderArray as $address){		//loop to get the details of the sender array and add it to the contact variable and then to the list of contacts
		if(isset($address -> personal)){
			$contact[0] = $address -> personal;
		}else{
			$contact[0] = "";
		}
		$contact[1] = $address -> mailbox."@".$address -> host;
		array_push($contacts, $contact);
	}	
	return $contacts;	
}
function outputCSV($contacts, $login){ 	//function to output the array to a csv file
	$fp = fopen($login.'.csv', 'w');	//open a file with the name of the email address being checked .csv
	foreach ($contacts as $contacts_list) { //loop through each contact
		fputcsv($fp, $contacts_list);	//put it into the file 
	}
	fclose($fp);	//close the open file
}
?>
