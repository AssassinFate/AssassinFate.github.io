<?php

require_once 'base.php';
require_once PROJECT_BASE . '/util/util.php';

////传递的参数3912
$surname_sid = getParam($_REQUEST,'sid');
$func = getParam($_REQUEST,'f','getgjj');
$user_sid = getParam($_REQUEST,'u');

$ret = array();
try{
    $ret = processRequest($user_sid,$func,$surname_sid);
}catch ( Exception $e ) {
    $ret ['e'] = $e->getMessage ();
}

if($func=='getNum')
{
    $outJson = json_encode ( $ret );
    echo ($outJson);
    exit;
}


function processRequest($user_sid,$func,$surname_sid){
    if($func=='getgjj'){
    
        ///通过$surname_sid取对应的公积金ID
        $userInfo = getDB()->GetRow("select * from sys_surname where sid=?",$surname_sid);
	
        if(!$userInfo){
            throw new Exception("未查询到对应公积金账号");
        }
        ///取对应的公积金
        $gjjInfo = getDB()->GetRow("select name,round(balance+fb_balance+once_balance,2) as balance,sid from sys_gjj where sid=?",$userInfo['gjj_sid']);
       
        if(!$gjjInfo){
            throw new Exception("公积金数据未更新！");
        }

        $output['user_sid']=$userInfo['user_sid'];
        $output['name']=decodeName($gjjInfo['name']);
        $balance=$output['balance']=$gjjInfo['balance'];
        /////取少于等于本人公积金的个数
        $output['fewer_num'] = getDB()->GetOneInt("select count(*) from sys_gjj where balance<?",$gjjInfo['balance']);
        /////取公积金的总个数
        $output['total_num'] = getDB()->GetOneInt("select count(*) from sys_gjj");
        ///判断占比
        $output['fewer_ratio']=round($output['fewer_num']/$output['total_num'],4)*100;
        if($output['fewer_ratio']>=50)
        {
            $rule=$output['rule']='打败了全国'.'<span id="text2">'.$output['fewer_ratio'].'</span>'.'%的用户，';
        }
        else
        {
            $fewer_ratio=100-$output['fewer_ratio'];
            $rule=$output['rule']='被全国'.'<span id="text2">'.$fewer_ratio.'</span>'.'%的用户打败，';
        }
        switch ($balance)
        {
            case $balance<3000:
                $output['level']=1;
                $output['title']='穷困潦倒';
                $output['share']='我的公积金余额是：'.$balance.'元,'.$rule.'被鉴定为：【穷困潦倒】， 求包养！';
                break;
            case $balance<7000 && $balance>=3000:
                $output['level']=2;
                $output['title']='一贫如洗';
                $output['share']='我的公积金余额是：'.$balance.'元,'.$rule.'被鉴定为：【一贫如洗】，衣不蔽体，食不果腹！';
                break;
            case $balance<10000 && $balance>=7000:
                $output['level']=3;
                $output['title']='家徒四壁';
                $output['share']='我的公积金余额是：'.$balance.'元,'.$rule.'被鉴定为：【家徒四壁】，仰人鼻息，三餐不继！';
                break;
            case $balance<20000 && $balance>=10000:
                $output['level']=4;
                $output['title']='衣食无忧';
                $output['share']='我的公积金余额是：'.$balance.'元,'.$rule.'被鉴定为：【衣食无忧】，苦尽甘来，应有尽有！';
                break;
            case $balance<30000 && $balance>=20000:
                $output['level']=5;
                $output['title']='财大气粗';
                $output['share']='我的公积金余额是：'.$balance.'元,'.$rule.'被鉴定为：【财大气粗】，鼻孔朝天，盛气凌人！';
                break;
            case $balance<50000 && $balance>=30000:
                $output['level']=6;
                $output['title']='豪门权贵';
                $output['share']='我的公积金余额是：'.$balance.'元,'.$rule.'被鉴定为：【豪门权贵】，夜夜笙歌，纸醉金迷！';
                break;
            case $balance<100000 && $balance>=50000:
                $output['level']=7;
                $output['title']='家财万贯';
                $output['share']='我的公积金余额是：'.$balance.'元,'.$rule.'被鉴定为：【家财万贯】，香车豪宅，醉生梦死！';
                break;
            case $balance<300000 && $balance>=100000:
                $output['level']=8;
                $output['title']='富甲一方';
                $output['share']='我的公积金余额是：'.$balance.'元,'.$rule.'被鉴定为：【富甲一方】，呼风唤雨，一手遮天！';
                break;
            case $balance>=300000:
                $output['level']=8;
                $output['title']='富可敌国';
                $output['share']='我的公积金余额是：'.$balance.'元,'.$rule.'被鉴定为：【富可敌国】，位极人臣，权倾朝野！';
                break;
            default:
                $output['level']=0;
                $output['title']='';
                $output['share']='我的公积金余额是：'.$balance.'元。';
                break;

        }
         
        ////抱大腿次数
        $follow_num = getDB()->GetOne("select follow_num from sys_follow where user_sid=?",$userInfo['user_sid']);
        if($follow_num=='')
       	{
       	 $follow_num=0;	
       	}
        $output['follow_num']=$follow_num;
        return array('gjj'=>$output);
    }
    elseif($func=='getNum')
    {
        //share.php?f=getNum&u=1421
        $num = getDB()->GetOneInt("select count(*) from sys_follow where user_sid=?",$user_sid);
        if($num>0)///有记录修改
        {
            $get_time=date('Y-m-d H:i:s',time());
            getDB()->Execute("update sys_follow set follow_num=follow_num+1,get_time=? where user_sid=?",array($get_time,$user_sid));
        }
        else ///无记录添加
        {
            getDB()->Execute("insert into sys_follow(user_sid,follow_num) values (?,?)",array($user_sid,1));

        }
        return true;
    }

}

function getParam($params,$key,$defaultValue=''){
    if(isset($params[$key])){
        return $params[$key];
    }
    return $defaultValue;
}
?>
<!DOCTYPE 5>
<html>

<head>
	<title>51公积金</title>
    <meta name="viewport" content="width=device-width, initial-scale=1,user-scalable=no"/>

    <link rel="stylesheet" href="css\index.css">
    <script type="text/javascript" src="js\jquery-2.1.3.js"></script>
    <meta charset="utf-8">
    <link rel="stylesheet" id="sc" type="text/css" href="css/index.css"/>

    <script type="text/javascript"> 
        var system = { 
            win: false, 
            mac: false, 
            xll: false,     
            ipad:false 
        };  
        var p = navigator.platform; 
        system.win = p.indexOf("Win") == 0; 
        system.mac = p.indexOf("Mac") == 0; 
        system.x11 = (p == "X11") || (p.indexOf("Linux") == 0); 
        system.ipad = (navigator.userAgent.match(/iPad/i) != null)?true:false; 
        window.onload=function(){ 
        var sc=document.getElementById("body");
        var main=document.getElementById("main");
        if (system.win || system.mac || system.xll) { 
            sc.setAttribute("style","height:1000px;width:700px;margin:0px auto; background-color: #FCFCFC;"); 
            main.setAttribute("style","width:100%;height:100%;background-color: #F0F0F0;margin:0 auto;");   
        }
        else  {
            sc.setAttribute("style","margin:0px; background-color: #F0F0F0;font-size:15px;"); 
        }
        }
    </script> 
</head>

<body  id="body">
    <div id="main">
    <div id="page1">
    <?php if(isset($ret ['e'])){ echo $ret['e'];} else{?>
	<div class="header">
		<img id="img1" src="img/shareBG.png"></img>
	</div>
    <div class="show">我的公积金余额是：<h id="number"><span id="numberint"></span>.<span id="numberfloat"></span></h> 元</div>
	<div class="main1" >
        <div id="main1_div">
		<img id="img2" src="img/shareBtn1.png">
        <br><div id="identify">鉴定一下</div></br>
        </div>
        <div id="load">
            <div class="money">
            <img id="money1" src="photo\amplify\1.png"></img>
            <img id="money2" src="photo\amplify\2.png"></img>
            <img id="money3" src="photo\amplify\3.png"></img>
            <img id="money4" src="photo\amplify\4.png"></img>
            <img id="money5" src="photo\amplify\5.png"></img>
            <img id="money6" src="photo\amplify\6.png"></img>
            <img id="money7" src="photo\amplify\7.png"></img>
            <img id="money8" src="photo\amplify\8.png"></img>
            </div>
        </div>
	</div>
    <div class="footer" id="footer">
    <div id="img5" style="background-image: url(photo/shareBtn2.png);background-size: 100% 100%;">
        <a href="http://zfgjj.jianbing.com/m/">查查我的公积金</a>
    </div>  
    </div>
    <div id="page2">
    <div id="page2header">
        <div id="back">BACK</div>
    </div>
		<div class="main2">
			<div class="describe">
				<div class="text">
                    <br>
                    <?php echo $ret['gjj']['rule'];?></br><br>被鉴定为“<span id="text4" style="color:#FF9700;"><?php echo $ret['gjj']['title'];?>”</span><!--更改-->
                </div>
				<div class="img">
                    <div id="img3"><img id="img4" src="img/rank/<?php echo $ret['gjj']['level'];?>.png"></img></div>
					<h id="imgdescribe"><?php echo $ret['gjj']['title'];?></h>
                    <div id="thank">+1</div>
				</div>
			</div>
            <div class="sum">
                <img id="ipoint" src="img/shareBtn3.png"></img>
                <div id="sum0"><span id="sum1">被抱了</span> <span id="opoint"><?php echo $ret['gjj']['follow_num'];?></span><span id="sum2"> 次大腿</span></div>
            </div>
        </div>
        <div id="zan">
            <div><img id="zanimg" src="img/shareLike.png"></img></div>
            <div id="zantext"><b>只能点一次哦~</b></div>
        </div>

        <div class="footer" id="footer2">
            <div id="img5" style="background-image: url(img/shareBtn2.png);background-size: 100% 100%;">
            <a href="http://zfgjj.jianbing.com/m/">查查我的公积金</a>
        </div>  
    </div>
    </div>
    </div>

<script type="text/javascript" src="/js/zepto.min.js"></script>
	<script type="text/javascript">
    var gjjnum = <?php echo $ret['gjj']['balance'];?>;
    var gjjnumint=Math.floor(gjjnum);
    var gjjnumfloat=gjjnum-gjjnumint;
    document.getElementById("numberint").innerHTML=gjjnumint;
    document.getElementById("numberfloat").innerHTML=(gjjnumfloat.toFixed(2))*100;

    var wocao = 0;
    var animateCurSumTime = 0;
    var handlerId;
    var beatson = <?php echo $ret['gjj']['fewer_ratio']; ?>;
        if(beatson-50<=0)
        {
            document.getElementById("ipoint").src="img/shareBtn4.png";
            document.getElementById("sum1").innerHTML = "被打赏 ";
            document.getElementById("sum2").innerHTML = " 次";
        }
        else{
            document.getElementById("ipoint").src="img/shareBtn3.png";
            document.getElementById("sum1").innerHTML = "被抱了 ";
            document.getElementById("sum2").innerHTML = " 次大腿";
        }
	var isClicked = false;
	var alreadyHuggedNum = <?php echo $ret['gjj']['follow_num'];?>;
	var u=<?php echo $userInfo['user_sid'];?>

    $("#page2header").click(function(){
        //$("#page2").slideToggle(2000);
        $("#back").hide(2000);
        $("#page2").animate({left:'-200%'},1000);
        $("#main2").animate({left:'300%'},1000,function(){
            $('#main1_div').fadeIn(function(){
                location.reload();
            });
        });
    });

	$("img2").click(function(){
        $('#main1_div').hide();

        $('#load').show();
        $('#money1').show();
        var frameInterval = 125;//~ 30 frames per second
        
        handlerId = setInterval(function(){
            var totalTime = 2 * 1000;   
            if(animateCurSumTime - totalTime >= 0){
                clearInterval(handlerId);
                $('#load').hide();
                $("#page2").animate({left:'0px'},1000);
                $("#main2").animate({left:'0px'},1000);
                $("#back").show(2000);
                return;
            }
            animateCurSumTime += 125;//~ 30 frames per second
            var range = [1, 2, 3, 4, 5, 6, 7, 8];
            var currentIndex = +wocao;
            var nextIndex = currentIndex + 1;
            var tmpStr = '';

            if(nextIndex === range.length){
                nextIndex = 0;
                wocao = 0;
            }else{
                wocao = +wocao;
                wocao += 1;
            }

            tmpStr = '#money' + range[currentIndex];
            $(tmpStr).hide();

            //show next
            tmpStr = "#money" + range[nextIndex];
            $(tmpStr).show();   
        }, frameInterval);
    });



	args = {f : 'getNum'};
	args['u']=<?php echo $ret['gjj']['user_sid'];?>;
	
	$('#ipoint').click(function ()
        {
            if(!!isClicked){
                $("#zan").fadeIn(500);
                $("#zan").click(function(){
                    $("#zan").fadeOut(300);
                });
            }else{
                $.ajax({
                url:'share.php'
                ,data:args
                ,success:function(data)
                	{
	                    if(!!data === true){
	                		alreadyHuggedNum += 1;
	                		 $('#opoint').html(alreadyHuggedNum);
	                	}else{
	                		alert("出错啦");
	                	}
                	}
                });
                isClicked = true;
                $('#opoint').html(alreadyHuggedNum+1);
                $("#thank").fadeTo("fast",0);
                $('#thank').animate({fontSize:'800%',bottom:'70px',opacity:1},400);
                $('#thank').animate({bottom:'80px',opacity:0},300);              
            }
        }
    );

	</script>
    <?php }?>
</body>
</html>
