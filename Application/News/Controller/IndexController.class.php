<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 15-4-28
 * Time: 上午11:30
 * @author 郑钟良<zzl@ourstu.com>
 */

namespace News\Controller;


use Think\Controller;

class IndexController extends Controller{

    protected $newsModel;
    protected $newsDetailModel;
    protected $newsCategoryModel;

    function _initialize()
    {
        if(isset($_POST['keywords'])){
            $_GET['keywords']=json_encode(trim($_POST['keywords']));
        }
        $keywords=$_GET['keywords'];

        $this->newsModel = D('News/News');
        $this->newsDetailModel = D('News/NewsDetail');
        $this->newsCategoryModel = D('News/NewsCategory');

        $tree = $this->newsCategoryModel->getTree(0,true,array('status' => 1));
        $this->assign('tree', $tree);
        $menu_list['left'][]=array( 'title' => '首页', 'href' => U('News/Index/index'),'tab'=>'home');
        foreach ($tree as $category) {
            $menu = array('tab' => 'category_' . $category['id'], 'title' => $category['title'], 'href' => U('News/index/index', array('category' => $category['id'],'keywords'=>$keywords)));
            if ($category['_']) {
                $menu['children'][] = array( 'title' => '全部', 'href' => U('News/index/index', array('category' => $category['id'],'keywords'=>$keywords)));
                foreach ($category['_'] as $child)
                    $menu['children'][] = array( 'title' => $child['title'], 'href' => U('News/index/index', array('category' => $child['id'],'keywords'=>$keywords)));
            }
            $menu_list['left'][] = $menu;
        }
        $menu_list['right']=array();
        if(is_login()){
            $menu_list['right'][]=array('tab' => 'myNews', 'title' => '我的投稿', 'href' =>U('News/index/my'));
        }

        $show_edit=S('SHOW_EDIT_BUTTON');
        if($show_edit===false){
            $map['can_post']=1;
            $map['status']=1;
            $show_edit=$this->newsCategoryModel->where($map)->count();
            S('SHOW_EDIT_BUTTON',$show_edit);
        }
        if($show_edit){
            $menu_list['right'][]=array('tab' => 'create', 'title' => '<i class="icon-edit"></i> 投稿', 'href' =>is_login()?U('News/index/edit'):"javascript:toast.error('登录后才能操作')");
            $menu_list['right'][]=array('type'=>'search', 'input_title' => '输入标题/摘要关键字','input_name'=>'keywords','from_method'=>'post', 'action' =>U('News/index/index'));
        }
        $this->assign('tab','home');
        $this->assign('sub_menu', $menu_list);
    }

    public function index($page=1)
    {
        if(json_decode($_GET['keywords'])!=''){
            $keywords=json_decode($_GET['keywords']);
            $this->assign('search_keywords',$keywords);
            $map['title|description']=array('like','%'.$keywords.'%');
        }else{
            $_GET['keywords']=null;
        }
        /* 分类信息 */
        $category = I('category',0,'intval');
        $current='';
        if($category){
            $this->_category($category);
            $cates=$this->newsCategoryModel->getCategoryList(array('pid'=>$category,'status'=>1));
            if(count($cates)){
                $cates=array_column($cates,'id');
                $cates=array_merge(array($category),$cates);
                $map['category']=array('in',$cates);
            }else{
                $map['category']=$category;
            }
            $now_category=$this->newsCategoryModel->find($category);
            $cid=$now_category['pid']==0?$now_category['id']:$now_category['pid'];
            $current='category_' . $cid;
        }
        $map['dead_line']=array('gt',time());
        $map['status']=1;

        $order_field=modC('NEWS_ORDER_FIELD','create_time','News');
        $order_type=modC('NEWS_ORDER_TYPE','desc','News');
        $order='sort desc,'.$order_field.' '.$order_type;

        /* 获取当前分类下资讯列表 */
        list($list,$totalCount) = $this->newsModel->getListByPage($map,$page,$order,'*',modC('NEWS_PAGE_NUM',20,'News'));
        foreach($list as &$val){
            $val['user']=query_user(array('space_url','nickname'),$val['uid']);
        }
        unset($val);
        /* 模板赋值并渲染模板 */
        $this->assign('list', $list);
        $this->assign('category', $category);
        $this->assign('totalCount',$totalCount);
        $current= ($current==''?'home':$current);
        $this->assign('tab',$current);
        $this->display();
    }

    public function my($page=1)
    {
        $this->_needLogin();
        $map['uid']=get_uid();
        /* 获取当前分类下资讯列表 */
        list($list,$totalCount) = $this->newsModel->getListByPage($map,$page,'update_time desc','*',modC('NEWS_PAGE_NUM',20,'News'));
        foreach($list as &$val){
            if($val['dead_line']<=time()){
                $val['audit_status']= '<span style="color: #7f7b80;">已过期</span>';
            }else{
                if($val['status']==1){
                    $val['audit_status']='<span style="color: green;">审核通过</span>';
                }elseif($val['status']==2){
                    $val['audit_status']='<span style="color:#4D9EFF;">待审核</span>';
                }elseif($val['status']==-1){
                    $val['audit_status']='<span style="color: #b5b5b5;">审核失败</span>';
                }
            }

        }
        unset($val);
        /* 模板赋值并渲染模板 */
        $this->assign('list', $list);
        $this->assign('totalCount',$totalCount);

        $this->assign('tab','myNews');
        $this->display();
    }

    public function detail()
    {
        $aId=I('id',0,'intval');

        /* 标识正确性检测 */
        if (!($aId && is_numeric($aId))) {
            $this->error('文档ID错误！');
        }

        $info=$this->newsModel->getData($aId);
        if($info['dead_line']<=time()&&!check_auth('News/Index/edit',$info['uid'])){
            $this->error('该资讯已过期！');
        }
        $author=query_user(array('uid','space_url','nickname','avatar64','signature'),$info['uid']);
        $author['news_count']=$this->newsModel->where(array('uid'=>$info['uid']))->count();
        /* 获取模板 */
        if (!empty($info['detail']['template'])) { //已定制模板
            $tmpl = 'Index/tmpl/'.$info['detail']['template'];
        } else { //使用默认模板
            $tmpl = 'Index/tmpl/detail';
        }

        $this->_category($info['category']);

        /* 更新浏览数 */
        $map = array('id' => $aId);
        $this->newsModel->where($map)->setInc('view');
        /* 模板赋值并渲染模板 */
        $this->assign('author',$author);
        $this->assign('info', $info);
        $this->setTitle('{$info.title|text} —— {$MODULE_ALIAS}');
        $this->setDescription('{$info.description|text} ——{$MODULE_ALIAS}');
        $this->display($tmpl);
    }

    public function edit()
    {
        $this->_needLogin();
        if(IS_POST){
            $this->_doEdit();
        }else{
            $aId=I('id',0,'intval');
            if($aId){
                $data=$this->newsModel->getData($aId);
                if(!check_auth('News/Index/edit',-1)){
                    if($data['uid']==is_login()){
                        if($data['status']==1){
                            $this->error('该资讯已被审核，不能被编辑！');
                        }
                    }else{
                        $this->error('你没有编辑该资讯权限！');
                    }
                }
                $this->assign('data',$data);
            }else{
                $this->checkAuth('News/Index/add',-1,'你没有投稿权限！');
            }
            $title=$aId?"编辑":"新增";
            $category=$this->newsCategoryModel->getCategoryList(array('status'=>1,'can_post'=>1),1);
            $this->assign('category',$category);
            $this->assign('title',$title);
        }
        $this->assign('tab','create');
        $this->display();
    }

    private function _doEdit()
    {
        $aId=I('post.id',0,'intval');
        $data['category']=I('post.category',0,'intval');

        if($aId){
            $data['id']=$aId;
            $now_data=$this->newsModel->getData($aId);
            if(!check_auth('News/Index/edit',-1)){
                if($now_data['uid']==is_login()){
                    if($now_data['status']==1){
                        $this->error('该资讯已被审核，不能被编辑！');
                    }
                }else{
                    $this->error('你没有编辑该资讯权限！');
                }
            }
            $category=$this->newsCategoryModel->where(array('status'=>1,'id'=>$data['category']))->find();
            if($category){
                if($category['can_post']){
                    if($category['need_audit']&&!check_auth('Admin/News/setNewsStatus')){
                        $data['status']=2;
                    }else{
                        $data['status']=1;
                    }
                }else{
                    $this->error('该分类不能投稿！');
                }
            }else{
                $this->error('该分类不存在或被禁用！');
            }
            $data['template']=$now_data['detail']['template']?:'';
        }else{
            $this->checkAuth('News/Index/add',-1,'你没有投稿权限！');
            $this->checkActionLimit('add_news','News',0,is_login(),true);
            $data['uid']=get_uid();
            $data['sort']=$data['position']=$data['view']=$data['comment']=$data['collection']=0;
            $category=$this->newsCategoryModel->where(array('status'=>1,'id'=>$data['category']))->find();
            if($category){
                if($category['can_post']){
                    if($category['need_audit']&&!check_auth('Admin/News/setNewsStatus')){
                        $data['status']=2;
                    }else{
                        $data['status']=1;
                    }
                }else{
                    $this->error('该分类不能投稿！');
                }
            }else{
                $this->error('该分类不存在或被禁用！');
            }
            $data['template']='';
        }
        $data['title']=I('post.title','','text');
        $data['cover']=I('post.cover',0,'intval');
        $data['description']=I('post.description','','text');
        $data['dead_line']=I('post.dead_line','','text');
        if($data['dead_line']==''){
            $data['dead_line']=2147483640;
        }else{
            $data['dead_line']=strtotime($data['dead_line']);
        }
        $data['source']=I('post.source','','text');
        $data['content']=I('post.content','','filter_content');

        if(!mb_strlen($data['title'],'utf-8')){
            $this->error('标题不能为空！');
        }
        if(mb_strlen($data['content'],'utf-8')<20){
            $this->error('内容不能少于20个字！');
        }

        $res=$this->newsModel->editData($data);
        $title=$aId?"编辑":"新增";
        if($res){
            if(!$aId){
                $aId=$res;
                if($category['need_audit']&&!check_auth('Admin/News/setNewsStatus')){
                    $this->success($title.'资讯成功！'.cookie('score_tip').' 请等待审核~',U('News/Index/detail',array('id'=>$aId)));
                }
            }
            $this->success($title.'资讯成功！'.cookie('score_tip'),U('News/Index/detail',array('id'=>$aId)));
        }else{
            $this->error($title.'资讯失败！'.$this->newsModel->getError());
        }
    }

    private function _category($id=0)
    {
        $now_category=$this->newsCategoryModel->getTree($id,'id,title,pid,sort',array('status'=>1));
        $this->assign('now_category',$now_category);
        return $now_category;
    }
    private function _needLogin()
    {
        if(!is_login()){
            $this->error('请先登录！');
        }
    }
} 