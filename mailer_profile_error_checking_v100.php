<?php
/****
2004-12-09: Here is a summary of how the error checking works:
Level 1 error: can save but not send
Level 2 error(s): cannot save or send
i.e. any error kills sending, but not all errors kill saving.
VOID	2004-07-17: So I have focused on error checking for the recipient location. The notes are below.
VOID	2004-07-16: what this file lacks is the ability to omit processes if we're not sending.  If we're only saving we don't necessarily need to check for presence of emails because the view may not have any data in it yet (they're setting up the profile first).
we want to auto detect whether their HTML or plain text choice makes sense
we want to see if they've proofed the email before we send it
file must correspond if selected, and also with URL

****/
if(!function_exists('errLevel')){
	function errLevel($x){
		global $errLevel;
		($x>$errLevel?$errLevel=$x:'');
	}
}
if(!function_exists('valid_3001_temp')){
	function valid_3001_temp($x){
		if(!preg_match('/^[_a-zA-Z0-9-]+(\.[_a-zA-Z0-9-]+)*@[a-zA-Z0-9-]+(\.[a-zA-Z0-9-]+)+$/i',$x)){
			return false;
		}
		return true;
	}
}

//error checking on recipients
switch($RecipientMethod){
	case 'group':
		unset($gs);
		if($Groups_ID){
			foreach($Groups_ID as $v){
				if(trim($v))$gs[]=$v;
			}
		}
		if(!$gs){
			errLevel(1);
			$err[1]['Group']='You selected a "CMS Group" as your recipient method.  Please select at least one CMS Group from the list on the "Select Recipients" tab';
		}

	break;
	case 'manual':
		//we need to have at least some values in field to send, but not to save
		if(!preg_match('/[_a-zA-Z0-9-]+(\.[_a-zA-Z0-9-]+)*@[a-zA-Z0-9-]+(\.[a-zA-Z0-9-]+)+/i',$ManualList)){
			errLevel(1);
			$err[1][ManualList]='You selected "Manually entered list" as your recipient list.  Please include at least one valid email in the Manually Entered List field';
		}
	break;
	case 'import':
		//------------- begin checking import qualifications ---------------
		/****
		note: this is a lot tougher than it looks, especially if we add xls and then iif file types.  For now I'm punting on reading the file type.  A file format error for a "from imported file" method is a soft error, so it'd save but not send
		****/
		switch($ImportType){
			case 'csv':
			
			case 'tab':
			
			$qualifiers=array('csv'=>',','tab'=>"\t");
			$qual=$qualifiers[$ImportType];
			break;
			case 'xls':
			
			case 'auto':
				?><script defer>alert('The auto recognize format feature is not currently developed');</script><?php
			break;
		}
		$fileFormatOK=1;
		switch(true){
			case ($compileTime>@filemtime($_SERVER['DOCUMENT_ROOT'].'/tmp/tmp_mailprofile'.$ID.'.txt')): //the file is not present, or it has not been uploaded since the window was open.  This means if they close the profile, they must upload again
				errLevel(1);
				$err[1][Import]='You selected "From Imported File" as your recipient list.  You have not uploaded a recipient list yet.\nEither select another option, or click Select File .. to upload a file\nNOTE: for this method of specifying a list, you need to upload a file each time you open the mail profile';
			break;
			case (!$fileFormatOK): //the file doesn't appear valid
				errLevel(1);
				$type=array( 'csv'=>'comma separated', 'tab'=>'tab delimited', 'xls'=>'Microsoft Excel worksheet', 'auto'=>'recognized');
				$err[1][Import]='Your file does not appear to be in a '.$type[$ImportType].' format.  For more assistance click the help tab.';
			break;
			case (!preg_match('/column[- ]*[0-9]+(\s*,\s*column[- ]*[0-9]+)*/i',$EmailColumns)): //email columns are not selected
				errLevel(1);
				$err[1][Import]='You have not selected the email column(s) within your uploaded file.  Click Select Email Column(s).., look for the column(s) containing an email address, and check the box for the column number.  Then click Use Selected Columns';	
			break;

			case (!valid_columns('import')): //the email columns don't contain valid emails
				errLevel(1);
				$err[1][Import]='The email column(s) you selected in the import file don\'t contain any email addresses!  Click Select Email Column(s).., look for the column(s) containing an email address, and check the box for the column number.  Then click Use Selected Columns';
			break;
			//this code is the same in Advanced SQL 
			case trim($_SESSION['mail'][$acct]['templates'][$ID]['advanced'][RequiredFields]):
				//make sure Required columns are present
				$fileString=$_SERVER['DOCUMENT_ROOT']."/tmp/tmp_mailprofile".$ID.'.txt';
				$fp=@fopen($fileString,'r');
				$r=fgetcsv($fp,40000, ($ImportType=='tab'?"\t":","));
				fclose($fp);
				//we presume at this point that there are columns present
				if(count($r)){
					foreach($r as $n=>$v){
						if(trim($v))$allFields[]=trim(strtolower($v));
					}
				}
				//required fields
				$q=explode(',',$_SESSION['mail'][$acct]['templates'][$ID]['advanced'][RequiredFields]);
				foreach($q as $n=>$v){
					if(trim($v)){
						if(!in_array(trim(strtolower($v)), $allFields)){
							$reqFieldsNotPresent=true;
							$missingReqFields[]=trim($v);
						}
					}
				}
				if($reqFieldsNotPresent){
					errLevel(1);
					$x= $err[1][Import]='This profile specifies some required fields: '.
					$_SESSION['mail'][$acct]['templates'][$ID]['advanced'][RequiredFields].
					'\nThe following field(s) are missing: '. implode(', ',$missingReqFields).
					'\nMake sure that you check "First Row Contains Column Names", and that the first row fields are properly named'.
					'\nTo remove required fields, click on Select Recipients > Advanced';
				}
			break;
		}
		//------------- end checking import qualifications ---------------
	break;
	case 'view':
		/***
		we're going to assume the system will not allow them to access a view or field through a view to which they don't have rights.  Initially we could do a go-nogo on Views_ID present, but eventually we should pre-check for the presence of emails
		
		***/
		//------------- begin checking view quals ---------------
		switch(true){
			case (!$Views_ID):
				errLevel(2);
				$err[2]['View']='You chose "From RelateBase View" as the method for a recipient list.  You must select a view, or select another method for a recipient list.';
			break;
			case (false): //no email columns detected or specified
				errLevel(2);
				$err[2]['View']='You have not selected an email column(s) from View '.$Views_ID.' ('.$ViewName.')\nClick Select Email Column(s).., look for the column(s) containing an email address, and check the box for the column number.  Then click Use Selected Columns';
			break;
			case ($checkRecordsOrEmails):
				errLevel(2);
				$err[2]['View']='The view you selected has no email addresses in the column(s) you selected.\n\nEither 1) Double-click the Eye icon next to the view to see its records 2) Click Select Email Column(s).., look for the column(s) containing an email address, and check the box for the column number.  Then click Use Selected Columns';
			break;
		}
		//------------- begin checking view quals ---------------
	break;
	case 'complex':
		//we don't check their query
		if(!trim($ComplexQuery)){
			errLevel(1);
			$err[1]['Complex']='You selected From Structured Query Language Query as your method for a recipient list.  Please type in a query, then click Test Query .. for records, and then Select Email Column(s) to choose the column(s) containing email an address';
			break;
		}
		if(!$EmailColumns){
			errLevel(1);
			$err[1]['Complex']='You have not selected which column(s) of your query contain an email address.  Click Select Email Column(s).., look for the column(s) containing an email address, and check the box for the column number.  Then click Use Selected Columns';
			break;
		}
		ob_start();
		$result=q(stripslashes($_POST[ComplexQuery]), ERR_ECHO);
		$errOut=ob_get_contents();
		ob_end_clean();
		
		if($errOut){
			errLevel(1);
			$err[1]['Complex']='Your query returned an error ('. mysqli_errno().'\n\nThis is the text of the error:\n'. addslashes(mysqli_error());
			break;
		}
		if(!mysqli_num_rows($result)){
			errLevel(1);
			$err[1]['Complex']='Your query did not return any records.  The mailer must have at least one record with a valid email column to work';
			break;
		}else{
			//used for required fields check below
			$checkColumns=mysqli_fetch_array($result,MYSQLI_ASSOC);
			mysql_data_seek($result, 0);
		}
		//2004-09-28 handle required columns
		if(trim($_SESSION['mail'][$acct]['templates'][$ID]['advanced'][RequiredFields])){
			//make sure Required columns are present
			$r=$checkColumns;
			//we presume at this point that there are columns present
			if(count($r)){
				foreach($r as $n=>$v){
					echo $n . "<br />";
					if(trim($n)){
						$allFields[]=trim(strtolower($n));
						$allFieldsUC[]=trim($n);
					}
				}
			}
			//required fields
			$q=explode(',',$_SESSION['mail'][$acct]['templates'][$ID]['advanced'][RequiredFields]);
			foreach($q as $n=>$v){
				if(trim($v)){
					if(!in_array(trim(strtolower($v)), $allFields)){
						$reqFieldsNotPresent=true;
						$missingReqFields[]=trim($v);
					}
				}
			}
			if($reqFieldsNotPresent){
				errLevel(1);
				$x= $err[1][Import]='This profile specifies some required fields: '. implode(', ',$allFieldsUC).
				'\nThe following field(s) are missing: '. implode(', ',$missingReqFields).
				'\nMake sure your SQL query uses the right field names or aliases'.
				'\nTo remove required fields, click on Select Recipients > Advanced';
			}
		}
		while($rd=mysqli_fetch_array($result)){
			//DEVNOTES 2004-07-17: note that if we go over 30 records with no matching emails, we need to email the administrator that they are clogging my server and we're not happy about that..
			foreach($_emailColumns as $v){
				if(preg_match('/[_a-zA-Z0-9-]+(\.[_a-zA-Z0-9-]+)*@[a-zA-Z0-9-]+(\.[a-zA-Z0-9-]+)+/i',$rd[$v-1])){
					//this is the final level of validation
					break(3);
				}
			}
		}
		//no email addresses were found
		errLevel(1);
		$err[1]['Complex']='The email column(s) you selected don\'t contain valid email addresses!  Click Select Email Column(s).., look for the column(s) containing an email address, and check the box for the column number.  Then click Use Selected Columns';
	break;
	default:
	    /*
		errLevel(2);
		$err[2]['RecipientMethod']='You have not selected any recipients.  Click the Select Recipients tab, and select a method.  If you need further assistance click the Help tab.';
	    */
	break;
}
//------------ SECTION TWO: Composition and correctness of the email
for($i=1;$i<=1;$i++){
	if($Composition=='blank'){
		//if there is no region _blank_email, then they have not created one.  If there is but the timestamp is too early, they have not proofed it.
	
	}else if($Composition=='template'){
		if($TemplateMethod=='file'){
			//get the VOS file
		}else if($TemplateMethod=='url'){
			if(!trim($TemplateLocationURL)){
				errLevel(1);
				$err[1]['Composition']='You selected Compose Email from Template > From URL\nPlease enter a valid URL to the template (example: http://mysite.com/Templates/mail.dwt)';
				break;
			}
			//locate the URL
			ob_start();
			$str=implode('',file($TemplateLocationURL));
			$x=ob_get_contents();
			ob_end_clean();
			if(strlen($x)){
				errLevel(1);
				$err[1]['Composition']='You selected Compose Email from Template > From URL\nThe URL you entered is not valid or isn\'t resolving right now.  Please check the spelling of the URL\n('.addslashes($TemplateLocationURL).')';
				break;
			}

			// DW 4.0
			$regexDW40 = '/<!-- (#|Template)'.'BeginEditable (name=)*"[^"]*" -->/i';


			if(preg_match($regexDW40,$str)){
				//OK
			}else{
				errLevel(2);
				$err[2]['Composition']='You selected Compose Email from Template > From URL\nThe file does not have any editable regions\n('. addslashes($TemplateLocationURL) .')\nClick the Help tab if you need help with editable regions';
				break;
			}
		}
	}
	//see if the compostion has been proofed since compileTime
	if(
	false && $compileTime>$_SESSION['mail'][$acct]['templates'][$ID]['compileTime']
	){
		errLevel(1);
		$err[1]['Composition']='You have not composed your email yet!';
		break;
	}
	//if a blank email, alert them -- blank meaning the regions for the selected template
	/*
	foreach($_SESSION['mail'][$acct]['templates'][$ID]['rName'] as $v){
		if(strlen($_SESSION['mail'][$acct]['templates'][$ID]['r'][$v])){
			$regionsHaveText=true;
			break;
		}
	}
	if(false && !$regionsHaveText){
		errLevel(1);
		$err[1]['Composition']='You are sending out a blank email'. ($Composition=='template'?' (no text in the editable areas of the template)':'') . '.  Are you sure?\nClick Compose Email .. and enter text for the email.  If you really do want all regions blank, enter a space character';
		break;
	}
	*/
	if(!$Content){
		errLevel(1);
		$err[1]['Composition']='Your email is blank!';
		break;
	}
	
	
	//if no subject line alert them
	if(!$Subject /*!strlen($_SESSION['mail'][$acct]['templates'][$ID]['subj'])*/){
		errLevel(1);
		$err[1]['Composition']='You do not have a subject line on the email.';
		break;
	}
}
//------------ SECTION THREE: Delivery Settings -----------------------------
for($i=1;$i<=1;$i++){
	if(!trim($FromName)){
		errLevel(1);
		$err[1]['Delivery']='You cannot send profile emails without specifying who they are from.  Click the Deliver Email tab, and enter a name in the From (Name) field';
		break;
	}
	if(!valid_3001_temp($FromEmail)){
		errLevel(1);
		$err[1]['Delivery']='You do not have a valid email FROM address under Deliver Email options.  Click the Deliver Email tab, and enter a valid email in the From (Email) field';
		break;
	}
	if(!valid_3001_temp($BounceEmail)){
		errLevel(1);
		$err[1]['Delivery']='You must specify a valid email to send bounced (bad emails) to.  Click the Deliver Email tab, and enter a valid email in the "Send bounced Emails to" field';
		break;
	}
}
//set formOK
if(!$errLevel){
	$formOK=1;
}else{
	$formOK=0;
}

function valid_columns($mode){
	global $acct, $ID, $qual, $_emailColumns;
	//check email columns
	$fp=@fopen($_SERVER['DOCUMENT_ROOT'].'/tmp/tmp_mailprofile'.$ID.'.txt','r');
	while($a = @fgetcsv($fp,2000,$qual)){
		if(count($a) && is_array($a)){
			foreach($_emailColumns as $v){
				if(preg_match('/[_a-zA-Z0-9-]+(\.[_a-zA-Z0-9-]+)*@[a-zA-Z0-9-]+(\.[a-zA-Z0-9-]+)+/i',$a[$v])){
					return true;
				}
			}
		}else{
			$noLine++;
			if($noLine>5)break; //process stops if a legit row isn't gotten after 5 tries
		}
	}
	return false;
}

?>