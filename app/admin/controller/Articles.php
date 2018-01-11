<?php
namespace app\admin\controller;
use app\common\controller\AdminBase;
// use app\common\Model\Article;
use extend\Upload;


/*
 * @table   article
 * @Auth weichunfeng
 * @version 2017.05.24
 * */

class Articles extends AdminBase
{
    /*
     * 文章列表
     */
    public function index(){
        $articles = $this->getTypeTree();
        $lists = model('article')->select_article(input());
        foreach ($lists as &$v){
            $v['cate_name'] = model('articleCate')->where('cate_id',$v['cate_id'])->value('cate_name');
            $contentLen = mb_strlen(trim($v['article_content']));
            $v['article_content'] = htmlspecialchars_decode($v['article_content']);
            if($contentLen>86){
                $v['article_content'] = mb_substr($v['article_content'],0,86,'utf-8')."……";
            }
            $v['article_content'] = htmlspecialchars_decode($v['article_content']);
        }
        return view([
            'lists'     =>$lists,
            'articles'  =>$articles,
            'cate_id'   =>input('cate_id'),
        ]);
    }

    /**
     * 获取树状类别列表
     */
    public function getCateTree($id = 0){
        $cates = model('ArticleCate')->field("cate_id,cate_name,pid,level")->where("status =1")->select();
        resultToArray($cates);
        $select = getTree($cates,['primary_key'=>'cate_id','class_name'=>'form-control i-select','form_name'=>'pid'],3)->makeSelect($id,'cate_name',"顶级分类");
        return $select;
    }

    /*
     * 添加文章
     */
    public function article_add(){
        $articles = $this->getTypeTree();
        if(request()->isAjax()){
            $data = input();
            $data['article_content'] = str_replace('<p><br></p>','',$data['article_content']);//过滤默认换行
            if(isset($data['uploadImg'])){//判断是否有图片
                $year = date('Y/m',time());
                $re  = Upload::uploadImg("article/$year")->getInfo();
                $article_img["image"] = "/article/".$year."/".$re[0];
                unset($data['uploadImg']);
                $data = $data+$article_img;
            }else{
                Api()->setApi('msg','请选择上传图片！')->setApi('url',0)->ApiError();
            }
            $data['image'] = $this->getUrl($data['image']);//url处理
            $re = model('article')->add_article($data);
            if($re > 0){
                Api()->setApi('url',url('Articles/index'))->ApiSuccess();
            }else{
                Api()->setApi('msg',$re)->setApi('url',0)->ApiError();
            }
        }
        return view([
            'articles'  => $articles,
        ]);
    }

    /*
     * 修改文章
     */
    public function article_edit(){
        if(request() -> isAjax()){
            $data = input();
            unset($data['page']);
            $data['article_content'] = str_replace('<p><br></p>','',$data['article_content']);//过滤默认换行
            if(isset($data['uploadImg'])){//判断是否有图片
                $year = date('Y/m',time());
                $re  = Upload::uploadImg("article/$year")->getInfo();
                $article_img["image"] = "/article/".$year."/".$re[0];
                unset($data['uploadImg']);
                $data = $data+$article_img;
                $data['image'] = $this->getUrl($data['image']);//url处理
            }else{
                unset($data['image']);
            }
            $re = model('article')->edit_article($data);
            if($re > 0){
                Api()->setApi('url',url('Articles/index',['page'=>input('page')]))->ApiSuccess();
            }else{
                Api()->setApi('msg',$re)->setApi('url',0)->ApiError();
            }
        }else{
            $articles = $this->getTypeTree();

            $article_id = input('article_id');
            $article_info = Article::get($article_id)->toArray();
            $article_info['image'] = $this->var['upload'].$article_info['image'];
            return view([
                'article_info'=>$article_info,
                'articles' => $articles,
            ]);
        }
    }

    /*
     * 删除文章
     */
    public function article_del(){
        if(request()->isAjax()){
            $time = time();
            $data = input();
            $obj =$this->setStatus('article',$time,$data['id'],'article_id','delete_time');
            if(1 == $obj->code){
                Api()->setApi('url',input('location'))->ApiSuccess();
            }else{
                Api()->setApi('url',0)->ApiError();
            }
        }
    }

    /*
	 * 文章状态修改
	 */
    public function article_status(){
        $status = input('status');
        $id = input('article_id');
        $url = input('location',url('Articles/index'));
        $re = $this->setStatus('article',$status,$id,'article_id');
        if($re->code == 1){
            Api()->setApi('url',$url)->ApiSuccess($re);
        }else{
            Api()->setApi('url',0)->ApiError();
        }
    }
}