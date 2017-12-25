<html>
<head>
<title>RelateBase MPM Batch Report</title>
<style>
p{
	margin:0;
}
body,p,td,div{
	font-family:Verdana,Arial,Sans-serif;
	font-size:11px;
}
</style>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>
<body bgcolor="#FFFFFF" text="#000000">
<h2>RelateBase Batch Report</h2>
<p>Here is your receipt for batch email sent out using the RelateBase Mail Profile 
  Management System.<br />
  Links in this batch report link to the appropriate help topic for that item. 
  Please take the time to learn about the Mail Profile system!<br />
  --RelateBase Staff </p>
<p>&nbsp;</p>
<p>Mail Profile Used: <?php echo htmlentities(stripslashes($ProfileName))?><br />
  Account Name: <?php echo $acct?><br />
  Sent by: <?php echo $cu?><br />
  Total Emails Sent: <?php echo $sendCount?><br />
  Batch started at: <?php echo $startTime?><br />
  Batch finished at: <?php echo $stopTime?><br />
</p>
<p><br />
</p>
<h3>Recipient Information (people who received the email)</h3>
<p>Total recipient count: <?php
echo $sendCount;
?><br />
<?php
$listMethod=array(
	'none' => 'Not known', 
	'manual' => 'Manually entered list',
	'import' => 'From Imported File',
	'view' => 'From RelateBase View',
	'complex' =>'From Structured Query Language Query'
);


?>
  Recipient List Method: <?php echo $listMethod[$RecipientSource]?><br />
<?php
switch($RecipientSource){
	case 'manual':
		//nothing needed

	break;
	case 'import':
		//echo file name

	break;
	case 'view':
		//echo view information

	break;
	case 'complex':
		?><div style="background-color:aliceblue; border-bottom:1px dashed darkseagreen; border-bottom:1px dashed darkseagreen; padding: 6"><?php echo nl2br(htmlentities(stripslashes($ComplexQuery)))?></div><?php
	break;
	case 'none':
	break;
}


?>
Recipients Receiving Duplicates: <?php
if(!count($duplicateEmails)){
	echo '(none)';
}else{
	foreach($duplicateEmails as $n=>$v){
		$x++;
		if($x>100){
			echo ' .. (more)';
			break;
		}
		echo $n . '('. $v .'), ';
	}
}
?><br />
  Email List: <?php
if(!count($emailSentList)){
	echo '(none)';
}else{
	foreach($emailSentList as $n=>$v){
		$x++;
		if($x>2000){
			echo ' .. (more)';
			break;
		}
		echo $v.', ';
	}
}
?><br />
  <br />
<h3>Email Content Information</h3>
<p>Average Email Size (not including attachments): 
  <?php echo number_format($totalSize/($sendCount?$sendCount:1),2)?>
  <br />
  Send mode: 
  <?php  echo $HTMLOrText==0?'Plain text':'HTML'?>
  <br />
  Template Used: 
  <?php echo $Composition=='template'?'Yes':'No'?>
  <br />
  Location of Template: 
  <?php
if($Composition=='template'){
	if($TemplateMethod=='file'){
		//get file using ID
		echo 'VOS FILE ( ' . $Files_ID . ' )';
	}else if($TemplateMethod=='url'){
		echo '<a href="'.$TemplateLocationURL . '">'.$TemplateLocationURL . '</a>';
	}
}else{
	echo 'N/A';
}
?>
  <br />
  Logic used: 
  <?php echo count($mm_logic)?'Yes':'No'?>
  <br />
  NOTE: A copy of uncompiled email body is being sent separately<br />
  <br />
  Attachments:<br />
  <?php
if(trim($AttachmentList)){
	$sql="SELECT VOSFileName, CreateDate, LocalFileName FROM relatebase_files WHERE ID IN(".preg_replace('/,$/','',$AttachmentList).") ORDER BY LocalFileName";
	$fl=__FILE__;$ln=__LINE__ +1;
	$result=mysqli_query($db_cnx, $sql) or sql_handle_exception($fl,$ln);
	if(mysqli_num_rows($result)){
		?><table cellpadding="2" cellspacing="0"><?php
		while($rd=mysqli_fetch_array($result)){
			$atRow++;
			if($atRow==1){
				?>
			  <tr bgcolor="#003366"> 
				 <td><font color="#FFFFFF">File Name</font></td>
				 <td><font color="#FFFFFF">File Size</font></td>
				 <td><font color="#FFFFFF">Created</font></td>
			  </tr>
			  <?php
						}
						?>
			  <tr> 
				 <td> 
					<?php
						echo htmlentities($rd[LocalFileName]);
						?>
				 </td>
				 <td> 
					<?php
						echo number_format(filesize("$VOS_ROOT/{$rd[VOSFileName]}")/1024,2) . 'kb';
						?>
				 </td>
				 <td> 
					<?php
						echo date('m/d/Y H:iA',strtotime($rd[CreateDate]));
						?>
				 </td>
			  </tr>
			  <?php
		}
		?></table><?php
	}else{
		//abnormal
	}
}else{
	?>(none)<br /><?php
}
?>
<p> <br />
<h3>Batch Delivery Information</h3>
<p>From: <?php echo htmlentities(stripslashes($FromName));?><br />
  Reply-To: <?php echo htmlentities(stripslashes($ReplyToName . ' ('.$ReplyToEmail . ')'))?><br />
  Bounce emails sent to: <?php echo $BounceEmail?><br />
  Important: <?php echo $Importance?'Yes':'No'?><br />
</p>
<p>Batch report (this report) sent to: <?php echo $BatchRecordEmail?><br />
  Batch Comments: <?php echo nl2br(htmlentities(stripslashes($BatchRecordComment)))?></p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p>for further assistance, please contact RelateBase at support@relatebase.com</p>
</body>
</html>
