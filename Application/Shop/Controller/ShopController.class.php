<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 14-6-18
 * Time: 上午10:07
 * @author 郑钟良<zzl@ourstu.com>
 */
namespace Admin\Controller;

use Admin\Builder\AdminConfigBuilder;
use Admin\Builder\AdminListBuilder;
use Admin\Builder\AdminTreeListBuilder;

use Think\Model;

/**
 * Class ShopController
 * @package Admin\controller
 * @郑钟良
 */
class ShopController extends AdminController
{

    protected $shopModel;
    protected $shop_configModel;
    protected $shop_categoryModel;

    function _initialize()
    {
        $this->shopModel = D('Shop/Shop');
        $this->shop_configModel = D('Shop/ShopConfig');
        $this->shop_categoryModel = D('Shop/ShopCategory');
        parent::_initialize();
    }

    /**商品分类
     * @author 郑钟良<zzl@ourstu.com>
     */
    public function shopCategory()
    {
        //显示页面
        $builder = new AdminTreeListBuilder();
        $attr['class'] = 'btn ajax-post';
        $attr['target-form'] = 'ids';

        $tree = $this->shop_categoryModel->getTree(0, 'id,title,sort,pid,status');

        $builder->title('商城分类管理')
            ->buttonNew(U('Shop/add'))
            ->data($tree)
            ->display();
    }

    /**分类添加
     * @param int $id
     * @param int $pid
     * @author 郑钟良<zzl@ourstu.com>
     */
    public function add($id = 0, $pid = 0)
    {
        if (IS_POST) {
            $title=$id?"编辑":"新增";
            if ($this->shop_categoryModel->editData()) {
                $this->success($title.'成功。', U('Shop/shopCategory'));
            } else {
                $this->error($title.'失败!'.$this->shop_categoryModel->getError());
            }
        } else {
            $builder = new AdminConfigBuilder();
            $categorys = $this->shop_categoryModel->select();
            $opt = array();
            foreach ($categorys as $category) {
                $opt[$category['id']] = $category['title'];
            }
            if ($id != 0) {
                $category = $this->shop_categoryModel->find($id);
            } else {
                $category = array('pid' => $pid, 'status' => 1);
                $father_category_pid=$this->shop_categoryModel->where(array('id'=>$pid))->getField('pid');
                if($father_category_pid!=0){
                    $this->error('分类不能超过二级！');
                }
            }
            $builder->title('新增分类')->keyId()->keyText('title', '标题')->keySelect('pid', '父分类', '选择父级分类', array('0' => '顶级分类') + $opt)
                ->keyStatus()->keyCreateTime()->keyUpdateTime()
                ->data($category)
                ->buttonSubmit(U('Shop/add'))->buttonBack()->display();
        }

    }

    /**分类回收站
     * @param int $page
     * @param int $r
     * @author 郑钟良<zzl@ourstu.com>
     */
    public function categoryTrash($page = 1, $r = 20,$model='')
    {
        $builder = new AdminListBuilder();
        $builder->clearTrash($model);
        //读取微博列表
        $map = array('status' => -1);
        $list = $this->shop_categoryModel->where($map)->page($page, $r)->select();
        $totalCount = $this->shop_categoryModel->where($map)->count();

        //显示页面

        $builder->title('商城分类回收站')
            ->setStatusUrl(U('setStatus'))->buttonRestore()->buttonClear('ShopCategory')
            ->keyId()->keyText('title', '标题')->keyStatus()->keyCreateTime()
            ->data($list)
            ->pagination($totalCount, $r)
            ->display();
    }

    /**
     * 设置商品分类状态：删除=-1，禁用=0，启用=1
     * @param $ids
     * @param $status
     * @author 郑钟良<zzl@ourstu.com>
     */
    public function setStatus($ids, $status)
    {
        $builder = new AdminListBuilder();
        $builder->doSetStatus('shopCategory', $ids, $status);
    }

    /**
     * 设置商品状态：删除=-1，禁用=0，启用=1
     * @param $ids
     * @param $status
     * @author 郑钟良<zzl@ourstu.com>
     */
    public function setGoodsStatus($ids, $status)
    {
        $builder = new AdminListBuilder();
        $builder->doSetStatus('shop', $ids, $status);
    }

    /**商品列表
     * @param int $page
     * @param int $r
     * @author 郑钟良<zzl@ourstu.com>
     */
    public function goodsList($page = 1, $r = 20)
    {
        $map['status'] = array('egt', 0);
        $goodsList = $this->shopModel->where($map)->order('createtime desc')->page($page, $r)->select();
        $totalCount = $this->shopModel->where($map)->count();
        $builder = new AdminListBuilder();
        $builder->title('商品列表');
        $builder->meta_title = '商品列表';
        foreach ($goodsList as &$val) {
            $category = $this->shop_categoryModel->where('id=' . $val['category_id'])->getField('title');
            $val['category'] = $category;
            unset($category);
            $val['is_new'] = ($val['is_new'] == 0) ? '否' : '是';
        }
        unset($val);
        $builder->buttonNew(U('Shop/goodsEdit'))->buttonDelete(U('setGoodsStatus'))->setStatusUrl(U('setGoodsStatus'));
        $builder->keyId()->keyText('goods_name', '商品名称')->keyText('category', '商品分类')->keyText('goods_introduct', '商品广告语')
            ->keyText('money_need', '商品价格')->keyText('goods_num', '商品余量')->keyText('sell_num', '已售出量')->keyLink('is_new', '是否为新品', 'Shop/setNew?id=###')->keyStatus('status', '出售状态')->keyUpdateTime('changetime')->keyCreateTime('createtime')->keyDoActionEdit('Shop/goodsEdit?id=###');
        $builder->data($goodsList);
        $builder->pagination($totalCount, $r);
        $builder->display();
    }

    /**设置是否为新品
     * @param int $id
     * @author 郑钟良<zzl@ourstu.com>
     */
    public function setNew($id = 0)
    {
        if ($id == 0) {
            $this->error('请选择商品');
        }
        $is_new = intval(!$this->shopModel->where(array('id' => $id))->getField('is_new'));
        $rs = $this->shopModel->where(array('id' => $id))->setField(array('is_new' => $is_new, 'changetime' => time()));
        if ($rs) {
            $this->success('设置成功！');
        } else {
            $this->error('设置失败！');
        }
    }

    /**商品回收站
     * @param int $page
     * @param int $r
     * @author 郑钟良<zzl@ourstu.com>
     */
    public function goodsTrash($page = 1, $r = 10,$model='')
    {
        $builder = new AdminListBuilder();
        $builder->clearTrash($model);
        //读取微博列表
        $map = array('status' => -1);
        $goodsList = $this->shopModel->where($map)->order('changetime desc')->page($page, $r)->select();
        $totalCount = $this->shopModel->where($map)->count();

        //显示页面

        $builder->title('商品回收站')
            ->setStatusUrl(U('setGoodsStatus'))->buttonRestore()->buttonClear('Shop/Shop')
            ->keyId()->keyLink('goods_name', '标题', 'Shop/goodsEdit?id=###')->keyCreateTime()->keyStatus()
            ->data($goodsList)
            ->pagination($totalCount, $r)
            ->display();
    }

    /**
     * @param int $id
     * @param $goods_name
     * @param $goods_ico
     * @param $goods_introduct
     * @param $goods_detail
     * @param $money_need
     * @param $goods_num
     * @param $status
     * @param $category_id
     * @param $is_new
     * @param $sell_num
     * @author 郑钟良<zzl@ourstu.com>
     */
    public function goodsEdit($id = 0, $goods_name = '', $goods_ico = '', $goods_introduct = '', $goods_detail = '', $money_need = '', $goods_num = '', $status = '', $category_id = 0, $is_new = 0, $sell_num = 0)
    {
        $isEdit = $id ? 1 : 0;
        if (IS_POST) {
            if ($goods_name == '' || $goods_name == null) {
                $this->error('请输入商品名称');
            }
            if (!is_numeric($goods_ico)) {
                $this->error('请上传商品图标');
            }
            if ($goods_introduct == '' || $goods_introduct == null) {
                if ($goods_detail == '' || $goods_detail == null) {
                    $this->error('请输入商品广告语');
                } else {
                    $goods_introduct = substr($goods_detail, 0, 25);
                }
            }
            if (!(is_numeric($money_need) && $money_need >= 0)) {
                $this->error('请正确输入商品价格');
            }
            if (!(is_numeric($goods_num) && $goods_num >= 0)) {
                $this->error('请正确输入商品剩余量');
            }
            if (!(is_numeric($sell_num) && $sell_num >= 0)) {
                $this->error('请正确输入商品已售量');
            }
            $goods['goods_name'] = $goods_name;
            $goods['goods_ico'] = $goods_ico;
            $goods['goods_introduct'] = $goods_introduct;
            $goods['goods_detail'] = $goods_detail;
            $goods['money_need'] = $money_need;
            $goods['goods_num'] = $goods_num;
            $goods['status'] = $status;
            $goods['category_id'] = $category_id;
            $goods['is_new'] = $is_new;
            $goods['sell_num'] = $sell_num;
            $goods['changetime'] = time();
            if ($isEdit) {
                $rs = $this->shopModel->where('id=' . $id)->save($goods);
            } else {
                //商品名存在验证
                $map['status'] = array('egt', 0);
                $map['goods_name'] = $goods_name;
                if ($this->shopModel->where($map)->count()) {
                    $this->error('已存在同名商品');
                }

                $goods['createtime'] = time();
                $rs = $this->shopModel->add($goods);
            }
            if ($rs) {
                $this->success($isEdit ? '编辑成功' : '添加成功', U('Shop/goodsList'));
            } else {
                $this->error($isEdit ? '编辑失败' : '添加失败');
            }
        } else {
            $builder = new AdminConfigBuilder();
            $builder->title($isEdit ? '编辑商品' : '添加商品');
            $builder->meta_title = $isEdit ? '编辑商品' : '添加商品';

            //获取分类列表
            $category_map['status'] = array('egt', 0);
            $goods_category_list = $this->shop_categoryModel->where($category_id)->order('pid desc')->select();
            $options = array_combine(array_column($goods_category_list, 'id'), array_column($goods_category_list, 'title'));
            $builder->keyId()->keyText('goods_name', '商品名称')->keySingleImage('goods_ico', '商品图标')->keySelect('category_id', '商品分类', '', $options)->keyText('goods_introduct', '商品广告语')->keyEditor('goods_detail', '商品详情')
                ->keyInteger('money_need', '商品价格')->keyInteger('goods_num', '商品余量')->keyInteger('sell_num', '已售出量')->keyBool('is_new', '是否为新品')->keyStatus('status', '出售状态');
            if ($isEdit) {
                $goods = $this->shopModel->where('id=' . $id)->find();
                $builder->data($goods);
                $builder->buttonSubmit(U('Shop/goodsEdit'));
                $builder->buttonBack();
                $builder->display();
            } else {
                $goods['status'] = 1;
                $builder->buttonSubmit(U('Shop/goodsEdit'));
                $builder->buttonBack();
                $builder->data($goods);
                $builder->display();
            }
        }
    }

    public function shopConfig()
    {
        $builder = new AdminConfigBuilder;
        $data = $builder->handleConfig();

        //初始化数据
        !isset($data['SHOP_SCORE_TYPE'])&&$data['SHOP_SCORE_TYPE']='1';
        !isset($data['SHOP_HOT_SELL_NUM'])&&$data['SHOP_HOT_SELL_NUM']='10';

        //读取数据
        $map = array('status' => array('GT', -1));
        $model = D('Ucenter/Score');
        $score_types = $model->getTypeList($map);
        $score_type_options=array();
        foreach($score_types as $val){
            $score_type_options[$val['id']]=$val['title'];
        }
        $builder->title('商城配置')
            ->keySelect('SHOP_SCORE_TYPE', '商城兑换使用的积分类型', '',$score_type_options)
            ->keyInteger('SHOP_HOT_SELL_NUM','商城热销阀值','销量超过该值时，商品为热销商品')
            ->group('基础配置','SHOP_SCORE_TYPE,SHOP_HOT_SELL_NUM')
            ->groupLocalComment('本地评论配置','goodsDetail')
            ->data($data)
            ->buttonSubmit()
            ->buttonBack()
            ->display();
    }

    /**已完成交易列表
     * @param int $page
     * @param int $r
     * @author 郑钟良<zzl@ourstu.com>
     */
    public function goodsBuySuccess($page = 1, $r = 20)
    {
        //读取列表
        $map = array('status' => 1);
        $model = M('shop_buy');
        $list = $model->where($map)->page($page, $r)->select();
        $totalCount = $model->where($map)->count();

        foreach ($list as &$val) {
            $val['goods_name'] = $this->shopModel->where('id=' . $val['goods_id'])->getField('goods_name');
            $address = D('shop_address')->find($val['address_id']);
            $val['name'] = $address['name'];
            $val['address'] = $address['address'];
            $val['zipcode'] = $address['zipcode'];
            $val['phone'] = $address['phone'];
        }
        unset($val);
        //显示页面
        $builder = new AdminListBuilder();

        $builder->title('完成的交易');
        $builder->meta_title = '完成的交易';

        $builder->buttonDisable(U('setGoodsBuyStatus'), '取消发货')
            ->keyId()->keyText('goods_name', '商品名称')->keyUid()->keyText('name', '收货人姓名')->keyText('address', '收货地址')->keyText('zipcode', '邮编')->keyText('phone', '手机号码')->keyCreateTime('createtime', '购买时间')->keyTime('gettime', '交易完成时间')
            ->data($list)
            ->pagination($totalCount, $r)
            ->display();
    }

    /**待发货交易列表
     * @param int $page
     * @param int $r
     * @author 郑钟良<zzl@ourstu.com>
     */
    public function verify($page = 1, $r = 10)
    {
        //读取列表
        $map = array('status' => 0);
        $model = M('shop_buy');
        $list = $model->where($map)->page($page, $r)->select();
        $totalCount = $model->where($map)->count();
        foreach ($list as &$val) {
            $val['goods_name'] = op_t($this->shopModel->where('id=' . $val['goods_id'])->getField('goods_name'));
            $address = D('shop_address')->find($val['address_id']);
            $val['name'] = op_t($address['name']);
            $val['address'] = op_t($address['address']);
            $val['zipcode'] = op_t($address['zipcode']);
            $val['phone'] = op_t($address['phone']);
        }
        unset($val);
        //显示页面
        $builder = new AdminListBuilder();

        $builder->title('待发货交易');
        $builder->meta_title = '待发货交易';

        $builder->setStatusUrl(U('setGoodsBuyStatus'))->buttonEnable('', '发货')
            ->keyId()->keyText('goods_name', '商品名称')->keyUid()->keyText('name', '收货人姓名')->keyText('address', '收货地址')->keyText('zipcode', '邮编')->keyText('phone', '手机号码')->keyCreateTime('createtime', '购买时间')->key('status','状态', 'status',array(0=>'未发货',1=>'已发货'))
            ->data($list)
            ->pagination($totalCount, $r)
            ->display();
    }


    public function setGoodsBuyStatus($ids, $status)
    {
        $builder = new AdminListBuilder();
        if ($status == 1) {
            $gettime = time();
            foreach ($ids as $id) {
                D('shop_buy')->where('id=' . $id)->setField('gettime', $gettime);
                $content = D('shop_buy')->find($id);
                $message = "你购买的商品已发货。现在可以在已完成交易列表中查看该交易。";
                D('Message')->sendMessageWithoutCheckSelf($content['uid'], '发货通知',$message,  'Shop/Index/myGoods', array('status' => '1'), is_login(), 1);

                //商城记录
                $goods_name = D('shop')->field('goods_name')->find($content['goods_id']);
                $shop_log['message'] = '在' . time_format($gettime) . '[' . is_login() . ']' . query_user('nickname', is_login()) . '发送了用户[' . $content['uid'] . ']' . query_user('nickname', $content['uid']) . '购买的商品：<a href="index.php?s=/Shop/Index/goodsDetail/id/' . $content['goods_id'] . '.html" target="_black">' . $goods_name['goods_name'] . '</a>';
                $shop_log['uid'] = is_login();
                $shop_log['create_time'] = $gettime;
                D('shop_log')->add($shop_log);
            }
        }
        $builder->doSetStatus('shop_buy', $ids, $status);
    }

    /**商城日志
     * @param int $page
     * @param int $r
     * @author 郑钟良<zzl@ourstu.com>
     */
    public function shopLog($page = 1, $r = 20)
    {
        //读取列表
        $model = M('shop_log');
        $list = $model->page($page, $r)->order('create_time desc')->select();
        $totalCount = $model->count();
        //显示页面
        $builder = new AdminListBuilder();

        $builder->title('商城信息记录');
        $builder->meta_title = '商城信息记录';

        $builder->keyId()->keyText('message', '信息')->keyUid()->keyCreateTime()
            ->data($list)
            ->pagination($totalCount, $r)
            ->display();
    }

}
