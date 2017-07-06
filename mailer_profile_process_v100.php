<?php
/*************
sample form post
Array
(
    [RecipientMethod] => import
    [Views_ID] => 
    [ViewEmialColumns] => 
    [OverrideViewFilters] => 
    [ComplexQuery] => 
    [recipientMethods_status] => import
    [ManualList] => 
    [ImportType] => csv
    [EmailColumns] => Column 1
    [filePresent] => 1
    [mode] => sendbatch
    --- [HTMLOrText] => 1 -- now this is in session from composer
    [Composition] => template
    [Files_ID] => 
    [TemplateMethod] => url
    [TemplateLocationURL] => http://www.relatebase.com/Templates/mail/mail_sample.dwt
    [select] => dw40
    [FromName] => Samuel Fullman
    [FromEmail] => sam-git@compasspointmedia.com
    [ReplyToName] => (optional)
    [ReplyToEmail] => (optional)
    [BounceEmail] => (optional)
    [DefaultBatchName] => Campaign 001
    [BatchRecordEmail] => 
    [BatchRecordComment] => 
    [RecordVersion] => 
    [LastUsageTime] => 
    [nullmailProfile_status] => emailRecords
)
****************/
/*
NOTE: these variables were used for including that we had composition in a separate window, so can mostly be phased out - however uploading a file uses this, and so does selecting the columns (I think) -
*/
prn($_SESSION['mail'][$acct]['templates'][$ID]);

//first we do the work of determining recipient data source, max line, record count, etc.
switch($RecipientMethod){
	case 'group':
		unset($ids, $groups);
		$ids=array();
		$groups=array();
		foreach($Groups_ID as $group){
			if(!trim($group)) continue;
			if(!in_array($group, $groups)) get_group_members_ids($group);
		}
		//2006-07-16: NOTE this is a slow query
		if(count($ids) && $groupQueryArray=q("SELECT Email, Email2, addr_contacts.* FROM addr_contacts WHERE ID IN(".implode(',',$ids).") ORDER BY LastName, FirstName", O_ARRAY)){
			$rowCount=count($groupQueryArray);
		}else{
			//no records
			$rowCount=0;
		}
		$_emailColumns=array(0,1);
	break;
	case 'import':
		//get the email columns
		$str=preg_replace('/Column(\s|-)/i','',trim($EmailColumns));
		$a=explode(',',$str);
		foreach($a as $v){
			//set to zero-based
			$_emailColumns[]=trim($v)-1;
		}
		switch($ImportType){
			case 'auto':
				//here we must auto-determine the type of file
				#$ImportType=tab | csv | xls | qbkscust, iow we change string value
								
			case 'tab':
			
			case 'csv':
				$fp=@fopen("$VOS_ROOT/$acct/tmp_mailprofile".$ID.".txt",'r');

				//get the file into a string
				$temp=@file("$VOS_ROOT/$acct/tmp_mailprofile".$ID.".txt");
				$rowCount=count($temp)-($ImportHeaders?1:0); //we don't count first header data
				if(is_array($temp)){
					foreach($temp as $v){
						if(strlen($v)>$maxLine)$maxLine=strlen($v);
					}
					$maxLine+=3;
				}
				//destroy file array from memory
				unset($temp);

				if($rowCount<1){
					//no records present in file, how'd we get this far
					?><script>alert('Undetermined error, no records in imported file');</script><?php
					exit;
				}
			case 'xls':
			break;
		}
	break;
	case 'complex':
		//get the rowCount
		if(!($rowCount=q(stripslashes($ComplexQuery),O_ROW)))error_alert('No records found for the SQL (Structured Query Language) Query');
		$rowCount=count($rowCount);
	break;
	case 'manual':
		//get data in array
		$buffer=preg_split("/[\n\r]+/",trim($ManualList));
		unset($ManualList);
		$ManualList=$buffer;
		foreach($ManualList as $n=>$v){
			$ManualList[$n]=trim($v);
		}
		//we don't need maxLine for a manual list
		$rowCount=count($ManualList);
	break;
}

//---------------------SECTION TWO: Loop through the records -----------------------------
/***
at this point we need $_emailColumns to have at least one element matching a key in $rd

todo: prge match on email below
***/

//convert HTMLOrText parameter -- 'plain' is a relic from enhanced_mail()
$HTMLOrText=='1' ? $sendType='html' : $sendType='plain';

if($mode!=='previewbatch'){
	echo "SENDING EMAIL BATCH ..<br />
NOTICE! Do not close this window or the send process will be terminated.  Depending on the size of emails (and esp. file attachments), sending can take up to 1.5 seconds per email.  When the process is completed this page will report the stop time<br />
<br />";
	echo "<pre>";
	$startTime=date('Y-m-d H:i:s');
	echo '<br />started at ' . $startTime . '<br />';
}


/*---------------------------- BATCH ENTRY ------------------------------
2004-07-19: At this point be enter into relatebase_mail_batches and mail_batches_logs

If you uncheck this option, a batch report will not be stored in your system.  If there is a problem during transmission, you will not be able to resume your batch in mid-stream.  Also, you will not be able to track email responses with the RelateBase mail system.  Continue?

Please note that a report will still be sent to so@aol.com, however the report will not include the emails the batch was actually sent to.

------------------------------------------------------------------------*/
if($BatchRecord || $BatchRecordEmail){
	
	//generate a batch number (receipt)
	$btchCt=q("SELECT COUNT(*) from relatebase_content_batches", O_VALUE);
	$seq=str_pad($btchCt, 5, "0", STR_PAD_LEFT);
	$receipt=date('y-m-d h:i ').$seq.'-'.$acct;
	
	//depending on method, we set null values
	$iComplexQuery=($RecipientMethod=='complex' ? "'".$ComplexQuery."'" : 'NULL');
	//get file name
	if($Composition=="template" && $TemplateMethod=="file" && $Files_ID){
		$a=q("SELECT FROM relatebase_files WHERE Files_ID='$Files_ID'", O_ROW);
		//prn($qr);
		$FileName= addslashes($a['LocalPath'].'/'. $a['LocalFileName']);
	}
	
	//insert the batch record - but there's something later about batch recovery where this may be embedded in if/else
	$Batches_ID=q("INSERT INTO relatebase_content_batches SET
	ContentObject='relatebase_mail_profiles',
	ContentKey�= ".($ID==0?"NULL":$ID).",
	BatchNumber�= '$receipt',
	FromName�= '$FromName',
	FromEmail�= '$FromEmail',
	ReplyToName�= '$ReplyToName',
	ReplyToEmail�= '$ReplyToEmail',
	BounceName�= '$BounceName',
	BounceEmail�= '$BounceEmail',
	Importance�= '$Importance',
	AttachedVCard�= '$AttachVCard',
	ReturnReceipt�= '$ReturnReceipt',
	StartTime = NOW(), /** stop time to be entered later **/
	BatchNotes�= '$BatchRecordComment',
	CreateDate�= NOW(),
	Creator�= '$acct'", O_INSERTID);
	prn($qr);
	
	//last usage time in session and db
	if($ID>0){
		q("UPDATE relatebase_mail_profiles SET LastUsageTime = '$dateStamp' WHERE ID='$ID'");
		prn($qr);
	}
}
//get file attachments declared
if(trim($AttachmentList)){
	$attachments=explode(',',$AttachmentList);
	foreach($attachments as $attachment){
		if(!trim($attachment))continue;
		$sql="SELECT LocalFileName, VOSFileName FROM relatebase_files WHERE ID='$attachment'";
		$fl=__FILE__;$ln=__LINE__ +1;
		$result=mysqli_query($db_cnx, $sql) or sql_handle_exception($fl,$ln);
		if(!mysqli_num_rows($result)){
			//the RBVOS record has been deleted
			//send alert to RB Staff
			$fileErr[$attachment]='An attachment ID was passed in a mail profile post but no record was located.  The file was most likely deleted between proof and send but highly unlikely!';
			mail($errReportEmail1,'Attachment ID in MPM passed, but not present in VOS',
			"Account: $acct
			Time ".date('Y-m-d H:i:s')."
			Mail Profile: $ID
			User: $cu", "From: bugreports@relatebase.com");
			continue;
		}
		$rdAttach=mysqli_fetch_array($result);
		if(!file_exists("$VOS_ROOT/$acct/".$rdAttach[VOSFileName])){
			//VOS and files are not in synch
			$fileErr[$attachment]='An attachment ID was passed in a mail profile post and the record was present; however the actual file does not exist in the account folder.  Check for file and VOS folder presence and proper permissions';
			mail($errReportEmail1,'Attachment ID in MPM passed, record present, but file not present in VOS folder',
			"Account: $acct
			Time ".date('Y-m-d H:i:s')."
			Mail Profile: $ID
			User: $cu", "From: bugreports@relatebase.com");
			continue;
		}
		$fileArrayAll[]="$VOS_ROOT/$acct/".$rdAttach[VOSFileName];
		$fileArrayNameAll["$VOS_ROOT/$acct/".$rdAttach[VOSFileName]] = $rdAttach[LocalFileName];
	}
}
/*$fileArrayAll[]="/home/cpm052/public_html/resources/pdf/Order-Form-New-Pricing-Effective-May-1st-2008.pdf";
$fileArrayNameAll["/home/cpm052/public_html/resources/pdf/Order-Form-New-Pricing-Effective-May-1st-2008.pdf"]="Order-Form-New-Pricing-Effective-May-1st-2008.pdf";
*/

//for now all recipients get all attachments
$fileArray=$fileArrayAll;
$fileArrayName=$fileArrayNameAll;
$emailSentList=array();
//assume logic compliation needed initially
$bodyCompilationNeeded=true;
$subjectCompilationNeeded=true;

//row index set to 1, only used though if mode=previewbatch
if(!$rowIdx)$rowIdx=1;
$i=0;
$j=0;
$sendCount=0;

while($rd=get_recipient_data_row($RecipientMethod)){
	$i++;
	//we allow max 20 seconds per iteration or something is wrong
	set_time_limit(20);
	

	//DEVNOTES 2004-07-12: The fieldlist can be declared from the first dataset
	/**-------------
	This is not really well-developed and needs to be addressed when we get to security of fields (using a view as a recipient source
	
	------------------------------------------------------------------**/
	if($i==1){
		$k=0;
		foreach($rd as $n=>$v){
			$fieldList[strtolower(preg_replace('/[^a-z0-9_]+/i','',$n))]=array($k,$n);
			$k++;
		}
		if($RecipientMethod=='complex'){
			//not sure what action is required
		
		}else if($RecipientMethod=='import' && $ImportHeaders){
			//need to translate the array emailCols to text header names
			foreach($_emailColumns as $v){
				$ec2[]=$headers[$v];
			}
			unset($_emailColumns);
			$_emailColumns=$ec2;
			//we skip the header row
			//if($RecipientMethod!=='complex')continue;
		}else if($RecipientMethod=='manual'){
			$_emailColumns[]=0;
		}
	}
	//now see if there are any email columns available
	$emails=array();
	foreach($_emailColumns as $v){
		//the function converts a header like First Name to FirstName
		if($RecipientMethod=='complex'){
			foreach($fieldList as $o=>$w){
				if($w[0]==$v-1)$key=$w[1];
			}
		}else{
			$key=$v;
		}
		if(preg_match_all('/[_a-zA-Z0-9-]+(\.([_a-zA-Z0-9-])*)*@[a-zA-Z0-9-]+(\.[a-zA-Z0-9-]+)+/i',$rd[$key],$a)){	
			for($k=0;$k<count($a[0]);$k++){
				$x=$emails[]=$a[0][$k];
			}
		}else{
			//bad emails
		}
	}
	//now we get the uncompiled message subject and body
	if($i==1){
		$emailSubject=get_email_subject($ID);
		$emailBody=get_email_body($ID);
	}
	//If this happens we need to report this row was skipped in batch report
	if(!count($emails)){
		if($mode!=='previewbatch')echo "Record $i skipped due to missing or invalid email<br />";
		continue;
	}

	//Added 2004-12-14: Test Email mode
	if($TestMode){
		if($sendCount+1<$TestEmailStart)continue;
		if($sendCount+1>$TestEmailBatch)break;
	}

	//log the event in db; NOTE THAT WE PROCESS THIS FOR A ROW, NOT FOR EACH EMAIL; we must insert before mailing to get BatchesContacts_ID
	if($Contacts_ID=$rd[$fieldList['contacts_id'][1]]){
		//OK
	}else if($Contacts_ID=$rd[$fieldList['id'][1]]){
		//OK
	}
	if($Contacts_ID || $Visitors_ID){
		if(q("SELECT ID FROM relatebase_BatchesContacts WHERE Batches_ID='$Batches_ID' AND ".($Contacts_ID ? "Contacts_ID='$Contacts_ID'" : "Visitors_ID='$Visitors_ID"), O_VALUE)){
			/*
			2011-05-22: NOTE that previously we went off the always-reliable EMAIL value; and did not consider Contacts_ID.  Mailer will still use imported files and etc. and prob. forward-enter imports into contacts while doing a mailing (this will be a tremendously feature rich and helpful console), but we will also gravitate more to source-record-table-equals-contacts.   
			
			here are my notes previously: "this is a duplicate email being sent, we haven't set this up for entry.  When recoving a failed batch, suppose the batch would have sent 3 emails to the email address x, but only 2 were sent.  Unfortunately on the level of complexity we have, the third email will NOT be sent out."
			*/
		}else{
			//enter record	
			//so we might want to do the following
			/*
			rsvp to this batch-content
			* note that it was opened
			* return receipt
			replied-on (they clicked at least one link)
				means I need to intercept the hrefs and add to query string
			
			
			*/
			$rd['BatchesContacts_ID']=q("INSERT INTO relatebase_BatchesContacts SET 
			Batches_ID='$Batches_ID',
			Status='sent',
			".($Contacts_ID ? "Contacts_ID=$Contacts_ID, " : "Visitors_ID=$Visitors_ID,")."
			CreateDate=NOW(),
			Creator='".$PHP_AUTH_USER."'", O_INSERTID);
			$fieldList['batchescontacts_id']=array(
				1=>'BatchesContacts_ID',
			);
		}
	}

	//compile the body and subject
	if($bodyCompilationNeeded){
		$logic_algorithm_i1['logicPresent']=false;
		$thisEmailBody=logic_algorithm_i1($emailBody);
		if($logic_algorithm_i1['logicPresent']==false) $bodyCompilationNeeded=false;
		$thisEmailBody = '?'.'>' .$thisEmailBody . '<'.'?php ';
		ob_start(); 
		eval($thisEmailBody); 
		$thisEmailBody = ob_get_contents(); 
		ob_end_clean();
	}else{
		$thisEmailBody=$emailBody;
	}
	if($subjectCompilationNeeded){
		$logic_algorithm_i1['logicPresent']=false;
		$thisEmailSubject=logic_algorithm_i1($emailSubject);
		if($logic_algorithm_i1['logicPresent']==false){
			/*** ----------------- DEVNOTES 2004-07-12 -----------------------------
			IF no compilation needed, we could send this out BCC once that feature can be made to work, which would cut down on server time tremendously...

			*** ---------------------------------------------------------------- ***/
			$subjectCompilationNeeded=false;
		}
		$thisEmailSubject = '?'.'>' .$thisEmailSubject . '<'.'?php ';
		ob_start(); 
		eval($thisEmailSubject); 
		$thisEmailSubject = ob_get_contents(); 
		ob_end_clean();
	}else{
		$thisEmailSubject=$emailSubject;
	}
	//rows before or after the rowIdx are excluded
	if($mode=='previewbatch'){
		if($i<$rowIdx){
			continue;
		}else if($i>$rowIdx){
			exit;
		}
		$from=from_email($FromName,$FromEmail);
		//generate the control form

		?><script>function document.onkeypress(){if(event.keyCode==27){window.close();}}</script>
		<form style="margin:0;" name="form1" method="post" action="">
		<div style="padding: 3 0 3 3; background-color:mintcream; border-bottom:1px solid #000000">
		<input type="button" name="Submit" value="Previous" <?php if($rowIdx==1)echo 'disabled';?> onClick="window.opener.d.rowIdx.value='<?php echo $rowIdx-1?>';window.opener.form1.mode.value='previewbatch';window.opener.form1.submit();return false;">&nbsp;&nbsp;&nbsp;		
		<input type="button" name="Submit2" value="Close" onClick="window.close();">&nbsp;&nbsp;&nbsp;		
		<input type="button" name="Submit3" value="Next" onClick="window.opener.d.rowIdx.value='<?php echo $rowIdx+1?>';window.opener.form1.mode.value='previewbatch';window.opener.form1.submit();return false;"><br />
		From: <?php echo htmlentities($from)?><br />
		To: <?php echo implode(', ',$emails);?><br />
		Subject: <?php echo $thisEmailSubject?><br />
		Row number: <?php echo $rowIdx?>&nbsp;&nbsp; Email Size: <?php echo number_format(strlen($thisEmailBody)/1024,2).'KB'?>&nbsp;&nbsp;
		<?php echo '<script>'?>
		var op=new Array();
		op['block']='none';
		op['none']='block';
		<?php echo '</script>'?>
		<a href='#' onClick="g('environment').style.display=op[g('environment').style.display];return false">Environment</a>
		<div id="environment" style="display:none; padding:5;"><?php prn($rd)?></div>
		</div></form>
		<title><?php echo 'Subject Line: '.$thisEmailSubject ?></title>
		<?php
		if($TextOrHTML){
			echo $thisEmailBody;
		}else{
			$thisEmailBodyText=nl2br($thisEmailBody);
			$thisEmailBodyText=str_replace("\t",'&nbsp;&nbsp;&nbsp;&nbsp;',$thisEmailBodyText);
			?><div style="font-family:Courier New, Monospace;font-size:13px;padding:0 3"><?php
			echo $thisEmailBodyText;
			?></div><?php
		}
		$assumeErrorState=false;
		exit;
	}

	//send out content and make db entries
	foreach($emails as $v){
		//send the mail out
		//name
		$from=from_email($FromName,$FromEmail);
		//handle replyToEmail
		if(preg_match('/[_a-zA-Z0-9-]+(\.[_a-zA-Z0-9-]+)*@[a-zA-Z0-9-]+(\.[a-zA-Z0-9-]+)+/',$ReplyToEmail)){
			if(preg_match('/"/',$ReplyToName) && $ReplyToName !=='(optional)'){
				$x='"'.str_replace('"','\"',$ReplyToName) . '"';
			}else{
				$x=$ReplyToName;
			}
			$replyTo='Reply-To: '.$x.'<'.$ReplyToEmail.'>';
		}
		$preHeaders=$replyTo;
		
		//we bounce the emails back to the sender, not the server, to minimize server load
		$bounce=($BounceEmail?$BounceEmail:$FromEmail);

		//handle batch recovery
		if($CrossCheckBatch && $CrossCheckBatchNumber && q("SELECT Email FROM relatebase_mail_batches_logs WHERE Profiles_ID = '$ID' AND Email = '$v' AND Batches_ID='$CrossCheckBatchNumber'", O_VALUE)){
			echo "[Batch Recovery Mode]-already sent to $v<br />";
			continue;
		}

		$rd['CurrentEmailSent']=$v;
		$fieldList['currentemailsent']=array(
			1=>'CurrentEmailSent',
		);

		$sendCount++;
		enhanced_mail($options=array(
			'to'=> ($TestMode ? $TestEmail : $v) /* 'sam-git@compasspointmedia.com' */,
			'subject'=> stripslashes($thisEmailSubject),
			'body'=> str_replace('{CurrentEmailSent}',$v,$thisEmailBody),
			'from'=> stripslashes($from),
			
			'mode'=> 'html',
			
			'fileArray'=> (count($fileArray)?$fileArray: NULL),
			'important'=> ($Importance==1?1:0),
			'preHeaders'=> NULL,
			'postHeaders'=> NULL,
			'output'=>'mail',
			'fSwitchEmail'=> $bounce,
			'creator'=> $acct,
			'logmail'=> true,
			'mailedBy'=> $PHP_AUTH_USER,
		)); 

		/* old:delete by 6/30/2011 - enhanced_mail(/*$v* / 'sam-git@compasspointmedia.com', stripslashes($thisEmailSubject), $thisEmailBody, stripslashes($from), $sendType, (count($fileArray)?$fileArray:''), ($Importance==1?1:0), $preHeaders, '', '', ($bounce?$bounce:'') ); */
		
		//log emails used to send batches twice
		if(!in_array($v,$emailSentList)){
			$emailSentList[]=$v;
		}else{
			$duplicateEmails[$v]++;
		}
		$totalSize+=strlen($thisEmailBody)/1024;
		echo 'Record '.$i.': '.$v . '<br />';
	}
}
//update batch
q("UPDATE relatebase_content_batches SET StopTime=NOW() WHERE ID = '$Batches_ID'");
echo '<br />finished at ' . $stopTime;

//send batch report
ob_start();
require($MASTER_COMPONENT_ROOT.'/mailer_profile_batchreport_v100.php');
$x=ob_get_contents();
ob_end_clean();
enhanced_mail($BatchRecordEmail, 'Batch Report', $x, 'batchreports@relatebase.com', 'HTML');
echo "<br />";
echo "Batch report mailed to ".$BatchRecordEmail;
?>