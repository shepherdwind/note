title: "webpack 热加载原理探索"
date: 2017-02-07 10:10:52
tags:
- 前端
---

## 前言

在使用 [dora](https://github.com/dora-js/dora) 作为本地 server 开发一个 React 组件的时候，默认使用了 hmr 插件。每次修改代码后页面直接更新，不需要手动 F5 ，感觉非常惊艳，这体验一旦用上后再也回不去了。

当时的 hot reload 实际上配置的是 live reload，也就是每次修改页面刷新。开发小组件每次更新也蛮快的，但如果一个应用应该使用上真正的 hot reload 才比较靠谱。

所谓的 hot reload(热加载) 就是每次修改某个 js 文件后，页面局部更新。

基于热加载这么一个功能，我们可以了解到 webpack 构建过程的基本原理。此外，还发现一个有趣的故事，redux 诞生自 React 热加载实现过程中。最后，针对现有 css 热加载实现的问题，我写了一个[css-hot-loader](https://github.com/shepherdwind/css-hot-loader)。

## webpack 热加载基本原理

基本实现原理大致这样的，构建 bundle 的时候，加入一段 HMR runtime 的 js 和一段和服务沟通的 js 。文件修改会触发 webpack 重新构建，服务器通过向浏览器发送更新消息，浏览器通过 jsonp 拉取更新的模块文件，jsonp 回调触发模块热替换逻辑。[官方文档](https://github.com/webpack/docs/wiki/hot-module-replacement-with-webpack#how-does-it-work)有比较详细的描述，可以参考下。

本文更关注的是具体实现逻辑，而不是实现思路。热加载基本思路一般是很简单的，监听本地文件修改，然后服务器推送到客户端，执行更新即可。没有 webpack 的时候就有很多各种开发者工具、浏览器插件实现了类似功能。但从 module bundler 角度来实现的热加载，这个思路是非常神奇的，这比普通的 live reload 多走了一步，这一步成本应该蛮高的。那么 webpack 为何要实现热加载功能呢？这个看起来不是一个核心功能，一定是顺带实现了的吧。

### 来源

通过 webpack 作者 sokra 的[分享](http://sokra.github.io/slides/webpack#27)来看，webpack 有两个核心概念

- Code Splitting
- Everything is a module

对于使用者而言，第二点会更加深刻，但我们通常对第一点 `Code Splitting` 没有体会。

所谓的 Code Splitting 不仅仅是把代码拆分成不同的模块，而是在代码中需要执行到的时候按需加载。这和纯前端 loader(比如 seajs、requirejs) 类似，但在 webpack 对模块设计上就区分了异步模块和同步模块，构建过程中自动构建成两个不同的 chunk 文件，异步模块按需加载。这一点突破是传统的 gulp 或者纯前端 loader 都无法做到的。

Code Splitting 还体现在对公共依赖的抽离(CommonsChunkPlugin)，如果一个构成过程有多入口文件，这些入口的公共依赖可以单独打包成一个 chunk 。

webpack 通过的 `require.ensure` 来定义一个[分离点](https://webpack.github.io/docs/code-splitting.html#defining-a-split-point)。`require.ensure` 在实际执行过程是触发了一个 jsonp 请求，这个请求回调后返回一个对象，这个对象包括了所有异步模块 id 与异步模块代码。举个例子

```js
webpackJsonp([1], {
  113: '' // code of module 113
});
```

这实际上是通过 webpackJsonp 方法动态在模块集合中增加一些异步模块，这和热加载逻辑是类似的，唯一的区别在于：热加载是替换已有的模块。webpack 可以实现动态新增模块，那么动态替换模块也就轻而易举了。

### 实现

热加载实现主要分为几部分功能

- 服务器构建、推送更新消息
- 浏览器模块更新
- 模块更新后页面渲染

#### 构建

热加载是通过内置的 HotModuleReplacementPlugin 实现的，构建过程中热加载相关的逻辑都在这个插件中。这个插件主要处理两部分逻辑

- 注入 HMR runtime 逻辑
- 找到修改的模块，生成一个补丁 js 文件和更新描述 json 文件

HMR runtime 主要定义了 jsonp callback 方法，这个方法会触发模块更新，并且对模块新增一个 `module.hot` 相关 API ，这个 API 可以让开发者自定义页面更新逻辑。

重点说下构建过程中需要对更新的文件打包出的两个文件，这两个文件名规则定义在 WebpackOptionsDefaulter

```js
	this.set("output.hotUpdateChunkFilename", "[id].[hash].hot-update.js");
	this.set("output.hotUpdateMainFilename", "[hash].hot-update.json");
```

这两个文件一个是说明更新了什么，另外一个是更新的模块代码。这两个文件生成逻辑

```js
compilation.plugin("additional-chunk-assets", function() {
  this.modules.forEach(function(module) {
    // 对比 md5 ，标记有修改的模块
    module.hotUpdate = records.moduleHashs[identifier] !== hash;
  });
  // 更新内容对象
  var hotUpdateMainContent = {};

  // 找到更新的 js 模块
  Object.keys(records.chunkHashs).forEach(function(chunkId) {
    // 渲染更新的 js ，并且追加到 assets
    var source = hotUpdateChunkTemplate.render(...);
    this.assets[hotUpdateChunkFilename] = source;
    hotUpdateMainContent.c.push(chunkId);
  }, this);

  var source = new RawSource(JSON.stringify(hotUpdateMainContent));
  // assets 中增加 json 文件
  this.assets[hotUpdateMainFilename] = source;
});
```

上面代码简化了很多，具体过程是在构建  chunk 过程中，定义一个插件方法 `additional-chunk-assets` ，在这个方法里面通过 md5 对比 hash 修改，找到修改的模块，如果有发现有模块 md5 修改了，那么说明有更新，这时候通过 `hotUpdateChunkTemplate.render` 生成一份更新的 js 文件，也就是上面定义的 `output.hotUpdateChunkFilename`，并且在 assets 中追加一份 json 描述文件，说明更新了哪个模块以及更新的 hash。

上面的代码也可以发现 webpack 构建过程提供了很多丰富的接口，并且追加一个 output 文件是非常容易的事情，只需要在 assets 中 push 一个文件即可。找到修改的文件也很方便，首先构建前遍历所有的模块，记录所有模块文件内容 hash ，构建完成后，在一个个对比一遍，就可以找到更新的模块。

构建大致这样了，这里可能还涉及到 webpack 插件一些概念，可以参考看看 webpack 插件文档。

#### 服务器推送

文件更新后，首先需要打包好新的补丁文件，还需要告诉浏览器文件修改了，可以拉代码了。

这一部分 webpack 自带了一个 [dev-server](https://webpack.github.io/docs/webpack-dev-server.html)。当开启热加载的时候，webpack-dev-server 会响应客户端发起的 [EventStream](https://www.html5rocks.com/en/tutorials/eventsource/basics/) 请求，然后保持请求不断开。这样服务器就可以在有更新的时候直接把结果 push 到浏览器。

服务器推送部分比较简单，构建一个 node 的 Server-Sent Events 服务器只需要几行代码，这里有一个[例子](https://www.html5rocks.com/en/tutorials/eventsource/basics/demo/node-sse.js)。

一次完成的构建流程大概是这样的

![Snip20170205_1.png](https://zos.alipayobjects.com/rmsportal/MrLNdSjTeZJdtvczOalS.svg)

上述步骤完成，热加载前两步就 ok 了。每次文件修改，浏览器模块代码也更新了。但是就这样而言，模块更新还算不上完整的热加载，因为模块更新了，页面还没更新。前面提到构建过程中会在入口文件中加入一段 HRM runtime 代码，其中就有加上 `module.hot` 相关 API 。这个 API 就是提供给开发者自定义页面更新用的。

下面，我们进入热加载最后一步，页面局部更新。

## React 热加载

React 热加载现在主要有两个工具包来实现

- [react-hot-loader](https://www.npmjs.com/package/react-hot-loader)
- [react-transform-hmr](https://www.npmjs.com/package/react-transform-hmr)

一个是 webpack 的 loader ，一个是 babel 插件，都是 redux 作者 Gaearon 开发的。现在两个都非常火，大家经常问这两个具体有啥区别，最近 Gaearon 准备把这两个都废弃，重新开发了 react-hot-loader 3.0 。其中曲折历程，可以看看作者写的文章 [Hot Reloading in React](https://medium.com/@dan_abramov/hot-reloading-in-react-1140438583bf)。

在研究 react-hot-loader 实现过程中，我发现一个神奇的故事:

> I wrote Redux while working on my React Europe talk called [“Hot Reloading with Time Travel”](https://www.youtube.com/watch?v=xsSnOQynTHs).

Redux 诞生自作者研究 React 热加载实现过程中。Gaearon 首先实现了 React 的热加载，然后发现当时使用的 flux 无法热加载，因为 flux 有一个全局的 store ，action 都是通过消息来沟通，当这个对象替换的时候还需要重新绑定事件(flux还在 componentDidMount里面绑定，无法替换)。要实现热加载，就需要对 flux 进行改造，然后一步步删除 flux 中多余的部分，redux(reducer + flux) 就诞生了。redux 里面 reducer 、action 都是一个个纯函数，所以做替换是非常简单的。

对于基于 React 的应用，实现 React 热加载的基本思路分为两种

- 直接基于 `module.hot` API
- 对每个组件进行一次包裹，组件更新后替换原有组件原型上的 render 方法和其他方法

第一种方案可以这样实现

```js
var App = require('./App')

// Render the root component normally
var rootEl = document.getElementById('root')
ReactDOM.render(<App />, rootEl)

if (module.hot) {
  // Whenever a new version of App.js is available
  module.hot.accept('./App', function () {
    // Require the new version and render it instead
    var NextApp = require('./App')
    ReactDOM.render(<NextApp />, rootEl)
  })
}
```

`module.hot` 是 webpack 在构建的时候给每个模块都加上的对象。通过 `accept` 可以接受这个文件以及相关依赖的更新，然后在回调函数里面重新 require 一遍获得新的模块对象，执行 render 。

这相当于整个页面重新渲染了，但这种会方案无法保存 React 组件的状态。如果组件都是纯 render 方法，这样基本没问题。

第二种方案 react-hot-loader 也需要重新执行 render ，只不过区别在于重新 render 的时候组件对象引用并没有修改，但每个组件都包裹了一层代理组件，代理过程会替换 render 方法。react-hot-loader 这套方案涉及到很多 React 的私有 API ，而且包裹代理对象过程有时候会失败，所以 Gaearon 发布两套方案，还在重构第三套，具体探索可以看看这篇文章[Hot Reloading in React](https://medium.com/@dan_abramov/hot-reloading-in-react-1140438583bf)。

## CSS hot loader

js 热加载基本上是通过自动更新组件，重新渲染页面两个步骤完成了。还有一个比较重要的是 css 热加载，webpack 官方提供的方案是 [style-loader](https://github.com/webpack-contrib/style-loader)。

一般的对 css 处理都是通过 extract-text-webpack-plugin 插件把 css 抽离到单独 css 文件中。但 extract-text-webpack-plugin 是不支持热加载的，所以 css 热加载需要两个步骤：

- 开发环境关闭 extract-text-webpack-plugin
- 开启 style-loader 插件

style-loader 实际上就是通过 js 创建一个 `style` 标签，然后注入内联的 css 。因为 css 是内联，并且通过 js 注入，那么页面刷新的时候一开始是没有任何 css 的，这种体验会非常差，闪一下然后页面重新渲染成功。

为什么 extract-text-webpack-plugin 就不能支持热加载呢? 这个问题很多人都遇到过 [extract-text-webpack-plugin#30](https://github.com/webpack-contrib/extract-text-webpack-plugin/issues/30)，这个 issue 还有人提 mr 直接支持热加载。

参考 react-hot-loader 来实现一个 [css-hot-loader](https://github.com/shepherdwind/css-hot-loader) 也不难。每次热加载都是一个 js 文件的修改，每个 css 文件在 webpack 中也是一个 js 模块，那么只需要在这个 css 文件对应的模块里面加一段代码就可以实现 css 文件的更新了。

```js
    if (module.hot) {
      const cssReload = require('./hotModuleReplacement')});
      module.hot.dispose(cssReload);
      module.hot.accept(undefined, cssReload);
    }
```

上面每个 css 对应的模块都会接受自身的修改时间，并且执行一次 `cssReload` 函数，在 `cssReload` 函数里面会找到需要修改的 css 外链标签，加一个时间戳让浏览器重新请求这个 css 文件，那么页面样式就更新了。

webpack 中扩展功能有两种方式

- loader
- plugin

一个 loader 是对模块进行处理，比如 css 处理过程可以用这样来描述

```
style-loader!css-loader!less
```

对每个 css 模块会依次执行 less、css-loader、style-loader 处理，每个 loader 处理后的结果作为下一个 loader 的输入字符串，有点像 linux 的管道，只是方向是反的。

插件处理的是 chunk ，loader 处理完成后，可以得到一个依赖树，每个模块都有一个处理结果的描述。在插件里面可以对整个 entry 输出的内容进行一些处理，比如热加载过程中增加 HRM runtime 脚本，对所有的 css 抽离到单独的静态资源中。

css-hot-loader 所做的是在 css 模块中注入一段脚本，所以是一个 loader ，并且是第一个 loader ，这样可以保证代码不会被 extract-text-webpack-plugin 抽出来。

## 总结

热加载只是开发体验的一小步提升，但这个技术背后包含了很多技术的铺垫，慢慢一路发展过来，最终达到让人耳目一新[Hot Reloading with Time Travel](https://www.youtube.com/watch?v=xsSnOQynTHs)。

webpack 诞生于对 Code Splitting 特性的实现，从 webmake 重写为 webpack 。redux 诞生于 React 热加载探索过程中。可见对一项看起来不起眼的技术的深入探索是非常值得的，也许某个伟大的开源作品就在探索中诞生了。
