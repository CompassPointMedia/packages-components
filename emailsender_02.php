<?php
/*
2009-05-14: simple email sender component - uses enhanced_mail and best practice I have so far
*/
ob_start();
require($emailSource);			
$out=ob_get_contents();
ob_end_clean();
$from=($emailFrom ? $emailFrom : $systemEmail['from']);
if(strtolower($systemEmail['content_disposition'])=='html'){
	$from=preg_replace('/From: /i','',$from);
	$sent=enhanced_mail(
		($emailTo ? $emailTo : $systemEmail['to']),
		($emailSubj ? $emailSubj : $systemEmail['subject']),
		$out,
		$from,
		'html',
		'',
		$fileArray,
		$important,
		$preHeaders,
		$postHeaders
	);
}else{
	$from='From: '.preg_replace('/From: /i','',$from);
	$sent=mail(
		($emailTo ? $emailTo : $systemEmail['to']),
		($emailSubj ? $emailSubj : $systemEmail['subject']),
		$out,
		$from
	);
}
?>