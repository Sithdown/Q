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
                                    <a class="btn btn-link btn-block" style="color:#aaa;" data-toggle="collapse" data-target="#finetune">Fine tune</a>
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

                                <div class="form-group col-md-1">
                                    <select class="form-control selectpicker" style="display: none;" name="searchmood" id="searchmood">
                                      <?php
                                      $m = array("fa-heart-o text-info","fa-smile-o text-success","fa-meh-o text-warning","fa-frown-o text-danger","fa-times text-danger");
                                      foreach ($moods as $key => $value) {
                                        if(isset($m[$key])){
                                            $w = " data-icon='fa fa-2x fa-fw ".$m[$key]."'";
                                        }
                                        else{
                                            $w = "";
                                        }
                                      ?>
                                      <option<?php echo $w;?>  value="<?php echo $value["ID"];?>"><?php echo utf8_encode(ucfirst($value["name"]));?></option>
                                      <?php } ?>
                                    </select>
                                </div>
                            </form>
                        </div>
                    </nav>
                </div>
            </div>


        </div>

        <script src="//ajax.googleapis.com/ajax/libs/jquery/2.0.3/jquery.min.js"></script>
        <script>window.jQuery || document.write('<script src="js/vendor/jquery-2.0.3.min.js"><\/script>')</script>

        <script src="js/bootstrap.min.js"></script>
        <script src="js/bootstrap-datetimepicker.min.js" charset="UTF-8"></script>
        <script src="js/locales/bootstrap-datetimepicker.es.js" charset="UTF-8"></script>
        <script src="js/bootstrap-progressbar.min.js" charset="UTF-8"></script>
        <script src="js/bootstrap-select.min.js" charset="UTF-8"></script>
        <script src="js/bootstrap-tagsinput.min.js" charset="UTF-8"></script>
        <script src="js/typeahead.min.js" charset="UTF-8"></script>
        <script src="js/ion.rangeSlider.min.js" charset="UTF-8"></script>
        <script src="js/handlebars.js" charset="UTF-8"></script>

        <script>
            var moods = [];
            <?php
            foreach ($moods as $key => $value) {
                echo "moods[".$key."] = '".utf8_encode($value["name"])."';\n";
            }
        ?>
        </script>

        <!--
        /* #####################################################################################################

        // TEMPLATES

        // ###################################################################################################*/
        -->
        <script id="template-row" type="text/x-handlebars-template">
            <tr id='log_{{val.id}}'>
                <td onclick='searchDuration({{val.duration}})' class='col-md-1 clickable' title='{{val.datetime}}' data-toggle='tooltip'>{{val.cleantime}}</td>
                <td class='col-md-7'>{{{val.description}}}</td>
                <td class='col-md-1' title='{{val.cleanmood}}' data-toggle='tooltip'>{{{icoMood}}}</td>
                <td class='col-md-3'>{{{val.t}}}</td>
            </tr>
        </script>

        <script id="template-breadlink" type="text/x-handlebars-template">
            <li>
                <a href="#" onclick="loadStats({ {{key}}:'{{value}}' });">{{{tag}}}</a>
            </li>
        </script>

        <script id="template-pbar-v" type="text/x-handlebars-template">
            <div class='progress vertical bottom'><div title='{{title}}' onclick="searchDay('{{day}}')" data-toggle='tooltip' class='progress-bar progress-bar-danger' aria-valuetransitiongoal='{{percent}}'></div></div>
        </script>

        <script id="template-thead" type="text/x-handlebars-template">
        {{{prev}}}
            <thead>
                <tr>
                    <td colspan='4' style='text-align:left;'>
                        <h2><i class='fa fa-calendar-o'></i> {{day}} {{mn}}</h2>
                    </td>
                </tr>
            </thead>
        <tbody>
        </script>

        <script src="js/main.js"></script>

    </body>
</html>