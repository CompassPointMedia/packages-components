<?php
/*
this component was created after a desire to modularize the nav buttons to the point where I can trust the underlying logic is solid, and send sensible paramters to this to accomodate any situation.  This includes jQuery integration when we start doing that.

$navbuttons_out is the main HTML output of this component, setting $componentRewrite=true will buffer this component as that variable

TODO:
the OK button, when in updateMode, should dynamically change to "Update", and pair off with the "Canel" button which should appear and alert - this is more intuitive.

*/


ob_start();

if(!$navbuttonClass)$navbuttonClass='navButton_A';
if(!$navbuttonWrapClass)$navbuttonWrapClass='fr';
if(!$navbuttonWrapID)$navbuttonWrapID='btns140';
$navbuttonsIdx++;
?><div id="<?php echo $navbuttonWrapID . ($navbuttonsIdx>1?'_'.$navbuttonsIdx:'');?>" class="<?php echo $navbuttonWrapClass?>">
<?php
ob_start();
?>
<input id="Previous" type="button" name="Submit" value="Previous" class="<?php echo $navbuttonClasses['Previous'] ? $navbuttonClasses['Previous'] : $navbuttonClass?>" onClick="focus_nav(-1, '<?php echo ($mode==$insertMode?'insert':'update')?>', <?php echo $mode==$insertMode?1:0?>, 0<?php echo $navQueryFunction ? ", '".$navQueryFunction . "'" :'';?>);" <?php echo $nullAbs<=1?'disabled':''?>>
<?php
//Handle display of all buttons besides the Previous button
if($mode==$insertMode){
	if($insertType==2 /** advanced mode **/){
		//save
		?><input id="Save" type="button" name="Submit" value="Save" class="<?php echo $navbuttonClasses['Save'] ? $navbuttonClasses['Save'] : $navbuttonClass?>" onClick="focus_nav(0,'insert',1,2<?php echo $navQueryFunction ? ", '".$navQueryFunction . "'" :'';?>);" <?php echo $saveInitiallyDisabled?> /><?php
	}
	//save and new - common to both modes
	?><input id="SaveAndNew" type="button" name="Submit" value="Save &amp; New" class="<?php echo $navbuttonClasses['SaveAndNew'] ? $navbuttonClasses['SaveAndNew'] : $navbuttonClass?>" onClick="focus_nav(0,'insert', 1,1<?php echo $navQueryFunction ? ", '".$navQueryFunction . "'" :'';?>);" <?php echo $saveAndNewInitiallyDisabled?> /><?php
	if($insertType==1 /** basic mode **/){
		//save and close
		?><input id="SaveAndClose" type="button" name="Submit" value="Save &amp; Close" class="<?php echo $navbuttonClasses['SaveAndClose'] ? $navbuttonClasses['SaveAndClose'] : $navbuttonClass?>" onClick="focus_nav(0,'insert', 1,0<?php echo $navQueryFunction ? ", '".$navQueryFunction . "'" :'';?>);" <?php echo $saveAndCloseInitiallyDisabled?> /><?php
	}
	?><input id="CancelInsert" type="button" name="Submit" value="Cancel" class="<?php echo $navbuttonClasses['CancelInsert'] ? $navbuttonClasses['CancelInsert'] : $navbuttonClass?>" onClick="focus_nav_cxl('insert');" /><?php
}else{
	//OK, and appropriate [next] button
	?><input id="OK" type="button" name="Submit" value="OK" class="<?php echo $navbuttonClasses['OK'] ? $navbuttonClasses['OK'] : $navbuttonClass?>" onClick="focus_nav(0,'<?php echo $mode==$insertMode?'insert':'update'?>',0,0<?php echo $navQueryFunction ? ", '".$navQueryFunction . "'" :'';?>);" />
	<?php 
	if($n=$navbuttonOverrideLabel['Cancel']){
		?><input type="button" name="Submit" value="<?php echo $n;?>" onclick="focus_nav_cxl('update');" class="<?php echo $navbuttonClasses['CancelUpdate'] ? $navbuttonClasses['CancelUpdate'] : $navbuttonClass?>" />
		<?php
	}
	?>
	<input id="Next" type="button" name="Submit" value="Next" class="<?php echo $navbuttonClasses['Next'] ? $navbuttonClasses['Next'] : $navbuttonClass?>" onClick="focus_nav(1,'<?php echo $mode==$insertMode?'insert':'update'?>',0,0<?php echo $navQueryFunction ? ", '".$navQueryFunction . "'" :'';?>);" <?php echo $nullAbs>$nullCount || ($denyNextToNew && $nullAbs==$nullCount) ?'disabled':''?> /><?php
}
$navbuttons=ob_get_contents();
ob_end_clean();
//2009-09-10 - change button names, set default as =submit, hide unused buttons
if(!$addRecordText)$addRecordText='Add Record';
if(!isset($navbuttonDefaultLogic))$navbuttonDefaultLogic=true;
if($navbuttonDefaultLogic){
	$navbuttonSetDefault=($mode==$insertMode?'SaveAndNew':'OK');
	if($cbSelect){
		$navbuttonOverrideLabel['SaveAndClose']=$addRecordText;
		$navbuttonHide=array(
			'Previous'=>true,
			'Save'=>true,
			'SaveAndNew'=>true,
			'Next'=>true,
			'OK'=>true
		);
	}
}
$navbuttonLabels=array(
	'Previous'		=>'Previous',
	'Save'			=>'Save',
	'SaveAndNew'	=>'Save &amp; New',
	'SaveAndClose'	=>'Save &amp; Close',
	'CancelInsert'	=>'Cancel',
	'OK'			=>'OK',
	'Next'			=>'Next'
);
foreach($navbuttonLabels as $n=>$v){
	if($navbuttonOverrideLabel[$n])
	$navbuttons=str_replace(
		'id="'.$n.'" type="button" name="Submit" value="'.$v.'"', 
		'id="'.$n.'" type="button" name="Submit" value="'.h($navbuttonOverrideLabel[$n]).'"', 
		$navbuttons
	);
	if($navbuttonHide[$n])
	$navbuttons=str_replace(
		'id="'.$n.'" type="button"',
		'id="'.$n.'" type="button" style="display:none;"',
		$navbuttons
	);
}
if($navbuttonSetDefault)$navbuttons=str_replace(
	'<input id="'.$navbuttonSetDefault.'" type="button"', 
	'<input id="'.$navbuttonSetDefault.'" type="submit"', 
	$navbuttons
);
echo $navbuttons;

// *note that we could go back to the same page the 'New Record' click appeared on, but there's major issues programmatically on whether it would shift because of the placement of the new record.
// *note that the primary key field is now included here to save time
?>
<input name="<?php echo $recordPKField?>" type="hidden" id="<?php echo $recordPKField?>" value="<?php echo $$object;?>" />
<input name="navVer" type="hidden" id="navVer" value="<?php echo $navVer?>" />
<input name="navObject" type="hidden" id="navObject" value="<?php echo $navObject?>" />
<input name="nav" type="hidden" id="nav" />
<input name="navMode" type="hidden" id="navMode" value="" />
<input name="count" type="hidden" id="count" value="<?php echo $nullCount?>" />
<input name="abs" type="hidden" id="abs" value="<?php echo $nullAbs?>" />
<input name="insertMode" type="hidden" id="insertMode" value="<?php echo $insertMode?>" />
<input name="updateMode" type="hidden" id="updateMode" value="<?php echo $updateMode?>" />
<input name="deleteMode" type="hidden" id="deleteMode" value="<?php echo $deleteMode?>" />
<input name="mode" type="hidden" id="mode" value="<?php echo $mode?>" />
<input name="submode" type="hidden" id="submode" value="" />
<input name="componentID" type="hidden" id="componentID" value="<?php echo $localSys['componentID']?>" />
<?php
if(count($_REQUEST))
foreach($_REQUEST as $n=>$v){
	if(substr($n,0,2)=='cb'){
		if(!$setCBPresent){
			$setCBPresent=true;
			?><!-- callback fields automatically generated --><?php
			echo "\n";
			?><input name="cbPresent" id="cbPresent" value="1" type="hidden" /><?php
			echo "\n";
		}
		if(is_array($v)){
			foreach($v as $o=>$w){
				echo "\t\t";
				?><input name="<?php echo $n?>[<?php echo is_numeric($o)? '': $o?>]" id="<?php echo $n?>[<?php echo is_numeric($o)? '': $o?>]" type="hidden" value="<?php echo stripslashes($w)?>" /><?php
				echo "\n";
			}
		}else{
			echo "\t\t";
			?><input name="<?php echo $n?>" id="<?php echo $n?>" type="hidden" value="<?php echo stripslashes($v)?>" /><?php
			echo "\n";
		}
	}
}
?></div><?php

$navbuttons_out=ob_get_contents();
ob_end_clean();
if(!$componentRewrite)echo $navbuttons_out;

?>