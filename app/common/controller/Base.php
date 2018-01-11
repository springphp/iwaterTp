<?php
namespace app\common\controller;
use think\Controller;
use think\Request;
use think\Session;
use extend\Encrypt;

/**
 * 基础类
 * by chick 2017-05-02
 */
class Base extends Controller
{
	protected $base_url,$var;
    public function __construct(){
        parent::__construct();
    }

    public function _initialize(){
        $this->base_setUrl();
        $this->base_setVar();
        Session::init(['prefix' => MODULE_NAME,]);
    }

    /**
     * 设置常用url为常量
     *
     */
    final protected function base_setUrl(){
    	$this->base_url['module_name']     = Request::instance()->module();//当前模块
    	$this->base_url['controller_name'] = Request::instance()->controller();//当前控制器
    	$this->base_url['action_name']     = Request::instance()->action();//当前方法
    	$this->base_url['public_path']     = ROOT_PATH.'/';
    	$this->base_url['static_path']     = ROOT_PATH.'/static/';
    	$this->base_url['web_public']      = config('STATIC_URL').'/';
    	$this->base_url['web_static']      = config('STATIC_URL').'/static/';
    	foreach ($this->base_url as $name => $value) {
    		$name = strtoupper($name);
    		!defined($name) && define($name, $value);
    	}
        $this->base_url['js'] =  WEB_PUBLIC.'theme/'.MODULE_NAME.'/static/js';
        $this->base_url['css'] = WEB_PUBLIC.'theme/'.MODULE_NAME.'/static/css';
        $this->base_url['img'] = WEB_PUBLIC.'theme/'.MODULE_NAME.'/static/img';

        $this->assign($this->base_url);//加载到模版
    }

    final protected function base_setVar(){
    	$this->var['title']        = config('web_name');
    	$this->var['admin_title']  = config('web_admin_name')?:config('web_name').'后台管理';
        $this->var['nowpage']      = input(config('paginate.var_page'))?:1;
        $this->var['upload']       = WEB_PUBLIC .'/public/upload/';
    	$this->assign($this->var);//加载到模版
    }

    /**
     * 初始化城市js数据
     */
    final protected function base_initCityJson($force = false){
        if ($force && Request::instance()->isAjax()) { return false; }
        $file = './static/cache/city.cache.js';
        !is_dir(dirname($file)) && mkdir(dirname($file),'0777',true);
        !file_exists($file)     && $force = true;
        //判断是否强制重写
        if (!$force) {//非强制重写
            $time = getFileTime($file);
            $compare =  time()-$time;
            if ($compare <= config('cache.expire')) return false;//文件更新时间小于设置时间
        }
        $citys = model('city')->where(['pid'=>['neq',0]])->select();
        resultToArray($citys);
        $citys = getTree($citys,['primary_key'=>'city_id','class_name'=>'form-control'])->makeTree(1);
        $cc = getCityJson($citys);
        $data = "//此文件由系统生成，修改无效\nvar ChineseDistricts = ".$cc;
        if (!$force && md5(file_get_contents($file)) == md5($data)) return false;//文件内容相同
        $f = fopen($file, 'w+');
        fwrite($f, $data);
        fclose($f);
    }
   


   /**
     * 检测用户是否登录
     * @return true 已登录  false 未登录
     *
     */
    final protected function is_login(){
        $api    = Api('this');
        $user_id = session('islogin');
        if (!$user_id) {
            return $api->setApi('msg','未登录')->ApiError();
        } else {
            $login_time = session('user.last_login_time');
            $map['admin_id'] = session('user.admin_id');
            $admin = model('admin')->get($map)->toArray();
            if($login_time != $admin['last_login_time']){
                return $api->setApi('msg','帐号在其它浏览器登录！')->ApiError();//1 账号被占用
            }
            if(config('login_timeout') != 0){
                $logintimed =time()-$login_time;
               if( $logintimed > config('login_timeout')){
                    return $api->setApi('msg','登录超时')->ApiError();//2 登录超时
                }
            }
            return $api->setApi('msg','-')->ApiSuccess();
        }
    }

    /**
     * 清除登录信息
     */
    final protected function destroyUser(){
        session('islogin',0);
        session('user',[]);
    }
    /**
     * 修改数据表指定字段
     * @param string    $table    要修改的数据表
     * @param string    $status   要修改成的值
     * @param int|array $id       主键ID
     * @param string    $pk       主键名，默认为表名_id
     * @param string    $field    要修改的字段，默认status
     */
    final protected function setStatus($table,$status,$id,$pk='',$field='status',$where='', $setAjax=false){
        $api    = Api('this',$setAjax);
        $pk     = $pk ?: $table.'_id';
        $field  = $field ?: 'status';
        $ids    = (array)$id;
        if (!$table  || !$ids || !$pk || !$field) {
            !$table      && $msg  = 'table';
            !$ids        && $msg  = 'id';
            !$pk         && $msg  = 'pk';
            !$field      && $msg  = 'field';
            return $api->setApi('msg','param error:'.$msg)->ApiWarning();
        }
        $model = model($table);
        foreach ($ids as $k => $id) {
            if (!(int)$id) continue;
            $where[$pk]    = (int)$id;
            $data[$field] = $status;
            $model->where($where)->update($data);
        }
        return $api->setApi('msg','操作成功')->ApiSuccess();
        
    }
}
