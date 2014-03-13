title: "第五届D2前端技术论坛的一些感受"
id: 291
date: 2010-12-19 04:03:50
tags: 
- d2
- 前端
categories: 
- 电脑网络
---

来到杭州十多天了，恰好赶上[D2前端大会](http://www.d2forum.org/)，非常幸运，发过微薄，还觉得不过瘾。

前端这个行业似乎越来越强大了，虽然国内比世界还慢了两年，但<span style="text-decoration: line-through;">从07年淘宝首先招聘前端工程师</span>（根据[Aether](http://woooh.com/)提供的信息，此处有误，中国何时开始有前端这个职位无从考证了）。在大会上还看到华为的工程师过来，还有思科来的嘉宾的杜欢，这些看起来和web没有关系的公司都对前端如此有兴趣，让我很是惊讶。

回顾上周刚刚结束的[velocity中国web性能大会](http://velocity.oreilly.com.cn/)，D2显得更加本土化。这样也好，让我们首先看看国外大神强如facebook都在干啥，再回顾回顾我们自己，不能只看着硅谷，那些大公司哪个能够在中国的大地占领市场呢。

## 嘉宾

这次来的嘉宾最有趣的要是来自谷歌的[Hedger](http://www.d2forum.org/d2/5/guest.html)了，还好他是华人，汉语讲得够过二甲了。土豆的[杨扬](http://www.d2forum.org/d2/5/guest.html)和淘宝系列都源自阿里集团，而阿里的技术又源自雅虎，[Hedger](http://www.d2forum.org/d2/5/guest.html)也从雅虎出来的，向雅虎致敬。最后，辣妈的出现，让我们都非常惊喜，呵呵，还有很多人不知道辣妈吧，她是现场唯一的女嘉宾，不过提起今年的js版植物大战僵尸，没有人（前端业内）没有听说过吧，辣妈就是js版植物大战僵尸的作者。辣妈应该说是勇敢的探险者，就像css禅意花园，做了一些伟大的尝试。有些事情从理论上分析也许不难实现，而理论在成为实现之前都只能说是一种可能，把理想实现者，给我们的是希望，现实的希望，我们原来真的可以做到，那么下一个目标也不远了吧。

## 波澜不惊

这个大会的组织还是非常好的，中途换了酒店反而对我更方便了，走路就可以到达。第一场分享，来自淘宝的“前端技术在电子商务领域的应用与实践”，感觉像是在给淘宝的装修大市场打广告，淘宝作为主办方，这样做也无可厚非，但是，作为一届技术大会，不谈技术占我们那么多时间就不地道了。最后一场来自淘宝[玉伯](http://www.d2forum.org/d2/5/guest.html#yubo)的“面向未来的前端类库开发”，感觉kissy还是缺少一些技术含量，至少没有达到前沿水平，与谷歌，fb等比起来，无法我们坐着的听人们目瞪口呆，而且，玉伯的表达能力有待增强。最后的分享环节中，淘宝渡劫和来人人网的俩分享反而不如前面那些无名人士的效果，可有可无吧。

## 波涛汹涌

大会总是有不少让所有人为之动容之处。首先是[Hedger](http://www.d2forum.org/d2/5/guest.html)的讲演，很有大师的感觉，无处不发散着来自世界最优秀团队的一种hack气质。[Hedger](http://www.d2forum.org/d2/5/guest.html)首先提出js中的很多问题，然后一一批评了那些世界顶级大师John Resig（jquery作者）和[Douglas Crockford](http://dancewithnet.com/2009/03/29/douglas-crockford/)的一些在业界被广为流传，并且大量使用的一些方法。用[Hedger](http://www.d2forum.org/d2/5/guest.html)来说，这些都是js中的奇技淫巧**，**我们每个人都是忍者（或者说侠客），缺乏一种统一的模式。jQuery和YUI的比较是没有意义的，他们都是把性能的代价放到了客户端，让用户的电脑来解决js中的问题。

这在我们一般的思维来说，完全没有问题，客户端浏览器运行js，我们在js的范围内开发，但是我们无法预料客户端的浏览器是哪种浏览器，这样就只能做浏览器兼容了，然后出现了无数伟大的js库，然后我们来讨论怎么做一个更好的库吧，jQuery or mootool or YUI Dojo Ext……淘宝说，我们要开发一个适合我们自己的库kissy。这样的思维似乎还无漏洞，淘宝在国内是走在前列。

这样，我们都是在js的范围内思考，那么会上有个90后小伙就问了，你们面对这样一个又一个的括号，你们难道不烦吗？呵呵，一语既出，雷到全场(主持人乌龙表示压力很大)，难道js不是这样写的吗？小伙说，我自己重新编译了js，然后刷刷写代码了，小家伙的思维确实很奇特，不过他应该没过大二吧，在大学的殿堂里慢慢研究吧，会有出息的。用于实际就有些过了(CoffeeScript和underscore的出现，说明，再好的想法只有实现了才能有用，我们还是应该多干活，少说话，把产品做好了才是王道)。

[Douglas Crockford](http://dancewithnet.com/2009/03/29/douglas-crockford/)在99年为js辩护，称JavaScript曾是“世界上最被误解的语言”，大师一言既出，立即在整个前端界内把js语言翻身为优雅的函数式语言。John Resig大神开发jQuery，更是让无数前端小子感受到js语言本身的独特魅力。就这样，在业界形成了一种共识：JavaScript有其独特的原形继承语言，它的语言中有很多优雅的特性，我们应该尽量避免使用语言中的鸡肋部分，总体上JavaScript是这一门非常优秀的语言。无形中，js带上一顶神圣的光环，90后小家伙居然说重新编译一门语言，这不是讨打么。

但90后前面有[Hedger](http://www.d2forum.org/d2/5/guest.html)的演讲谈[Closure Compiler](http://code.google.com/intl/zh-CN/closure/compiler/)，其实谷歌在09年就已经开始尝试从编译的角度重新构造js了，[Closure Compiler](http://code.google.com/intl/zh-CN/closure/compiler/)的基本思想是：让客户端的js只运行所需要的部分。谷歌的实现是通过[Closure Compiler](http://code.google.com/intl/zh-CN/closure/compiler/)工具对js进行重新编译，把js按照一种严格的模式在服务器端首先编译好一份最简单的js文件。这种事情该是多么复杂啊，也只有大神谷歌能这样做吧，也只有谷歌对性内的极限性内要求才有如此需求。这样既不需要修改js语言，又可以以一种非常规范的模式编写js代码，这样的js代码规范在大型项目中非常有用。[Hedger](http://www.d2forum.org/d2/5/guest.html)说谷歌的[Closure ](http://code.google.com/intl/zh-CN/closure/compiler/)库可以自动生成说明文档，这应该与zend frameworks是一样的，否则zend哪有时间写一千多页的api文档，代码及时文档。

最后需要谈谈[老赵](http://blog.zhaojie.me/)，他的思想和谷歌的[Closure Compiler](http://code.google.com/intl/zh-CN/closure/compiler/)有相似之处，只是相对而言，[Closure Compiler](http://code.google.com/intl/zh-CN/closure/compiler/)更加复杂而已。老赵的[Jscex](http://github.com/JeffreyZhao/jscex)则是用js语言来改变语言运行的模式，老赵来自盛大，当然专注于游戏行业了，[Jscex](http://https//github.com/JeffreyZhao/jscex)对于js游戏开发是一个无比强大的工具。js的异步事件模型是js语言的一个强大的特性，客户端程序通常都是基础事件操作。但是异步事件在一次又一次的循环中（游戏开发中），任何人都只能望洋兴叹了，这一个事件接着一个事件，程序员很快会失去自我。不过，不能不说，Jscex也失去了JavaScript强大的事件驱动，Node.js最大特色之一就是无阻塞的异步执行，也正是建立在事件驱动之上。

在PHP的开发中，我们都被教导，不要使用goto，突然把程序断了，程序员会失去对程序的控制的。但是，js的每一个事件就是一个goto模块，当然，这在模型事件很少的时候我们还能控制场面，但是，面对动画，这一切就无能为力了。动画每隔200毫秒需要一个状态，那每一个状态的变化都需要一个函数，这样需要处理的事件就太多了。这时候flash的时间线反而更有用，[Jscex](http://github.com/JeffreyZhao/jscex)使用一个函数来处理一步处理过程，这样，我们就可以像处理一条流水线一样处理动画了。

老赵只用了十分钟，就把全场震撼住了，老赵的语言能以也非常强悍。

## 总结

会场分两个场，只能呆在一个场，有些遗憾，希望有视频上传。

对于大会的主办方，淘宝要加油才行，虽然淘宝的技术很先进，但还没有达到可以show的前沿水平(Facebook,Twitter,Google级别)，不过淘宝推荐行业发展的精神是非常值得赞赏的。

最后，语言都有其适用的范围，谷歌做谷歌的大型应用，淘宝要重用UI做各种奇形怪状的页面，盛大做游戏，90后幻想就行了。一句话，以用户为中心，以实践为引导。JavaScript已经到了一个需要超越web的时代了，走出浏览器，走向更广泛的空间。

## 扩展

1.  [Hedger](http://www.d2forum.org/d2/5/guest.html)的[Coding Better Object-Oriented JavaScript with Closure Compiler](http://calendar.perfplanet.com/2010/coding-better-object-oriented-javascript-with-closure-compiler/ "Permanent Link to Coding Better Object-Oriented JavaScript with Closure Compiler")
2.  [通用JS时代的模块机制和编译工具](http://www.limboy.com/2010/12/19/module-and-compiler-for-common-js/ "通用JS时代的模块机制和编译工具")
3.  [基于Jscex.Async的JavaScript动画/游戏](http://blog.zhaojie.me/2010/12/animations-and-games-based-on-jscex-async.html)