<?php
    
    include_once($_SERVER['DOCUMENT_ROOT'].'/system/session.php');

    $REQUEST_METHOD = mb_strtoupper($_SERVER["REQUEST_METHOD"], 'UTF-8');

    if( $REQUEST_METHOD == 'POST' ){
        $lang = $SysFunctions->correctName($_POST['lang']);
        $formName = $SysFunctions->correctName($_POST['formName']); //die();
        
        
        // Нагрузка на сервер
        $serverLoadFormsNames = array("font_download","create_form","xls_upload","toExcel");
        if(in_array($formName, $serverLoadFormsNames)){
            include_once($_SERVER['DOCUMENT_ROOT'].'/system/db.php');
            
            $serverLoadResult = 0;
            
            $sql= "SELECT `server_load` FROM `pdfs_users_access` WHERE `user_id`='".(int)$_SESS['userId']."'";

            $sqlQuery = mysql_query($sql);

            $sqlServerLoad = mysql_result($sqlQuery, 0);

            $sqlServerLoad = unserialize($sqlServerLoad);
            if( $sqlServerLoad['date'] != $_ServerLoadDay ){
                $_ServerLoad['date'] = $_ServerLoadDay;
                $_ServerLoad['fonts'] = 0;        
                $_ServerLoad['create'] = 0;        
                $_ServerLoad['save'] = 0;        
                $_ServerLoad['gen'] = 0;          
                $_ServerLoad['total'] = 0;        
            }else{
                $_ServerLoad = $sqlServerLoad;
            }
            //$_ServerLoad['total'] = 10; // Заглушка!
            if( $_ServerLoad['total'] > $_ServerLoadTotal ){ 
                $sleepTime = $_ServerLoad['total'] / $_ServerLoadTotalPercent;
                $sleepTime = ceil($sleepTime);
                sleep($sleepTime); 
            }
        }
        // Нагрузка на сервер    END
        
        
        if( !isset($_POST['lang']) || !isset($_POST['formName']) || !$SysFunctions->getConstants($lang.'-'.mb_strtoupper($lang, 'UTF-8')) ){
            
            if( $lang == '' ){ $lang = 'ru'; }
            
            if(in_array($formName, $ajaxForms)){
                $err['status'] = 'error';
                $err['data'] = 'ERROR! Not found language or formName.';
                $err['error'] = 'alert';                          
                echo json_encode($err); 
            }else{
                $SysFunctions->error('&des=notfound_language_or_formname&lang='.$lang, 0);
            }
            die();
        }
        
        if( !$_COOKIE['sess'] ){
            if(in_array($formName, $ajaxForms)){
                $err['status'] = 'error';
                $err['data'] = str_replace('<br>', "\n", SYS_ERR_COOKIE_NOTFOUND);
                $err['error'] = 'alert';                          
                echo json_encode($err); 
            }else{
                $SysFunctions->error('&des=COOKIE_NOTFOUND&lang='.$lang, 0); 
            }
            die();
        }        

        if( empty($_SESS) ){
            
            
        }
        
        
        $fileFormUrl = $_SERVER['DOCUMENT_ROOT']."/system/".$formName.".php";
        
        if(in_array($formName, $formNamesProtected)){
            
            if( !empty($_SESS) ){
                if( $currentTime > $_SESS['timeOut'] ){
                    $SysFunctions->error('&des=protectedPage&lang='.$lang, 0);
                }
                $fileFormUrl = $_SERVER['DOCUMENT_ROOT']."/engine/forms/".$formName.".php";
            }else{
                if(in_array($formName, $ajaxForms)){
                    $err['status'] = 'error';
                    $err['data'] = str_replace('<br>', "\n", SYS_ERR_SESS_NOTFOUND); // SYS_ERR_ACCESS_TIME;
                    $err['error'] = 'alert';                          
                    echo json_encode($err); 
                }else{

                    $SysFunctions->error('&des=SESS_NOTFOUND&lang='.$lang, 0);  
                }
                die();
            }            
        }
        
        
        if( $formName != 'regpage' && $formName != 'forgotpage' && $formName != 'cabinet_form' ){
            //echo $fileFormUrl; die();
            if( file_exists($fileFormUrl) ){
                include_once( $fileFormUrl );
            }else{
                $SysFunctions->error('&des=404_notfound_page&lang='.$lang, 0);
            }
            die();
        }else{
            $get_lang = $lang; 
            $get_page = $formName;
        }
    }
  
    define('BLOCK',1);
    if(isset($_GET['page']) && isset($_GET['lang'])){
        $get_page = $SysFunctions->checkForm($_GET['page']);
        $get_lang = $SysFunctions->checkForm($_GET['lang']);
    }
    
    $ver = '';

	
    // Получаем все языковые папки!
    $getLangs = array();
    $categLang = array();
    $langDir = $_SERVER['DOCUMENT_ROOT'].'/system/lang/';
    $skip = array('.', '..');
    $files = scandir($langDir);
    foreach($files as $num=>$file) {
        if(!in_array($file, $skip)){
            $categLang[$num]['name'] = file_get_contents($langDir.$file.'/name.txt');
            $asd = explode('-', $file);
            $categLang[$num]['lang'] = $asd[0];
            array_push($getLangs,  $asd[0]);
        }
    }


    // загружаем языковой файл для системных сообщений. 
    if(!isset($get_lang) || !in_array($get_lang, $getLangs) || ($_SERVER['REQUEST_URI'] == '/') || ($_SERVER['REQUEST_URI'] == '/index.php'))  {
        // !!! Тут добавляем автоопределние языка
        $lang="en";        
        header("Location: ".$_httpHost.'?page=index&lang='.$lang);
        die();
    } else { $lang = $get_lang; }   
    
    if(!in_array($get_page, $pagesArrPublic) && !in_array($get_page, $pagesArrProtected)){
        // !!! Такая страница не существует! header location на error!
        $SysFunctions->error('&des=404_notfound_page&lang='.$lang, 0);
    }


    $SysFunctions->getConstants($lang.'-'.mb_strtoupper($lang, 'UTF-8'));


    // Проверяем на авторизацию! Если запрашивается защищенная страница
    // Закрытый раздел!
    if(in_array($get_page, $pagesArrProtected)){
        
        if( !$_COOKIE['sess'] ){
            $SysFunctions->error('&des=COOKIE_NOTFOUND&lang='.$lang, 0); 
        }        
        
        if( !empty($_SESS) ){
            if( $currentTime > $_SESS['timeOut'] ){
                $SysFunctions->error('&id='.$_SESS['userId'].'&des=protectedPage&lang='.$lang, 0);
            }else if( $currentTime > $_SESS['period'] ){
                //Это нужно перенести только на формы! Т.к. входить пользователь должен!!! Запрет только на выполнение опреаций!!!!
            }
        }else{
            $SysFunctions->error('&des=SESS_NOTFOUND&lang='.$lang, 0);
            die();                

        }
        
        
        $_SESS['timeOut'] = $currentTime + $sessTime;
        if( file_put_contents($_SERVER['DOCUMENT_ROOT']."/sess/".$sess_cookie, serialize($_SESS)) ){
            setcookie('sess', $sess_cookie, time()+$sessTime, "/", $_SERVER['HTTP_HOST'], $httpSecureCookie, true);
        } 
  
    }
    
	
	$content = ''; // Далее в самом низу!!!

	
    // создаем тег <select> языковой
    $tagLangSelect = '';
    $tagLangSelect .= '<select name="lang" id="selectLang" onchange="top.location=this.value" style="font-family: \'PT Sans\', sans-serif;">';
    foreach($categLang as $num=>$d){
       
        $newLang = preg_replace("#lang=[a-zA-Z]{2,3}#ismU", "lang=".$d['lang'], $_getStringUri);
        $href = $_httpHost.$newLang; 
        
        if($d['lang'] == $lang){
            $tagLangSelect .= '<option value="'.$href.'" selected="selected">';
        } else { 
            $tagLangSelect .= '<option value="'.$href.'">';
        }
        $tagLangSelect .= $d['name'];
        $tagLangSelect .= "</option>";
    }
    $tagLangSelect .= '</select>';
    
    
    $cacheAttr = unserialize(file_get_contents('attr.txt'));
    
    
    $toExcelERRAccess = $_http.$_SERVER['SERVER_NAME'].'?page=error_access&file='.'folderName'.'&des='.urlencode (SYS_PROTECTED_ERR_SAVETOEXCEL_PROJECT_ACCESS.SYS_ERR_ACCESS_TIME).'&lang='.$lang;
    $xlsUploadERRAccess = $_http.$_SERVER['SERVER_NAME'].'?page=error_access&file='.'folderName'.'&des='.urlencode (SYS_PROTECTED_ERR_CREATE_PROJECT_ACCESS.SYS_ERR_ACCESS_TIME).'&lang='.$lang;
 
    $timeLeft = 0;
    $timeLeftInt = 0;
    $deleteAccountTime = 10; // кол-во дней после окончания периода
    if(isset($_SESS['period'])){
        $timeLeftInt = ((int)$_SESS['period'] - time()) / (60*60*24);
    }
    $deleteAccountTimeAuto = $deleteAccountTime + ceil($timeLeftInt);
    if($deleteAccountTimeAuto <= 0 ){ $deleteAccountTimeAuto = 0; }    
    $timeLeftBlockText = sprintf(SYS_PROTECTED_MENUTOP_TEXT_TIME_LEFT_ACCAUNT_DELL, $deleteAccountTimeAuto);
    $timeLeftBlock = '<div class="timeLeftBlock">'.$timeLeftBlockText.'</div>';
    if( $timeLeftInt < 0 ){ $timeLeft = 0;}
    else{ $timeLeft = ceil($timeLeftInt); $timeLeftBlock = '';}
    
    $days = 0; $hours = 0; $min = 0; $sub = 0;
    if(isset($_SESS['period'])){$sub = $_SESS['period'] - time();}
    if($sub > 0){
        $sub = abs($sub);
        $days = (int)($sub / (24*60*60));
        $hours = (int)(($sub - $days * 24 * 60 * 60) / (60*60));
        $min = (int)(($sub - $days * 24 * 60 *60 - $hours * 60 * 60) / 60);
    }
    

// automate any printing products
//Виртуальный верстальщик



?>
<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<meta name="description" content="Генератор PDF" />
	<title><?php echo SYS_TITLE_SUITE;?></title>
	<!--<link rel="stylesheet" href="/font-awesome-4.7.0/css/font-awesome.css" type="text/css" />-->
	<link rel="stylesheet" href="/font-awesome-4.7.0/PTsans/stylesheet.css" type="text/css" />
	
	<link rel="stylesheet" href="/files.php?access=public&type=css&file=all.css&cache=<?php echo $cacheAttr['change']; ?>" type="text/css" />
	<link href="<?php echo $_httpHost.'/favicon.ico'; ?>" rel="shortcut icon" type="image/vnd.microsoft.icon" />	
	<script type='text/javascript' src="https://ajax.googleapis.com/ajax/libs/jquery/1.4.3/jquery.min.js"></script>
	<script type='text/javascript' src="/files.php?access=public&type=js&file=system.js&cache=<?php echo $cacheAttr['change']; ?>"></script>
	<script type="text/javascript" src = "/files.php?access=public&type=js&file=background_size_emu.js&cache=<?php echo $cacheAttr['change']; ?>"></script>
	<!-- <j!d!oc:include type="head" /> -->
	<script type='text/javascript'>var thisLang="<?php echo $lang; ?>"</script>
	
	<?php
	    if($get_page == 'gen' || $get_page == 'cabinet'){ include_once($_SERVER['DOCUMENT_ROOT'].'/system/js/js1.php'); }
	?>
	<!--[if lt IE 9]><script src="<?php /*echo JUri::root(true);*/ ?>/media/jui/js/html5.js"></script><![endif]-->
</head>
<body> 

<!-- Yandex.Metrika counter -->
<script type="text/javascript" >
    (function (d, w, c) {
        (w[c] = w[c] || []).push(function() {
            try {
                w.yaCounter46418358 = new Ya.Metrika({
                    id:46418358,
                    clickmap:true,
                    trackLinks:true,
                    accurateTrackBounce:true,
                    webvisor:true
                });
            } catch(e) { }
        });

        var n = d.getElementsByTagName("script")[0],
            s = d.createElement("script"),
            f = function () { n.parentNode.insertBefore(s, n); };
        s.type = "text/javascript";
        s.async = true;
        s.src = "https://mc.yandex.ru/metrika/watch.js";

        if (w.opera == "[object Opera]") {
            d.addEventListener("DOMContentLoaded", f, false);
        } else { f(); }
    })(document, window, "yandex_metrika_callbacks");
</script>
<noscript><div><img src="https://mc.yandex.ru/watch/46418358" style="position:absolute; left:-9999px;" alt="" /></div></noscript>
<!-- /Yandex.Metrika counter -->

    <div id="container">
        
        <div id="sysMessage"></div>
        
        <div id="menu">
            <div class="allTopMenu"></div>
            
                <?php
                    
                    //if($get_page != "engine")  {
                    if(in_array($get_page, $pagesArrProtected)){    
                        // тут инклюдим личный кабинет и все остальное!
                        echo $timeLeftBlock;
                        include_once($_SERVER['DOCUMENT_ROOT']."/pages/menu_protected.php");
                    } else {
                        include_once($_SERVER['DOCUMENT_ROOT']."/pages/menu.php");
                    }
                ?>
            
        </div>
            
            
        <div id="bodyBlock">

            
            
            <div id="content" style="min-height:550px;">
            <?php
                
                if(in_array($get_page, $pagesArrProtected)){    
                    $menuStart = array(SYS_PROTECTED_MENU_CREATE, SYS_PROTECTED_MENU_OPEN, SYS_PROTECTED_MENU_FONTS, SYS_PROTECTED_MENU_BULLIT, SYS_PROTECTED_MENU_PICTURES, SYS_PROTECTED_MENU_CABINET);
                    $menuStartPagesAndClass = array("create", "open", "fonts", "bullits", "images", "cabinet");
                    include_once($_SERVER['DOCUMENT_ROOT'].'/engine/'.$get_page.'.php');
                } else {
                    
                    // Страницы error и confirm
                    // Тут подключаются страницы index description и др.
                    $lg = $lang.'-'.mb_strtoupper($lang, 'UTF-8');
                    
                    if( $get_page == 'error'){
                        $content = file_get_contents($langDir.$lg.'/'.$get_page.'.html');
                        $deff = 'SYS_ERR_'.mb_strtoupper($SysFunctions->checkForm($_GET['des']), 'UTF-8');
                        $content = str_replace('BODYTEXT', constant($deff), $content);
                    }else if( $get_page == 'confirm'){
                        include_once($_SERVER['DOCUMENT_ROOT'].'/system/confirm.php');
                    }else if( $get_page == 'changepass'){
                        include_once($_SERVER['DOCUMENT_ROOT'].'/system/changepass.php');
                    }else if( $get_page == 'regpage'){
                        include_once($_SERVER['DOCUMENT_ROOT'].'/system/regpage.php');
                    }else if( $get_page == 'forgotpage'){
                        include_once($_SERVER['DOCUMENT_ROOT'].'/system/forgotpage.php');
                    }else if( $get_page == 'cabinet'){
                        include_once($_SERVER['DOCUMENT_ROOT'].'/engine/cabinet.php');
                    }else{
                        //$content = file_get_contents($langDir.$lg.'/'.$get_page.'.html');
                        include_once($langDir.$lg.'/'.$get_page.'.php');
                    }  
                    
                    echo $content; 
                }
                

                if( $REQUEST_METHOD == 'POST' ){
                	if( $formName == 'regpage' ){
                		include_once($_SERVER['DOCUMENT_ROOT'].'/system/regpage.php');
                	}
                }                
                
                
            ?>
            </div>
            
            <div id="footer">
            </div>    
            
        </div>
        

    </div>



	<br><br><br>


	<br>

	<br>


	<div class="preLoader"><div class="loader"></div></div>
	<div id="onconsu"></div>


<?php

$footerTel = '';
if(in_array($get_page, $pagesArrProtected)){
    
    $footerTel .= '<div style="height:20px;"></div>';
    $footerTel .= '<div style="height:20px;width:100%; position: relative;"></div>';
    $footerTel .= '<div style="height:10px;width:100%;"></div>';
    $footerTel .= '<div style="margin-top: -100px;height:100px;width:100%; background: #373d40;">';
        $footerTel .= '<div style="width:1200px;font-size:20px;text-align:center;margin:0 auto;padding:10px 0 20px 0">';
            $footerTel .= 'pdf@pdf-2.com';
        $footerTel .= '</div>';
    $footerTel .= '</div>';
}
echo $footerTel;

?>





</body>

	<?php
	    if($get_page == 'gen'){ include_once($_SERVER['DOCUMENT_ROOT'].'/system/js/js2.php'); }
	?>

</html>
