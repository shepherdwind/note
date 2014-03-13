title: "使用php获得听力光盘中需要考试的类容"
id: 62
date: 2010-06-27 08:17:07
tags: 
- php探索
categories: 
- 电脑网络
---

都大三了，还要考听力，是在汗颜啊。作为东师人，大家都知道，听力考试只需要背诵那么几部分，而那基本分的听力都全部在光盘中snd文件夹中，wma格式，OK，那个软件太bt——每次都把电脑分辨率调到800px，都什么时代了，而且看电脑挺太磨叽。直接拷贝出音频还可以在p3上听。

任务就这样了，作为一个程序员有一个基本素质是：不做重复的工作——那是电脑的任务（vim创始人之语）。10个单元的题目，怎么把它拷贝出来呢？
首先，把第一单元的听力考出来，放到文件夹“听力”中，一共8个文件，文件名分别是

1.  u01LIT1f.wav
2.  u01LIT2f.wav
3.  u01LIT3f.wav
4.  u01LSD01f.wav
5.  u01LSD02f.wav
6.  u01LSD03f.wav… (1到5)

然后写一个php脚本：

```
define('SOURSES','G:/snd/');

for($i=6;$i&amp;lt;=10;$i++)
{
    for($j=1;$j&amp;lt;=3;$j++)
    {
        if($i==10)
        {
            $filename = 'u'. $i .'LIT'. $j .'f.wav';
        }
        else
        {
            $filename = 'u0'. $i .'LIT'. $j .'f.wav';
        }
        $p = fopen($filename,'w+');
        copy(SOURSES.$filename,$filename);
        fclose($p);
    }

    for($k = 1;$k&amp;lt;=5;$k++)
    {
        if($i==10)
        {
            $filename = 'u'. $i .'LSD0'. $k .'f.wav';
        }
        else
        {
            $filename = 'u0'. $i .'LIT'. $j .'f.wav';
        }
        $p = fopen($filename,'w+');
        copy(SOURSES.$filename,$filename);
        fclose($p);
    }
}
```

Ok，在建立一个a.bat文件输入：

```
$ php a.php
```

a.php是php文件名，把这两个文件放在最初建立的‘听力’文件夹中，双击 a.bat就可以啦。那个php代码非常简单，只使用了一个copy函数，基本会英语的人就能看明白什么意思
注：前提是在个人电脑上安装好了 php。当然也可以使用其他语言，但是作为phper，也应该多尝试尝试php的各种功能，还是很有用的
