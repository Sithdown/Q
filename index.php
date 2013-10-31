<?php

require_once "connection.php";

function getData($data){

    global $pdo;
    $insert = 'SELECT * FROM '.$data;
    $ready = $pdo->prepare($insert);
    $result = $ready->execute();
    if($result) {
        $obj = array();
        while($r = $ready->fetch()){
            $obj[] = $r;
        }
        return $obj;
    }
    return false;
}

function getDayTotal(){

    global $pdo;
    $insert = 'SELECT DAY(datetime) AS monthday, DATE(datetime) as date, SUM(duration) AS duration_total FROM logs WHERE YEAR(datetime) = YEAR(CURDATE()) AND MONTH(datetime) = MONTH(CURDATE()) GROUP BY YEAR(datetime), MONTH(datetime), DAY(datetime)';
    
    $ready = $pdo->prepare($insert);
    $ready->execute();

    $result = $ready->fetchAll();

    return $result;
}

function pTime($minutes){

    if($minutes>=60){
        $h = floor($minutes/60);
        $m = floor($minutes%60);
        $r = $h."h".($m!=0?$m."m":"");
    }
    else{
        $r = floor($minutes)."m";
    }

    return $r;
}

$moods = getData("moods");
$daytotals = getDayTotal();

?>
<!DOCTYPE html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js"> <!--<![endif]-->
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <title></title>
        <meta name="description" content="">
        <meta name="viewport" content="width=device-width">

        <link rel="stylesheet" href="css/normalize.min.css">
        <link rel="stylesheet" href="css/bootstrap.min.css">
        
        <link rel="stylesheet" href="css/bootstrap-datetimepicker.min.css">
        <link rel="stylesheet" href="css/ion.rangeSlider.css">
        <link rel="stylesheet" href="css/ion.rangeSlider.skinFlat.css">
        <link rel="stylesheet" href="css/bootstrap-progressbar.min.css">
        <link rel="stylesheet" href="css/bootstrap-select.min.css">
        <link rel="stylesheet" href="css/bootstrap-tagsinput.css">
        <link rel="stylesheet" href="css/font-awesome.min.css">

        <link rel="stylesheet" href="css/app.css">

        <!--[if lt IE 9]>
            <script src="//html5shiv.googlecode.com/svn/trunk/html5.js"></script>
            <script>window.html5 || document.write('<script src="js/vendor/html5shiv.js"><\/script>')</script>
        <![endif]-->
    </head>
    <body>
        <!--[if lt IE 7]>
            <p class="chromeframe">You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade your browser</a> or <a href="http://www.google.com/chromeframe/?redirect=true">activate Google Chrome Frame</a> to improve your experience.</p>
        <![endif]-->

        <nav class="navbar navbar-inverse navbar-fixed-top" role="navigation">
          <!-- Brand and toggle get grouped for better mobile display -->
          <div class="navbar-header">
            <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-ex1-collapse">
              <span class="sr-only">Toggle navigation</span>
              <span class="icon-bar"></span>
              <span class="icon-bar"></span>
              <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="#">Q</a>
          </div>

          <!-- Collect the nav links, forms, and other content for toggling -->
          <div class="collapse navbar-collapse navbar-ex1-collapse">
            <ul class="nav navbar-nav">
              <li class="active"><a href="#add" data-toggle="tab" id="togAdd"><i class="fa fa-plus"></i></a></li>
              <li><a href="#stats" data-toggle="tab" id="togStats"><i class="fa fa-bar-chart-o"></i></a></li>
            </ul>
            <ul class="nav navbar-nav navbar-right">

                <li class="dropdown">
                    <a id="drop-settings" role="button" data-toggle="dropdown" href="#">
                        <i class="fa fa-cog fa-lg"></i>
                    </a>
                    <ul class="dropdown-menu" role="menu" aria-labelledby="drop-settings">
                        <li role="presentation"><a role="menuitem" tabindex="-1" href="#">Toggle greedy search</a></li>
                        <li role="presentation"><a role="menuitem" tabindex="-1" href="#">Reset statistics</a></li>
                        <li role="presentation" class="divider"></li>
                        <li role="presentation"><a role="menuitem" tabindex="-1" href="#">Logout</a></li>
                    </ul>
                </li>
            </ul>
          </div><!-- /.navbar-collapse -->
        </nav>


        <div class="container">

            <div class="tab-content">

                <div class="tab-pane active" id="add">
                    <form id="addform" action="add.php" class="form-horizontal"  role="form">
                        <fieldset>
                            <div class="form-group time-basic">
                                <div class="col-md-offset-4 col-md-4">
                                    <button id="toggleTrack" class="btn btn-danger btn-block"><p style="font-size:4em;"></p><span>Start tracking</span></button>
                                    <br>
                                    <button class="btn btn-link btn-block" style="color:#aaa;" data-toggle="collapse" data-target="#finetune">Fine tune</button>
                                </div>
                            </div>
                            <div id="finetune" class="collapse">
                                <div class="form-group time-advanced">
                                    <label for="datetime" class="col-md-4 control-label">Date</label>
                                    <div class="input-group date form_datetime col-md-4" data-date="" data-date-format="dd MM yyyy - HH:ii p" data-link-field="datetime">
                                        <input class="form-control" size="16" type="text" value="" readonly>
                                        <span class="input-group-addon"><i class="fa fa-times"></i></span>
                                        <span class="input-group-addon"><i class="fa fa-th fa-calendar"></i></span>
                                    </div>
                                    <input type="hidden" id="datetime" name="datetime" value="" /><br/>
                                </div>
                                <div class="form-group">
                                    <label for="duration" class="col-md-4 control-label">Length</label>
                                    <div class="input-grodup col-md-4">
                                        <input name="duration" id="duration" class="form-control" size="16" type="text">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="description" class="col-md-4 control-label">Description</label>
                                    <div class="col-md-4">
                                        <textarea class="form-control" rows="3" name="description" id="description"></textarea>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="mood" class="col-md-4 control-label">Mood</label>
                                    <div class="col-md-4">
                                        <select class="form-control selectpicker" style="display: none;" name="mood" id="mood">
                                          <?php
                                          $m = array("fa-heart-o text-info","fa-smile-o text-success","fa-meh-o text-warning","fa-frown-o text-danger","fa-times text-danger");
                                          foreach ($moods as $key => $value) {
                                            if(isset($m[$key])){
                                                $w = " data-icon='fa fa-lg fa-fw ".$m[$key]."'";
                                            }
                                            else{
                                                $w = "";
                                            }
                                          ?>
                                          <option<?php echo $w;?>  value="<?php echo $value["ID"];?>"><?php echo utf8_encode(ucfirst($value["name"]));?></option>
                                          <?php } ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="tags" class="col-md-4 control-label">Tags</label>
                                    <div class="col-md-4">
                                        <input spellcheck="false" autocomplete="off" autocapitalize="off" type="text" class="col-md-4 form-control" name="tags" id="tags" data-role="tagsinput"></input>
                                    </div>
                                    <div class="col-md-4" id="exampletags"></div>
                                </div>

                                <div class="form-group">
                                    <div class="col-md-offset-4 col-md-4">
                                        <button type="submit" id="smt" class="btn btn-danger btn-block">Add</button>
                                    </div>
                                </div>
                            </div>
                        </fieldset>

                    </form>

                </div>

                <div class="tab-pane" id="stats">

                </div>

                <div id="bot">
                    <nav class="navbar navbar-default navbar-inverse navbar-fixed-bottom" role="navigation">
                        <div class="container">
                            <form class="navbar-form navbar-left" role="search">

                                <div class="form-group col-md-4" id="dtholder">
                                    <div class="input-group date search_datetime" data-date="" data-date-format="dd MM yyyy - HH:ii p" data-link-field="search_datetime">
                                        <input class="form-control" size="16" type="text" value="" readonly>
                                        <span class="input-group-addon"><i class="fa fa-times"></i></span>
                                        <span class="input-group-addon"><i class="fa fa-th fa-calendar"></i></span>
                                    </div>
                                    <input type="hidden" id="search_datetime" name="search_datetime" value="" />
                                </div>

                                <div class="form-group col-md-4" id="csholder">
                                    <input id="combinedsearch" type="text" spellcheck="false" autocomplete="off" autocapitalize="off" class="form-control" placeholder="Search">
                                </div>
                                <!--
                                <div class="form-group col-md-4">
                                    <input spellcheck="false" autocomplete="off" autocapitalize="off" type="text" class="col-md-4 form-control" name="tagfs" id="tafgs" data-role="tagsinput"></input>
                                </div>
                                -->
                            </form>
                        </div>
                    </nav>
                </div>
            </div>


        </div>

        <script src="//ajax.googleapis.com/ajax/libs/jquery/2.0.3/jquery.min.js"></script>
        <script>window.jQuery || document.write('<script src="js/vendor/jquery-2.0.3.min.js"><\/script>')</script>

        <script src="js/plugins.js"></script>
        <script src="js/main.js"></script>
        <script src="js/bootstrap.min.js"></script>
        <script src="js/bootstrap-datetimepicker.min.js" charset="UTF-8"></script>
        <script src="js/locales/bootstrap-datetimepicker.es.js" charset="UTF-8"></script>
        <script src="js/bootstrap-progressbar.min.js" charset="UTF-8"></script>
        <script src="js/bootstrap-select.min.js" charset="UTF-8"></script>
        <script src="js/bootstrap-tagsinput.min.js" charset="UTF-8"></script>
        <script src="js/typeahead.min.js" charset="UTF-8"></script>
        <script src="js/ion.rangeSlider.min.js" charset="UTF-8"></script>

        <script type="text/javascript">

            var curoptions = {};
            var starttime = 0;
            var tracking = false;

            var instance_searchdatetime = $('.search_datetime').datetimepicker({
                pickerPosition: 'top-left',
                language:  'en',
                weekStart: 1,
                todayBtn:  1,
                autoclose: 0,
                todayHighlight: 1,
                startView: 2,
                forceParse: 0,
                showMeridian: 1,
                viewSelect: 4
            }).on('changeDate', function(ev){

                if(ev.date==null){
                    loadStats({});
                }
                else{

                    var dt = new Date(ev.date.valueOf());

                    var y = dt.getFullYear();
                    var m = dt.getMonth()+1;
                    var d = dt.getDate();
                    var h = dt.getHours();
                    var mm = dt.getMinutes();
                    var s = "00";//dt.getSeconds();

                    var dt = y+"-"+m+"-"+d+" "+h+":"+mm+":"+s;

                    loadStats({datelt:dt});
                }
            });

            var instance_datetime = $('.form_datetime').datetimepicker({
                pickerPosition: 'bottom-left',
                language:  'en',
                weekStart: 1,
                todayBtn:  1,
                autoclose: 1,
                todayHighlight: 1,
                startView: 2,
                forceParse: 0,
                showMeridian: 1
            });

            var instance_duration = $("#duration").ionRangeSlider({
                min: 0,                        // min value
                max: 300,                       // max value
                type: 'single',                 // slider type
                step: 5,                       // slider step
                //postfix: ' minutos',             // postfix text
                hasGrid: false,//true,                  // enable grid
                hideMinMax: false,               // hide Min and Max fields
                hideFromTo: false,               // hide From and To fields
                prettify: true,                 // separate large numbers with space, eg. 10 000
                onChange: function(obj){        // function-callback, is called on every change
                },
                onFinish: function(obj){        // function-callback, is called once, after slider finished it's work
                }
            });

            $('.selectpicker').selectpicker({width:"100%"});

            var tg = $('#tags');
            tg.tagsinput({
                confirmKeys: [13, 44, 188]
            });
            
            tg.tagsinput('input').typeahead({
                local: [],
                freeInput: true
            }).bind('typeahead:selected', $.proxy(function (obj, datum) {  
                this.tagsinput('add', datum.value);
                this.tagsinput('input').typeahead('setQuery', '');
            }, tg));



            var cs = $('#combinedsearch');
            cs.tagsinput({
                confirmKeys: [13, 44, 188],
            });

            cs.change(function(){
                if($(this).val()!=""){
                    loadStats({terms:$(this).val()});
                }
                else{
                    loadStats({});
                }
            });
            
            function partInArray(text,array){
                for ( var i = 0, length = array.length; i < length; i++ ) {
                    if ( typeof array[i] === 'string' && text.indexOf(array[i]) > -1 ) {
                        return i;
                    }
                }
                return -1; 
            }
            
            function hintTerms(desc,terms){

                terms = (""+terms).split(",");
                $.each(terms,function(key, val){

                    if(val.indexOf("href") == -1){

                        var create_hint = function (term) {
                            return "<span class='hint'>"+term+"</span>";
                        };
                        var re = new RegExp(val, 'ig');
                        desc = desc.replace(re, create_hint(val));
                    }
                });
             
                return desc;  
            }


            function pText(str) {
                var create_link = function (url, text) {
                    var link = $("<a>", {
                        text: text,
                        href: url,
                        target: "_blank"
                    });
             
                    return link.prop('outerHTML');
                };
             
                // parse URLs
                str = str.replace(/[A-Za-z]+:\/\/[A-Za-z0-9-_]+\.[A-Za-z0-9-_:%&~\?\/.=]+/g, function (s) {
                    return create_link(s, s);
                });
             
                // parse username
                str = str.replace(/[@]+[A-Za-z0-9_]+/g, function (s) {
                    return create_link("http://twitter.com/" + s.replace('@', ''), s);
                });
             
                // parse hashtags
                str = str.replace(/[#]+[áéíóúÁÉÍÓÚñÑA-Za-z0-9_]+/g, function (s) {
                    return create_link("http://search.twitter.com/search?q=" + s.replace('#', ''), s);
                });
             
                return str;
            };

            function pTime(minutes){

                if(minutes>=60){
                    var h, m;
                    h = Math.floor(minutes/60);
                    m = Math.floor(minutes%60);
                    r = h+"h"+(m!=0?m+"m":"");
                }
                else{
                    r = Math.floor(minutes)+"m";
                }

                return r;
            }

            $("#togAdd").on("click", function(){
                $("#bot").removeClass("visible");
                $("body").removeClass("padd");
                $("#finetune").collapse('hide');
                $("#toggleTrack > p").html("");
            });

            function loadStats(options){

                if($.isEmptyObject(options)){
                    loadStatsReal({});
                }
                else{
                    var merge = $.extend(curoptions, options);
                    loadStatsReal(merge);
                }
            }

            function loadStatsReal(options){

                var args = "";
                if(!$.isEmptyObject(options)){
                    //args+="&"
                    $.each( options, function( key, value ) {
                        if(args!="&"){
                            args+="&";
                        }
                        args+=key+"="+value;
                    });

                }

                var meses = ["Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"];
                var icoMoods = ["fa-heart-o text-info","fa-smile-o text-success","fa-meh-o text-warning","fa-frown-o text-danger","fa-times text-danger"];

                var moods = [];
                <?php
                foreach ($moods as $key => $value) {
                    echo "moods[".$key."] = '".utf8_encode($value["name"])."';\n";
                }
                ?>

                $("#stats").html('<div class="col-md-1 col-md-offset-5" style="text-align:center;padding-top:100px;"><i class="fa fa-refresh fa-5x fa-spin"></i></div>');
                $.getJSON( "list.php?"+args, { } )
                  .done(function( json ) {

                    if(!$.isEmptyObject(json)){

                        var items=[];

                        var curday = 0;

                        var today = new Date().getDate();

                        $.each(json, function(key, val){
                            var t = "";

                            $.each(val["tags"], function(key, val){
                                if(t!=""){
                                    t+=" ";
                                }
                                var lb = "label-default";
                                if(!$.isEmptyObject(options)){
                                    if(!$.isEmptyObject(options["tags"])){
                                        if(partInArray(val.trim(), options["tags"].split(",")) > -1){
                                            lb = "label-danger";
                                        }
                                    }
                                    else{
                                        if(!$.isEmptyObject(options["terms"])){
                                            if(partInArray(val.trim(), options["terms"].split(",")) > -1){
                                                lb = "label-danger";
                                            }
                                        }
                                    }
                                }

                                t+='<span onclick="searchtag(\''+val.trim()+'\')" class="clickable label '+lb+'">'+val.trim()+'</span>';
                            });

                            var dt = val["datetime"].split(" ");
                            var date = dt[0];
                            date = date.slice(5).split("-");
                            var day = date[1];
                            var mn = meses[date[0]-1];
                            date = date[1];//+" "+meses[date[0]-1];
                            var time = dt[1];

                            var icoMood = "<i onclick='searchmood("+val["mood"]+")' class='clickable fa "+icoMoods[val["mood"]-1]+"'></i>";

                            if(curday!=day){
                                var kk = "";
                                if(curday!=0){
                                    var kk = "</tbody></table><table class='col-md-4 table table-striped' style='text-align:center;background-color:#FAFAFA;'>";
                                }
                                items.push(kk+"<thead><tr><td colspan='4' style='text-align:left;'><h2><i class='fa fa-calendar-o'></i> "+day+" "+mn+"</h2></td></tr></thead><tbody>");
                                curday = day;
                            }

                            val["description"] = pText(val["description"]);

                            if(!$.isEmptyObject(options["terms"])){
                                val["description"] = hintTerms(val["description"],options["terms"]);
                            }
                            if(!$.isEmptyObject(options["description"])){
                                val["description"] = hintTerms(val["description"],options["description"]);
                            }


                            items.push("<tr id='log_"+val["ID"]+"'><td onclick='searchduration("+val["duration"]+")' class='col-md-1 clickable' title='"+val["datetime"]+"'>"+pTime(val["duration"])+"</td><td class='col-md-7'>"+val["description"]+"</td><td class='col-md-1' title='"+moods[val["mood"]-1][0].toUpperCase()+moods[val["mood"]-1].slice(1)+"'>"+icoMood+"</td><td class='col-md-3'>"+t+"</td></tr>");
                        });

                        var bars = "";
 
                        var daytotals = $.getJSON( "list.php?daytotals=true"+args, { } )
                        .done(function( js ) {

                            var brs = "";
                            var j = 0;
                            for (var i = 0; i < 31; i++) {

                                if(js[j]!==undefined){
                                    if((js[j]["monthday"]-1)==i){
                                        brs+='<div class=\'progress vertical bottom\'><div title=\''+pTime(js[j]['duration_total'])+'\' class=\'progress-bar progress-bar-danger\' aria-valuetransitiongoal=\''+(js[j]['duration_total']/(1440-480)*100)+'\'></div></div>';
                                        j+=1;
                                    }
                                    else{
                                        brs+='<div class=\'progress vertical bottom\'><div class=\'progress-bar progress-bar-danger\' aria-valuetransitiongoal=\'0\'></div></div>'; 
                                    }
                                }
                                else{
                                    brs+='<div class=\'progress vertical bottom\'><div class=\'progress-bar progress-bar-danger\' aria-valuetransitiongoal=\'0\'></div></div>';
                                }
                            };
                            bars = "<div style=\'height:300px;\'>"+brs+"</div>";

                            var bread = '<ol class="breadcrumb"><li><a href="#" onclick="loadStats({});">Stats</a></li>';


                            $.each( options, function( key, value ) {
                                var v = value;
                                if(key=="tags"){
                                    var vv = (v+"").split(",");
                                    v = 'Tagged';
                                    $.each(vv, function(ke, val){
                                        v+=' <span class="label label-default">'+val+'</span>';
                                    });
                                }
                                if((key=="description")||(key=="terms")){
                                    var vv = (v+"").split(",");
                                    v = 'With terms';
                                    $.each(vv, function(ke, val){
                                        v+=' <span class="label label-default">'+val+'</span>';
                                    });
                                }
                                if((key=="dategte")||(key=="datelt")){
                                    var vv = (v+"").split(",");
                                    if(key=="dategte"){
                                        v = 'Starting on';
                                    }
                                    else{
                                        v = 'Before';
                                    }
                                    $.each(vv, function(ke, val){
                                        val = val.split(" ");
                                        valt = val[1].split(":");
                                        if(valt[0]<10){
                                            valt[0] ="0"+valt[0];
                                        }
                                        if(valt[1]<10){
                                            valt[1] ="0"+valt[1];
                                        }
                                        vald = val[0].split("-");
                                        val = vald[2]+"-"+meses[vald[1]-1]+"-"+vald[0]+" "+valt[0]+":"+valt[1];
                                        v+=' <span class="label label-default">'+val+'</span>';
                                    });
                                }
                                if((key=="durationgte")||(key=="durationlt")){
                                    if(key=="durationgte"){
                                        v = 'De <span class="label label-default">'+pTime(value)+'</span> or higher';
                                    }
                                    else{
                                        v = '<span class="label label-default">'+pTime(value)+'</span> or lower';
                                    }
                                }
                                if(key=="mood"){
                                    var vv = (v+"").split(",");
                                    v = 'With mood';
                                    $.each(vv, function(ke, val){
                                        v+=' <span class="label label-default">'+moods[val-1][0].toUpperCase()+moods[val-1].slice(1)+'</span>';
                                        //v+=' <span class="label label-default">'+val+'</span>';
                                    });
                                }
 
                                bread+='<li><a href="#" onclick="loadStats({'+key+':\''+value+'\'});">'+v+'</a></li>';
                            });


                            bread+='</ol>';

                            $("#stats").html(bread+bars+'<div class="table-responsive"><table class="col-md-4 table table-striped" style="text-align:center;padding-top:100px;background-color:#FAFAFA;">'+items.join( "" )+'</tbody></table></div>');
                            $('.progress-bar').progressbar();
                        });

                    } 
                    else{
                        var bread = '<ol class="breadcrumb"><li><a href="#" onclick="loadStats({});">Stats</a></li>';

                        $.each( options, function( key, value ) {
                            var v = value;
                            if(key=="tags"){
                                var vv = (v+"").split(",");
                                v = 'Tagged';
                                $.each(vv, function(ke, val){
                                    v+=' <span class="label label-default">'+val+'</span>';
                                });
                            }
                            if((key=="description")||(key=="terms")){
                                var vv = (v+"").split(",");
                                v = 'With terms';
                                $.each(vv, function(ke, val){
                                    v+=' <span class="label label-default">'+val+'</span>';
                                });
                            }
                            if((key=="dategte")||(key=="datelt")){
                                var vv = (v+"").split(",");
                                if(key=="dategte"){
                                    v = 'Starting on';
                                }
                                else{
                                    v = 'Before';
                                }
                                $.each(vv, function(ke, val){
                                    val = val.split(" ");
                                    valt = val[1].split(":");
                                    if(valt[0]<10){
                                        valt[0] ="0"+valt[0];
                                    }
                                    if(valt[1]<10){
                                        valt[1] ="0"+valt[1];
                                    }
                                    vald = val[0].split("-");
                                    val = vald[2]+"-"+meses[vald[1]-1]+"-"+vald[0]+" "+valt[0]+":"+valt[1];
                                    v+=' <span class="label label-default">'+val+'</span>';
                                });
                            }
                            if((key=="durationgte")||(key=="durationlt")){
                                if(key=="durationgte"){
                                    v = '<span class="label label-default">'+pTime(value)+'</span> or higher';
                                }
                                else{
                                    v = '<span class="label label-default">'+pTime(value)+'</span> or lower';
                                }
                            }
                            if(key=="mood"){
                                var vv = (v+"").split(",");
                                v = 'With mood';
                                $.each(vv, function(ke, val){
                                    v+=' <span class="label label-default">'+moods[val-1][0].toUpperCase()+moods[val-1].slice(1)+'</span>';
                                });
                            }

                            bread+='<li><a href="#" onclick="loadStats({'+key+':\''+value+'\'});">'+v+'</a></li>';
                        });
                        $("#stats").html(bread+'</ol><div class="alert alert-default"><h1>No results found matching the selected criteria.</h1></div>');

                    }

                    $("#bot").addClass("visible");
                    $("body").addClass("padd");

                    $('.progress-bar').progressbar();

                    $("#csholder > .bootstrap-tagsinput > input").focus();
                    $("#csholder > .bootstrap-tagsinput > input").attr("placeholder",$("#combinedsearch").attr("placeholder"));

                  })
                  .fail(function( jqxhr, textStatus, error ) {
                    var err = textStatus + ", " + error;
                    console.log( "Request Failed: " + err );
                });
                
            }

            // Load stats
            $("#togStats").on("click", function(){
                curoptions = {};
                loadStats({});
                $("#combinedsearch").val("");
                $("#combinedsearch").tagsinput('removeAll');
                $("#csholder > .bootstrap-tagsinput > input").val("");
            });

            $("#toggleTrack").on("click", function(){
                if(tracking==true){
                    $("#toggleTrack > span").html("Resume tracking");
                    $("#toggleTrack").removeClass("tracking");
                    $("#finetune").collapse('show');
                    tracking = false;
                }
                else{
                    starttime = new Date();
                    timer = setTimeout(updateTrack, 500);
                    $("#toggleTrack span").html("Stop tracking");
                    $("#toggleTrack").addClass("tracking");
                    $("#finetune").collapse('hide');
                    tracking = true;
                }
            });

            function updateTrack(){
                var end = new Date();
                var rng = Math.floor((end - starttime)/1000);

                var text = "";

                var h, m, s;
                    h = '0' + Math.floor(rng/3600);
                    m = '0' + Math.floor((rng/60)%60);
                    s = '0' + Math.floor(rng%60);

                if(h!="00"){
                    text+=h.substr(-2)+":";
                }
                else{
                    //text+="00:";
                }
                if(m!="00"){
                    text+=m.substr(-2)+":";
                }
                else{
                    text+="00:";
                }
                text+=s.substr(-2);

                instance_duration.ionRangeSlider("update", {from:m});
                $("#toggleTrack > p").html(text);
                if(tracking==true){
                    timer = setTimeout(updateTrack, 500);
                }
            }

            // Attach a submit handler to the form
            $( "#addform" ).submit(function( event ) {
             
              // Stop form from submitting normally
              event.preventDefault();

              $("#smt").addClass("disabled");
                          
              var $form = $( this ),
              datetime = $form.find( "input[name='datetime']" ).val(),
              duration = $form.find( "input[name='duration']" ).val(),

              description = $form.find( "textarea[name='description']" ).val(),
              mood = $form.find( "select[name='mood']" ).val(),
              tags = $form.find( "input[name='tags']" ).val(),
              url = $form.attr( "action" );

              if((datetime)&&(duration)){

                // Send the data using post
                $.post( url, $form.serialize() )

                    .done(function( data ) {
                        $("body").addClass("success");
                        instance_datetime.datetimepicker('update', new Date());
                        instance_duration.ionRangeSlider("update", {from:0});

                        $form.find( "textarea[name='description']" ).val("");
                        $form.find( "select[name='mood']" ).selectpicker('val','1');
                        $form.find( "input[name='tags']" ).val("");
                        $('#tags').tagsinput('removeAll');
                    });
                    
              }

              ///$("#toggleTrack > p").html("");
              //$("#toggleTrack > span").html("Start tracking");
              $("#smt").removeClass("disabled");
            });


            $("#csholder > .bootstrap-tagsinput > input").bind('input keyup', function(){
                var $this = $(this);
                var delay = 200; // 0.2 seconds delay after last input

                clearTimeout($this.data('timer'));
                $this.data('timer', setTimeout(function(){

                    $this.removeData('timer');
                    if($this.val()!=""){
                        if(($("#combinedsearch").val()=="")||($("#combinedsearch").val()==$this.val())){
                            loadStats({terms:$this.val()});
                        }
                        else{
                            loadStats({terms:$("#combinedsearch").val()+","+$this.val()});
                        }
                    }
                    else{
                        loadStats({terms:$("#combinedsearch").val()});
                    }
                }, delay));
            });

            function searchtag(value){
                loadStats({tags:value});
            }

            function searchmood(value){
                loadStats({mood:value});
            }

            function searchduration(value){
                loadStats({duration:value});
            }

            var _gaq=[['_setAccount','UA-XXXXX-X'],['_trackPageview']];
            (function(d,t){var g=d.createElement(t),s=d.getElementsByTagName(t)[0];
            g.src='//www.google-analytics.com/ga.js';
            s.parentNode.insertBefore(g,s)}(document,'script'));
        </script>
    </body>
</html>