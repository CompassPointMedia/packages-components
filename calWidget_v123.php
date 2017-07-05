<?php
$componentVersions['cal']=1.21;
/*
2010-10-06: we need to be able to pull from something besides cal_cal
* calCustomEventsQuery = string; allows events to be pulled from a custom function; $date is the only parameter passed.  Needs to return a non-associative array

version 1.21
------------ 
- forked this off to start doing cool stuff with calendars
* 2010-09-19 introduced componentVersions as an array similar to functionVersions
* added	$calDayShowRange=7 by default
* added $calDayOffset=0 by default; setting to 1 moves first day shown on left to Monday
* added $calWeekDayLabels=0-based array of labels for the days (could be Sunday-Saturday, or Su-Sa for example)

cal widget 1.2 b003
-------------------
2010-03-12
* now the only call to date() function without a 2nd parameter is in setting $systemDate, and if $systemDate is already passed we use that instead - now we can make "today" be any day we want

cal widget 1.0 b002
-------------------
2009-08-24
* changed css logic, stored as a variable
* separated out colors into separate classes for the headers, border colors etc

cal widget 1.0 [development]
----------------------------
The cal widget presents a calendar grid (calWidget proper with both calNav and calGrid) and a calendar event listing (calEventListing).  The calEventListing can be slaved to the cal widget.  Wherever an event is listed, it leads to the site's event focus page (or perhaps an external URL in some cases).

The default table for the calendar is cal_events

News and events is a stepchild and was originally used with Kyle Chamber and may be excluded from this widget in the future

Todo:
-----
* standard simple algorithm for when field structure says clicking an event opens an external URL vs. the pageHandles.calendarFocus page
	
2009-04-17
----------
added variable calCalGrid
added calCustomReplaceRegions for navigating via javascript
	
2009-02-25
----------
added calDayModulus to handle permanently repeating shifts (you could also use this to gray out the weekends using mod7), clases declared for the grid td a mod1, mod2, .. modM

2009-02-15
----------
* removed $adminMode as the permissions driver, instead use the all-or-nothing (so far) $calAccessToken
2009-02-07
----------
	* NOTE: calGridDisplayFunction, if declared, overrides both usages below (see cal_grid_display() sample page bottom)
	* gridDayUsage = load (default) | focus | none
		load [legacy from Kyle Chamber] means gridDay is a link to load the events of that day in the calEventListing pane
		focus (not developed) will mean a link to a day focus mode (as a more.. link below event snippets might also)
	* gridEventUsage = focus (default) | none

	* control modes are:
		navMonthEventCalendar - load a new calendar - need to pass month and year.  this will load the new month call and that month's events in the calEventListing pane
		fetchEventsEventCalendar - [legacy from Kyle Chamber] - this is when the calendar is in 
		
2009-02-03
----------
	* finished abstraction including variable saving and hiding certain parts
2009-01-23 - converted into a component from Kyle Chamber, did most of the abstraction
	* URL's abstracted to default

2008-08-24 - Calendar Widget right inset for Kyle Chamber - table logic from Moscow VFD.
also features a news feature - with some logic for presenting news



CSS structure and buffered variables
------------------------------------
#cal
	#newsSection					$calNewsSection
		#newsHeader
		#newsListing				
			.newsItem
				//multiple
	#calSection						$calCalSection
		#calHeader
		#calWidget
			#calNav					$calCalNav
			#calGrid
				.gridDay
				.gridEvents
					.gridEvent
		#calEventListing			$calCalEventListing
			.calEventHeader
			.calEvent
			.calEventAll
*/


//1.20 improvement: allows us to make "today" be any day
if($systemDate && ($n=strtotime($systemDate))!=-1 && ($n=strtotime($systemDate))!=false){
	$systemDate=date('Y-m-d',$n);
}else{
	$systemDate=date('Y-m-d');
}
$systemDateQbks=date('m/d/Y',strtotime($systemDate));

//news
if(!$pageHandles['newsList'])$pageHandles['newsList']='/News-Press-List.php';
if(!$pageHandles['newsFocus'])$pageHandles['newsFocus']='/News-and-Press.php';
if(!isset($newsHeaderText))$newsHeaderText='News and Press';
if(!isset($maxNewsItemListings))$maxNewsItemListings=4;

//cal
if(!$pageHandles['calendarList'])$pageHandles['calendarList']='/Event-Calendar.php';
if(!$pageHandles['calendarFocus'])$pageHandles['calendarFocus']='/Event-Calendar-Item.php';
if(!$pageHandles['calRequestURL'])$pageHandles['calRequestURL']='/index_01_exe.php';

//images
if(!$calNavLeftImage)$calNavLeftImage='/images/i/arrows/2_white_left.png';
if(!$calNavRightImage)$calNavRightImage='/images/i/arrows/2_white_right.png';

if(!isset($calAccessToken))$calAccessToken=$adminMode; //adminMode is always explicitly declared true or false
if(!isset($calWidget1Rewrite))$calWidget1Rewrite=false;
if(!isset($maxCalItemListings))$maxCalItemListings=5;
if(!isset($calOverrideCalNewsCSS))$calOverrideCalNewsCSS=false;
if(!isset($calHeaderText))$calHeaderText='Calendar';
if(!isset($calForceEventAllLink))$calForceEventAllLink=false;
if(!isset($showCalEventHeaderOnEmpty))$showCalEventHeaderOnEmpty=true;
if(!$calEventWhereClause){
	$calEventWhereClause='1 AND ';
}else{
	$calEventWhereClause=preg_replace('/\s+AND$/i','',$calEventWhereClause).' AND ';
}
if(!isset($calEventFunction))$calEventFunction='event_write'; //puts the write of the event listing into a user-defined function
if(!isset($hideEventsAllLink))$hideEventsAllLink=false;
if(!isset($calEventAllLinkText))$calEventAllLinkText='See calendar page..';
if(!isset($calEventNoEventsPresent))$calEventNoEventsPresent='';

if(!isset($calSelfRefresh))$calSelfRefresh=true;
if(!isset($calHideGrideEvents))$calHideGrideEvents=false;

if(!isset($calPreventPastNavigation))$calPreventPastNavigation=true;
if(!$calPreventFutureNavigation)$calPreventFutureNavigation=''; //format should be 201209 i.e. YYYYMM - default unlimited future navigation

if(!$calDayShowRange)$calDayShowRange=7; //how many days to show
if(!$calDayOffset)$calDayOffset=0; //which day to start on (default of 0=sunday)
if(!$calWeekDayLabels) $calWeekDayLabels=array('Sun', 'Mon', 'Tues', 'Wed', 'Thurs', 'Fri', 'Sat');

if(!isset($hideNewsSection))$hideNewsSection=true;

//$calGridDisplayFunction($thisDay, $events) = cal_grid_display - can be used if desired


if(!isset($gridDayUsage))$gridDayUsage='load';
if(!isset($gridEventUsage))$gridEventUsage='focus';

if($calGetRange && !isset($past))  $past  =date('Y-m-d H:i:s',strtotime(trim($systemDateQbks.'  -7 day')));
if($calGetRange && !isset($future))$future=date('Y-m-d H:i:s',strtotime(trim($systemDateQbks.' +45 day')));

$calRegisteredFunctions=array(
	'simpleBlockPanthers'=>true
);

//functions needed for this component
if(!function_exists('get_contents')){
	$functionVersions['get_contents']=.01;
	function get_contents(){
		/* 2008-06-30 - for handling output buffering 
		2009-11-29 - made an "official" function in a_f; it was in 5 files.  Only in comp_tabs v2.00 (+?) the end logic is NOT if(beginnextbuffer) then ob_start() ELSE return gcontents.out - instead the logic is if(beginnextbuffer) then ob_start(); return gcontents.out PERIOD
		HOWEVER, beginnextbuffer is never flagged in comp_tabs so I have no fear of back-compat problems
		this function will return output and can optionally start the next buffer.
		GOTCHA! since this is a function, we must ob_start() before we return the contents.  Therefore, if you store the value returned as a variable, thats great, but if you wish to echo it, you are already in the next buffer.  So you cannot do a rewrite as done in cal widget and etc.
		*/
		$cmds=array('striptabs','beginnextbuffer','trim');
		global $gcontents;
		unset($gcontents);
		if($a=func_get_args()){
			foreach($a as $v){
				if(in_array(strtolower($v),$cmds)){
					$v=strtolower($v);
					$$v=true;
				}
			}
		}
		$gcontents['out']=ob_get_contents();
		if($trim)$gcontents['out']=trim($gcontents['out'])."\n";
		ob_end_clean();
		if($striptabs)$gcontents['out']=str_replace("\t",'',$gcontents['out']);
		if($beginnextbuffer){
			ob_start();
		}else{
			return $gcontents['out'];
		}
	}
}
if(!function_exists('event_write')){
	function event_write($v){
		global $pageHandles,$calAccessToken,$qr,$fl,$ln,$developerEmail,$fromHdrBugs;
		extract($v);
		//date
		?><div class="itemHdr"><?php
		if($EndDate!='0000-00-00'){
			echo date('M jS',strtotime($StartDate)). ($StartDate!=$EndDate ? ' - '.date('M jS',strtotime($EndDate)) : '');
		}else{
			echo date('l, M jS',strtotime($StartDate));
		}
		if($StartTime!='00:00:00'){
			echo '<br />';
			?><span class="time"><?php
			if($EndTime!='00:00:00'){
				echo date('g:i a',strtotime($StartTime)) . ' to '.date('g:i a',strtotime($EndTime));
			}else{
				echo '@ '.date('g:i a',strtotime($StartTime));
			}
			?></span><?php
		}
		?></div>
		<div class="itemName"><?php
		if($calAccessToken){
			?><a title="Edit this event" href="/console/events.php?Events_ID=<?php echo $ID?>" onclick="return ow(this.href,'l1_events','700,700');"><img src="/images/i/plusminus-plus.gif" width="11" height="11" alt="edit" /></a>
			&nbsp;<?php
		}
		?><a title="See details about this event" href="<?php echo $pageHandles['calendarFocus'];?>?Events_ID=<?php echo $ID?>"><?php echo $Name?></a></div>
		<?php
		if($BriefDescription){
			?><div class="itemDesc"><?php echo $BriefDescription;?></div><?php
		}
		if($ContactName || $ContactEmail || $ContactPhone){
			?><div class="contactInfo">
			<strong>Contact:</strong> <?php
			echo $ContactName;
			if($ContactPhone){
				echo ' '.$ContactPhone;
			}
			if($ContactEmail){
				if($ContactPhone)echo '<br />';
				//encrypt email
				js_email_encryptor($ContactEmail);
			}
			?></div><?php
		}
		if($URL){
			?><div class="url"><a title="Website link" href="<?php echo $URL?>"><?php echo $URL?></a></div><?php
		}
	}
}
if(!function_exists('cal_grid_display')){
	function cal_grid_display($day, $events, $options=array()){
		global $pageHandles, $calAccessToken, $year, $month, $qr, $qx, $fl, $ln, $developerEmail, $fromHdrBugs, $Cal_ID;
		?>
		<div class="gridDay"><?php 
		if($calAccessToken){
			?><a href="/console/events.php?Cal_ID=<?php echo $Cal_ID?$Cal_ID:1?>&StartDate=<?php echo $year.'-'.$month.'-'.str_pad($day,2,'0',STR_PAD_LEFT);?>" title="Add a new event for this day" onclick="return ow(this.href,'l1_events','700,700',true);"><img src="/images/i/plusminus-plus.gif" width="11" height="11" alt="new event" /></a> <?php
		}
		echo $day?></div>
		<div class="gridEvents"><?php
		if(count($events))
		foreach($events as $n=>$v){
			?><div class="gridEvent"><a class="gridEventLink" title="<?php echo h($v['Description'])?>" href="<?php echo $pageHandles['calendarFocus']?>?Events_ID=<?php echo $v['ID']?>"><?php
			//handle text length eventually
			echo $v['Name'];
			?></a></div><?php
		}
		?></div>
		<?php
	}
}


if(!$day)$day=date('d',strtotime($systemDate));
if(!$month)$month=date('m',strtotime($systemDate));
if(!$year)$year=date('Y',strtotime($systemDate));
//get next and previous objects starting with year
$nextYear=$year+1;
$prevYear=$year-1;
$nextMonth=$month+1; if($nextMonth==13)$nextMonth=1;
$prevMonth=$month-1; if($prevMonth== 0)$prevMonth=12;
$nextDay=$day+1;
if($nextDay>date('t',strtotime("$year-$month-$day")))$nextDay=1;
//previous day not developed!
$prevDay=$day-1;


//what day of the week does this month's date start
$dayStartPosition=date('w',strtotime("$year-$month-01 00:00:00"))+1;
$daysInMonth=date('t',strtotime("$year-$month-01 00:00:00"));
$rows= ceil(($dayStartPosition+$daysInMonth-1)/7);
$cells=$rows*7;

if($calWidget1Rewrite) ob_start();

if(!$refreshComponentOnly){
	ob_start();
	?><style type="text/css">
	/* NOTE: you can override this calendar/news CSS by setting $calOverrideCalNewsCSS=true before the widget */
	#cal{
		}
	#calHeader{
		display:none;
		}
	#calNav{
		}
	.cal10{
		border-collapse:collapse;
		width:100%;
	}
	.cal10 th{
		padding:6px 0px 2px 6px;
		font-size:115%;
		font-weight:400;
		/* font-family:Georgia, "Times New Roman", Times, serif; */
		}
	.cal10 td{
		padding:2px 5px;
		}
	.gridSquare{
		width:14%;
		height:85px;
		}
	.gridDay{
		float:right;
		font-size:11px;
		}
	.cnclr{
		font-size:139%;
		font-family:Georgia, "Times New Roman", Times, serif;
		}
	#cal .unavailable{
		filter:alpha(opacity=40);
		-moz-opacity:.40;
		opacity:.40;
		}
	#cal a:hover{
		}
	#calHeader{
		padding:5px 0px 2px 12px;
		font-weight:400;
		font-size:104%;
		margin-bottom:7px;
		margin-top:10px;
		}
	#calGrid{
		}

	#newsHeader{
		padding:5px 0px 2px 12px;
		font-weight:400;
		font-size:104%;
		margin-bottom:7px;
		margin-top:10px;
		}
	#newsListing{
		}
	.dot{
		}
	.monthYear{
		}
	/* ---- borders, colors and background colors ------- */
	#cal a{
		}
	.cnclr{
		}
	.nhclr, .chclr{
		color:#FFF;
		background-color:#1c4879;
		}
	.cal10{
		}
	.cal10 th{
		background-color:tan;
		border-bottom:1px solid #000;
		}
	.cal10 tbody tr{
		border-left:1px solid #000;
		border-right:1px solid #000;
		}
	.cal10 td{
		border:1px dotted #777;
		}
	.cal10 .bottom td{
		border-bottom:1px solid #000;
		}
	.hasEvent{
		}
	.today{
		background-color:darksalmon;
		}
	.noday{
		background-color:#ccc;
		cursor:auto;
		}			
	</style><?php
	$calDefaultCalNewsCSS=get_contents();
	if(!$calOverrideCalNewsCSS)echo $calDefaultCalNewsCSS;
	?><script language="javascript" type="text/javascript">
	if(typeof calNav=='undefined'){
		function calNav(o,y,m){
			if(o.parentNode.className.match(/unavailable/gi)){
				alert('You cannot go here on the calendar');
				return false;
			}
			return true;
		}
	}
	</script><?php
}
?><div id="cal"><?php
	//#1: news section - display a list of news articles vertically
	if(!$hideNewsSection){ 
		ob_start();
		?><div id="newsSection">
			<div id="newsHeader" class="nhclr"><?php echo $newsHeaderText?></div>
			<div id="newsListing"><?php
				if($a=q("SELECT ID, Title, SubTitle, Description, PostDate FROM cms1_articles 
					WHERE Active=1 AND Private=0 AND Category='Article' AND PostDate BETWEEN '$past' AND '$future' 
					ORDER BY IF(LeadArticle=1,1,2), Priority, PostDate DESC", O_ARRAY)){
					$i=0;
					foreach($a as $v){
						//--------------------- indiv. news item -------------------------
						$i++;
						?><div id="newsItem<?php echo $i?>" class="newsItem">
						<span class="dot">&middot;&nbsp;</span>
						<a title="<?php echo h(strip_tags($v['SubTitle'] . (strip_tags($v['Description']) ? ' - '.$v['Description'] : '')))?>" href="<?php echo $pageHandles['newsFocus']?>?ID=<?php echo $v['ID']?>"><?php 
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
						?></a>
						</div><?php
						//--------------------- end news item -------------------------
						if($i>$maxNewsItemListings)break;
					}
					if(count($a)>$maxNewsItemListings){
						?>[<a title="View entire list of current and archived news items" href="<?php echo $pageHandles['newsList']?>">All news items</a>]<br />
						<?php
					}
				}else{
					//no events
				}
				?>
			</div>
		</div><?php
		echo $calNewsSection=get_contents();
	}
	//#2: calendar section
	if(!$hideCalSection){
		ob_start();
		?><div id="calSection"><?php
			//header - "Calendar" for example
			?><div id="calHeader" class="chclr"><?php echo $calHeaderText?></div><?php

			//calendar layout
			if(!$hideCalWidget){
				?><div id="calWidget">
					<?php
					ob_start();
					?>
					<div id="calNav" class="cnclr">
					<span id="prevMonth" class="<?php if($calPreventPastNavigation && ($year.str_pad($month,2,'0',STR_PAD_LEFT) <= date('Ym')))echo 'unavailable';?>"><a href="<?php echo $pageHandles['calRequestURL']?>?mode=navMonthEventCalendar&Cal_ID=<?php echo $Cal_ID?$Cal_ID:1?>&year=<?php echo $month==1?$prevYear:$year?>&month=<?php echo $prevMonth?><?php echo $calNavQueryStringParams?>" title="View previous Month" target="w2" onclick="return calNav(this,<?php echo $month==1?$prevYear:$year?>,<?php echo $prevMonth?>);"><img src="<?php echo $calNavLeftImage?>" alt="previous month" align="absbottom" /></a>
					</span>
					<span class="monthYear">
					<?php echo date('F',strtotime($year.'-'.str_pad($month,2,'0',STR_PAD_LEFT).'-01'))?> <?php echo $year?>
					</span>
					<span id="nextMonth" class="<?php if($calPreventFutureNavigation > 0 && $calPreventFutureNavigation<= $year.str_pad($month,2,'0',STR_PAD_LEFT))echo 'unavailable';?>"><a href="<?php echo $pageHandles['calRequestURL']?>?mode=navMonthEventCalendar&Cal_ID=<?php echo $Cal_ID?$Cal_ID:1?>&year=<?php echo $month==12?$nextYear:$year?>&month=<?php echo $nextMonth?><?php echo $calNavQueryStringParams?>" title="View next nonth" target="w2" onclick="return calNav(this,<?php echo $month==12?$nextYear:$year?>,<?php echo $nextMonth?>);"><img src="<?php echo $calNavRightImage?>" alt="next month" align="absbottom" /></a>
					</span>
					</div>
					<?php
					echo $calCalNav=get_contents();
					ob_start();
					?>
					<div id="calGrid"><table class="cal10" cellpadding="0" cellspacing="0"><?php
					$i=0;
					for($i=1; $i<=$cells; $i++){
						if(!(($i-1)%7)){
							if($i>6){
								$j++;
								echo '</tr>';
								echo '<tr row="'.$j.'"'.($cells-$i<=7 ? 'class="bottom"':'').'>';
							}
							if(!$calHeaderPrinted){
								$calHeaderPrinted=true;
								?><thead class="calDays"><tr>
									<?php
									for($k=1; $k<=$calDayShowRange-($calDayOffset-1); $k++){
										$idx= fmod($k+$calDayOffset - 1, 7);
										?><th><?php echo $calWeekDayLabels[$idx]?></th><?php
									}
									?>
								</tr>
								</thead><tr><?php
							}
						}
						$thisDay=$i-$dayStartPosition+1;
						$thisDay<1 || $thisDay>date('t',strtotime("$year-$month-$day 00:00:00"))?$thisDay=NULL:'';
				
						//Event cell here =============================================
	
						//handle styles such as background color
						$date="$year-".str_pad($month,2,'0',STR_PAD_LEFT).'-'.str_pad($thisDay,2,'0',STR_PAD_LEFT);
						$class='class="gridSquare';
						$title='title="';
						
						if(is_null($thisDay))$class.=' noday';
						if($thisDay && $calDayModulus){
							if(!$modulusSet){
								if(!$calDayModulusStart)$calDayModulusStart='01-01-'.date('Y',strtotime($systemDate));
								$modulusSet=floor(strtotime($calDayModulusStart)/(3600*24));
							}
							$modulus=floor(strtotime($date)/(3600*24));
							$modulus=$modulus-$modulusSet;
							$modulus=fmod($modulus,$calDayModulus);
							if($modulus<0)$modulus=$calDayModulus+$modulus;
							$modulus+=1;
							$class.=' mod'.$modulus;
						}
						$class.= (date('Ymd',strtotime($systemDate))==str_replace('-','',$date)?' today':'');
						if($calCustomEventsQuery){
							//handled in the function
							$events=$calCustomEventsQuery($date);
						}else if($events=q("SELECT * FROM
							cal_events WHERE $calEventWhereClause
							Active=1 AND
							(
							(StartDate='$date' AND (!EndDate OR EndDate IS NULL)) OR (StartDate<='$date' AND EndDate >='$date')
							)", O_ARRAY)){
							$class.= ' hasEvent';
							foreach($events as $n=>$v)unset($events[$n]['Description']);
							$qrs[]=array_merge($qr,$events);
						}
						$class.='"';
						$title.='"';
						//output the cell..
						if(
							/* before a left offset */
							($i-1)%7 < $calDayOffset || 
							/* after range of days */
							$calDayShowRange < ($i-1)%7
						)continue;

						if($calGridDisplayFunction){
							//handle both gridDay and gridEvents; must take $thisDay and $events as the first two parameters passed
							$calGridDisplayFunction($thisDay, $events);
						}else{
							?><td <?php echo !is_null($thisDay)?'id="day'.$thisDay.'" ':''?> <?php echo $class?> <?php echo $title?>><?php
							if(is_null($thisDay)){
								//we are in grid but outside day range
								echo '&nbsp;';
							}else{
								//------------------ gridDay and usage ------------------
								if($calAccessToken){
									?><a href="/console/events.php?StartDate=<?php echo $year.'-'.$month.'-'.str_pad($thisDay,2,'0',STR_PAD_LEFT);?>" title="Add a new event for this day" onclick="return ow(this.href,'l1_events','700,700',true);"><img src="/images/i/plusminus-plus.gif" width="11" height="11" alt="new event" /></a> <?php
								}
								if($gridDayUsage=='load' && count($events)){
									?><a title="Click this date for a quick list of events (<?php echo count($events)?> event<?php echo count($events)>1?'s':''?>)" href="#" onclick="window.open('<?php echo $pageHandles['calRequestURL']?>?mode=fetchEventsEventCalendar&year=<?php echo $year?>&month=<?php echo $month?>&day=<?php echo $thisDay?>','w2');return false;"><?php
								}
								?><div class="gridDay"><?php echo $thisDay?></div><?php
								if($gridDayUsage=='load' && count($events)){
									?></a><?php
								}
								//------------------- events and usage --------------------
								if($gridEventUsage=='focus'){
									?><div class="gridEvents"><?php
									if(count($events) && !$calHideGrideEvents){
										foreach($events as $n=>$v){
											?><div class="gridEvent<?php if($v['Cal_ID'])echo ' fromCal'.$v['Cal_ID']?>"><?php
											if($gridEventUsage=='focus'){
												?><a class="gridEventLink" title="<?php echo h($v['Description'])?>" href="<?php echo $pageHandles['calendarFocus']?>?Events_ID=<?php echo $v['ID']?>"><?php
											}
											//handle text length eventually
											if($function=$calGridEventOutputFunction && $direction=$calRegisteredFunctions[$calGridEventOutputFunction]){
												if(is_bool($direction)){
													echo $calGridEventOutputFunction($v);
												}else{
													//undeveloped; more complex call
												}
											}else{
												echo $v['Name'];
											}
											if($gridEventUsage=='focus'){
												?></a><?php
											}
											?></div><?php
										}
									}
									?></div><?php
								}
							}
							?></td><?php 
						}
					}
					?></tr></table></div>
					<?php
					echo $calCalGrid=get_contents();
					?>
					
				</div><?php
				unset($events);
			}
			// c. calendar events list below calendar layout
			if(!$hideCalEventListing){
				ob_start();
				?><div id="calEventListing"><?php
				if(isset($events)){
					//events array being passed specifically
					$calEventMethod='array';
					if(!isset($calEventHeaderText))$calEventHeaderText='EVENTS';

				}else if($eventSQL){
					//sql query passed
					$events=q($eventSQL, O_ARRAY);
					$calEventMethod='sql';
					if(!isset($calEventHeaderText))$calEventHeaderText='EVENTS';

				}else if($thispage=='index_01_exe.php' && $past && $future && $calGetRange && $events=q("SELECT * FROM 
					cal_events WHERE $calEventWhereClause
					Active=1 AND StartDate BETWEEN '$past' AND '$future' 
					ORDER BY StartDate", O_ARRAY)){
					//events touching a specific range
					$calEventMethod='range';
					if(!isset($calEventHeaderText))$calEventHeaderText='EVENTS';
					
				}else if($eventDate && $events=q("SELECT * FROM
					cal_events WHERE $calEventWhereClause
					Active=1 AND
					(
					(StartDate='$eventDate' AND EndDate='0000-00-00') OR (StartDate<='$eventDate' AND EndDate >='$eventDate' AND EndDate!='0000-00-00')
					) ORDER BY StartDate", O_ARRAY)){
					//events touching a specific date
					$calEventMethod='date';
					if(!isset($calEventHeaderText))$calEventHeaderText='EVENTS FOR '.date('m/d/Y',strtotime($eventDate));

				}else if($events=q("SELECT * FROM
					cal_events WHERE $calEventWhereClause
					Active=1 AND
					(
					(StartDate='".date('Y-m-d',strtotime($systemDate))."' AND EndDate='0000-00-00') OR (StartDate<='".date('Y-m-d',strtotime($systemDate))."' AND EndDate >='".date('Y-m-d',strtotime($systemDate))."' AND EndDate!='0000-00-00')
					) ORDER BY StartDate", O_ARRAY)){
					//events touching today
					$calEventMethod='today';
					if(!isset($calEventHeaderText))$calEventHeaderText='TODAY';

				}else if(($thispage!==trim($pageHandles['calendarList'],'/')  || $overrideHideMonthQuickEvents) && $month && 
					$events=q("SELECT * FROM cal_events WHERE $calEventWhereClause
					Active=1 AND (
					StartDate>='$year-$month-01' AND StartDate <='$year-$month-".date('t',strtotime("$year-$month-01"))."'
					) ORDER BY StartDate ASC", O_ARRAY)){
					//events in this month
					$calEventMethod='month';
					if(!$eventDate)$eventDate="$year-$month-01";
					if(!isset($calEventHeaderText))$calEventHeaderText='EVENTS FOR '.strtoupper(date('F',strtotime($eventDate)));

				}
				if(count($events) || $showCalEventHeaderOnEmpty){
					?><h3 class="calEventHeader"><?php echo $calEventHeaderText; ?></h3><?php
				}
				if(count($events)){
					$i=0;
					foreach($events as $v){
						$i++;
						//handle event output here
						if(!$hideCalEventContainer){ 
							?><div id="calEvent<?php echo $i?>" class="calEvent evtFromCal<?php echo $v['Cal_ID']?>"><?php
						}
						if($calEventFunction){
							$calEventFunction($v);
						}else if($calEventSnippet){
							eval($calEventSnippet);
						}else{
						
						}
						if(!$hideCalEventContainer){ 
							?></div><?php
						}
						//handle break out
						if($maxCalItemListings>0 && $i>$maxCalItemListings){
							$calBreakout=true;
							break;
						}

					}
					if(!$hideEventsAllLink && ($calBreakout || $calForceEventAllLink)){
						//------------ view all events ---------------
						?><div id="calEvent0" class="calEventAll">
						<a title="Click here to view complete calendar" href="<?php echo $pageHandles['calendarList']?>"><?php echo $calEventAllLinkText;?></a>
						
						</div><?php
						//--------------------------------------------
					}
				}else{
					//no event text here
					echo $calEventNoEventsPresent;
				}
				if($calAccessToken){
					?><a title="Edit this event" href="/console/events.php?Cal_ID=1&cbFunction=refreshList" onclick="return ow(this.href,'l1_events','700,700',true);"><img src="/images/i/plusminus-plus.gif" width="11" height="11" alt="edit" />&nbsp;
					New event</a><?php
				}
				?></div><?php
				echo $calCalEventListing=get_contents();
			}
		?></div><?php
		echo $calCalSection=get_contents();
	}
	?>
</div>
<?php
if($calWidget1Rewrite){
	$standardLayout=get_contents();
} 

if(($mode=='navMonthEventCalendar' || $mode=='fetchEventsEventCalendar') && $calSelfRefresh && !$calWidget1Rewrite){
	if($mode=='fetchEventsEventCalendar')$eventDate="$year-$month-$day";
	?><script language="javascript" type="text/javascript">
	<?php if(!$calCustomReplaceRegions){ ?>
	window.parent.g('cal').innerHTML=document.getElementById('cal').innerHTML;
	<?php }else{ foreach($calCustomReplaceRegions as $v){ ?>
	window.parent.g('<?php echo $v?>').innerHTML=document.getElementById('<?php echo $v?>').innerHTML;
	<?php }} ?>
	</script><?php	
	$assumeErrorState=false;
	exit;
}

//-------------- functions only from here ----------------------
?>
