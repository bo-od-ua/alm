<?

error_reporting(E_ERROR | E_PARSE | E_COMPILE_ERROR);
@ini_set("display_errors",1);
define('__SCRIPTNAME__', basename(__FILE__));

alm::init();

final class alm
{
	public static  $logAccess= '/var/www/user/data/logs/site.com.access.log';
	public static  $logError=  '/var/www/user/data/logs/site.com.error.log';

	public static function init()
	{
		$c= $_REQUEST['c'];
		$c= lcfirst(str_replace('_', '', ucwords($c, '_')));
		if(method_exists(__CLASS__, $c))
		{
			self::$c();
			exit();
		}
	}

	public static function phpinfo()
	{
		phpinfo();
	}

	public static function tail()
	{
		print json_encode([
			'e'=> self::tailGet(self::$logError),
			'a'=> self::tailGet(self::$logAccess),
		]);
	}

	function tailGet($File)
	{
		$Cmd= 'tail ';
		$Size= 200;
		if(is_numeric($_REQUEST['size']) && ($_REQUEST['size']< 1000))
		{
			$Size= $_REQUEST['size'];
		}
		$Cmd.= '-'.$Size.' '.$File.' ';
		if((32> strlen($_REQUEST['filter'])) && (2< strlen($_REQUEST['filter'])))
		{
			$Cmd.= "| grep ".$_REQUEST['filter'];
		}
		exec($Cmd, $aL);

	return(implode("<br>", $aL));
	}

	public static function whois()
	{
		$IP= $_REQUEST['ip'];
		if(filter_var($IP, FILTER_VALIDATE_IP))
		{
			exec('whois '.$IP, $aL);
		}
		print implode("<br>", $aL);
	}

	public static function top()
	{
		exec('top -b -n 1', $aL);
		print json_encode(['t'=> implode("<br>", $aL)]);
	}

	function accessLog()
	{
		$IP=     $_REQUEST['ip'];
		$Filter= $_REQUEST['filter'];

		if(filter_var($IP, FILTER_VALIDATE_IP) && is_file(self::$logAccess))
		{
			$hList= array();
			if((32< strlen($Filter)) || (2> strlen($Filter)) || empty($Filter)) $Filter= '';

			$F= fopen(self::$logAccess, "r");
			while(!feof($F))
			{
				$Line= fgets($F);

				if($Filter && !preg_match('/'.preg_quote($Filter, '/').'/', $Line))
				{
					continue;
				}

				if(preg_match('/^'.$IP.' /', $Line, $aM))
				{
					$hList[]= $Line;
				}
			}
			fclose($F);

			print json_encode(['data'=> $hList, 'count'=> count($hList)]);
		}
	return(1);
	}

	function hostList()
	{
		$Content= '';
		$hList= [];
		$hRow= [];

		$Filter= $_REQUEST['filter'];

		if((32< strlen($Filter)) || (2> strlen($Filter)) || empty($Filter)) $Filter= '';

		if(is_file(self::$logAccess))
		{
			$F= fopen(self::$logAccess, "r");
			while(!feof($F))
			{
				$Line= fgets($F);
				preg_match('/^(\S+)/', $Line, $aM);

				if($Filter && !preg_match('/'.preg_quote($Filter, '/').'/', $Line)){ continue;}

				if(!empty($hList[$aM[0]])){ $hList[$aM[0]]++;}
				else{ $hList[$aM[0]]= 1;}
			}
			fclose($F);

			arsort($hList);
			$hRow= ['err'=> 0, 'data'=> $hList, 'count'=> count($hList)];
		}
		else
		{
			$hRow['err']= 1;
			$hRow['message']= 'can`t find access log file';
		}

		print json_encode($hRow);
	}
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
	<meta http-equiv="Content-type" content="text/html; charset=utf-8" />

	<title>ApacheLogMonitor</title>

<link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css">

<style type="text/css">
body{font-family:Geneva,Arial,Helvetica,sans-serif;font-size:100%}.ui-layout-pane{background:#fff;border:1px solid #bbb;padding:10px;overflow:auto}.ui-layout-content{padding:10px;position:relative;overflow:auto}.layout-child-container,.layout-content-container{padding:0;overflow:hidden}.layout-child-container{border:0}.layout-scroll{overflow:auto}.layout-hide{display:none}.ui-layout-resizer{background:#ddd;border:1px solid #bbb;border-width:0}.ui-layout-resizer-dragging,.ui-layout-resizer-open-hover{background:#c4e1a4}.ui-layout-resizer-dragging{border:1px solid #bbb}.ui-layout-resizer-north-dragging,.ui-layout-resizer-south-dragging{border-width:1px 0}.ui-layout-resizer-east-dragging,.ui-layout-resizer-west-dragging{border-width:0 1px}.ui-layout-resizer-dragging-limit{background:#e1a4a4}.ui-layout-resizer-closed-hover{background:#ebd5aa}.ui-layout-resizer-sliding{opacity:.1}.ui-layout-resizer-sliding-hover{opacity:1}.ui-layout-resizer-north-sliding-hover{border-bottom-width:1px}.ui-layout-resizer-south-sliding-hover{border-top-width:1px}.ui-layout-resizer-west-sliding-hover{border-right-width:1px}.ui-layout-resizer-east-sliding-hover{border-left-width:1px}.ui-layout-toggler{border:1px solid #bbb;background-color:#bbb}.ui-layout-resizer-hover .ui-layout-toggler{opacity:.6}.ui-layout-resizer-hover .ui-layout-toggler-hover,.ui-layout-toggler-hover{background-color:#fc6;opacity:1}.ui-layout-toggler-north,.ui-layout-toggler-south{border-width:0 1px}.ui-layout-toggler-east,.ui-layout-toggler-west{border-width:1px 0}.ui-layout-resizer-sliding .ui-layout-toggler{display:none}.ui-layout-toggler .content{color:#666;font-size:12px;font-weight:700;width:100%;padding-bottom:.35ex}.ui-layout-mask{border:none!important;padding:0!important;margin:0!important;overflow:hidden!important;position:absolute!important;opacity:0!important;filter:Alpha(Opacity="0")!important}.ui-layout-mask-inside-pane{top:0!important;left:0!important;width:100%!important;height:100%!important}@media print{html{height:auto!important;overflow:visible!important}body.ui-layout-container{position:static!important;top:auto!important;bottom:auto!important;left:auto!important;right:auto!important}.ui-layout-resizer,.ui-layout-toggler{display:none!important}.ui-layout-pane{border:none!important;background:0 0!important;position:relative!important;top:auto!important;bottom:auto!important;left:auto!important;right:auto!important;width:auto!important;height:auto!important;overflow:visible!important}}

html, body {
	background:	#666;
	width:		100%;
	height:		100%;
	padding:	0;
	margin:		0;
	overflow:	auto;

	font-family: "Verdana", "helvetica", "arial", sans-serif;
	font-size: 12px;
}
#container {
	background:	#999;
	height:		100%;
	margin:		0 auto;
	width:		100%;
	_width:		700px;
}
#tabs_monitor{
	font-size: 10px;
}
#tabs_ip_info{
	float:left;
	font-size: 10px;
}

#tabs-access_log li:hover{
	text-decoration: underline;
}

.ui-tabs .ui-tabs-panel{
	overflow: auto;
}

.TopPanelItem{
	padding: 0px 0px 0px 10px;
}

.ItemWrapper{
	width: 100%;
	border-bottom: 1px solid #999999;
	cursor: pointer;
}
.ItemWrapper:hover{
	background: #DDDDDD;
}
.ItemWrapper.Selected{
	background: #CCFFFF;
}

.ItemCount{
	width: 60px;
	float:left;
	font-weight: bold;
	padding-left:5px;
}
.ItemCaption{
	float:left;
	font-size: 11px;
}

.ipList{
	padding:0px;
	float:left;
	overflow: auto;
	padding:0px;
}

.blinking {
	padding:20px;
	font-weight: bold;
	animation: blink 2s infinite;
}
@keyframes blink {
	from { opacity: 1; }
	to { opacity: 0; }
}

#modal_iframe{width:100%; height:100%;}

.highlight { background-color: yellow;}

.pane {
	display:	none;
}
</style>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jquery.ui.layout@1.0.0/jquery.layout.js"></script>

<script>
$(document).on('click', '.ItemWrapper', function(e){
	$('.ItemWrapper').removeClass("Selected");
	$(this).addClass("Selected");

	$.ajax({
		url: '<?= __SCRIPTNAME__; ?>?c=whois',
		type: "POST",
		data: "ip="+ $(this).attr('ip')+ '&filter='+ $('#ip_list_input').val(),
		success: function(html){
			$('#tabs-whois').html(html);
			setIntabHeight();
		},
		dataType: 'html',
	});

	$.ajax({
		url: '<?= __SCRIPTNAME__; ?>?c=access_log',
		type: "POST",
		data: "ip="+ $(this).attr('ip')+ '&filter='+ $('#ip_list_input').val(),

		dataType: 'json',
		success: function(resp){
			$('#tabs-access_log').html('<ol></ol>');
			$.each(resp.data, function(K, V){
				let Content= '<li>&gt;&nbsp;'+ V+ '</li>';
				$('#tabs-access_log>ol').append(Content);
			});
		},
	});

	if($('#highlight_cb').is(':checked') == true){ highLight('#tabs_ip_info');}
});

$(document).ready(function() {
	setIntabHeight();
	$(window).resize(function() {
		setIntabHeight();
	});

	host_list();
});
$(document).on('click', '#ip_list_a', function(e){
	host_list();
	return false;
});

$(document).on('click', '#is_tail_logs', function(e){
	if($(this).is(':checked') == true)
	{
		intervalID= setInterval(tail_logs, 10000);
	}
	else
	{
		clearInterval(intervalID);
	}
});

$(document).on('click', '.ModalA', function(e){
	var URL= $(this).attr('href');

	URL= URL+ '&r='+ (Math.random() * (9999 - 1) + 1);
	set_modal(URL);
	return false;
});

$( function() {
	$( "#tabs_monitor" ).tabs({
		activate: function( event, ui ) {setIntabHeight();}
	});
	$( "#tabs_ip_info" ).tabs({
		activate: function( event, ui ) {setIntabHeight();}
//		heightStyle: "fill"
	});

	$("#modal").dialog({
		autoOpen: false,
		height: $(window).height()- 200,
		width:  $(window).width()- 200,
	});
});

$(document).on('click', '#highlight_cb', function(e){
	if($(this).is(':checked') == true)
	{
		highLight();
	}
	else
	{
		$("body").removeHighlight();
	}
});

$(document).on('click', '#tabs_monitor ul li a', function(e){
	var tab_id= $(this).attr('href');

	if(tab_id== '#tabs-top')
	{
		get_top();
		TopIID= setInterval(get_top, 10000);
	}
	else
	{
		if (typeof TopIID !== 'undefined') clearInterval(TopIID);
	}
});
</script>


<script>
function finder_modal_sizes(){

	var H=   $('#finder_modal').height();
	var HFL= H- $('#finder_toolbar').height()- 5;
	$('#finder_list').height(HFL);
	$('#finder_content').height(HFL- $('#finder_info').height()- 10);
}

function get_top(){
	var URL= '<?= __SCRIPTNAME__; ?>?c=top';
		$.ajax({
		url: URL,
		type: "POST",
		dataType: 'JSON',
		success: function(JSON){
			$('#tabs-top').html(JSON.t);
		},
	});
}

function tail_logs(){
	var URL= '<?= __SCRIPTNAME__; ?>?c=tail&size='+ $('#tail_size_input').val()+ '&filter='+ $('#tail_filter_input').val();
		$.ajax({
		url: URL,
		type: "POST",
		dataType: 'JSON',
		success: function(JSON){
			$('#tabs-tail-error-log').html(JSON.e);
			$('#tabs-tail-access-log').html(JSON.a);
			if($('#highlight_cb').is(':checked') == true){ highLight('#tabs_monitor');}
		},
	});
}

function set_modal(URL){

	$('#modal_iframe').attr('src', URL);
	$( "#modal" ).dialog( "open" );
}

function host_list(){
	var URL= '<?= __SCRIPTNAME__; ?>?c=host_list&filter='+ $('#ip_list_input').val();
	$.ajax({
		url: URL,
		type: "POST",
		dataType: 'json',
		beforeSend: function(){
			$('#ip_list').html('<span class="blinking">loading data...</span>');
		},
		success: function(resp){
			if(!resp.err){
				$('#ip_list').html('<ol></ol>');
				$.each(resp.data, function(K, V){
					let Content= '<li class="ItemWrapper" ip="'+ K+ '"><span class="ItemCount">'+ V+ '</span><span class="ItemCaption">'+ K+ '</span></li>';
					$('#ip_list>ol').append(Content);
				});
				$('#count_visitors').html(resp.count);
			}
			else{ $('#ip_list').html('<span class="blinking">'+ resp.message+ '</span>');}
		},
		error: function(){console.log(URL);},
	});
}

$.fn.replaceText = function( search, replace, text_only ) {
	return this.each(function(){
		var node = this.firstChild,
			val,
			new_val,
			remove = [];
		if ( node ) {
			do {
				if ( node.nodeType === 3 ) {
					val = node.nodeValue;
					new_val = val.replace( search, replace );
					if ( new_val !== val ) {
						if ( !text_only && /</.test( new_val ) ) {
							$(node).before( new_val );
							remove.push( node );
						} else {
							node.nodeValue = new_val;
						}
					}
				}
			} while ( node = node.nextSibling );
		}
		remove.length && $(remove).remove();
	});
};

jQuery.fn.removeHighlight = function() {
 return this.find("span.highlight").each(function() {
	with (this.parentNode) {
		replaceChild(this.firstChild, this);
	}
 }).end();
};

function highLight(Attr, searchTerm) {
	if(Attr === undefined) Attr= 'body';
	if(searchTerm === undefined) searchTerm = $('#highlight_input').val();

	searchRegex  = new RegExp(searchTerm, 'g');
	$(Attr+ " *").replaceText( searchRegex, '<span class="highlight">'+searchTerm+'</span>');
}

function setIntabHeight() {
	$(".ui-tabs").each(function(i, e){
		let H= $(e).height()- 50;
		$(e).find('.ui-tabs-panel').height(H);
	});
}

function start_tool(Action, URL)
{
	var obj= {
		finder: function()
		{
			$('#finder_modal').dialog( "open" );
			finder_modal_sizes();
		},
		iframe: function(URL)
		{
			URL= URL+ '&r='+ (Math.random() * (9999 - 1) + 1);
			set_modal(URL);
		},
	}

	obj[Action](URL);
}
</script>

<script type="text/javascript">
$(document).ready(function () {
	var lLayout= $('#container').layout({
		west__closable: false,
		north__closable: false,
		north__resizable: false,
	});
	lLayout.sizePane("west",  400);
	lLayout.sizePane("south", 300);
});
</script>


</head>
<body>
	<div id="container">

		<div id="tabs_ip_info" class="pane ui-layout-center">
			<ul>
				<li><a href="#tabs-whois">whois</a></li>
				<li><a href="#tabs-access_log">access log for ip</a></li>
			</ul>
			<div id="tabs-whois"></div>
			<div id="tabs-access_log"></div>
		</div>

		<div class="pane ui-layout-north">
			<span class="TopPanelItem">visitors:&nbsp;<b><span id="count_visitors"></span></b></span>
			<span class="TopPanelItem">my ip: <b><?= $_SERVER['REMOTE_ADDR'] ?></b></span>
			<span class="TopPanelItem">[ filter log <input id="ip_list_input" placeholder="more than two symbols" size="25" title="ip filter">&nbsp;<a id="ip_list_a" href="#">go</a>&nbsp;]</span>
			<span class="TopPanelItem">[ highlighting <input id="highlight_cb" type="checkbox" value="1">&nbsp;<input id="highlight_input"  placeholder="more than two symbols" size="25" title="highlighting">&nbsp;]</span>
			<span class="TopPanelItem">[ tail log: <input id="is_tail_logs" type="checkbox" value="1">&nbsp;<input id="tail_size_input" value="200"  size="4"  title="tail size">,&nbsp;<input id="tail_filter_input" placeholder="tail filter" size="16" title="tail filter">&nbsp;]</span>
			<span class="TopPanelItem">[&nbsp;<b>tools:</b>&nbsp;
			<a id="phpinfo_a" class="ModalA" href="<?= __SCRIPTNAME__; ?>?c=phpinfo">phpinfo</a>&nbsp;]</span>
		</div>

		<div id="tabs_monitor" class="pane ui-layout-south">
			<ul>
				<li><a href="#tabs-tail-access-log">tail access log</a></li>
				<li><a href="#tabs-tail-error-log">tail error log</a></li>
				<li><a href="#tabs-top">top</a></li>
			</ul>
			<div id="tabs-tail-access-log"></div>
			<div id="tabs-tail-error-log"></div>
			<div id="tabs-top"></div>
		</div>

		<div id="ip_list" class="pane ui-layout-west ipList"></div>
	</div>

	<div id='modal' title="">
		<iframe id="modal_iframe" src="" frameBorder="0"></iframe>
	</div>
</body>
</html>