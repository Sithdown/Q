var appoptions = {};
var curoptions = {};
appoptions.lat = 0;
appoptions.lon = 0;
appoptions.starttime = 0;
appoptions.tracking = false;
appoptions.maxduration = 16*60;
appoptions.appid = "1a8418253428a64beb61481ae76af331"; //get yours at http://openweathermap.org/login

var monthsArray = ["Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"];
var icoMoods = ["fa-heart-o text-info","fa-smile-o text-success","fa-meh-o text-warning","fa-frown-o text-danger","fa-times text-danger"];

/* -----------------------------------------------------------------------------------------------------

// TEMPLATES

// ---------------------------------------------------------------------------------------------------*/

var temp_breadlink = Handlebars.compile($("#template-breadlink").html());
var temp_row = Handlebars.compile($("#template-row").html());
var temp_thead = Handlebars.compile($("#template-thead").html());
var temp_pbar_v = Handlebars.compile($("#template-pbar-v").html());

/* -----------------------------------------------------------------------------------------------------

// UTILS

// ---------------------------------------------------------------------------------------------------*/

/**
 * Description
 * @method partInArray
 * @param {String} text
 * @param {Array} array
 * @return UnaryExpression
 */
function partInArray(text,array){
    for ( var i = 0, length = array.length; i < length; i++ ) {
        if ( typeof array[i] === 'string' && text.indexOf(array[i]) > -1 ) {
            return i;
        }
    }
    return -1; 
}

/**
 * Description
 * @method hintTerms
 * @param {String} desc
 * @param {Array} terms
 * @return desc
 */
function hintTerms(desc,terms){

    terms = (""+terms).split(",");
    $.each(terms,function(key, val){

        if(val.indexOf("href") == -1){

            /**
             * Description
             * @method create_hint
             * @param {} term
             * @return BinaryExpression
             */
            var create_hint = function (term) {
                return "<span class='hint'>"+term+"</span>";
            };
            var re = new RegExp(val, 'ig');
            desc = desc.replace(re, create_hint(val));
        }
    });
 
    return desc;  
}

/**
 * Description
 * @method pText
 * @param {String} str
 * @return str
 */
function pText(str) {
    /**
     * Description
     * @method create_link
     * @param {String} url
     * @param {String} text
     * @return CallExpression
     */
    var create_link = function (url, text,title) {
        var link = $("<a>", {
            text: text,
            title: title,
            href: url,
            onmouseover: 'showThumbnail(this)',
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
        return create_link("http://twitter.com/" + s.replace('@', '',true), s, s);
    });
 
    // parse hashtags
    str = str.replace(/[#]+[áéíóúÁÉÍÓÚñÑA-Za-z0-9_]+/g, function (s) {
        return create_link("https://twitter.com/search?q=" + s.replace('#', ''), s);
    });
 
    return str;
};

/**
 * Description
 * @method pTime
 * @param {Int} minutes
 * @return r
 */
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

/**
 * Description
 * @method showThumbnail
 * @param {Object} e
 * @return 
 */
function showThumbnail(e){
    var c = '<img src="http://free.pagepeeker.com/v2/thumbs.php?size=m&url='+$( e ).attr('href')+'">';
    if(($(e).attr('title')!="")&&($(e).attr('title')!=undefined)){
        c = '<img style="margin:auto;text-align:center;width:100%" src="http://twitter.com/api/users/profile_image?screen_name='+$(e).attr('title')+'&size=bigger">';
    }
    $( e ).popover({
        container: '#stats',
        html: true,
        title: '',
        trigger: 'hover',
        content: c
    });
    $( e ).popover('show');
}


/**
 * Description
 * @method createBreadCrumb
 * @param {Object} options
 * @param {Object} moods
 * @return 
 */
function createBreadCrumb(options,moods){

    bread='<ol class="breadcrumb"><li><a href="#" onclick="reloadStats();">Stats</a></li>';

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
        if((key=="dategte")||(key=="datelte")){
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
                val = vald[2]+"-"+monthsArray[vald[1]-1]+"-"+vald[0]+" "+valt[0]+":"+valt[1];
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
            });
        }

        var html = temp_breadlink({key:key,value:value,tag:v});
        bread+=html;

    });

    bread+='</ol>';

    return bread;
}

function createBarInsight(js){
    /*
        <div style="clear:both;height: 300px;background-color: white;margin-bottom: 20px;"><div class="progress" style="
        height: 48px;
    ">
          <div class="progress-bar progress-bar-danger" style="width: 35%">
            <span class="sr-only">35% Complete (success)</span>
          </div>
          <div class="progress-bar progress-bar-danger" style="width: 20%">
            <span class="sr-only">20% Complete (warning)</span>
          </div>
          <div class="progress-bar progress-bar-danger" style="width: 10%">
            <span class="sr-only">10% Complete (danger)</span>
          </div>
      </div>
      <div style="width:35%;float: left;padding: 10px;overflow-wrap: break-word;"></div>
      <div style="width:20%;float: left;padding: 10px;overflow-wrap: break-word;"></div>
      <div style="width:10%;float: left;padding: 10px;overflow-wrap: break-word;overflow: auto;"></div>

    </div>
    */

    var brs = "<div class='progress' style='height: 48px;'>";
    var brt = "";
    var j = 0;

    $.each(js,function(key,val){
       brs+='<div class="progress-bar progress-bar-danger" style="width: '+parseInt(val["duration"])/(16*60/100)+'%"></div>';
    });

    t = brs+"</div>"+brt;


    return "<div style='clear:both;height:300px;background-color:white;margin-bottom:20px;'>"+t+'<canvas id="myChart" width="200" height="200"></canvas>'+"</div>";
}

/**
 * Description
 * @method createBarGraph
 * @param {Object} options
 * @param {Object} moods
 * @return 
 */
function createBarGraph(js){

    console.log(js);

    var brs = "";
    var j = 0;
    for (var i = 0; i < 31; i++) {

        if(js[j]!==undefined){
            if((js[j]["monthday"]-1)==i){

                var c = "";
                if(js[j]["monthday"]%5==0){
                    c+="number";
                }
                if(js[j]["monthday"]>9){
                    c+=" double";
                }

                brs+= temp_pbar_v({c:c,monthday:js[j]['monthday'],title:pTime(js[j]['duration_total']),day:js[j]["date"],percent:(js[j]['duration_total']/(1440-480)*100)});

                j+=1;
            }
            else{
                brs+= temp_pbar_v({title:'',percent:0});
            }
        }
        else{
            brs+= temp_pbar_v({title:'',percent:0});
        }
    };
    return "<div class='barholder'>"+brs+"</div>";

}

/**
 * Description
 * @method createResumeInfo
 * @param {Object} options
 * @param {Object} moods
 * @return 
 */
function createResumeInfo(js){

    var brs = "";
    var j = 0;

    var days = [0,0,0,0,0,0,0];
    var total = 0;

    function sumWeekDayTime(date, time){
        var k = new Date(date);
        days[k.getDay()] += parseInt(time);
    }

    var day = [];
    for (var i = 0; i < 31; i++) {

        if(js[j]!==undefined){
            if((js[j]["monthday"]-1)==i){

                sumWeekDayTime(js[j]['date'],js[j]['duration_total']);
                total+=parseInt(js[j]['duration_total']);

                j+=1;
            }
        }
    };
    return "<div class='table-responsive'><table class='col-md-4 table table-striped' style='text-align:center;padding-top:100px;background-color:#FAFAFA;'><thead><tr><td><h2>Monday</h2></td><td><h2>Tuesday</h2></td><td><h2>Wednesday</h2></td><td><h2>Thursday</h2></td><td><h2>Friday</h2></td><td><h2>Saturday</h2></td><td><h2>Sunday</h2></td></tr></thead><tbody><tr><td>"+pTime(days[1])+"</td><td>"+pTime(days[2])+"</td><td>"+pTime(days[3])+"</td><td>"+pTime(days[4])+"</td><td>"+pTime(days[5])+"</td><td>"+pTime(days[6])+"</td><td>"+pTime(days[0])+"</td></tr><tr><td>"+Math.round(days[1]/(1440-480)*25)+"%</td><td>"+Math.round(days[2]/(1440-480)*25)+"%</td><td>"+Math.round(days[3]/(1440-480)*25)+"%</td><td>"+Math.round(days[4]/(1440-480)*25)+"%</td><td>"+Math.round(days[5]/(1440-480)*25)+"%</td><td>"+Math.round(days[6]/(1440-480)*25)+"%</td><td>"+Math.round(days[0]/(1440-480)*25)+"%</td></tr></tbody><thead><tr><td colspan='7'><h2>"+pTime(total)+" ("+Math.round((total/(1440-480)*100)/31)+"%)</h2></td></tr></thead></table></div>";

}

/**
 * Description
 * @method createResultsTable
 * @param {Object} json
 * @param {Object} options
 * @param {String} args
 * @return 
 */
function createResultsTable(json,options,args){
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

                t+='<span onclick="addSearchTerm(\''+val.trim()+'\')" class="clickable label '+lb+'">'+val.trim()+'</span>';
            });

            var dt = val["datetime"].split(" ");
            var date = dt[0];
            date = date.slice(5).split("-");
            var day = date[1];
            var mn = monthsArray[date[0]-1];
            date = date[1];
            var time = dt[1];

            var icoMood = "<i onclick='searchMood("+val["mood"]+")' class='clickable fa "+icoMoods[val["mood"]-1]+"'></i>";

            if(curday!=day){
                var prev = "";
                if(curday!=0){
                    var prev = "</tbody></table><table class='col-md-4 table table-striped' style='text-align:center;background-color:#FAFAFA;'>";
                }

                var html = temp_thead({prev:prev,day:day,mn:mn});
                items.push(html);

                curday = day;
            }

            val["description"] = pText(val["description"]);
            val["cleantime"] = pTime(val["duration"]);
            val["cleanmood"] = moods[val["mood"]-1][0].toUpperCase()+moods[val["mood"]-1].slice(1);
            val["t"] = t;

            if(!$.isEmptyObject(options["terms"])){
                val["description"] = hintTerms(val["description"],options["terms"]);
            }
            if(!$.isEmptyObject(options["description"])){
                val["description"] = hintTerms(val["description"],options["description"]);
            }

            var html = temp_row({val:val,icoMood:icoMood});
            items.push(html);

        });

        if(args.indexOf("date=")!==-1){
            var ag = "list.php?"+args;
        }
        else{
            var ag = "list.php?daytotals=true&"+args;  
        }

        var daytotals = $.getJSON( ag, { } )
        .done(function( js ) {

            var xtra = '';

            var bread = createBreadCrumb(options,moods);
            if(args.indexOf("date=")!==-1){
                var bars = createBarInsight(js);
            }
            else{
                var bars = createBarGraph(js);
                xtra = createResumeInfo(js);
            }

            $("#stats").html(bread+bars+xtra+'<div class="table-responsive"><table class="col-md-4 table table-striped" style="text-align:center;padding-top:100px;background-color:#FAFAFA;">'+items.join( "" )+'</tbody></table></div>');
            $('.progress-bar').progressbar();
            $('.progress-bar').tooltip({container:'body',placement:'bottom'});
            $('td').tooltip({container:'#stats',placement:'bottom'});

            if(args.indexOf("date=")!=-1){
                //Get context with jQuery - using jQuery's .get() method.
                var ctx = $("#myChart").get(0).getContext("2d");
                //This will get the first returned node in the jQuery collection.
                var k = []
                $.each(js,function(key,val){
                    k.push({value: parseInt(val["duration"]),color:"rgba(210, 50, 45,"+parseInt(val["duration"])/300+")"});
                })
                var myNewChart = new Chart(ctx).Doughnut(k,{});
            }
        });

    } 
    else{

        var bread=createBreadCrumb(options,moods);
        
        $("#stats").html(bread+'<div class="alert alert-default"><h1>No results found matching the selected criteria.</h1></div>');

    }
}


/* -----------------------------------------------------------------------------------------------------

// INITS

// ---------------------------------------------------------------------------------------------------*/

appoptions.instance_searchdatetime = $('.search_datetime').datetimepicker({
    pickerPosition: 'top-left',
    language:  'en',
    weekStart: 1,
    todayBtn:  1,
    autoclose: 0,
    todayHighlight: 1,
    startView: 2,
    forceParse: 0,
    showMeridian: 0,
    viewSelect: 4
}).on('changeDate', function(ev){

    if(ev.date==null){
        reloadStats();
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

        loadStats({datelte:dt});
    }
});

appoptions.instance_datetime = $('.form_datetime').datetimepicker({
    pickerPosition: 'bottom-left',
    language:  'en',
    weekStart: 1,
    todayBtn:  1,
    autoclose: 1,
    todayHighlight: 1,
    startView: 2,
    forceParse: 0,
    showMeridian: 0
});

$('.selectpicker').selectpicker({width:"100%"});

var cs = $('#combinedsearch');
cs.tagsinput({
    confirmKeys: [13, 44, 188],
    freeinput: true
});

cs.change(function(){
    if(!appoptions.onhold){
        if($(this).val()!=""){
            loadStats({terms:$(this).val()});
        }
        else{
            reloadStats();
        }
    }
});

/**
 * Description
 * @method reloadStats
 * @return 
 */
function reloadStats(){
    appoptions.onhold = true;
    //$("#combinedsearch").val("");
    $("#combinedsearch").tagsinput('removeAll');
    $("#csholder > .bootstrap-tagsinput > input").val("");
    curoptions={};
    loadStatsReal(curoptions);
    appoptions.onhold = false;
}

/**
 * Description
 * @method loadStats
 * @param {} options
 * @return 
 */
function loadStats(options){

    if($.isEmptyObject(options)){
        loadStatsReal({});
    }
    else{
        var merge = $.extend(curoptions, options);
        loadStatsReal(merge);
    }
}

/**
 * Description
 * @method loadStatsReal
 * @param {} options
 * @return 
 */
function loadStatsReal(options){

    var args = "";
    var searchurls = false;
    var searchtwitter = false;

    args+="userid="+userid;

    if(!$.isEmptyObject(options)){
        //args+="&"
        $.each( options, function( key, value ) {
            if(args!="&"){
                args+="&";
            }
            if(key=="terms"){ //search commands
                var c = value.split(",");
                for (var i = 0; i < c.length; i++) {
                    if(c[i].indexOf(":url")==0){
                        value=value.replace(":url","");
                        value=value.replace(",,",",");
                        searchurls=true;
                        break;
                    }
                    if(c[i].indexOf(":twitter")==0){
                        value=value.replace(":twitter","");
                        value=value.replace(",,",",");
                        searchtwitter=true;
                        break;
                    }
                };
            }
            if(value!=""){
                args+=key+"="+value;
            }
        });

        if(searchurls==true){
            args+="&containsurls=true";
        }
        if(searchtwitter==true){
            args+="&containstwitter=true";
        }
        if(args.indexOf("&&")!==-1){
            args= args.replace("&&","&");
        }
        if(args.indexOf(",&")!==-1){
            args= args.replace(",&","&");
        }
        if(args.indexOf("=,")!==-1){
            args= args.replace("=,","=");
        }

    }

    //$("#stats").html('<div class="col-md-1 col-md-offset-5" style="text-align:center;padding-top:100px;"><i class="fa fa-refresh fa-5x fa-spin"></i></div>');

    console.log(args);

    $.getJSON( "list.php?"+args, { } )
      .done(function( json ) {

        console.log(json);

        createResultsTable(json,options,args);

        $("#bot").addClass("visible");
        $("body").addClass("padd");

        $(".tooltip").remove();

        $('.progress-bar').progressbar();

        $("#csholder > .bootstrap-tagsinput > input").focus();
        $("#csholder > .bootstrap-tagsinput > input").attr("placeholder",$("#combinedsearch").attr("placeholder"));

      })
      .fail(function( jqxhr, textStatus, error ) {
        var err = textStatus + ", " + error;
        console.log( "Request Failed: " + err );
    });
    
}





/**
 * Description
 * @method addSearchTerm
 * @param {} value
 * @return 
 */
function addSearchTerm(value){
    cs.tagsinput('add', value);
    loadStats({terms:curoptions.terms});
}

/**
 * Description
 * @method searchMood
 * @param {} value
 * @return 
 */
function searchMood(value){
    loadStats({mood:value});
}

/**
 * Description
 * @method searchDuration
 * @param {} value
 * @return 
 */
function searchDuration(value){
    loadStats({duration:value});
}

function searchDay(value){
    loadStats({date:value});
}


/* -----------------------------------------------------------------------------------------------------

// BINDINGS

// ---------------------------------------------------------------------------------------------------*/

$("#togStats").on("click", function(){
    reloadStats();
});

$("#toggleTrack").on("click", function(){

  // Stop form from submitting normally
  event.preventDefault();

    if(appoptions.tracking==true){
        $("#toggleTrack > span").html("Start tracking");
        $("#toggleTrack").removeClass("tracking");
        $("#finetune").collapse('show');
        appoptions.tracking = false;
    }
    else{
        appoptions.starttime = new Date();
        appoptions.instance_datetime.datetimepicker('update', new Date());
        timer = setTimeout(updateTrack, 500);
        $("#toggleTrack span").html("Stop tracking");
        $("#toggleTrack").addClass("tracking");
        $("#finetune").collapse('hide');
        appoptions.tracking = true;
    }
});

$("#searchmood").change(function(){
    loadStats({mood:$("#searchmood").val()});
})

$("#csholder > .bootstrap-tagsinput > input").bind('input keyup', function(){
    var $this = $(this);
    var delay = 200; // 0.2 seconds delay after last input

    clearTimeout($this.data('timer'));
    $this.data('timer', setTimeout(function(){

        $this.removeData('timer');
        if(!appoptions.onhold){
            var t = "";
            t+=$("#combinedsearch").val();
            if($this.val().length>2){
                t+=($("#combinedsearch").val()!="")? "," : "";
                t+= $this.val();
            }
            if(t!=""){
                loadStats({terms:t});
            }
        }
    }, delay));
});

$( window ).load(function(){
    reloadStats();
});