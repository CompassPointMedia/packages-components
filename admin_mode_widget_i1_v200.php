<?php
/* Created 2010-04-13
2011-01-17
added thisnode && thissubfolder - Juliet vars

Documentation
	How to set up admin mode(After all symbolic linking and database setup is done)
		Copy an admin.php and index_01_exe from another site
		Put the appropriate coding in place on the Template (<title><?php echo metatags_i1('title');?></title> and <?php echo metatags_i1('meta');?>)
		Put the CSS (Which can be found in this document) in the local .css file (And make sure to link to the file)
		Add the I-Frames to the Template
		If you run into any errors look at the "Common Errors" section
		
	Common Errors
		Admin Access Mode is not "above" the rest of the document
			Need to add the CSS to the local stylesheet(Or document)
		Admin Access Mode values are not "sticking"
			Make sure you have <title><?php echo metatags_i1('title');?></title> and <?php echo metatags_i1('meta');?>
			Have index_01_exe.php (A working copy) in the root of the site
			Have all symbolic links
			Have I-Frames
			If you still cannot solve the problem, put the I-frames in the document, look at them in firebug, unhide them and see what they are outputting upon posting the document.
		Admin Access is not outputting source code
			Be sure to require the admin mode widget (the latest version)
		Admin Access is outputting source code but does not display
			Make sure all the symbolic links you need are present (Check firebug's error console) 
*/
//settings
if(!$amWidgetMode)$amWidgetMode='form';

/* Title and Metatag editor - abstracted to component 2009-01-24 */
if(false && 'Use Metatag Editor CSS'){
	?><style type="text/css">
	/* CSS used for admin mode toolbar */
	#adminMode{
		background-color:SADDLEBROWN;
		border:1px solid #000;
		width:200px;
		padding:0px 5px;
		color:WHITE;
		font-family:Arial;
		position:fixed;
		top:15px;
		left:15px;
		}
	#adminModeExit{
		float:right;
		padding:0px 2px;
		margin:1px;
		border:1px solid WHITE;
		font-weight:900;
		font-family:Arial;
		}
	#adminModeExit a{
		color:#FFF;
		text-align:center;
		font-size:9px;
		}
	.amBtn{
		float:right;
		padding:0px 2px;
		margin:1px;
		border:1px solid WHITE;
		font-weight:900;
		font-family:Arial;
		}
	.amBtn a{
		color:#FFF;
		text-align:center;
		font-size:9px;
		font-weight:900;
		padding:0px 3px;
		}
	#adminMode{
		text-align:left;
		width:275px;
		-moz-opacity:.85;
		background-color:DARKKHAKI;
		}
	textarea.amFlds{
		font-size:11px;
		line-height:13px;
		width:250px;
		border:1px solid #000;
		}
	#amBody{
		clear:both;
		text-align:left;
		}
	</style><?php
}
if($adminMode){
	if($amWidgetMode=='form'){
		$hideCtrlSection=false;
		?><div id="adminMode" <?php echo 'style="display:none;"';?>>
			<div class="amBtn">
				<a title="Leave Admin Access Mode" onclick="return confirm('Leave Administrative Access Mode?');" href="../juliet-site-editor?logout=1&amp;src=<?php echo urlencode($_SERVER['PHP_SELF'] . ($_SERVER['QUERY_STRING'] ? '?'.$_SERVER['QUERY_STRING'] : ''))?>">X</a>
			</div><div class="amBtn">
				<a id="amMin" title="<?php echo $_COOKIE['amBody']=='none'?'Expand':'Minimize'?> Admin Toolbar" href="#" onclick="return adminAccessMin();"><?php echo $_COOKIE['amBody']=='none'?'+':'-'?></a>
			</div>
			Administrative Access Mode
			<form id="amBody" style="display:<?php echo $_COOKIE['amBody'] ? $_COOKIE['amBody'] : 'block';?>;" action="<?php echo stristr($_SERVER['REQUEST_URI'],'~'.$MASTER_DATABASE.'/') ? '/~'.$MASTER_DATABASE : '';?>/index_01_exe.php" target="w2" onsubmit="g('adminModeUpdate').disabled=true;return beginSubmit();" method="post">
			<strong>Page Title</strong>:<br />
			<input title="<?php echo h($metatags['title']);?>" name="MetaTitle" class="amFlds" id="MetaTitle" style="border:1px solid #000;" value="<?php echo h($metatags['title']);?>" size="25" <?php 
			if(strlen($metatags['record']['TField']) && !preg_match('/^[a-z0-9_]+$/i',$metatags['record']['TField'])){
				$noEditTitle=true;
				echo 'disabled';
			}
			?> /><?php
			if($noEditTitle){
				?>&nbsp;<img title="Title is compiled from more than one field and cannot be edited directly" src="Library/Library/images/i/del2.gif" alt="no edit" style="cursor:pointer;" onclick="alert(this.title);" width="16" height="18" /><?php
			}
			?>
			<br />
			<strong>Meta Description</strong>:<br />
			<textarea class="amFlds" name="MetaDescription" rows="3" id="MetaDescription"><?php echo h($metatags['description']);?></textarea><br />
			<strong>Meta Keywords</strong><br />
			<textarea class="amFlds" name="MetaKeywords" rows="3" id="MetaKeywords"><?php echo h($metatags['keywords']);?></textarea><br />
			
			
			<?php
			//additional fields to change things page-wide
			if($amWidgetAdditionalFieldsLocation){
				require($amWidgetAdditionalFieldsLocation);
			}
			?>
			
			<input name="thisnode" type="hidden" id="thisfolder" value="<?php echo htmlentities($thisnode)?>" />
			<input name="thispage" type="hidden" id="thispage" value="<?php echo htmlentities($thispage)?>" />
			<input name="thissubfolder" type="hidden" id="thissubfolder" value="<?php echo htmlentities($thissubfolder)?>" />
			<input name="thisfolder" type="hidden" id="thisfolder" value="<?php echo htmlentities($thisfolder)?>" />
			<input name="QUERY_STRING" type="hidden" id="QUERY_STRING" value="<?php echo htmlentities($_SERVER['QUERY_STRING'])?>" />
			
			<input name="mode" type="hidden" id="mode" value="updateMetaTags" />
			<br />
			<input id="adminModeUpdate" type="submit" name="Submit" value=" Update " />
			&nbsp;&nbsp;&nbsp;
			<input type="button" name="Submit" value="Config.." onclick="if(confirm('This feature is only for database administrators. Continue?'))window.location='/resources/configure_metatags.php?page='+g('thispage').value+'&amp;folder='+g('thisfolder').value;" />
	
			
			<input type="button" name="Submit" value="Image Manager.." onclick="ow('/admin/file_explorer/?uid=imgmanager','l1_imgmanager','700,700');" />
			</form>
		</div><script language="JavaScript" type="text/javascript">g('adminMode').style.display='block'</script><?php
	}else{
		if($mode=='updateMetaTags'){
			/*
			this works in conjunction with function_metatags_i1_v101.php, note the explode by ':' - you should also have those fields present, and so the latest revision in the admin toolbar is to hidden-field-ize the query string vars
			
			
			*/
			if(!$adminMode)error_alert('You can only perform this task in Administrative Access Mode');
			$thispage=$_POST['thispage'];
			$thisfolder=$_POST['thisfolder'];
			//call function twice
			metatags_i1('title');
			metatags_i1('meta');
			prn($metatags);
			extract($metatags['record']);
			//parse the query string
			if($_POST['QUERY_STRING']){
				//HOPE IT'S ENCODED RIGHT!
				$a=explode('&',$_POST['QUERY_STRING']);
				foreach($a as $v){
					$b=explode('=',$v);
					$c[$b[0]]=urldecode($b[1]);
				}
				foreach($c as $n=>$v){
					//globalize
					$$n=$v;
				}
			}
			
			//title first
			if(isset($MetaTitle)){
				if($TTable && $TField && $TVar1){
					$and='';
					if(strlen($TVar2)){
						$TVar2=explode(':',$TVar2);
						$and=" AND ".$TVar2[0]."='".( $TVar2[1] ? $$TVar2[1] : $$TVar2[0] )."'";
					}
					$TVar1=explode(':',$TVar1);
					if(!preg_match('/^[a-z0-9_]+$/i',$TField)){
						//no go
					}else{
						if(q("SELECT * FROM $TTable WHERE ".$TVar1[0]."='".( $TVar1[1] ? $$TVar1[1] : $$TVar1[0] )."' $and", O_ROW)){
							//update
							ob_start();
							q("UPDATE $TTable SET $TField='".$MetaTitle."' WHERE ".$TVar1[0]."='".( $TVar1[1] ? $$TVar1[1] : $$TVar1[0] )."'", ERR_ECHO);
							$err=ob_get_contents();
							ob_end_clean();
							prn($qr);
							if($err)mail($developerEmail,'error in file '.__FILE__.', line '.__LINE__,get_globals(),$fromHdrBugs);
						}else{
							//insert
							q("INSERT INTO $TTable SET ".$TVar1[0]."='".( $TVar1[1] ? $$TVar1[1] : $$TVar1[0] )."'".($and ? ", ".$TVar2[0]."='".( $TVar2[1] ? $$TVar2[1] : $$TVar2[0] )."'" : '').", $TField='".$MetaTitle."', CreateDate=NOW(), Creator='system'");
						}
					}	
				}else{
					if($r=q("SELECT * FROM site_metatags WHERE ThisFolder='$thisfolder' AND ThisPage='$thispage'", O_ROW)){
						q("UPDATE site_metatags SET Title='$MetaTitle', EditDate=NOW() WHERE ThisFolder='$thisfolder' AND ThisPage='$thispage'");
						prn($qr);
						if(!$qr['affected_rows'])mail($developerEmail,'error in file '.__FILE__.', line '.__LINE__,get_globals(),$fromHdrBugs);
					}else{
						q("INSERT INTO site_metatags SET Title='$MetaTitle', EditDate=NOW(), ThisFolder='$thisfolder', ThisPage='$thispage'");
					}
				}
			}	
	
			//description
			if(isset($MetaDescription)){
				if($DTable && $DField && $DVar1){
					$and='';
					if(strlen($DVar2)){
						$DVar2=explode(':',$DVar2);
						$and=" AND ".$DVar2[0]."='".( $DVar2[1] ? $$DVar2[1] : $$DVar2[0] )."'";
					}
					$DVar1=explode(':',$DVar1);
	
					if(!preg_match('/^[a-z0-9_]+$/i',$DField)){
						//no go
					}else{
						if(q("SELECT * FROM $DTable WHERE ".$DVar1[0]."='".( $DVar1[1] ? $$DVar1[1] : $$DVar1[0] )."' $and", O_ROW)){
							//update
							ob_start();
							q("UPDATE $DTable SET $DField='".$MetaDescription."' WHERE ".$DVar1[0]."='".( $DVar1[1] ? $$DVar1[1] : $$DVar1[0] )."'", ERR_ECHO);
							$err=ob_get_contents();
							ob_end_clean();
							prn($qr);
							if($err)mail($developerEmail,'error in file '.__FILE__.', line '.__LINE__,get_globals(),$fromHdrBugs);
						}else{
							//insert
							q("INSERT INTO $DTable SET ".$DVar1[0]."='".( $DVar1[1] ? $$DVar1[1] : $$DVar1[0] )."'".($and ? ", ".$DVar2[0]."='".( $DVar2[1] ? $$DVar2[1] : $$DVar2[0] )."'" : '').", $DField='".$MetaDescription."', CreateDate=NOW(), Creator='system'");
						}
					}	
				}else{
					if($r=q("SELECT * FROM site_metatags WHERE ThisFolder='$thisfolder' AND ThisPage='$thispage'", O_ROW)){
						q("UPDATE site_metatags SET Description='$MetaDescription', EditDate=NOW() WHERE ThisFolder='$thisfolder' AND ThisPage='$thispage'");
						prn($qr);
						if(!$qr['affected_rows'])mail($developerEmail,'error in file '.__FILE__.', line '.__LINE__,get_globals(),$fromHdrBugs);
					}else{
						q("INSERT INTO site_metatags SET Description='$MetaDescription', EditDate=NOW(), ThisFolder='$thisfolder', ThisPage='$thispage'");
					}
				}
			}	
			//keywords
			if(isset($MetaKeywords)){
				if($KTable && $KField && $KVar1){
					$and='';
					if(strlen($KVar2)){
						$KVar2=explode(':',$KVar2);
						$and=" AND ".$KVar2[0]."='".( $KVar2[1] ? $$KVar2[1] : $$KVar2[0] )."'";
					}
					$KVar1=explode(':',$KVar1);
	
					if(!preg_match('/^[a-z0-9_]+$/i',$KField)){
						//no go
					}else{
						$and=(strlen($KVar2) ? " AND ".$KVar2[0]."='".( $KVar2[1] ? $$KVar2[1] : $$KVar2[0] )."'" : '');
						if(q("SELECT * FROM $KTable WHERE ".$KVar1[0]."='".( $KVar1[1] ? $$KVar1[1] : $$KVar1[0] )."' $and", O_ROW)){
							//update
							ob_start();
							q("UPDATE $KTable SET $KField='".$MetaKeywords."' WHERE ".$KVar1[0]."='".( $KVar1[1] ? $$KVar1[1] : $$KVar1[0] )."'", ERR_ECHO);
							$err=ob_get_contents();
							ob_end_clean();
							prn($qr);
							if($err)mail($developerEmail,'error in file '.__FILE__.', line '.__LINE__,get_globals(),$fromHdrBugs);
						}else{
							//insert
							q("INSERT INTO $KTable SET ".$KVar1[0]."='".( $KVar1[1] ? $$KVar1[1] : $$KVar1[0] )."'".($and ? ", ".$KVar2[0]."='".( $KVar2[1] ? $$KVar2[1] : $$KVar2[0] )."'" : '').", $KField='".$MetaKeywords."', CreateDate=NOW(), Creator='system'");
						}
					}	
				}else{
					if($r=q("SELECT * FROM site_metatags WHERE ThisFolder='$thisfolder' AND ThisPage='$thispage'", O_ROW)){
						q("UPDATE site_metatags SET Keywords='$MetaKeywords', EditDate=NOW() WHERE ThisFolder='$thisfolder' AND ThisPage='$thispage'");
						prn($qr);
						if(!$qr['affected_rows'])mail($developerEmail,'error in file '.__FILE__.', line '.__LINE__,get_globals(),$fromHdrBugs);
					}else{
						q("INSERT INTO site_metatags SET Keywords='$MetaKeywords', EditDate=NOW(), ThisFolder='$thisfolder', ThisPage='$thispage'");
					}
				}
			}
			//additional fields to change things page-wide
			if($amWidgetAdditionalFieldsLocation){
				require($amWidgetAdditionalFieldsLocation);
			}
			?><script language="javascript" type="text/javascript">
			window.parent.g('adminModeUpdate').disabled=false;
			</script><?php
			$assumeErrorState=false;
		}else if($mode=='configureMetatags'){
			if(!function_exists('sql_insert_update_generic')){
				require('functions/function_sql_insert_update_generic_v100.php');
			}
			$sql=sql_insert_update_generic($MASTER_DATABASE,'site_metatags','REPLACE INTO');
			prn($sql);
			ob_start();
			q($sql, ERR_ECHO);
			$err=ob_get_contents();
			ob_end_clean();
			if($err){
				mail($developerEmail,'error in file '.__FILE__.', line '.__LINE__,get_globals(), $fromHdrBugs);
				error_alert('Abnormal error in submitting config - developer notified');
			}else{
				?><script language="javascript" type="text/javascript">
				if(confirm('Changes made; return to page?'))window.parent.location='<?php echo $referer;?>';
				</script><?php
			}
		}
	}
}else if($amWidgetMode!='form'){
	mail($developerEmail, 'Error file '.__FILE__.', line '.__LINE__,get_globals(),$fromHdrBugs);
	error_alert('You are not currently in administrative mode, or do not have permission to do this task');
}
?>