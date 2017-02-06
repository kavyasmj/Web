<!DOCTYPE HTML>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Stock Search</title>
<style>
    .container{
        text-align: center;
    }    
    body{
        font-family: Times New Roman, sans-serif;  
        font-size: 16px;
    }
    form{
        padding: 10px;
        border: 1px solid lightgray;
        background-color:whitesmoke;
        width: 400px;
        height: 150px;
        margin: auto;              
    }
    .input{
       padding-top: 10px;
    }
    h1{
        font-size: 30px;
        font-weight:lighter;
        font-style: italic;
        margin: 0px;
    }
    hr{
        color: whitesmoke;
    }
    #search, #clear{
        background-color:white;
        border-radius: .3em;
        border: 1px solid lightgray;
        font-size: 12px;        
    }
    #search{
        margin-left:55px;
    }
    .buttons{
        padding-top: 5px;
        padding-bottom: 5px;
    }
    #label{
        text-align: left;
    }
    table{
        background-color:#fafafa;
        margin: 10px auto;
        text-align: left;
        border-collapse: collapse;
    }
    table, th, td {
        border: 1px solid lightgray;
    }
    th{
        background-color:#f2f2f2;
    }
    td,th{    
        padding: 5px 10px 5px 2px;
    } 
    #json{
        width: 600px;
    }
    #json th{ 
        width: 280px;
    }
    #json td{ 
        width: 280px;
        text-align: center;
    }
    #error{
        text-align: center;
        border: 1px solid lightgray;
        background-color:#fafafa;
        width: 550px;
        height: 23px;
        margin: 10px 0px 0px 400px;
    }
    p{
       margin:0px;
    }
    img{
        height: 14px;
        width: 12px;
        padding-left: 2px;
    }
</style>
</head>
<body>
<div class="container">
<form method="post" id="stockSrch" action="stock.php" >
    <h1>Stock Search</h1>
    <hr/>
    <div class="input">
        <div id="label">
        <label for="sym">Company Name or Symbol:</label>
        <?php 
            if(isset($_POST['Clear'])){
                $input = '';
            }
            elseif(isset($_POST["sym"])) {
                $input = $_POST["sym"];
            }
            elseif(isset($_GET["osym"])){
                $input = $_GET["osym"];
            }
            else{
                $input = '';
            }
        ?>
        <input type="text" name="sym" id="sym" value="<?php echo $input ?>" required/> </br>
        </div>
        <div class="buttons">
            <input type="submit" name="Submit" value="Search" id="search" /> 
            <input type="submit" name="Clear" value="Clear" id="clear";/> </br>
        </div>
        <a href="http://www.markit.com/product/markit-on-demand">Powered by Markit on Demand</a></br>
    </div>
</form>   

<?php
 
if(isset($_POST['Submit'])){
    
     if(isset($_POST["sym"])) { 
        $sym = $_POST["sym"];
        $html = "";
        $url = "http://dev.markitondemand.com/MODApis/Api/v2/Lookup/xml?input=" . $sym;        
        
        $xml = simplexml_load_file($url);
        if(!isset($xml) || empty($xml) || is_null($xml) ){
            $html .="<div id='error'><p>No records have been found</p></div>"; 
            echo $html;
            exit;
        }
        $result_arr = $xml->LookupResult;
        $pre_link = "<a href='stock.php?osym=" . $sym . "&symbol=";
        $post_link = "';>More info</a>";
        $html .= "<table id='lookup'><tr><th>Name</th><th>Symbol</th><th>Exchange</th><th>Details</th></tr>";
        
        foreach ( $result_arr as $val){
            $symb = $val->Symbol;
            $name = $val->Name;
            $exch = $val->Exchange;
            $link = $pre_link . $val->Symbol . $post_link;            
            $html .= "<tr><td>$name</td><td>$symb</td><td>$exch</td><td>$link</td></tr>";
            
        }
        $html .= "</table>";
        echo $html;  
    }      
}
               
//start of info retrival logic
    if(isset($_GET["symbol"])) { 
        
        $quote_url = 'http://dev.markitondemand.com/MODApis/Api/v2/Quote/json?symbol=' . $_GET["symbol"];   
        $data = file_get_contents($quote_url); 
        $content = json_decode($data, true);

        $stat = strtoupper($content['Status']); 

        if(strpos($stat, 'FAILURE') !== false){
            $html_stk ="<div id='error'><p>There is no stock information available</p></div>"; 
            echo $html_stk;
            unset($html_stk);
            exit;
        }
        
//        date_default_timezone_set('America/Los_Angeles');  
        date_default_timezone_set('UTC');   
        $nam = $content['Name'];
        $symbol = $content['Symbol'];
        $lp = $content['LastPrice'];
        $ch = number_format($content['Change'],2);
        $cp = number_format($content['ChangePercent'],2);
        
        $var = gmdate('Y-m-d H:i:s', strtotime($content['Timestamp']));
        $dt = new DateTime($var);
        $tz = new DateTimeZone('America/New_York'); 
        $dt->setTimezone($tz);
        $ts = $dt->format('Y-m-d h:i A') . ' EST';

//      $ts = gmdate('Y-m-d h:i A', strtotime($content['Timestamp'])) .' PST';
        $mc = $content['MarketCap'];
        if($content['MarketCap'] < 10000000){
             $mc = number_format(($content['MarketCap']/1000000),2) .' M';
        }
        else{
            $mc = number_format(($content['MarketCap']/1000000000),2) .' B'; 
        }
       
        $vol = number_format($content['Volume'],0,".",",");
        $cytd = number_format(($content['LastPrice'] - $content['ChangeYTD']),2);
        $cptyd = number_format(($content['ChangePercentYTD']),2);
        $high = $content['High'];
        $high = $content['Low'];
        $open = $content['Open'];

        $link_green_arrow = "<img src='http://cs-server.usc.edu:45678/hw/hw6/images/Green_Arrow_Up.png'>";
        $link_red_arrow = "<img src='http://cs-server.usc.edu:45678/hw/hw6/images/Red_Arrow_Down.png'>";

        $html_stk = "<table id='json'><tr><th>Name</th><td>$nam</td></tr>";
        $html_stk .= "<tr><th>Symbol</th><td>$symbol</td></tr>";
        $html_stk .= "<tr><th>Last Price</th><td>$lp</td></tr>";

        ( $ch < 0 ? ($ch = $ch . $link_red_arrow) :  ($ch > 0 ? ( $ch = $ch . $link_green_arrow) : '') );
        $html_stk .= "<tr><th>Change</th><td>$ch</td></tr>"; 

        ( $cp < 0 ? ($cp = $cp . '%' .$link_red_arrow ) : ($cp > 0 ? ( $cp = $cp . '%' . $link_green_arrow ) : $cp = $cp . '%') );
        $html_stk .= "<tr><th>Change Percent</th><td>$cp</td></tr>"; 

        $html_stk .= "<tr><th>Timestamp</th><td>$ts</td></tr>";
        $html_stk .= "<tr><th>Market Cap</th><td>$mc</td></tr>";
        $html_stk .= "<tr><th>Volume</th><td>$vol</td></tr>";

        ( $cytd < 0 ? ($cytd = "(" . $cytd . ")" .  $link_red_arrow) :  ($cytd > 0 ? ( $cytd = $cytd . $link_green_arrow) : '') );    
        $html_stk .= "<tr><th>Change YTD</th><td>$cytd</td></tr>";

        ( $cptyd < 0 ? ($cptyd = $cptyd . '%' . $link_red_arrow) :  ($cptyd > 0 ? ( $cptyd = $cptyd . '%' . $link_green_arrow) : $cptyd = $cptyd . '%') );
        $html_stk .= "<tr><th>Change Percent YTD</th><td>$cptyd</td></tr>"; 

        $html_stk .= "<tr><th>High</th><td>$high</td></tr>";
        $html_stk .= "<tr><th>Low</th><td>$high</td></tr>";
        $html_stk .= "<tr><th>Open</th><td>$open</td></tr>";

        $html_stk .= "</table>";
        echo $html_stk;    
    }
?>  
</div>
<noscript>
</noscript>
</body>
</html>