title: "[翻译]内容安全策略(CSP)"
date: 2014-03-13 18:15:33
tags: [安全, CSP, 翻译]
---

> 原文：[Content Security Policy](https://github.com/blog/1477-content-security-policy)<br>
> 作者：[josh](https://github.com/josh)

** 说明 **：本文是Github全站部署CSP策略过程总结的经验。总体上，CSP安全策略很大程度上解决了JS非同源策略引起的问题，通过JS加载源白名单机制，能够很好的保护用户。

作为前端，CSP策略所提倡的方案，都是JS和CSS规范的方案，CSP所做的是完全抛弃不规范内联JS写法，只要我们稍微注意就能够做到了，而且这也是一个专业的前端应该做到的。

另外，框架开发者也应该考虑CSP兼容的问题了，测试过程很简单

```php
<?php
//header('Content-Security-Policy: default-src *; report-uri /csp/recode.php');
header("X-Frame-Options: SAMEORIGIN");
header("Content-Security-Policy: default-src *; script-src 'self' g.tbcdn.cn");
?>
```

--------------------------------------

全文翻译如下：

我们新部署了一个叫**内容安全策略(CSP)**的策略。对于用户，这能够更好地保护你的账户不被XSS攻击。需要注意的是，这可能引起一些浏览器插件或者书签的问题(译注：浏览器插件使用inline script功能的会被禁止，有一些书签本身是javascript伪协议地址)。

[Content Security Policy](http://www.w3.org/TR/CSP/)是一种新的HTTP头域，用于防范XSS攻击来加强网络安全性。CSP通过阻止inline script执行和限制被允许加载的脚本域名来做到(安全防护)。这不意味着你可以不用在服务端过滤用户输入的数据了，而是，当你的过滤器被绕过了，CSP可以给你提供最后一层防御。

## 准备好你的应用服务器

### CSP header

在Rails应用激活CSP是轻松的，它只是一句简单的头信息。你不需要引入任何其他库；设置一个before filter就够了(译注：before filter应该是请求处理前置的hook)。

```ruby
before_filter :set_csp

def set_csp
  response.headers['Content-Security-Policy'] = "default-src *; script-src https://assets.example.com; style-src https://assets.example.com"
end
```

这个头信息定义可以允许加载内容的白名单url路径。`script-scr`和`style-src`指令都是用于配置我们静态资源的域名。然后，除了我们自己的域名之外的脚本都无法加载。最后，`default-src`是为所有没有定义的配置项设置默认值。比如，`image-src`和`media-src`可以用于限定图片、视频和音频的加载地址。

如果你想让更多地浏览器支持，可以为`X-Content-Security-Policy`和`X-WebKit-CPS`设置同样的值。不考虑向后兼容，你只需要设置正确处理标准的`Content-Security-Policy`(译注：最新的chrome和firefox都已经支持标准协议)。

由于CSP实现趋于成熟，这在将来也许会成为Rails自身开箱即用的功能。

开启CSP策略很容易，真正的挑战在于让你应用准备好面对(CSP带来的改变)。

### 内联脚本

除非设置了`unsafe-inline`，所有的内联脚本标签都会被拦截。这是实现你想要的XSS防御的主要手段。

大部分内嵌的脚本都是用来配置页面属性的。

```html
<script type="text/javascript">
GitHub.user = 'josh'
GitHub.repo = 'rails'
GitHub.branch = 'master'
</script>
```

更好地方式是以下代码展示的，把配置信息放在`data-*`的属性中。

```html
<div data-user="josh" data-repo="rails" data-branch="master">
</div>
```
 
### 内联事件绑定

和内联脚本标签一样，内联的事件绑定也过时了。

如果你在2008年之后写过JS，你大概会使用一种不那么直接的方式绑定事件。但是在你的代码库里面，可能还隐藏着一些内联的事件绑定。

```html
<a href="" onclick="handleClick();"></a>
<a href="javascript:handleClick();"></a>
```


一直到Rails 3，Rails自带的模板还会通过`link_to`和`form_tag`生成一些内联的事件绑定。

```
<%= link_to "Delete", "/", :confirm => "Are you sure?" %>
```

将会输出

```html
<a href="/" data-confirm="Are you sure?">Delete</a>
```

你需要使用UJS引擎，比如[jquery-ujs](https://github.com/rails/jquery-ujs)或者[rails-behaviors](https://github.com/josh/rails-behaviors)，通过配置data属性来实所有的效果。

### Eval

`eval()`的使用是被禁止的，除非配置了`unsafe-eval`(译注：eval包括其他类似的函数setTimeout, Function).

尽管你也许没有在代码里直接使用`eval()`，但你可能使用了某种浏览器端的模板库。通常的，字符串模板在浏览器端被解析编译为JS函数，通过eval的方式执行，这样开发更方便。比如[@jeresig](https://github.com/jeresig)的[classic micro-templating script](http://ejohn.org/blog/javascript-micro-templating/)。更好地方式，应该使用在服务端预编译模板库，比如[sstephenson/ruby-ejs](https://github.com/sstephenson/ruby-ejs).

另一个中招的是RJS模板，它通过服务端输出JavaScript。jQuery和Prototype都需要使用`eval()`来运行RJS模板通过异步请求返回的代码。很不幸这种方式无法正常工作...(译注：这一段关于rjs模板，没看明白，省略了，之和ruby开发者相关)。

### 行内CSS

除非`style-src`定义了`unsafe-inline`，否则所有的html中style属性都是无效的。

最常用于控制元素加载时隐藏的方式如下

```html
<div class="tab"></div>
<div class="tab" style="display:none"></div>
<div class="tab" style="display:none"></div>
```

更好的方式是使用CSS状态的class

```html
<div class="tab selected"></div>
<div class="tab"></div>
<div class="tab"></div>
```


```css
.tab { display: none }
.tab.selected { display: block }
```

尽管如此，使用此特性还需要谨慎。像jQuery或者Modernizr之类的类库，执行一系列浏览器特性侦探的时候，会在页面注入一些自定义的CSS，这将触发CSP警报而被阻止。暂时，大部分应用都需要禁止这个特性。

## 缺点

### 书签功能

CSP文档所定义的，浏览器书签功能应该不受CSP影响.

> 执行CSP策略应该不干扰用户自定义的脚本比如第三方插件和JavsScript书签的功能

[http://www.w3.org/TR/CSP/#processing-model](http://www.w3.org/TR/CSP/#processing-model)

> 每次客户端要执行javascript URI中的脚本时，拒绝执行这些脚本.
> (客户端应该可以执行书签链接中的脚本，即使执行CSP策略的情况.)

但是，没有浏览器正确处理这种情况。都违背CSP定义，阻止书签功能。

尽管这有点让人沮丧，你也可以在特定条件下禁止Firefox执行CSP。打开`about:config`，设置`security.csp.enable`为`false`.

### 扩展

和书签一样，CSP不支持浏览器插件与页面交互。不过实际上并不总是如此。具体来说，Chrome和Safari，扩展是用JS实现的，通常会修改当前页面，这有可能触发CSP异常。

Chrome插件[LastPass](https://lastpass.com/)就有CSP兼容问题，因为它试图在页面注入`<script>`标签。我们已经和LastPass开发者报告了这个问题。

### CSSOM限制

内联CSS是被禁用的，这是CSP默认限制的一部分，除非定义`style-src`指令为`unsafe-inline`。这一次，只有Chrome正确的实现这种限制。

你依然可以通过CSSOM动态修改CSS样式。

> 客户端同样不能阻止CSS对象模型对样式的控制

[http://www.w3.org/TR/CSP/#style-src](http://www.w3.org/TR/CSP/#style-src)

这是非常必要的，比如你希望在你的网站上实现一个需要动态绝对定位的自定义提示条效果(译注：提示条需要改变left和top值)。

对于行内CSS序列化实现还是有一些bug(译注：CSS规则序列化的结果输出为[cssText](http://dev.w3.org/csswg/cssom/#dom-cssrule-csstext)，序列化过程指cssText转换为CSSOM的解析过程).

一个bug具体的例子是克隆一个有CSS属性元素(译注：这个bug已经修复了).

```js
var el = document.createElement('div');
el.style.display = 'none'
el.cloneNode(true);
> Refused to apply inline style because it violates the following Content Security Policy directive: "style-src http://localhost".
```

同时，正如前面提到过得，像jQuery和Modernizr这种类库执行一些浏览器特性侦探的时候，在页面注入一些自定义的样式用于测试，可能会引起异常。但愿这些问题能够被这些组件自己去解决。

### 报告

CSP报告的特性是很巧妙的想法(译注：CSP有一个字段report-uri，被CSP规则拦截的时候，会向reqort-uri发送一个请求，报告错误信息)。如果一个攻击者在你的网站上发现一个XSS绕过漏洞，被攻击者访问页面的时候，在CSP开启的情况下XSS攻击会被报告到服务器。这在一定程度上可以作为一个XSS攻击监控系统。

但是，由于当前书签和浏览器扩展的一些问题，CSP警告的误报会淹没你的后端日志。报告的payload信息也可能很模糊，这取决于浏览器。如果幸运的话，你能获得攻击触发对应的压缩js文件的行数。通常也很难分辨错误是发生在你的JS还是浏览器插件注入的代码。这导致误报无法被过滤。

### 总结

尽管有一些问题，我们还是选择部署CSP策略。希望最新的[CSP 1.1 draft](http://www.w3.org/TR/CSP11/)提案能够解决一些问题。

最后，特别感谢来自谷歌的[mikewest](https://github.com/mikewest)给与我们的帮助。
