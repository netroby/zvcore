<?php
class zload{
    //define the assets base;
    public static $assets_base="./assets/";
    /**
     * main function 
     */
    public static function load($type,$file){
        //Restore The true file with path;
        $trueFile=self::restoreFile($file);
        //check content type;
        switch($type){
        case "js":
            self::loadType('text/javascript',$trueFile);
            break;
        case "css":
            self::loadType('text/css',$trueFile);
            break;
        case "jpg":
            self::loadType('image/jpeg',$trueFile);
            break;
        case "gif":
            self::loadType('image/gif',$trueFile);
            break;
        case "png":
            self::loadType('image/png',$trueFile);
        }
    }
    public static function loadType($contentType,$trueFile){
        header("Pragma: public");
        header("Expires: ".gmdate('D, d M Y H:i:s' ,(time()+3600*3600)) . ' GMT'); 
        $lastmodify=filemtime($trueFile);
        header("Last-Modified: ".gmdate('D, d M Y H:i:s',$lastmodify) . ' GMT');
        header('Cache-Control: max-age=604800');
        header('Content-Type:'.$contentType);
        if (strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) == $lastmodify) {  
            header("HTTP/1.1 304 Not Modified"); //服务器发出文件不曾修改的指令  
            exit();  
        }  
        
        
        echo file_get_contents($trueFile);
        
        ob_end_flush();
    }
    public static function restoreFile($file){
        $file_step_a=str_replace("_~_","/",$file);
        $file_step_b=str_replace("~_~",".",$file_step_a);
        $file_now=self::$assets_base.$file_step_b;
        if(!file_exists($file_now)){
            exit("File Not Exists");			
        }else{
            return $file_now;
        }		
    }
    public static function buildit($type,$file){
        $file_build_a=str_replace("/","_~_",$file);
        $file_build_b=str_replace(".","~_~",$file_build_a);
        $zload_url="./zload-load-type-".$type."-file-".$file_build_b.".html";
        echo $zload_url;
    }
}
