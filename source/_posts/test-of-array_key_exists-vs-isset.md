title: "isset和array_key_exists的比较测试"
id: 170
date: 2010-07-15 10:10:58
tags: 
- php探索
categories: 
- 电脑网络
---

经常使用isset和array_key_exists测试数组中的变量是否存在，然后忽然很想知道到底两者有什么区别，然后百度了一下，有说 array_key_exists[更快](www.laohucheng.com/post/122/)的，也用说isset更快的，当然似乎更多人建议 isset。

于是自己做了一个测试，比较一下两者的差异：注，本文最初发在[ci论坛](http://codeigniter.org.cn/forums/thread-5991-1-1.html)

结果大致如下：

当数组个数为10时，两者差异就体现出来了——isset速度要快近10倍，但不是很明显，而且对于变量是否存在，两者之间的差异没有太大，当变量存在时运行更快，但是这种趋势在是非常微弱的。使用 array_key_exists随着循环的次数增加，程序运行的时间增加量是成几何级数增加的，当一个数组元素个数超过 1000时运行速度就非常慢了。

最后总结如下：

1、isset和array_key_exists在对判断一个数组函数中某个元素是否存在，isset速度要更快，而且这 种速度差异是非常大的

2、isset属于php中的语言结构，而后者是函数，所以前者更快，isset不可 以用于可变函数

3、对于变量值的判断，当变量为NULL时，isset返回的结果是false，而后者只判断变量是否存在。所以如果判断一个数组中的某个 元素，并且判断其是否是否为真，应该用isset

4、isset属于php特定语言结构，后者在其他语言中也存在，更具可读性

具体测试过程，使用[ci](http://codeigniter.org.cn)基准测试类测试

```
    function issetVsArray()
    {
        $loop = 10;

        $test = array();
        for( $i = 0; $i &lt;= $loop; $i++)
        {
            $test['test'.$i] = $i;
        }

        $this-&gt;benchmark-&gt;mark('issetAllFalse');

        for( $i = 0; $i&lt;=$loop; $i++)
        {
            isset($test['t'.$i]);
        }
        $this-&gt;benchmark-&gt;mark('arraysAllFalse');
        for( $i = 0; $i&lt;=$loop; $i++)
        {
            array_key_exists('t'.$i,$test);
        }

        $this-&gt;benchmark-&gt;mark('issetAllTrue');
        for( $i = 0; $i&lt;=$loop; $i++)
        {
            isset($test['test'.$i]);
        }
        $this-&gt;benchmark-&gt;mark('arraysAllTrue');
        for( $i = 0; $i&lt;=$loop; $i++)
        {
            array_key_exists('test'.$i,$test);
        }
        $this-&gt;benchmark-&gt;mark('end');

        echo $this-&gt;benchmark-&gt;elapsed_time('issetAllFalse','arraysAllFalse').'&lt;br /&gt;';
        echo $this-&gt;benchmark-&gt;elapsed_time('arraysAllFalse','issetAllTrue').'&lt;br /&gt;';
        echo $this-&gt;benchmark-&gt;elapsed_time('issetAllTrue','arraysAllTrue').'&lt;br /&gt;';
        echo $this-&gt;benchmark-&gt;elapsed_time('arraysAllTrue','end').'&lt;br /&gt;';
    }
```

几次测试结果：

```
循环10次，$loop=10
0.0001
0.0009
0.0001
0.0007

循环100次，$loop=100
0.0003
0.0185
0.0003
0.0189

循环1000次，$loop=1000
0.0015
0.2831
0.0020
0.2839

循环10000次，$loop=10000
0.0157
8.4764
0.0164
8.4101
```
