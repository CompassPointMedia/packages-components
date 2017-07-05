<?php
/*
2009-01-23 - converted into a component from Kyle Chamber, did most of the abstraction
	* URL's abstracted to default

2008-08-24 - Calendar Widget right inset for Kyle Chamber - table logic from Moscow VFD.
also features a news feature - with some logic for presenting news


*/
//cal
if(!$pageHandles['calendarList'])$pageHandles['calendarcalendarList']='/Event-Calendar.php';
if(!$pageHandles['calendarFocus'])$pageHandles['calendarFocus']='/EventCalendar-Item.php';
if(!$pageHandles['calendarRequestURL'])$pageHandles['calendarRequestURL']='/index_01_exe.php';
//news
if(!$pageHandles['newsList'])$pageHandles['newsList']='/News-Press-List.php';
if(!$pageHandles['newsFocus'])$pageHandles['newsFocus']='/News-and-Press.php';
if(!$pageHandles['calendarFocus'])$pageHandles['calendarFocus']='/Event-Calendar-Item.php';



if(!$day)$day=date('d');
if(!$month)$month=date('m');
if(!$year)$year=date('Y');
//get next and previous objects starting with year
$nextYear=$year+1;
$prevYear=$year-1;
$nextMonth=$month+1; if($nextMonth==13)$nextMonth=1;
$prevMonth=$month-1; if($prevMonth== 0)$prevMonth=12;
$nextDay=$day+1;
if($nextDay>date('t',strtotime("$year-$month-$day")))$nextDay=1;
//previous day not developed!
$prevDay=$day-1;


$actualDay=date('d');
$actualMonth=date('m');
$actualYear=date('Y');

//what day of the week does this month's date start
$dayStartPosition=date('w',strtotime("$year-$month-01 00:00:00"))+1;
$daysInMonth=date('t',strtotime("$year-$month-01 00:00:00"));
$rows= ceil(($dayStartPosition+$daysInMonth-1)/7);
$cells=$rows*7;


if(!$overrideCalNewsCSS){
	?><style type="text/css">
	#cal{
		float:right;
		width:190px;
		padding:0px 5px 15px 20px;
		margin:0px 0px 10px 10px;
		}
	#calNav{
		text-align:center;
		font-weight:400;
		margin-bottom:4px;
		color:#5081A1;
		font-size:109%;
		}
	.cal09{
		border-collapse:collapse;
		border-top:2px solid #5081A1;
	}
	.cal09 th{
		font-weight:900;
		font-size:10px;
		color:darkblue;
		width:18px;
		}
	.cal09 td{
		font-size:10px;
		text-align:center;}
	.cal09 td{
		border:1px solid darkblue;
		}
	#cal a{
		color:#1c4879;
		}
	#cal a:hover{
		text-decoration:underline;
		}
	.hasEvent{
		background-color:#889EB7;
		}				
	#newsHdr, #calHdr{
		background-color:#1c4879;
		padding:5px 0px 2px 12px;
		font-weight:400;
		color:#FFF;
		font-size:104%;
		margin-bottom:7px;
		margin-top:10px;
		}
	#newsWidget{
		padding:0px 0px 0px 12px;
		}
	#calObject{
		padding:0px 0px 7px 12px;
		}
	.dot{
		}
	#cal .today{
		background-color:#e17a40;
		}
	</style><?php
}
?>
<div id="cal">
	<?php if (!$hideNewsSection) { ?>
	<div id="newsHdr">News and Press</div>
	<div id="newsWidget">
		<?php
		$future=date('Y-m-d H:i:s',strtotime('+7 day'));
		$past  =date('Y-m-d H:i:s',strtotime('-45 day'));
		if($a=q("SELECT ID, Title, SubTitle, Description, PostDate FROM cms1_articles WHERE Active=1 AND Private=0 AND Category='Article' AND PostDate BETWEEN '$past' AND '$future' ORDER BY IF(LeadArticle=1,1,2), Priority, PostDate DESC", O_ARRAY)){
			$i=0;
			foreach($a as $v){
				$i++;
				?>
				<span class="dot">&middot;&nbsp;</span><a title="<?php echo h(strip_tags($v['SubTitle'] . (strip_tags($v['Description']) ? ' - '.$v['Description'] : '')))?>" href="<?php echo $pageHandles['newsFocus']?>?ID=<?php echo $v['ID']?>"><?php 
				//need logic on name presentation
				$name=$v['Title'];
				if(strlen($name)>$colWidthEst && false){
					/* unfinished */
					$b=explode(' ',$name);
					$j=0;
					foreach($b as $o=>$w){
						$j++;
						$nameLength+=1+strlen($w);

					}
				}else{
					echo $v['Title'];
				}
				?></a><br />
				<?php
				if($i>2)break;
			}
			if(count($a)>2){
				?>[<a title="View entire list of current and archived news items" href="<?php echo $pageHandles['newsList']?>">All news items</a>]<br />
				<?php
			}
		}
		?>
	
	</div>
    <? } ?>
    
	<div id="calHdr">Quick Calendar</div>
	<div id="calWidget">
		<div id="calNav">
			<a href="#" onClick="window.open('<?php echo $pageHandles['calendarRequestURL']?>?mode=navMonthEventCalendar&year=<?php echo $month==1?$prevYear:$year?>&month=<?php echo $prevMonth?>','w2');return false;" title="View previous Month"><img src="/images/i/left_blue_triangle.jpg" alt="previous month" width="8" height="9" border="0" /></a> 
			<span style="padding:0px 8px;">
				<?php echo date('F',strtotime($year.'-'.str_pad($month,2,'0',STR_PAD_LEFT).'-01'))?> <?php echo $year?>			</span>
			<a href="#" onClick="window.open('<?php echo $pageHandles['calendarRequestURL']?><?php echo $pageHandles['calendarRequestURL']?>?mode=navMonthEventCalendar&year=<?php echo $month==12?$nextYear:$year?>&month=<?php echo $nextMonth?>','w2');return false;" title="View next nonth"><img src="/images/i/right_blue_triangle.jpg" alt="next month" width="8" height="9" border="0" /></a>		</div>
		<div id="calObject">
		<table border="1" class="cal09" cellpadding="2" cellspacing="0">
		<?php
		$i=0;$j=0;
		for($i=1;$i<=$cells;$i++){
			if(! (($i-1)%7)){
				if($i>6)echo "</tr>";
				if(!$calHeaderPrinted){
					$calHeaderPrinted=true;
					?><thead><tr>
					<th>Su</th>
					<th>Mo</th>
					<th>Tu</th>
					<th>We</th>
					<th>Th</th>
					<th>Fr</th>
					<th>Sa</th>
					</tr>
					<tr></thead><?php
				}
			}
			$thisDay=$i-$dayStartPosition+1;
			$thisDay<1 || $thisDay>date('t',strtotime("$year-$month-$day 00:00:00"))?$thisDay=NULL:'';
	
			//Event cell here =============================================
			?><td valign="top" <?php
			//handle styles such as background color
			$date="$year-".str_pad($month,2,'0',STR_PAD_LEFT).'-'.str_pad($thisDay,2,'0',STR_PAD_LEFT);
			echo 'class="';
			echo (date('Ymd')==str_replace('-','',$date)?'today ':'');
			if($events=q("SELECT * FROM
			cal_events WHERE
			Active=1 AND
			(
			(StartDate='$date' AND EndDate='0000-00-00') OR (StartDate<='$date' AND EndDate >='$date' AND EndDate!='0000-00-00')
			)", O_ARRAY)){
				echo 'hasEvent';
				foreach($events as $n=>$v)unset($events[$n]['Description']);
				$qrs[]=array_merge($qr,$events);
			}
			echo '"';
			?>><?php
			if(is_null($thisDay)){
				echo '&nbsp;';
			}else{
				if(false /* =q("SELECT ev_name FROM cal_events WHERE ev_day=$thisDay AND ev_year=$year AND ev_month=$month",O_VALUE)*/){
					
					?><?php
				}
				if(count($events)){
					?><a title="Click this date for a quick list of events (<?php echo count($events)?> event<?php echo count($events)>1?'s':''?>)" href="#" onclick="window.open('<?php echo $pageHandles['calendarRequestURL']?>?mode=fetchEventsEventCalendar&year=<?php echo $year?>&month=<?php echo $month?>&day=<?php echo $thisDay?>','w2');return false;"><?php
				}
				?><?php echo $thisDay?><?php
				if(count($events)){
					?></a><?php
				}
			}
			?></td><?php //==================================================
			
			//close header row
			
		
		}
		echo "</tr>";
		?>
		</table>
		</div>
	</div>
	<div id="calEventList">
		<?php
		if($eventDate && $events=q("SELECT * FROM
			cal_events WHERE
			Active=1 AND
			(
			(StartDate='$eventDate' AND EndDate='0000-00-00') OR (StartDate<='$eventDate' AND EndDate >='$eventDate' AND EndDate!='0000-00-00')
			) ORDER BY StartDate", O_ARRAY)){
			foreach($events as $v){
				?>
				<strong><?php echo date('M j',strtotime($v['StartDate']))?>
				<?php 
				if($v['StartDate']!==$v['EndDate'] && $v['EndDate']!=='0000-00-00'){
					echo ' - ';
					echo date('M j',strtotime($v['EndDate']));
					echo '<br />';
				}
				?></strong>&nbsp;&nbsp;<a title="Click to view this event" href="/<?php echo $pageHandles['calendarFocus']?>?Events_ID=<?php echo $v['ID']?>">
				<?php echo $v['Name'];?>
				</a><br>
				<?php
			}
		}else if($events=q("SELECT * FROM
			cal_events WHERE
			Active=1 AND
			(
			(StartDate='".date('Y-m-d')."' AND EndDate='0000-00-00') OR (StartDate<='".date('Y-m-d')."' AND EndDate >='".date('Y-m-d')."' AND EndDate!='0000-00-00')
			) ORDER BY StartDate", O_ARRAY)){
			?>TODAY:<br><?php
			foreach($events as $v){
				?>
				<a title="Click to view this event" href="/<?php echo $pageHandles['calendarFocus']?>?Events_ID=<?php echo $v['ID']?>">
				<?php echo $v['Name'];?>
				</a><br>
				<?php
			}
		}else if($thispage!==trim($pageHandles['calendarList'],'/') && $month && $events=q("SELECT * FROM cal_events WHERE
			Active=1 AND (
			StartDate>='$year-$month-01' AND StartDate <='$year-$month-".date('t',strtotime("$year-$month-01"))."'
			) ORDER BY StartDate ASC", O_ARRAY)){
			?>
			EVENTS THIS MONTH:<br />
			<?php
			$i=0;
			foreach($events as $v){
				$i++;
				?>
				.<?php echo substr($v['StartDate'],-2);?>&nbsp;<a title="Click to view this event" href="/<?php echo $pageHandles['calendarFocus']?>?Events_ID=<?php echo $v['ID']?>">
				<?php echo $v['Name'];?>
				</a><br>
				<?php
				if($i>5){
					?>
				<a title="Click here to view complete calendar" href="<?php echo $pageHandles['calendarList']?>">see calendar page..</a>
				<?php
					break;
				}
			}
			?>
			<br>
			<br><?php
		}
		?>
	</div>
</div>
