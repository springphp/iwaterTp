<?php

use think\Image;
use think\Session;
    /* $name  string    为type="file"的input框的name值
     * $file string     存在图片的文件夹 (文件夹必须在upload之下)
     * return  string   返回图片的文件夹和名字
     * */
function upload_img($name,$file){
        $up_dir = "./upload/$file";
        if (!file_exists($up_dir)) {
            mkdir($up_dir, 0777, true);
        }
        $image = Image::open(request()->file($name));//打开上传图片
        $size = input('avatar_data');//裁剪后的尺寸和坐标
        $size_arr=json_decode($size,true);
        $type= substr($_FILES [$name]['name'],strrpos($_FILES [$name]['name'],'.')+1);
        $name = time().".".$type;
        $info =$image->crop($size_arr['width'], $size_arr['height'],$size_arr['x'],$size_arr['y'])->save("./upload/$file/$name");
        if($info){
           return $file."/".$name;
        }else{
            return false;
        }
}
/**
 * 商城文章分类
 * by pengqiang 2017/7/4
 */
function getArticle(){
    $article_arr['notice']=[]; $article_arr['rules']=[]; $article_arr['message']=[]; $article_arr['learning']=[];
    $articles   = model('Article')->where(['status'=>1])->select();
    resultToArray($articles);
    foreach ($articles as $k=>&$v){
        $v['create_time']             =date('Y-m-d',$v['create_time']);
        if($v['cate_id'] == 120){//平台公告/商城公告
            $article_arr['notice'][]  = $articles[$k];
        }elseif($v['cate_id'] == 121){//行业资讯
            $article_arr['message'][] = $articles[$k];
        }elseif($v['cate_id'] == 139){//商城规则
            $article_arr['rules'][]   = $articles[$k];
        }elseif($v['cate_id'] == 140){//商城学习
            $article_arr['learning'][]= $articles[$k];
        }
    }
    return $article_arr;
}

/**
 * 商城发送消息
 * $receive_uid int 接收消息ID
 * $send_uid    int 发送者ID  默认为1系统发送
 * $flag        int  1系统消息  2其他消息
 * $content     string 消息内容
 * $title   string  消息标题  默认为"系统消息
 *
 */
function sendMessage($data){
    $data['receive_uid'] =  $data['receive_uid'];
    $data['content']     = $data['content'];
    $data['title']       = $data['title'];
    $data['flag']        = $data['flag'];
    $data['send_uid']    = $data['send_uid'];
    $message = model('Message')->add_message($data);
}


/**
 *  获取10位以上不重复的随机字符串
 */
function getUniqueStr($length=16,$param='',$start=''){
    if ($length<10) return '';
    $mstime = (int)($_SERVER['REQUEST_TIME_FLOAT']*1000);
    $str    = strtoupper(md5($param.$start.$mstime));
    $count  = $length - strlen($param) - strlen($start);
    if ($count<=4){
        $un_str = substr($str,0,$count);
    } else {
        $un_str  = substr($mstime,-($count-4),$count-4);
        $un_str  .= substr($str,0,4);
    }
    return  $start.$param.$un_str;
}
/**
 * 获取文件处理时间
 * create 创建时间
 * edit   编辑时间
 * active 访问时间
 * by chick 2017-05-03
 */
function getFileTime($file,$act = 'edit'){
    if (!is_file($file)) return false;
    switch($act){
        case 'create':
            $time = filectime($file);break;
        case 'edit':
            $time = filemtime($file);break;
        case 'active':
            $time = fileatime($file);break;
        default:
            $time = filemtime($file);break;
    }
    return $time;
}

/**
 *低版本的array_column函数
 */
if(!function_exists('array_column')){
    function array_column($input, $columnKey, $indexKey = NULL){
        $columnKeyIsNumber = (is_numeric($columnKey)) ? TRUE : FALSE;
        $indexKeyIsNull = (is_null($indexKey)) ? TRUE : FALSE;
        $indexKeyIsNumber = (is_numeric($indexKey)) ? TRUE : FALSE;
        $result = array();
        foreach ((array)$input AS $key => $row){
            if ($columnKeyIsNumber){
                $tmp = array_slice($row, $columnKey, 1);
                $tmp = (is_array($tmp) && !empty($tmp)) ? current($tmp) : NULL;
            }else{
                $tmp = isset($row[$columnKey]) ? $row[$columnKey] : NULL;
            }
            if ( ! $indexKeyIsNull){
                if ($indexKeyIsNumber){
                    $key = array_slice($row, $indexKey, 1);
                    $key = (is_array($key) && ! empty($key)) ? current($key) : NULL;
                    $key = is_null($key) ? 0 : $key;
                }else{
                    $key = isset($row[$indexKey]) ? $row[$indexKey] : 0;
                }
            }
            $result[$key] = $tmp;
        }
        return $result;
    }
}
/**
 * model查出来的数组对象转为数据数组
 * by chick 2017-05-02
 */
function resultToArray(&$results){
    foreach ($results as &$result) {
        $result = $result->getData();
    }
}

/**
 * 创建Tree对象
 * by chick 2017-05-03
 */
function getTree($data,$options=[],$level=0){
    return new \extend\Tree($data,$options,$level);
}

function getNamebyPk($model,$pk_name,$getField,$pk_value){
    return  model($model)->where([$pk_name=>$pk_value])->find()->$getField;
}
/**
 * 创建Api对象
 * by chick 2017-05-05
 */
function Api($type = '',$setApi=false){
    $app_debug = config('app_debug');
    $api = new \app\common\controller\Api($app_debug);
    return $api->setType($type,$setApi);
}
/**
 * 获取图片用于显示
 */
function getImg($imgName,$isUrl=false){
    if ($isUrl) {
        $url = $imgName;
    } else {
        $url   = config('STATIC_URL').'/upload/'.$imgName;
        $url_t   = ROOT_PATH.'public/upload/'.$imgName;
    }
    if (!is_file($url_t)) {
        $url = config('static_url').'/upload/'.config('default_img');
        $url_t = ROOT_PATH.'public/upload/'.config('default_img');
        $url = is_file($url_t) ? $url : config('static_url').'/static/img/default1.png';
    }
    return $url;
}
/**
 * html模版文件是否存在
 */
function htmlFileExists($html){
    $path = config('template.view_path').MODULE_NAME.'/'.$html.'.'.config('template.view_suffix');
    return file_exists($path);
}
/**
 * 判断值是否为空
 */
function isValue($data,$key=false){
    if ($key !== false) {
        if(!is_array($data)) return false;
        if(!array_key_exists($key,$data)) return false;
        $v = $data[$key];
    } else {
        $v = $data;
    }
    if ($v === 0 || $v === '0') return true;
    if($v != '') return true;
    if (is_array($v) && $v !=[]) return true;
    return false;
}

/**
 * 根据城市名称获取ID
 */
function getCityIdByNames($name){
    if (!$name) {
        return [0,0,0];
    }
    if (!is_array($name)) {
        $name = explode('/', $name);
    }
    array_walk($name, 'trim');
    $model = model('city');
    $city['id'][] = $model->where(['city_name'=>$name[0],'pid'=>1,])->value('city_id');
    $city['id'][] = $city['id'][0] ? $model->where(['city_name'=>$name[1],'pid'=>$city['id'][0],])->value('city_id') : 0;
    $city['id'][] = $city['id'][1] ? $model->where(['city_name'=>$name[2],'pid'=>$city['id'][1],])->value('city_id') : 0;
    foreach ($city['id'] as $k => $v) {
        $city[$city['id'][$k]] = $name[$k];
    }

    return $city['id'];
}
/*
 * 根据city_id获取city_name
 */
function getCityName($data){
    if(is_array($data)){
        return false;
    }
    $province = model('City')->where("city_id = {$data['area_id1']}")->value('city_name');
    $city = model('City')->where("city_id = {$data['area_id2']}")->value('city_name');
    $distrct = model('City')->where("city_id = {$data['area_id3']}")->value('city_name');
    $citys = $province.' '.$city.' '.$distrct;
    return $citys;
}

/*
 * 根据id获取city_name
 */
function getCityNames($data){
    if(is_array($data)){
        return false;
    }
    $province = model('City')->where("city_id = {$data['province']}")->value('city_name');
    $city = model('City')->where("city_id = {$data['city']}")->value('city_name');
    $distrct = model('City')->where("city_id = {$data['area']}")->value('city_name');
    $citys = $province.'/'.$city.'/'.$distrct;
    return $citys;
}

/**
 * 获取城市json数据，用于citypicker插件，只遍历三级
 */
function getCityJson($citys,$index = 1){
    foreach ($citys as $k => $v) {//省
        if ($v['pid'] == $index){
            $ascii = ord(strtoupper($v['en_name']{0}));
            if ($ascii >= 65 && $ascii <= 71) {
                $cc[86]['A-G'][] = ['code'=>$v['city_code'],'address'=>$v['city_name'],];
            } else if($ascii >= 72 && $ascii <= 75){
                $cc[86]['H-K'][] = ['code'=>$v['city_code'],'address'=>$v['city_name'],];
            } else if($ascii >= 76 && $ascii <= 83){
                $cc[86]['L-S'][] = ['code'=>$v['city_code'],'address'=>$v['city_name'],];
            } else if($ascii >= 83 && $ascii <= 90){
                $cc[86]['T-Z'][] = ['code'=>$v['city_code'],'address'=>$v['city_name'],];
            } else {
                $cc[86]['其他'][] = ['code'=>$v['city_code'],'address'=>$v['city_name'],];
            }
            if (array_key_exists('child', $v)) {
                foreach ($v['child'] as $k1 => $v1) {//市
                    $cc[$v['city_code']][$v1['city_code']] = $v1['city_name'];
                    if (array_key_exists('child', $v1)) {
                        foreach ($v1['child'] as $k2 => $v2) {//区
                            $cc[$v1['city_code']][$v2['city_code']] = $v2['city_name'];
                        }
                    }
                }
            }
        }
    }
    return jsonFormat($cc, $indent=null);
}

/** Json数据格式化
* @param  Mixed  $data   数据
* @param  String $indent 缩进字符，默认4个空格
* @return JSON
*/
function jsonFormat($data, $indent=null){

    // 对数组中每个元素递归进行urlencode操作，保护中文字符
    array_walk_recursive($data, 'jsonFormatProtect');

    // json encode
    $data = json_encode($data);

    // 将urlencode的内容进行urldecode
    $data = urldecode($data);

    // 缩进处理
    $ret = '';
    $pos = 0;
    $length = strlen($data);
    $indent = isset($indent)? $indent : '    ';
    $newline = "\n";
    $prevchar = '';
    $outofquotes = true;

    for($i=0; $i<=$length; $i++){

        $char = substr($data, $i, 1);

        if($char=='"' && $prevchar!='\\'){
            $outofquotes = !$outofquotes;
        }elseif(($char=='}' || $char==']') && $outofquotes){
            $ret .= $newline;
            $pos --;
            for($j=0; $j<$pos; $j++){
                $ret .= $indent;
            }
        }

        $ret .= $char;

        if(($char==',' || $char=='{' || $char=='[') && $outofquotes){
            $ret .= $newline;
            if($char=='{' || $char=='['){
                $pos ++;
            }

            for($j=0; $j<$pos; $j++){
                $ret .= $indent;
            }
        }

        $prevchar = $char;
    }

    return $ret;
}



/** 将数组元素进行urlencode
* @param String $val
*/
function jsonFormatProtect(&$val){
    if($val!==true && $val!==false && $val!==null){
        $val = urlencode($val);
    }
}

function get_login_user_name(){
    return session('user.nickname') ?:session('user.account');
}
function get_login_admin_group(){
    $group = session('user.group');
    if (!$group) { return;}
    $name = model('auth_group')->where(['group_id'=>$group])->value('group_name');
    return $name;
}


if (!function_exists('urldo')) {
    /**
     * Url生成
     * @param string        $url 路由地址
     * @param string|array  $vars 变量
     * @param bool|string   $suffix 生成的URL后缀
     * @param bool|string   $domain 域名
     * @return string
     */
    function urldo($url = '', $vars = '', $suffix = true, $domain = true)
    {
        return url($url, $vars, $suffix, $domain);
    }
}
/**
 * 随机纯数字字符串
 * @param  [number] $length [字符串长度]
 * @return [string]         [字符串]
 * wanggang
 */
function make_code($length){
    $output='';
    for ($i = 0; $i < $length; $i++) {
    $output .= rand(0, 9); //生成php随机数
    }
    return $output;
}
// -------------------------------------------------------------
/**
 * thikphp5 已删除C/D/U/M/W/I等函数
 * 重写单字母函数C/D/U/M/W/I
 * by chick 2017-05-02
 */
function C($name = '', $value = null, $range = ''){
    return config($name, $value, $range );
}
function D($name = '', $layer = 'model', $appendSuffix = false){
    return model($name, $layer, $appendSuffix);
}
function M($name = '', $config = [], $force = true){
    return db($name, $config, $force);
}
function U($url = '', $vars = '', $suffix = true, $domain = false){
    return url($url, $vars, $suffix, $domain);
}
function W($name, $data = []){
    return widget($name, $data);
}
function I($key = '', $default = null, $filter = ''){
    return input($key, $default, $filter);
}

/**
 * 获取客户端IP地址
 * @param integer $type 返回类型 0 返回IP地址 1 返回IPV4地址数字
 * @return mixed
 */
function get_client_ip($type = 0) {
    $type       =  $type ? 1 : 0;
    static $ip  =   NULL;
    if ($ip !== NULL) return $ip[$type];
    if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $arr    =   explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        $pos    =   array_search('unknown',$arr);
        if(false !== $pos) unset($arr[$pos]);
        $ip     =   trim($arr[0]);
    }elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
        $ip     =   $_SERVER['HTTP_CLIENT_IP'];
    }elseif (isset($_SERVER['REMOTE_ADDR'])) {
        $ip     =   $_SERVER['REMOTE_ADDR'];
    }
    // IP地址合法验证
    $long = sprintf("%u",ip2long($ip));
    $ip   = $long ? array($ip, $long) : array('0.0.0.0', 0);
    return $ip[$type];
}

function get_caller_info() {
    $c = '';
    $file = '';
    $func = '';
    $class = '';
    $trace = debug_backtrace();
    if (isset($trace[2])) {
        $file = $trace[1]['file'];
        $func = $trace[2]['function'];
        if ((substr($func, 0, 7) == 'include') || (substr($func, 0, 7) == 'require')) {
            $func = '';
        }
    } else if (isset($trace[1])) {
        $file = $trace[1]['file'];
        $func = '';
    }
    if (isset($trace[3]['class'])) {
        $class = $trace[3]['class'];
        $func = $trace[3]['function'];
        $file = $trace[2]['file'];
    } else if (isset($trace[2]['class'])) {
        $class = $trace[2]['class'];
        $func = $trace[2]['function'];
        $file = $trace[1]['file'];
    }
    if ($file != '') $file = basename($file);
    $c = $file . ":";
    $c .= ($class != '') ? ":" . $class . "->" : "";
    $c .= ($func != '') ? $func . "(): " : "";
    return($c);
}

//获取订单状态
function getOrderStatus(){
    $status = model('OrderStatus')->order('id')->select();
    resultToArray($status);
    return $status;
}

//获取用户昵称
function getUserNickName(){
    $nickname = model('User')->order('user_id')->column('nickname','user_id');
    return $nickname;
}

//获取分页参数设置
function _pageconfig($listRows){
    config(['paginate'=>['type'      => 'bootstrap1','list_rows' => $listRows,'var_page'  => 'page',]]);
    Session::set('pageSize', config('paginate.list_rows'));
}

//验证收货地址表单
function checkForm($data){
    /*验证表单*/
    if(isset($data['address_id'])){
        if(!$data['citys']){
            Api()->setApi('msg','请选择所在区域！')->setApi('url',0)->ApiError();
        }
    }
    if(!$data['address']){
        Api()->setApi('msg','详细地址不能为空')->setApi('url',0)->ApiError();
    }elseif (!$data['post_code']){
        Api()->setApi('msg','邮政编码不能为空')->setApi('url',0)->ApiError();
    }elseif (!$data['user_name']){
        Api()->setApi('msg','请输入收货人！')->setApi('url',0)->ApiError();
    }elseif (!$data['user_phone']){
        Api()->setApi('msg','请输入手机号码')->setApi('url',0)->ApiError();
    }else{
        /*验证手机号码和邮编*/
        $post_code = preg_replace("/[\. -]/", "", $data['post_code']);//去掉多余的分隔符
        $tel =  preg_replace("/[\. -]/", "", $data['user_phone']);
        if(!preg_match("/^[1-9][0-9]{5}$/", $post_code)){   //验证邮编
            Api()->setApi('msg','请输入正确的6位邮编')->setApi('url',0)->ApiError();
        }
        if(!preg_match("/^1[34578]\d{9}$/", $tel)){ //验证手机号码
            Api()->setApi('msg','请输入正确的11位手机号码')->setApi('url',0)->ApiError();
        }
    }
}









