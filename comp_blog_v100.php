<?php 
/*
blog piece - started 2009-05-11; copied over from NCAAB where it was very specific.  a/o today it's being used for LACE (cpm050) and is still pretty specific - the parameter Articles_ID:ID is still hard-coded in, and we can't really tie this into cmsb_sections which I'd really want










*/


if(!$blogTable)$blogTable='addr_contacts_posts';
if(!$blogFields)$blogFields=array(
	'IDField'=>'ID',
	'parentIDField'=>'Posts_ID',
	'dateField'=>'PostDate',
	'titleField'=>'Name',
	'contentField'=>'Content',
	'ownerField'=>'OwnerContacts_ID'
);
if(!$sectionFilter)$sectionFilter=array(
	'fields'=>array(
		'Articles_ID'

	),
	'values'=>array(
		'eval:$_REQUEST[\'ID\']'

	)
);
if(!$newEntryTitleText)$newEntryTitleText='Add a Comment..';




























































if(!$refreshComponentOnly){
	?><script language="javascript" type="text/javascript">
	function postToBlog(){
		window.location='#lastpost';
		//g('PostContent').focus();
	}
	function resetPostToBlog(){
		g('replyToBlog'+formisat).innerHTML='';
		formisat='';
		g('blogNew').innerHTML=moveform;
		g('formLabel').innerHTML='New Entry';
		//g('PostContent').focus();
	}
	var formisat='';
	var moveform='';
	function replyToBlog(id){
		if(!moveform)moveform=g('blogNew').innerHTML;
		if(formisat){
			g('replyToBlog'+formisat).innerHTML='';
			g('replyToBlog'+id).innerHTML=moveform;
		}else{
			g('blogNew').innerHTML='[<a href="javascript:resetPostToBlog();">Post New Entry</a>]';
			g('replyToBlog'+id).innerHTML=moveform;
		}
		g('formLabel').innerHTML='Your Reply';
		formisat=id;
		//g('PostContent').focus();
	}
	</script>
	<style type="text/css">
	#blog{
		/*width:450px;
		margin-left:20px;*/
		}
	#blogEntries{
		}
	.blogEntry{
		border-bottom:1px solid #ccc;
		margin-bottom:15px;
		}
	.blogEntry .name{
		font-size:109%;
		font-weight:900;
		color:#333;
		}
	.blogEntry .contents{
		background-color:navajowhite;
		border:1px dotted #999;
		padding:10px 15px;
		}
	.blogEntry .stats{
		display:inline;
		font-size:11px;
		padding:0px 5px;
		background-color:navajowhite;
		}
	.replies{
		padding:5px 0px 5px 30px;
		/* background-color:aliceblue; */
		}
	.replies .blogEntry{
		border-bottom:none;
		}
	#blogNew{
		}
	.blogCtrls{
		float:right;
		}
	#formLabel{
		margin-top:15px;
		font-size:119%;
		font-weight:400;
		color:#333;
		}
	</style><?php
}




//-----------------------------------------------------------------------

//-----------------------------------------------------------------------
























?>
<div id="blog">
	<div id="blogEntries"><?php
	function blog_thread($parentID='',$options=array()){
		global $blog_thread, $qr, $developerEmail, $fromHdrBugs, $fl, $ln, $MASTER_DATABASE;

		extract($options);
		if(!$blogTable) global $blogTable;
		if(!$blogFields) global $blogFields;
		if(!$sectionFilter) global $sectionFilter;
		if(!$newEntryTitleText)$newEntryTitleText='Add a comment to this article';

		extract($blogFields);
		
		$sql='SELECT ';
		foreach($blogFields as $n=>$v){
			$i++;
			$sql.=$v . ($i<count($blogFields) ? ', ':'');
		}
		$sql.=' FROM '.$blogTable;
		$sql.=' WHERE Active=1 AND ';
		$sql.=$parentIDField.($parentID ? "=$parentID ":' IS NULL ');
		if(count($sectionFilter)){
			foreach($sectionFilter['fields'] as $n=>$v){
				$str=$v . ' = \'';
				if(preg_match('/^eval:/',$sectionFilter['values'][$n])){
					eval('$str.='.str_replace('eval:','',$sectionFilter['values'][$n]).';');
				}else if(preg_match('/^fixed:/',$sectionFilter['values'][$n])){
					$str.=str_replace('fixed:','',$sectionFilter['values'][$n]);
				}else{
					$x=$sectionFilter['values'][$n];
					global $$x;
					$str.=addslashes($$x);
				}
				$str.='\'';
				$a[]=$str;
			}
			$sql.=' AND '.implode(' AND ',$a);
		}
		$sql.=' ORDER BY '.$dateField;
		$blogs=q($sql, O_ARRAY);
		//prn($qr);
		if(count($blogs)){
			if($parentID){
				$tailDiv=true;
				?><div class="replies"><?php
			}else{
				$tailDiv=false;
			}
			foreach($blogs as $blog){
				extract($blog);
				?><div id="blog<?php echo $ID?>" class="blogEntry">
					<?php
					$OwnerContacts_ID=q("SELECT Contacts_ID FROM cms1_articles WHERE ID='".$_REQUEST['ID']."'", O_VALUE);
					if($_SESSION['cnx'][$MASTER_DATABASE]['primaryKeyValue']==$OwnerContacts_ID){
						?><div class="blogCtrls">
						[<a href="index_01_exe.php?mode=deleteBlog&Articles_ID=<?php echo $_REQUEST['ID']?>&ID=<?php echo $$IDField?>" target="w2">delete</a>] 
						<?php
						if(!$parentID){
							?>[<a href="javascript:replyToBlog(<?php echo $$IDField?>);">reply</a>]<?php
						}
						?><br />
						</div><?php
					}
					?>
					<div class="name"><?php echo $$titleField?></div>
					<div class="content"><?php echo preg_match('/<(p|br|div)[^>]*>/i',$$contentField) ? $$contentField : nl2br($$contentField)?></div>
					<div class="stats">Posted <?php echo date('m/d/Y \a\t g:iA',strtotime($$dateField));?></div>
					<span id="replyToBlog<?php echo $$IDField?>">
					
					</span>
					<?php
					blog_thread($$IDField,$options);
					?>
				</div><?php
			}
			if($tailDiv){
				?></div><?php
			}
		}
	}
	blog_thread();
	?></div>
	<a name="lastpost"></a>
	<div id="blogNew">
		<div id="formLabel"><?php echo $newEntryTitleText?></div>
		<form name="form1" action="index_01_exe.php" target="w2" method="post" onsubmit="g('Posts_ID').value=formisat;">
		<table border="0" cellspacing="0" cellpadding="0">
			<tr>
				<td>Your name:</td>
				<td><input name="PostName" type="text" id="PostName" value="<?php echo trim($_SESSION['firstName'] . ' ' . $_SESSION['lastName']);?>" /></td>
			</tr>











			<tr>
				<td>Comment:</td>
				<td><textarea name="Content" cols="45" rows="5" id="PostContent"></textarea></td>
			</tr>
			<?php if($blogRequireCaptcha){ ?>
			<tr>
				<td colspan="100%">
				<?php
				if($blogCaptchaMethod){
					//user defined
					?><?php
				}else{
					//generate the question
					$fields=array('st_capital');
					$attrib=array('capital city');
					$row=q("SELECT st_code, st_name FROM aux_states WHERE st_country='United States' AND st_inception IS NOT NULL ORDER BY RAND() LIMIT 1", O_ROW, $public_cnx);
					$rand=0;
					$field=$fields[$rand];
					$qid=array(
						'st_code'=>$row['st_code'],
						'field'=>$field
					);
					foreach($qid as $n=>$v){
						?><input type="hidden" name="qid[]" value="<?php echo h($v);?>" /><?php
					}
				}
				?>
				<div id="captcha">
				<br />
				<strong>To prevent spamming</strong>:
				What is the <?php echo $attrib[$rand]?> of <?php echo $row['st_name']?>?<br />
				Answer: 
				<input name="cAnswer" type="text" id="cAnswer" size="15" /> 
				[<a title="list of United States" onclick="return ow(this.href,'l1_states','700,700');" href="http://en.wikipedia.org/wiki/U.S._state#List_of_states">click here for answer</a>]
				</div>
				</td>
			</tr>
			<?php }?>
		</table>
		<input name="Posts_ID" type="hidden" id="Posts_ID" value="" />
		<input name="mode" type="hidden" id="mode" value="submitBlog" />
		<input name="ID" type="hidden" id="ID" value="<?php echo $ID?>" />
		<input class="btn1" type="submit" name="Submit" value="Submit Post" />
		<?php if(!$_SESSION['identity']){ ?>
		<input class="btn1" type="button" name="Submit" value="Sign In" onclick="window.location='cgi/login.php?src=<?php echo urlencode('../articles.php?ID='.$_REQUEST['ID'].'#lastpost');?>';" />
		<?php } ?>
		</form>
	</div>
</div>
