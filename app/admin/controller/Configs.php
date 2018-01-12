<?php
namespace app\admin\controller;
use app\common\controller\AdminBase;
use app\common\Model\Config;
/**
 * 系统设置
 */
class Configs extends AdminBase
{
    public function defaults(){
        $this->redirect(url('Configs/index'));
    }
    /*
    * 配置列表
    * */
    public function index(){
        $authGroup  = model('Config')->select();
        resultToArray($authGroup);
        $admins     = model('Config')->select_config(input());
        return  view([
            'admins'=>$admins,
        ]);
    }
    /*
    * 添加配置
    * */
    public function add(){
        $page = input('page');
        if(request()->isAjax()){
            $data                = input('post.');
            $data['config_mark'] = strtoupper($data['config_mark']) ;
            $re = model('Config')->add_config($data);
            if($re >0 ){
                Api()->setApi('url',url('Configs/index',['page'=>input('page')]))->ApiSuccess($re);
            }else{
                Api()->setApi('msg',$re)->setApi('url',0)->ApiError();
            }
        }
        return view(['page'=>$page]);
    }


    /*
     * 修改配置
     * */
    public function edit(){
        $page = input('page','1','trim');
        $config_id = input('id');
        $config_info = db('Config')->where('id',$config_id)->find();
        if (request()->isAjax()){
            $data = input('post.');
            $data['update_time']   = time();
            $re  = model('Config')->edit_config($data);
            if($re >0){
                Api()->setApi('url',url('Configs/index',['page'=>input('page')]))->ApiSuccess($re);
            }else{
                Api()->setApi('msg',$re)->setApi('url',0)->ApiError();
            }
        }
        return view(
            ['config_info'=>$config_info,'page'=>$page]
        );

    }
    /*
     * 删除配置
     * */
    public function del(){
        if(request()->isAjax()){
            $data = input();
            $time = time();
            $re   = $this->setStatus('Config',$time,$data['id'],'id','delete_time');
            if($re){
                Api()->setApi('url',input('location'))->ApiSuccess($re);
            }else{
                Api()->setApi('url',0)->ApiError();
            }
        }
    }
}