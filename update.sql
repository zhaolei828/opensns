ALTER TABLE `ocenter_seo_rule` ADD `summary` VARCHAR( 500 ) NOT NULL COMMENT 'seo变量介绍';

DELETE FROM `ocenter_seo_rule` WHERE `id` <=1000;
INSERT INTO `ocenter_seo_rule` (`id`, `title`, `app`, `controller`, `action`, `status`, `seo_keywords`, `seo_description`, `seo_title`, `sort`, `summary`) VALUES
(1000, '整站标题', '', '', '', 1, '', '', '', 7, '-'),
(1001, '用户中心', 'Ucenter', 'index', 'index', 1, '{$user_info.username|text}的个人主页', '{$user_info.username|text}的个人主页', '{$user_info.nickname|op_t}的个人主页', 3, '-'),
(1002, '网站首页', 'Home', 'Index', 'index', 1, '', '', '', 0, '-'),
(1003, '积分商城首页', 'Shop', 'Index', 'index', 1, '', '', '', 0, '-'),
(1004, '商品列表', 'Shop', 'Index', 'goods', 1, '', '', '', 0, 'category_name：当前分类名\nchild_category_name：子分类名'),
(1005, '商品详情', 'Shop', 'Index', 'goodsdetail', 1, '', '', '', 0, 'content：商品变量集\n   content.goods_name 商品名\n   content.goods_introduct 商品简介\n   content.goods_detail 商品详情'),
(1006, '活动主页', 'Event', 'Index', 'index', 1, '', '', '', 0, '-'),
(1007, '活动详情', 'Event', 'Index', 'detail', 1, '', '', '', 0, 'content：活动变量集\n   content.title 活动名称\n   content.type.title 活动分类名\n   content.user.nickname 发起人昵称\n   content.address 活动地点\n   content.limitCount 人数限制'),
(1008, '活动成员', 'Event', 'Index', 'member', 1, '', '', '', 0, '-'),
(1009, '专辑首页', 'Issue', 'Index', 'index', 1, '', '', '', 0, '-'),
(1010, '专辑详情', 'Issue', 'Index', 'issuecontentdetail', 1, '', '', '', 0, 'content：专辑内容变量集\n   content.user.nickname 内容发布者昵称\n   content.user.signature 内容发布者签名\n   content.url 内容的Url\n   content.title 内容标题\n   content.create_time|friendlyDate 发布时间\n   content.update_time|friendlyDate 更新时间'),
(1011, '论坛主页', 'Forum', 'Index', 'index', 1, '', '', '', 0, '-'),
(1012, '某个版块的帖子列表', 'Forum', 'Index', 'forum', 1, '', '', '', 0, 'forum：版块变量集\n   forum.description 版块描述\n   forum.title 版块名称\n   forum.topic_count 主题数\n   forum.total_count 帖子数'),
(1013, '帖子详情', 'Forum', 'Index', 'detail', 1, '', '', '', 0, 'post：帖子变量集\n   post.title 帖子标题\n   post.content 帖子详情\n   post.forum.title 帖子所在版块标题\n   '),
(1014, '搜索帖子', 'Forum', 'Index', 'search', 1, '', '', '', 0, 'keywords：搜索的关键词，2.4.0以后版本提供'),
(1015, '随便看看', 'Forum', 'Index', 'look', 1, '', '', '', 0, '-'),
(1016, '全部版块', 'Forum', 'Index', 'lists', 1, '', '', '', 0, '-'),
(1017, '资讯首页/某个分类下的文章列表', 'News', 'Index', 'index', 1, '', '', '', 0, 'now_category.title 当前分类的名称'),
(1018, '某篇文章的内容页', 'News', 'Index', 'detail', 1, '', '', '', 0, 'now_category.title 当前分类的名称\ninfo：文章变量集\n   info.title 文章标题\n   info.description 文章摘要\n   info.source 文章来源\n   info.detail.content 文章内容\nauthor.nickname 作者昵称\nauthor.signature 作者签名\n   '),
(1019, '微博首页', 'Weibo', 'Index', 'index', 1, '{$MODULE_ALIAS}', '{$MODULE_ALIAS}首页', '{$MODULE_ALIAS}-{$website_name}', 0, 'title：我关注的、热门微博、全站关注'),
(1020, '某条微博的详情页', 'Weibo', 'Index', 'weibodetail', 1, '{$weibo.title|text},{$website_name},{$MODULE_ALIAS}', '{$weibo.content|text}', '{$weibo.content|text}——{$MODULE_ALIAS}', 0, 'weibo:微博变量集\n   weibo.user.nickname 发布者昵称\n   weibo.content 微博内容'),
(1021, '微博搜索页面', 'Weibo', 'Index', 'search', 1, '', '', '', 0, 'search_keywords：搜索关键词'),
(1022, '热门话题列表', 'Weibo', 'Topic', 'topic', 1, '', '', '', 0, '-'),
(1023, '某个话题的话题页', 'Weibo', 'Topic', 'index', 1, '', '', '', 0, 'topic：话题变量集\n   topic.name 话题名称\nhost：话题主持人变量集\n   host.nickname 主持人昵称'),
(1024, '自动跳转到我的群组', 'Group', 'Index', 'index', 1, '', '', '', 0, '-'),
(1025, '全部群组', 'Group', 'Index', 'groups', 1, '', '', '', 0, '-'),
(1026, '我的群组-帖子列表', 'Group', 'Index', 'my', 1, '', '', '', 0, '-'),
(1027, '我的群组-全部关注的群组列表', 'Group', 'Index', 'mygroup', 1, '', '', '', 0, '-'),
(1028, '某个群组的帖子列表页面', 'Group', 'Index', 'group', 1, '', '', '', 0, 'search_key：如果查找帖子，则是关键词\ngroup：群组变量集\n   group.title 群组标题\n   group.user.nickname 创始人昵称\n   group.member_count 群组人数'),
(1029, '某篇帖子的内容页', 'Group', 'Index', 'detail', 1, '', '', '', 0, 'group：群组变量集\n   group.title 群组标题\n   group.user.nickname 创始人昵称\n   group.member_count 群组人数\npost：帖子变量集\n   post.title 帖子标题\n   post.content 帖子内容'),
(1030, '创建群组', 'Group', 'Index', 'create', 1, '', '', '', 0, '-'),
(1031, '发现', 'Group', 'Index', 'discover', 1, '', '', '', 0, '-'),
(1032, '精选', 'Group', 'Index', 'select', 1, '', '', '', 0, '-'),
(1033, '找人首页', 'People', 'Index', 'index', 1, '{$MODULE_ALIAS}', '{$MODULE_ALIAS}', '{$MODULE_ALIAS}', 0, '-'),
(1034, '微店首页', 'Store', 'Index', 'index', 1, '', '', '', 0, '-'),
(1035, '某个分类下的商品列表页', 'Store', 'Index', 'li', 1, '', '', '', 0, 'type：当前分类变量集\n   type.title 分类名称'),
(1036, '搜索商品列表页', 'Store', 'Index', 'search', 1, '', '', '', 0, 'searchKey：搜索关键词'),
(1037, '单个商品的详情页', 'Store', 'Index', 'info', 1, '', '', '', 0, 'info：商品变量集\n   info.title 商品标题\n   info.des 商品描述\n   info.shop：店铺变量集\n       info.shop.title 店铺名称\n       info.shop.summary 店铺简介\n       info.shop.position 店铺所在地\n       info.shop.user.nickname 店主昵称'),
(1038, '店铺街', 'Store', 'Index', 'lists', 1, '', '', '', 0, '-'),
(1039, '某个店铺的首页', 'Store', 'Index', 'detail', 1, '', '', '', 0, 'shop：店铺变量集\n   shop.title 店铺名称\n   shop.summary 店铺简介\n   shop.position 店铺所在地\n   shop.user.nickname 店主昵称'),
(1040, '某个店铺下的商品列表页', 'Store', 'Index', 'goods', 1, '', '', '', 0, 'shop：店铺变量集\n   shop.title 店铺名称\n   shop.summary 店铺简介\n   shop.position 店铺所在地\n   shop.user.nickname 店主昵称'),
(1041, '分类信息首页', 'Cat', 'Index', 'index', 1, '', '', '', 0, '-'),
(1042, '某个模型下的列表页', 'Cat', 'Index', 'li', 1, '', '', '', 0, 'entity：当前模型变量集\n   entity.alias 模型名'),
(1043, '某条信息的详情页', 'Cat', 'Index', 'info', 1, '', '', '', 0, 'entity：当前模型变量集\n   entity.alias 模型名\ninfo：当前信息\n   info.title 信息名称\nuser.nickname 发布者昵称'),
(1044, '待回答页面', 'Question', 'Index', 'waitanswer', 1, '', '', '', 0, '-'),
(1045, '热门问题', 'Question', 'Index', 'goodquestion', 1, '', '', '', 0, '-'),
(1046, '我的回答', 'Question', 'Index', 'myquestion', 1, '', '', '', 0, '-'),
(1047, '全部问题', 'Question', 'Index', 'questions', 1, '', '', '', 0, '-'),
(1048, '详情', 'Question', 'Index', 'detail', 1, '', '', '', 0, 'question：问题变量集\n   question.title 问题标题\n   question.description 问题描述\n   question.answer_num 回答数\nbest_answer：最佳回答\n   best_answer.content 最佳答案内容');


ALTER TABLE  `ocenter_follow` ADD  `alias` VARCHAR( 40 ) NOT NULL COMMENT  '备注';

ALTER TABLE  `ocenter_follow` ADD  `group_id` INT NOT NULL COMMENT  '分组ID';

INSERT INTO `ocenter_hooks` (`id`, `name`, `description`, `type`, `update_time`, `addons`) VALUES
(69, 'filterHtmlContent', '渲染富文本', 2, 1441951420, '');



--话题链接表
CREATE TABLE IF NOT EXISTS `ocenter_topic_link` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `topic_id` int(11) NOT NULL COMMENT '话题ID',
  `link_to_id` int(11) NOT NULL COMMENT '链接到的ID',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `link_mark` varchar(20) NOT NULL COMMENT '链接标识',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='话题关联表' AUTO_INCREMENT=1 ;
