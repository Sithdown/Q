var appoptions = {};
var curoptions = {};
appoptions.lat = 0;
appoptions.lon = 0;
appoptions.starttime = 0;
appoptions.tracking = false;
appoptions.maxduration = 300;

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
 * @method getLocation
 * @return 
 */
function getLocation(){
    if (navigator.geolocation){
        navigator.geolocation.getCurrentPosition(getCoords);
    }
    else{
        return false;
    }
}

/**
 * Description
 * @method getCoords
 * @param {Object} pos
 * @return 
 */
function getCoords(pos){
    appoptions.lat = pos.coords.latitude;
    appoptions.lon = pos.coords.longitude;
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

/**
 * Description
 * @method createBarGraph
 * @param {Object} options
 * @param {Object} moods
 * @return 
 */
function createBarGraph(js){

    var brs = "";
    var j = 0;
    for (var i = 0; i < 31; i++) {

        if(js[j]!==undefined){
            if((js[j]["monthday"]-1)==i){

                brs+= temp_pbar_v({title:pTime(js[j]['duration_total']),percent:(js[j]['duration_total']/(1440-480)*100)});

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
    return "<div style=\'height:300px;\'>"+brs+"</div>";

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

        var daytotals = $.getJSON( "list.php?daytotals=true"+args, { } )
        .done(function( js ) {

            var bread = createBreadCrumb(options,moods);
            var bars = createBarGraph(js);

            $("#stats").html(bread+bars+'<div class="table-responsive"><table class="col-md-4 table table-striped" style="text-align:center;padding-top:100px;background-color:#FAFAFA;">'+items.join( "" )+'</tbody></table></div>');
            $('.progress-bar').progressbar();
            $('.progress-bar').tooltip({container:'body',placement:'bottom'});
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

appoptions.instance_duration = $("#duration").ionRangeSlider({
    min: 0,                        // min value
    max: appoptions.maxduration,   // max value
    type: 'single',                // slider type
    step: 5,                       // slider step
    //postfix: ' minutos',         // postfix text
    hasGrid: false,//true,         // enable grid
    hideMinMax: false,             // hide Min and Max fields
    hideFromTo: false,             // hide From and To fields
    prettify: true,                // separate large numbers with space, eg. 10 000
    /**
     * Description
     * @method onChange
     * @param {} obj
     * @return 
     */
    onChange: function(obj){        // function-callback, is called on every change
    },
    /**
     * Description
     * @method onFinish
     * @param {} obj
     * @return 
     */
    onFinish: function(obj){        // function-callback, is called once, after slider finished it's work
    }
});

$('.selectpicker').selectpicker({width:"100%"});

var tg = $('#tags');
tg.tagsinput({
    confirmKeys: [13, 44, 188],
    freeinput: true
});

tg.tagsinput('input').typeahead(
    {
    prefetch: 'list.php?getalltags'
}).bind('typeahead:selected', $.proxy(function (obj, datum) {  
    this.tagsinput('add', datum.value);
    this.tagsinput('input').typeahead('setQuery', '');
}, tg));

var cs = $('#combinedsearch');
cs.tagsinput({
    confirmKeys: [13, 44, 188],
    freeinput: true
});

cs.change(function(){
    if($(this).val()!=""){
        loadStats({terms:$(this).val()});
    }
    else{
        reloadStats();
    }
});


/**
 * Description
 * @method reloadStats
 * @return 
 */
function reloadStats(){
    curoptions={};
    loadStatsReal(curoptions);
}

/**
 * Description
 * @method loadStats
 * @param {} options
 * @return 
 */
function loadStats(options,add){

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
            args+="&containsurls";
        }
        if(searchtwitter==true){
            args+="&containstwitter";
        }
        if(args.indexOf("&&")==0){
            args=args.substring(2);
        }
        if(args.indexOf("&")==0){
            args=args.substring(1);
        }
        if(args.indexOf(",&")!==-1){
            args= args.replace(",&","&");
        }

    }
    console.log(args);

    $("#stats").html('<div class="col-md-1 col-md-offset-5" style="text-align:center;padding-top:100px;"><i class="fa fa-refresh fa-5x fa-spin"></i></div>');

    $.getJSON( "list.php?"+args, { } )
      .done(function( json ) {

        createResultsTable(json,options,args);

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

/**
 * Description
 * @method updateTrack
 * @return 
 */
function updateTrack(){
    var end = new Date();
    var rng = Math.floor((end - appoptions.starttime)/1000);

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

    appoptions.instance_duration.ionRangeSlider("update", {from:m});
    $("#toggleTrack > p").html(text);
    if(appoptions.tracking==true){
        timer = setTimeout(updateTrack, 500);
    }
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




/* -----------------------------------------------------------------------------------------------------

// BINDINGS

// ---------------------------------------------------------------------------------------------------*/

$("#togAdd").on("click", function(){
    $("#bot").removeClass("visible");
    $("body").removeClass("padd");
    $("#finetune").collapse('hide');
    $("#toggleTrack > p").html("");
});

$("#togStats").on("click", function(){
    curoptions = {};
    reloadStats();
    $("#combinedsearch").val("");
    $("#combinedsearch").tagsinput('removeAll');
    $("#csholder > .bootstrap-tagsinput > input").val("");
});

$("#toggleTrack").on("click", function(){
    if(appoptions.tracking==true){
        $("#toggleTrack > span").html("Resume tracking");
        $("#toggleTrack").removeClass("tracking");
        $("#finetune").collapse('show');
        appoptions.tracking = false;
    }
    else{
        appoptions.starttime = new Date();
        timer = setTimeout(updateTrack, 500);
        $("#toggleTrack span").html("Stop tracking");
        $("#toggleTrack").addClass("tracking");
        $("#finetune").collapse('hide');
        appoptions.tracking = true;
    }
});


$( "#smt" ).on("click", function( event ) {
 
  // Stop form from submitting normally
  event.preventDefault();

  $("#smt").addClass("disabled");
              
  var $form = $("#addform"),
  datetime = $form.find( "input[name='datetime']" ).val(),
  duration = $form.find( "input[name='duration']" ).val(),

  description = $form.find( "textarea[name='description']" ).val(),
  mood = $form.find( "select[name='mood']" ).val(),
  tags = $form.find( "input[name='tags']" ).val(),
  url = $form.attr( "action" );

  if((datetime)&&(duration!=0)){

    var cloudiness = 0;
    var wind = 0;
    var weather = 0;
    var sunrise = 0;
    var sunset = 0;
    var temperature = 0;
    var humidity = 0;

    //?q=Gijon
    $.getJSON( "http://api.openweathermap.org/data/2.5/weather?callback=?&lat="+appoptions.lat+"&lon="+appoptions.lon)
      .done(function(w) {
        console.log(w);
        cloudiness = w["clouds"]["all"]; //percentage
        wind = w["wind"]["speed"];
        weather = w["weather"][0]["id"];
        sunrise = w["sys"]["sunrise"];
        sunset = w["sys"]["sunset"];
        temperature = w["main"]["temp"];
        humidity = w["main"]["humidity"]; //percentage
      })
      .fail(function() {
        console.log( "error" );
      });

    // Send the data using post
    $.post( url, $form.serialize() )

        .done(function( data ) {
            $("body").addClass("success");
            appoptions.instance_datetime.datetimepicker('update', new Date());
            appoptions.instance_duration.ionRangeSlider("update", {from:0});

            $form.find( "textarea[name='description']" ).val("");
            $form.find( "select[name='mood']" ).selectpicker('val','1');
            $form.find( "input[name='tags']" ).val("");
            $('#tags').tagsinput('removeAll');
        });
        
  }

  $("#smt").removeClass("disabled");

});

$("#csholder > .bootstrap-tagsinput > input").bind('input keyup', function(){
    var $this = $(this);
    var delay = 200; // 0.2 seconds delay after last input

    clearTimeout($this.data('timer'));
    $this.data('timer', setTimeout(function(){

        $this.removeData('timer');
        if(($this.val()!="")&&($this.val().length>2)){
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

$( window ).load(function(){
    getLocation();
});