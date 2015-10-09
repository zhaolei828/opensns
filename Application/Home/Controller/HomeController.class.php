<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Admin\Controller;

use Admin\Builder\AdminConfigBuilder;


class HomeController extends AdminController
{


    public function config()
    {
        $builder = new AdminConfigBuilder();
        $data = $builder->handleConfig();

        $data['OPEN_LOGIN_PANEL'] = $data['OPEN_LOGIN_PANEL'] ? $data['OPEN_LOGIN_PANEL'] : 1;


        $builder->title(L('home_setting'));

        $modules = D('Common/Module')->getAll();
        foreach ($modules as $m) {
            if ($m['is_setup'] == 1 && $m['entry'] != '') {
                if (file_exists(APP_PATH . $m['name'] . '/Widget/HomeBlockWidget.class.php')) {
                    $module[] = array('data-id' => $m['name'], 'title' => $m['alias']);
                }
            }
        }
        $module[] = array('data-id' => 'slider', 'title' => L('carousel'));

        $default = array(array('data-id' => 'disable', 'title' => L('disabled'), 'items' => $module), array('data-id' => 'enable', 'title' =>L('enabled'), 'items' => array()));
        $builder->keyKanban('BLOCK', L('display_block'),L('tip_display_block'));
        $data['BLOCK'] = $builder->parseKanbanArray($data['BLOCK'], $module, $default);
        $builder->group(L('display_block'), 'BLOCK');


        $builder->keySingleImage('PIC1', L('picture'));
        $builder->keyText('URL1', L('link'));
        $builder->keyText('TITLE1', L('title'));
        $builder->keyRadio('TARGET1', L('new_window_open'), '', array('_blank' => L('new_window'), '_self' => L('self_window')));

        $builder->group(L('slide1'), 'PIC1,URL1,TITLE1,TARGET1');

        $builder->keySingleImage('PIC2', L('picture'));
        $builder->keyText('URL2', L('link'));
        $builder->keyText('TITLE2', L('title'));
        $builder->keyRadio('TARGET2', L('new_window_open'), '', array('_blank' => L('new_window'), '_self' => L('self_window')));

        $builder->group(L('slide2'), 'PIC2,URL2,TITLE2,TARGET2');


        $builder->keySingleImage('PIC3', L('picture'));
        $builder->keyText('URL3', L('link'));
        $builder->keyText('TITLE3', L('title'));
        $builder->keyRadio('TARGET3', L('new_window_open'), '', array('_blank' => L('new_window'), '_self' => L('self_window')));

        $builder->group(L('slide3'), 'PIC3,URL3,TITLE3,TARGET3');

        $show_blocks = get_kanban_config('BLOCK_SORT', 'enable', array(), 'Home');


        $builder->buttonSubmit();


        $builder->data($data);


        $builder->display();
    }


}
