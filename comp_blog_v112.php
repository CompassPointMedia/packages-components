<?php 
/*
blog piece - started 2009-05-11; copied over from NCAAB where it was very specific.  a/o today it's being used for LACE (cpm050) and is still pretty specific - the parameter Articles_ID:ID is still hard-coded in, and we can't really tie this into cmsb_sections which I'd really want

todo:
-----
delete a post also and cascade
anonymous@123.75.48.100
[ ] <- profile picture + gender shape if known and allowed
text management
	convert_links() and add nofollow attribute
	convert email addresses
	
is this blog open to comments controlled by global VAR in the article referred to
better protocol on what the "parent object" is
ability to "claim my posts" based on my email and IP Address - provided I verify my email


version 1.12 - 2011-06-08
* forked off just to make any mods needed for Kyle Fire articles (first using this)

update 2011-04-01:
1. posted BY: this is not present
2. css is weak and needs improved
3. FB-style mouseover my posts = button to delete mine (which will cascade delete children)
on submission of blog we need to make sure the article ALLOWS for a submission to itself
4. .. and that the parent blog id is in this stream..
5. filter for profanity and etc.
6. 

*/
if(!$blogFocusPage)$blogFocusPage='/Kyle-Fire-Department-articles.php';
if(!$blogKeyField)$blogKeyField='Articles_ID';
if(!$blogReferenceTable)$blogReferenceTable='cms1_articles';
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
		'Objects_ID',
		'ObjectName',
	),
	'values'=>array(
		'eval:$GLOBALS[\''.$blogKeyField.'\']',
		'fixed:'.$blogReferenceTable,
	)
);
if(!$newEntryTitleText)$newEntryTitleText='Add a Comment..';
if(!$blogCommentRequiresLogin)$blogCommentRequiresLogin=true;
if($blogLoginFunction){
	$blogLoggedIn=$$blogLoginFunction();
}else{
	$blogLoggedIn=$_SESSION['cnx'][$cnxKey]['identity'];
}

if($mode=='submitBlog'){
	if($key){
		if($_SESSION['special']['blogQueue'][$key]){
			extract($_SESSION['special']['blogQueue'][$key]);
		}else{
			//silently do nothing
			$abortEntry=true;
		}
	}else{
		//error checking
		if(!$PostName)error_alert('Enter your name');
		if(isset($Email) && !valid_email($Email))error_alert('Enter a valid email');
		if(!strlen(trim($Content)))error_alert('Enter a comment');

		if(!$blogLoggedIn && $blogCommentRequiresLogin){
			//save post in session
			$key=md5(rand(1,1000000).time());
			$_SESSION['special']['blogQueue'][$key]=$_POST;
			
			require($_SERVER['DOCUMENT_ROOT'].'/cgi/usemod_settings_messages.php');
			?><script language="javascript" type="text/javascript"><?php
			if(!$message['blogsigninfirst']){
				mail($developerEmail, 'Notice file '.__FILE__.', line '.__LINE__,get_globals($msg='The cgi for this site has no message var "blogSignInFirst".  Please create it as the user is getting a standard alert() in its place'),$fromHdrBugs);
				?>if(confirm('To post a comment you must sign in or create an account.  Would you like to do so now?'))<?php
			}
			?>window.parent.location='/cgi/login.php?messageCode[loginFormHeader]=blogsigninfirst&src=<?php echo urlencode($blogFocusPage.'?Articles_ID='.$$blogKeyField.'&key='.$key.'&mode=submitBlog');?>';
			</script><?php
			$assumeErrorState=false;
			exit;
		}
	}
	
	if(!$abortEntry){
		//entry
		ob_start();
		$Blogs_ID=q("INSERT INTO addr_contacts_posts SET
		".($_SESSION['cnx'][$cnxKey]['primaryKeyValue'] ? "Contacts_ID=".$_SESSION['cnx'][$cnxKey]['primaryKeyValue']."," : '')."
		Objects_ID='".$$blogKeyField."',
		ObjectName='$blogReferenceTable',
		".($Posts_ID ? "Posts_ID='$Posts_ID', " : '')."
		PostDate=NOW(),
		IPAddress='".$_SERVER['REMOTE_ADDR']."',
		Name='".($_SESSION['cnx'][$cnxKey]['primaryKeyValue'] ? '' : $PostName)."',
		Content='$Content',
		Category='$Category'", O_INSERTID, ERR_ECHO);
		$err=ob_get_contents();
		ob_end_clean();
		if($err){
			mail($developerEmail, 'Error file '.__FILE__.', line '.__LINE__,get_globals($err),$fromHdrBugs);
			error_alert('Unable to post your comment, site developer has been notified',1);
		}

		//destroy session
		unset($_SESSION['special']['blogQueue'][$key]);
	}
}else if($mode=='deleteBlog'){
	
}

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
?>
<div id="blog">
	<div id="blogEntries"><?php
	function blog_thread($parentID='',$options=array()){
		global $blog_thread, $qr, $developerEmail, $fromHdrBugs, $fl, $ln, $MASTER_DATABASE, $blogTable, $blogReferenceTable;

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
				?><div id="blog<?php echo $$blogKeyField?>" class="blogEntry">
					<?php
					$OwnerContacts_ID=q("SELECT Contacts_ID FROM $blogReferenceTable WHERE ID='".$GLOBALS[$blogKeyField]."'", O_VALUE);
					?><div class="blogCtrls"><?php
					if($_SESSION['cnx'][$acct]['primaryKeyValue']==$OwnerContacts_ID){
						?>
						[<a href="index_01_exe.php?mode=deleteBlog&Articles_ID=<?php echo $GLOBALS[$blogKeyField]?>&ID=<?php echo $$IDField?>" target="w2">delete</a>] 
						<?php
					}
					if(!$parentID){
						?>[<a href="javascript:replyToBlog(<?php echo $$IDField?>);">reply</a>]<?php
					}
					?></div><?php
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
			<?php
			if(!$blogLoggedIn){
				?><tr>
				<td>Your email:</td>
				<td><input name="Email" type="text" id="Email" value="" /></td>
				</tr>
				<tr>
				<td colspan="100%"><em style="color:#666;">Your email will never be shown publicly</em></td>
				</tr><?php
			}
			?>
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
		<input name="<?php echo $blogKeyField?>" type="hidden" id="<?php echo $blogKeyField?>" value="<?php echo $$blogKeyField?>" />
		<input class="btn1" type="submit" name="Submit" value="Submit Post" />
		<?php if(!$_SESSION['identity']){ ?>
		<input class="btn1" type="button" name="Submit" value="Sign In" onclick="window.location='/cgi/login.php?src=<?php echo urlencode($blogFocusPage.'?'.$blogKeyField.'='.$$blogKeyField.'#lastpost');?>';" />
		<?php } ?>
		</form>
	</div>
</div>
