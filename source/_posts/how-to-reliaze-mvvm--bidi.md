title: "如何实现一个mvvm组件"
date: 2014-05-17 15:56:11
tags:
- mvvm
---

去年，因为业务需要，实现了一个mvvm组件，[bidi](https://github.com/shepherdwind/bidi)。在此，记录一下组件实现的过程与经验。

### 来源

mvvm是Model-View-ViewModel，主要在于双向绑定。这个概念很早就有了，我一直没有具体研究过这个东西，直到有一天，我遇到一个这样的需求：

![needs](https://cloud.githubusercontent.com/assets/452899/3004411/be0da078-dd99-11e3-97de-8e07e384b3cb.png)

业务场景大致是这样的，在淘宝购物，如果需要退款，首先需要填一个表单，这个表单要求，你说明你的投诉原因，投诉原因下面又分一个二级：具体原因。具体原因决定赔偿金额校验，这些区别在于，某些原因是可以申请赔偿运费的，某些原因是不可以的，这个赔偿金额是由原因决定的。

这是一个比较简单地场景，还有更复杂的表单，上面的原因可能有三级。

这个项目，改造的是非常古老淘宝退款项目，累计了8年，现在终于重写了。我当时就想，这么多逻辑规则，需要有一种通用的解决方案。于是，我想到了所谓的mvvm，实在太适合了。mvvm天生适合于复杂的表单逻辑，处理表单联动，主要也就是在于，监控表单改变，然后改变其他表单元素。这样的联动实际上，就是V -> M 然后 M -> V的过程，视图改变，对应的JS改变，改变其他表单视图元素。

### 约定

然后，我开始研究现有的几种mvvm组件，主要看了[knockoutjs](http://knockoutjs.com/), [emberjs](http://emberjs.com/), [angularjs](https://angularjs.org/)。首先，我看这些已有的组件我能不能直接用，当时做的是pc端业务，不能放弃IE6，emberjs和angularjs都不能用了，只剩下knockout，但是我也不想用jQuery，在淘宝用的都是KISSY。所以，我就想，那么就自己实现一个吧。

然后，我主要关注这些已有的组件，他们的使用方式。至于他们怎么实现的，我不是很关注。mvvm的实现，比较重要的是一个东西是view怎么写，在我看来，主要是有两种不同的形式。

1. html文档，knockout和angular都是这样的
2. JS模板，比如handlebars，ember就是基于handlebars来实现的

最后，我使用了一种最简单地方案，把ember和knockout实现的结合。定义了几个基本原则，用这种约定的方式来写mvvm模板的语法：

1. 数据和dom的绑定，通过写一个JS模板语法，{{watch "action: model.var"}}的方式来绑定
2. 每个绑定占据一个dom节点
3. view通过XTemplate模板来写

下面我来解释一下，整个实现过程：

### 模板

在我做的bidi中，用户首先写的是一段模板：

```
 <div class="bidi-viewer" data-view="user">
    <script type="text/xtemplate">
      <p>hello, I am <strong {{watch  "text: fullName"}}></strong></p>
      <p>hello, I am <strong {{watch "text: firstName"}}></strong></p>
      First Name: <input {{watch "value: firstName"}}/><br>
      Last Name: <input {{watch "value: lastName"}}/>
      <button {{watch "click: capitalizeLastName"}}>Go caps</button>
    </script>
  </div>
```

这里描述了一段view视图规则，首先，有一个最完成的容器`.bidi-viewer`，这个容器还通过data-view定义了view所对应的模型。里面是一段模板，包裹在script里面，在js里面，会自动把模板拿出来，和数据放到一起，然后把运算得到的html放到当前视图中去。

然后，仔细看下这里的绑定是怎么写的：

```
<strong {{watch  "text: fullName"}}></strong>
```

这里fullName绑定在strong上了，{{watch}}是XTemplate模板的一个自定义函数，后面的是参数。这里有几个细节需要注意，这里的绑定fullName，同时存在strong元素，也许大家会觉得{{fullName}}这样写，不是要有好得多么？angular是这么实现，非常牛叉。但是这样搞的问题在于，绑定一个变量，你的知道这个变量在什么地方，这样当JS这个变量改变的时候，你知道去改dom中哪个元素。

如果angular那样，你就不能简单的找到这个{{fullName}}具体对应的dom节点了。你得找到这个变量所在的上下文，找到最上层的元素。这样的查找还是非常费事情，所以我选择了最简单的方式，直接一个元素绑定一个变量，通过{{watch}}这样的语法，然后这个自定义函数，返回一个字符串` id=bidi-{uid}`，这样就非常简单地解决了把view和model关联起来的任务了。

当然，这也意味着，id是保留属性，每个绑定都不能有id，这个是通过bidi自动生成的。

这个问题，把view和model联系起来，是mvvm里面，最关健的一步了。

### 函数

前面，主要谈了view怎么写，继续看js怎么写。

```
 KISSY.use('gallery/bidi/1.0/index', function (S, Bidi) {

      Bidi.xbind('user', {
        firstName: 'Song',
        lastName: 'Eward',
        fullName: function(){
          return this.get('firstName') + ' ' + this.get('lastName');
        }
      }, {
        capitalizeLastName: function(){
          var lastName = this.get('lastName').toUpperCase();
          this.set('lastName', lastName);
        }
      });

      Bidi.init();
})
```

对于用户而言，使用this.get()和this.set()来赋值。这里有一个问题，就是fullName是一个函数，fullName如何和firstName和lastName关联起来的。

回到前面的模板，{{watch "text: fullName"}} 这一个运行的时候，我得去取fullName的值，这个时候，会运行执行fullName这个函数，现在，我只需要在fullName执行之前记录一个空数组，然后在fullName执行过程中，所有通过get的变量都记录在那个数组里面就可以了，但fullName执行完毕，我看下这个数组，就知道fullName和哪些变量有关了。

这种方式加缪提到过，其实看起来很炫，其实实现很挫的方式。这里种方式，有一个大坑，如果出现循环关联，可能就悲剧了。另外，这种方式，函数都必须是同步的，如果在模板中处理异步的，就没法搞了。

### 实现过程

现在，可以把整个流程串起来了。

![image](https://cloud.githubusercontent.com/assets/452899/3004433/4fa42c68-dd9b-11e3-8402-c7f293f9e5aa.png)

view通过innerHTML获得模板，model通过toJSON方法，获得数据，两者结合，通过XTemplate解析模板。遇到{{watch}}自定义函数的时候，执行绑定，绑定函数同时返回一个dom的id。模板解析后，这些id成为视图上一个个dom节点的id，完成dom渲染，前面的绑定会和dom事件进行绑定。

在dom区域，有一个#bidi-{uid2}的元素，出发，构成一个V - M - V的绑定循环。bidi-{uid2}元素，是一个input，change事件触发，input的value改变，watch.value方法，监听了dom的change事件，在内部执行`this.set('var', value)`，set方法，触发`change:var`事件，而watch.text方法，监听了来model内部的change事件，watch.text会修改#bidi-{udi1}元素的innerHTML，这样，一个循环完成。

### 表单元素

前面是一个简单例子，基本的原理，就是这个图片所说的了。实际情况，我得解决业务需求，就前面那种简单地MVVM，还是没法解决表单联动的。这里有一个问题，通常情况，我们是一个变量和一个dom对应，但是对于表单，出了单个变量之外，还有一种集合形式的，这就是radio、checkbox。所以，为了解决这种问题，我把一组radio看做一个变量，和Model对应。这样做，还得定义一个约定。

![form](http://gtms04.alicdn.com/tps/i4/T1ZvIIFjdkXXamTp3B-751-436.png)

有了这样的约定，就可以这么描述一个联动关系了

```
    <select name="key1" {{watch "select: problem.defaultValue">
      {{#each problem.values}}
        <option value="{{value}}">{{text}}</option>
      {{/each}}
    </select>
    <div>
      {{#watch "linkage: reasons.values: problem" "radio: reasons.defaultValue"}}
        <label class="radio-inline"> <input type="radio" value="{{value}}"/>{{text}} </label>
      {{/watch}}
    </div>
```

### 优点

优势还是蛮多的，最方便的是，处理开发各种诡异的需求，简单多了。举个例子，开发说，二级原因被选中的时候，需要发一个异步请求，然后获得三级原因，然后现实三级原因。比如，他们经常说，哎呀，这个二级原因的文本也得给我，放隐藏域里吧。这样的需求，对于我而言，不是很复杂，稍微改下模板，不用动其他业务逻辑。

### 缺点

缺陷同样蛮多的，最开始做这个的时候，我觉得还慢不错的。但是，当我听到react的分享后，我瞬间觉得这个东东弱爆了。下面讲讲问题：

1.  一个变量绑定一个dom的方式，看起来很简单，也很美好。但现实要骨感得多，比如，一个表单元素，当某个二级原因选中的时候，这一行表单元素需要隐藏，实现这个，要做的涉及到很多改动，第一，这个表单元素需要隐藏起来，同时表单本身需要被disabled，不能直接被删掉，也许什么时候，这个元素又要出来了。也就是，一个变量的修改，可能涉及到很多dom的修改，这直接去修改dom，还是比较浪费的。

2. 与其他JS组件沟通的问题，MVVM通常直接与dom绑定，但是有些其他组件，比如表单元素模拟的组件，也要处理表单，经过模拟的表单元素，你甚至看不到表单元素了，这样的问题还是不好解决的。

3. 模块粒度问题，bidi没法处理view嵌套的情况。这个问题，其实不算大问题，如果能够处理好view的嵌套，就可以做出webcompement了，angular在往这个方向发展。但是做得还是不够好，我觉得上面我遇到的问题，react都能解决。

最好讲讲react，当我听到react杀手锏是，自己定义了一套虚拟的dom，这个dom和html文档里面的dom进行沟通。JS本身不直接接触dom，而是虚拟的dom。这样的好处，可以随时随地修改dom，但是这些操作不会直接对dom进行改动，统一通过visual dom映射到dom中。这其实在dom操作和js中间，搭建了一个桥，所有的操作首先放到桥上，然后整理一下，用最快的方式，在dom中操作。

另外，一个大多数mvvm或者其他web compemnt组件所做不到的是，visual dom是JS实现的，所以，react可以在服务器端跑，这样一来，页面不仅仅可以同时在前后端渲染了。这是非常非常好的特性了。
