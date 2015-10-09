<?php


namespace Admin\Controller;

use Admin\Builder\AdminConfigBuilder;

class PeopleController extends AdminController
{
    public function config()
    {
        $builder = new AdminConfigBuilder();
        $data = $builder->handleConfig();
        $builder->title(L('basic_conf'));
        $data['MAX_SHOW_HEIGHT'] = $data['MAX_SHOW_HEIGHT'] ? $data['MAX_SHOW_HEIGHT'] :160;
        $builder->keyInteger('MAX_SHOW_HEIGHT', L('max_show_height'))->keyDefault('MAX_SHOW_HEIGHT',160);

        $role_list=M('Role')->where(array('status'=>1))->field('id,title')->select();
        foreach($role_list as &$val){
            $val=array('data-id' => $val['id'], 'title' => $val['title']);
        }
        unset($val);
        $default = array(array('data-id' => 'disable', 'title' => L('disable'), 'items' => $role_list), array('data-id' => 'enable', 'title' => L('enabled'), 'items' => array()));
        $builder->keyKanban('SHOW_ROLE_TAB', L('identity_tab'),'identity_tab_affix');
        $data['SHOW_ROLE_TAB'] = $builder->parseKanbanArray($data['SHOW_ROLE_TAB'], $role_list, $default);
        $builder->group(L('basic_conf'), 'MAX_SHOW_HEIGHT,SHOW_ROLE_TAB');

        $data['USER_SHOW_TITLE1'] = $data['USER_SHOW_TITLE1'] ? $data['USER_SHOW_TITLE1'] : L('active_member');
        $data['USER_SHOW_COUNT1'] = $data['USER_SHOW_COUNT1'] ? $data['USER_SHOW_COUNT1'] : 5;
        $data['USER_SHOW_ORDER_FIELD1'] = $data['USER_SHOW_ORDER_FIELD1'] ? $data['USER_SHOW_ORDER_FIELD1'] : 'score1';
        $data['USER_SHOW_ORDER_TYPE1'] = $data['USER_SHOW_ORDER_TYPE1'] ? $data['USER_SHOW_ORDER_TYPE1'] : 'desc';
        $data['USER_SHOW_CACHE_TIME1'] = $data['USER_SHOW_CACHE_TIME1'] ? $data['USER_SHOW_CACHE_TIME1'] : '600';


        $data['USER_SHOW_TITLE2'] = $data['USER_SHOW_TITLE2'] ? $data['USER_SHOW_TITLE2'] : L('new_member');
        $data['USER_SHOW_COUNT2'] = $data['USER_SHOW_COUNT2'] ? $data['USER_SHOW_COUNT2'] : 5;
        $data['USER_SHOW_ORDER_FIELD2'] = $data['USER_SHOW_ORDER_FIELD2'] ? $data['USER_SHOW_ORDER_FIELD2'] : 'reg_time';
        $data['USER_SHOW_ORDER_TYPE2'] = $data['USER_SHOW_ORDER_TYPE2'] ? $data['USER_SHOW_ORDER_TYPE2'] : 'desc';
        $data['USER_SHOW_CACHE_TIME2'] = $data['USER_SHOW_CACHE_TIME2'] ? $data['USER_SHOW_CACHE_TIME2'] : '600';


        $score=D("Ucenter/Score")->getTypeList(array('status'=>1));
        $order['reg_time']=L('register_time');
        $order['last_login_time']=L('last_login_time');


        foreach ($score as $s) {
            $order['score'.$s['id']]='【'.$s['title'].'】';
        }

        $builder->keyText('USER_SHOW_TITLE1', L('title_name'), L('block_title'));
        $builder->keyText('USER_SHOW_COUNT1', L('show_people'), L('tip_after_enabled'));
        $builder->keyRadio('USER_SHOW_ORDER_FIELD1', L('sort_number'), L('show_sort_style'), $order);
        $builder->keyRadio('USER_SHOW_ORDER_TYPE1', L('sort_style'),L('show_sort_style'), array('desc' => L('counter'), 'asc' => L('direct')));
        $builder->keyText('USER_SHOW_CACHE_TIME1', L('cache_time'), L('tip_cache_time'));

        $builder->keyText('USER_SHOW_TITLE2', L('title_name'), L('block_title'));
        $builder->keyText('USER_SHOW_COUNT2', L('show_people'), L('tip_after_enabled'));
        $builder->keyRadio('USER_SHOW_ORDER_FIELD2', L('sort_number'), L('show_sort_style'), $order);
        $builder->keyRadio('USER_SHOW_ORDER_TYPE2', L('sort_style'), L('show_sort_style'), array('desc' => L('counter'), 'asc' => L('direct')));
        $builder->keyText('USER_SHOW_CACHE_TIME2', L('cache_time'), L('tip_cache_time'));



        $builder->group(L('home_show_left'), 'USER_SHOW_TITLE1,USER_SHOW_COUNT1,USER_SHOW_ORDER_FIELD1,USER_SHOW_ORDER_TYPE1,USER_SHOW_CACHE_TIME1');
        $builder->group(L('home_show_right'), 'USER_SHOW_TITLE2,USER_SHOW_COUNT2,USER_SHOW_ORDER_FIELD2,USER_SHOW_ORDER_TYPE2,USER_SHOW_CACHE_TIME2');
        $builder->data($data);
        $builder->buttonSubmit();
        $builder->display();
    }

}