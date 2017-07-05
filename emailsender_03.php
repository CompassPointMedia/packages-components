<?php
/*
2010-10-08:
* if length of emailSource is > 255 characters, it is considered a string vs. a file path
2010-02-24: 
* added testing where it is returned vs mailed
	emTest=1 - treat as a test and then reset the value to 0
	emTest=2 - treat as a test and do NOT reset the value
	emTestAction=[returnParams | returnAllParams | shunt]
* alerted when not using enhanced_mail version 2.1+
* simplified from emailsender_02

2010-02-22: added coding to accommodate the new enhanced_mail v2.10 which includes db maillogging; this entire if structure could now collapse quite a bit to simplify
2009-05-14: simple email sender component - uses enhanced_mail and best practice I have so far
*/

for($__es__=1; $__es__<=1; $__es__++){ //------------ break loop ----------


if(strlen($emailSource)>255){
	$emailOut=$emailSource;
}else{
	if(!file_exists($emailSource) || is_dir($emailSource)){
		mail($developerEmail, 'Email file non-existent! Error file '.__FILE__.', line '.__LINE__,get_globals(),$fromHdrBugs);
		break;
	}
	ob_start();
	require($emailSource);			
	$emailOut=ob_get_contents();
	ob_end_clean();
}

if($functionVersions['enhanced_mail']<2.10){
	mail($developerEmail, 'Error file '.__FILE__.', line '.__LINE__,get_globals(),$fromHdrBugs);
	error_alert('Component emailsender_03.php requires function enhanced_mail() version 2.10 or greater');
}
$from=($emailFrom ? $emailFrom : $systemEmail['from']);
$from=preg_replace('/^From: /i','',$from);
if($testingEmails){
	//$emailTo='mgatesting@hotmail.com';
}
$sent=enhanced_mail( array(
	'to'=>($emailTo ? $emailTo : $systemEmail['to']),
	'subject'=>($emailSubj ? $emailSubj : $systemEmail['subject']),
	'body'=>$emailOut,
	'from'=>$from,
	'mode'=>($systemEmail['content_disposition'] ? strtolower($systemEmail['content_disposition']) : 'plaintext'),
	'fileArray'=>($fileArray ? $fileArray : NULL),
	'important'=>($important ? $important : NULL),
	'preHeaders'=>($preHeaders ? $preHeaders : NULL),
	'postHeaders'=>($postHeaders ? $postHeaders : NULL),
	
	/* --------- new options -------- NOTE: 'logmail'=> [must be set globally] */
	'emTest'=> (isset($emTest) ? $emTest : NULL),
	'emTestAction'=> (isset($emTestAction) ? $emTestAction : NULL),

	'creator'=> (isset($emailCreator) ? $emailCreator : NULL),
	'cnx'=> (isset($emailCnx) ? $emailCnx : NULL),
	'mailedBy'=> (isset($mailedBy) ? $mailedBy : NULL),
	'maillogNotes'=> (isset($mailLogNotes) ? $mailLogNotes : NULL),
	'templateSource'=> (isset($templateSource) ? $templateSource : NULL),
	'maillogTable'=> (isset($maillogTable) ? $maillogTable : NULL),

	/* better define what the email's about: go into ML_Category, ML_Department and ML_Notes */
	'emCategory'=> (isset($emCategory) ? $emCategory : NULL),
	'emDepartment'=> (isset($emDepartment) ? $emDepartment : NULL),
	'emNotes'=> (isset($emNotes) ? $emNotes : NULL)
));
}	//----------- end break loop -------------

?>