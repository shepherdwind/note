title: "一个小小的php程序"
id: 113
date: 2009-04-18 09:50:32
tags: [php探索, php]
categories: [电脑网络]
---

记得有一次樊老师让我把一个文件夹中所有的文件改一下文件名，弄了很久弄出一个bat的批处理文件，自己还不大明白什么意思，不过终于是弄好了。这次帮朋友下载了一百多个字体，而且每一个都在不同的文件夹中，这样用粘贴复制累死了，我想用php写一个不会有什么问题吧，然后结合网上一些资料，这个家伙弄出来了：

```
/**
 * 使用方法如下
 * shift函数的第一个文件为查找文件，后一参数为拷贝后的文件夹,
 * 后一个文件路径一定不能位于第一个文件中，这样可能进入死循环
 * 注意:文件路径一定要用/而不是\，路径要全，不能留开口如：H:/fonts；默认的是查找ttf格式字体文件，查找其他需要自行修改
 * 虽然这些稍加修改就能避免的，但是这仅仅给自己用用，我肯定假设自己会用，故稍加注意就好使
 * 只要电脑上配有php，在任何地方都可以运行，双击我给的批处理文件就行
 * 测试发现拷贝大些的文件速度不快，但是对内存和cpu的占有不是很大
 */
function type($filename)
{

    $match_type = array(&quot;ttf&quot;);//目标后缀数组

    foreach($match_type as $val)
    {
        //循环匹配数组$match_type中的后缀，自行修改匹配模式可以选择不同的文件名，不分大小写
        if(preg_match(&quot;/\.$val$/i&quot;,$filename))
        {
            return true;
        }
    }

    return false;
}

function shift($dir,$md)
{
    if(is_dir($dir))
    {
        $pp = fopen($md.'message.txt',&quot;w+&quot;);//创建信息文件，文件将说明成功复制的文件及失败了文件

        $mesage = '';//初始化信息

        if ($dh = opendir($dir))
        {
            //循环读取文件中的文件名
            while (($file= readdir($dh)) !== false)
            {

                $dir_new=$dir;

                $dir_new.=$file.&quot;/&quot;;

                //如果是文件夹，递归重新调用shift函数
                if((is_dir($dir_new)) &amp;&amp; $file!=&quot;.&quot; &amp;&amp; $file!=&quot;..&quot;)
                {
                    shift($dir_new,$md);
                }
                else
                {
                    if($file!=&quot;.&quot; &amp;&amp; $file!=&quot;..&quot;)
                    {
                        //如果是文件
                        if(type($file))
                        {
                            $newfile =$md.$file;
                            $p=fopen($newfile,'w+');//在目标文件夹中创建拷贝文件
                            if(copy($dir_new,$newfile))
                            {//复制文件
                                $mesage .= &quot;成功复制&quot;.$file.&quot; : $dir_new=&gt;$md//记录\n&quot;;
                            }
                            else
                            {
                                $mesage .= &quot;复制失败&quot;.$file.&quot;//记录\n&quot;;
                            }

                            fclose($p);

                        }

                    }

                }

            }
            closedir($dh);
            fwrite($pp,$mesage);
            fclose($pp);
        }
    }
}

shift("H:/fonts/","E:/ttf/");
```

运行使用的bat批处理文件就两句

```
$ php file.php
```

呵呵，原理php还是很有用嘛……下面是附件，php在本地运行不需要在发布根目录下，只要两个文件在一起就行
