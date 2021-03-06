title: "同伴提名测验平台"
id: 317
date: 2011-05-15 09:05:43
tags: 
- php
- 前端
categories: 
- 电脑网络
---

因为找工作的事情，在杭州呆了近四个月，然后回到长沙，闲置了一个月，工作还是没有确定，无奈，只能匆匆忙忙赶回学校写论文了，剩不到二十天时间。和[盖老师](http://www.nenu.edu.cn/professor/pro/show.php?flag=1&amp;id=668)商量，做实验或者写研究综述都来不及了，于是我说，如果能够做一个毕业设计啥的，也许更容易些。于是，盖老师给我推荐了这么个题目，[同伴提名测验平台](http://peersay.sinaapp.com/)。

对于任何计算机开发项目，最重要的就是一个词**速度**，开发速度和运行速度，具体来说表现在代码可读性、可维护性、重用以及程序运行的效率、性能等方面。不过，通常程序员写代码的速度会和程序运行速度相冲突的，微观来看，我们经常遇到的就是一个算法中时间与空间的权衡。那么究竟如何处理，这就得依据具体情况而定了。

## 大胆使用开源吧

一直对开源怀有一种敬畏的心态，感觉就像古人瞻仰圣人“高山仰止，景行行止。虽不能至，然心向往之。”有时甚至妄自菲薄，感叹，这个世界有如此完美的技术，强如jQuery、WordPress，那么我们这些程序员还能再做什么呢。当然，那时候我依然停留在Dreamweaver+ZendStudio+WAMP开发时代，实际上，我离开源还有一段好长的距离。同时，作为web开发者，总是对自己作为一个程序员是否合格感到心虚，从传统影响来看，桌面应用才算是真正的程序吧。去一个学校应聘时，那信息中心的老师说，哦，就是一个网页啊，哪里是什么信息系统啊。

现在想想，任何实现变量、流控制和基本运算的语言不都能完成其他任何一种图灵完全的语言的任务。他们的差别不过在于使用某种语言的人们是如何使用这门语言吧。和JavaScript相比，PHP都显得中规中矩，虽然大多数时间JS让人无比郁闷，但我还是更喜欢那种灵活的使用方式。还尝试过一段时间学习Pascal，我想我无论如何也无法理解，使用它的人们会称之女神。但依然有那么多人能够理解Pascal，任何一种技术，都只有被理解，被使用了，才算是成功的。所以，参与开源，就大胆地使用它们吧。开源之所以存在，不仅仅是为了人们去创造更多有意思的技术，更重要的是它们需要是有价值的，需要被使用。最近，看到[John Resig](http://ejohn.org/)在博客上谈到自己转到另一个开源公司了（而且是做教育方面的，呵呵，我也得去搞教育了，不过悲剧的是去教office），我很惊讶，美国那么多开源技术公司，它们是如何生存的，作为中国人，我有些无法理解，开源的公司也能存在。

讲了这么多废话，开始说说这个同伴提名测验平台吧。

## 前端MVC

第一次听说前端搞MVC还是今年4月初的时候，去阿里云闲谈了一会儿（算是一个不正式的面试吧），被问起你是如何看待前端MVC的。当时顿感鸭梨甚大，就随便答了几句。现在终于明白了，前端的MVC模式最重要的就是解决传统web开发中View层的可重复、模块化开发问题的吧。一年前在雅礼做教师信息平台时，感觉[Codeigniter](http://codeigniter.org)的MVC模式非常好用，就是在View层功能实在太弱了。每一个页面都得放一个视图的html文件，完全不可重复。写代码最难受的事情就是复制了。当时很郁闷的在控制器中定义一个数组来配置视图，勉强实现了头部、左侧导航和底部版权信息部分的重用。但是，头部稍微修改一个小链接，就得把View的php代码改得一塌糊涂。最近CI发布新版本，还整合了一个JavaScript类——神啊，JavaScript如此强大，如何能屈居于PHP之下。

所以，但今天，我再也不能忍受PHP和HTML混在一起了。OK，那就得用MVC模式啦。测验平台还是使用Codeigniter框架，只是它的MVC被我只用了M和C，View只有[一个文件](https://github.com/shepherdwind/peersay/blob/master/application/views/admin/research_index.php)，简单定义下HTML头，加载一下css和js，其他的就[这么些了](https://github.com/shepherdwind/peersay/blob/master/application/views/admin/research_index.php)，本来和特意准备了一个文件夹来放View的。这就是所谓的单页面web app啦。

### [Backbone ](http://documentcloud.github.com/backbone/)And [Seajs](http://seajs.com/)

下面最重要的两位上场了，首先谈谈Seajs。在使用YUI时，觉得，那个seed确实很爽，想用什么，直接load就行了，维护页面一堆script标签也是一件非常无聊的事情。在杭州时，就听说玉伯在考虑seajs了，当时想，什么都mixin，和我直接把其他对象的prototype引用过来有啥不一样吗。现在看看，就简单的这样引人类似于YUI中的seed文件了

```
<script src="assets/js/sea.js" type="text/javascript"></script>
```

比YUI更酷的是，这个seed完全不用配置，而且足够小巧。下面简单谈谈自己使用seajs的一些感受，不过这么十来天，seajs已经发布0.9正式版了，我使的是beta版，也许有所出入吧。

1.  seajs中的require运行就像js中var申明，所谓的代码提升，不管这段代码发在什么地方，require都会先运行，所以这样是无效的,require总是会把所需要的js引入的：
`try { JSON; } catch (e ) { require("libs/json2"); }`
2.  在定义一个module时，定义依赖关系，最好使用require，只有在某些情况(必须需要条件判断)才使用module.load来根据某种事件来执行某个js文件，[例如此处](http://github.com/shepherdwind/peersay/blob/master/assets/js/app/app.js#L127)。
3.  [seajs最好放在js文件根目录上](http://github.com/seajs/seajs/issues/40)，这样好处理依赖关系，并且可以使用相对路径
4.  最后，虽然seajs约定所有变量输出需要使用module.exports，但是，有些时候，如果必须打破约定，就直接忽视约定吧。规矩是人定的，如何使用才是最重要的。[这里](http://github.com/shepherdwind/peersay/blob/master/assets/js/libs/ckeditor/ckeditor.js)使用ckeditor就只能把CKEDITOR释放了。[这里](http://github.com/shepherdwind/peersay/blob/master/assets/js/libs/json.js)使用json2.js，都没有define直接用module.load('libs/json')了(额，我也刚发现，这样也能用)。
对于Backbone，我觉得如果是简单的模型数据操作，最重要的模块是View。通常模型只需要定义一下url属性和validate方法即可（[例如](http://github.com/shepherdwind/peersay/blob/master/assets/js/app/models/test.js)）。url定义当模型调用fetch或者save方法时，向哪一个url发送请求，而validate则是定义save模型属性时，会执行validate方法，如果validate返回错误信息，则save或者set方法将返回error，然后触发error事件，再然后，我们想怎么样就怎么样啦，我们只需要关注发生什么事情了，这就是基于事件模型的js的巨大优势了。Model主要负责数据的操作，从后端返回的JSON对象的所有属性会被copy到Model上，这里，需要非常小心的是，Backbone使用了JSON对象，而JSON全局对象在IE 7中没有，所以只能调用老道的[json.js](https://github.com/douglascrockford/JSON-js)。为了不至于报错（关于js对象判断，[参考此文](http://www.ruanyifeng.com/blog/2011/05/how_to_judge_the_existence_of_a_global_object_in_javascript.html)），我使用了自认为很酷的。

```
try {
  JSON;
}catch (e){
  module.load('lib/json'); //加载json2.js
}
```

对于controller，如果和我一样使用PHP，则需要配置Backbone.emulateHTTP = true;Backbone.emulateJSON = true;并且，所有的路由必须在Backbone.history.start();运行以后才能开始追踪url改变的事件。

## 关于用户体验

再补充一些关于用户体验的，关于什么是用户体验，每个角色都会有不同的定义，视觉注重美观，而前端的我们呢。刚刚做这个项目时，我最想尝试使用炫酷的技术，那是用户体验的内容吗？在测验中，需要做选择，最初，我使用的是推拽，花了好多时间解决那些选项扰人的关系，拖拽了的是被选中的还是未选中的，拖拽的目的地是选中还是未选中，晕了半天终于搞定了。第二天，看看觉得拖拽实在麻烦，当选择过多时还得提示，怎么提示呢——使用弹窗，使用jQuery UI的Dialog可以华丽的跳出来，然后绚烂地离开。但事实上，这些华丽却阻止了用户的行为，过大的干扰，和直接使用alert没有多少差别。

想想还是尽可能对用户少一些干扰好吧。如此，便整体上重构代码，把花了大半天实现的拖拽效果和弹窗提示并禁止继续选择的代码全部删掉。把所有的选择项目由achor标签改为checkbox，看了很久觉得有些舍不得，但还是都改了，做了一个飞跃的效果（选中一个则从下面的box中跳出到上面，反之亦然）——程序员总是忘不了炫耀自己的技术，尤其是前端。代码简单了，我却有些甘心了。

到了今天终于发布了，拿到班上进行测验。班上同学都非常支持，尤其是女生，刚发有动作了，非常感谢她们。然后，有人发来回馈，找名字找得好累啊，把字调大一些吧。最后，同学说，一个东西跑了其他得都需要往前跳一个位置，眼花缭乱的，干脆把跳跃的效果也删了。这时候Backbone的MVC威力大显，只需要稍微修改一些html模板，然后删除跳跃动画的函数就行了。小小的删除，却删去我费了好多精力写的代码。但是，用户体验与程序员的技术无关啊，我需要的自己的东西给用户使用，这才是真正用户体验所要关注的吧，很多时候使用简单的方法实现就好，关键是有效吧。

## 结束语

转眼一个下午过去了啊，暂时就写到这里吧，以后再补充。最后发一下发布线上的[地址](http://peerasy.sinaapp.com/)，使用测试账户可以登陆玩玩，用户名从被试1-被试31，密码是全拼。
