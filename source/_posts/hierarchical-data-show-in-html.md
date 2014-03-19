title: "树结构数据的html展示实现"
id: 177
date: 2010-07-16 15:30:52
tags: 
- php探索
categories: 
- 电脑网络
---

网页中经常会需要展示一些树结构数据，而现在流行的关系型数据库（比如MySQL）都是以二位的数据形式贮存，对于通常用到的树结构需要转化为二维关系来放在数据库中。关于树结构数据的贮存，Mike Hillyer一文[Managing Hierarchical Data in MySQL](http://dev.mysql.com/tech-resources/articles/hierarchical-data.html)有非常详细的描述。

Mike Hillyer提出两种方式：

1、毗邻目录模式(adjacency list model)

2、预排序遍历树算法(modified preorder tree traversal algorithm)

虽然两种方式很好地实现了数据存储过程，但网页显示还需要做得更多。本文仅仅探讨树结构数据的展示部分。

Mike Hillyer的两种模式中，第二种是直接把数据的结构关系转存起来，数据的展示不存在问题（具体细节参考原文）。第一数据结构的存在更容易理解，所以似乎被更多人采用。这种方法的原理是把每个数与其父节点数据的一个标识符一起贮存在一列中，然后就像摸着石子过河一样，找到一个点然后可以得到前一个点（或者后一个点），然后所有的点就都可以穿在一起了。这种方法有些数学中数学归纳法的意味，它的好处在于存储数据时非常清晰——只需要处理数据本身和父节点的关系，而且可扩展性好，数据所处理的关系越少，这大概就是软件所强调的松耦合思想吧。当然这种方法的代价是低效率的查询，通常无法确定整个树的深度，这就需要使用递归查询来获得数据，递归查询数据库对于系统资源的消耗是非常巨大的。此外还有一个致命的缺陷，修改数据时容易出现节点闭合的情况，也就是这些数据围成了一个圈，如下图所示

```text
1----&gt;2-----&gt;4------&gt;5
|                    |
1&lt;----8&lt;-----7&lt;------6
```

1后面的自带元素永远不知道它们起源于1整个节点，一旦出现这种情况就会到时递归查询进入死循环，所以存储过程一定要对数据进行合理性检验（就像结婚需要避免血缘关系）。当然在做中小型网站时，这种方法还是非常好的，而且在数据变换不是很大时可以使用缓存来解决循环查询的问题。

在生产html展示页时，首先要完成的是从数据库中提取数据，并且转换为树结构。假设有如下数据：

```
+-------------+----------------------+--------+
|          id | name                 | parent |
+-------------+----------------------+--------+
|           1 | 1                    |      0 |
|           2 | 2                    |      0 |
|           3 | 3                    |      0 |
|           4 | 4                    |      2 |
|           5 | 5                    |      2 |
|           6 | 6                    |      3 |
|           7 | 7                    |      3 |
|           8 | 8                    |      6 |
|           9 | 9                    |      6 |
+-------------+----------------------+--------+
```

第一列id为一组数据的标识。用集合来描述这列数据是{1,2{4,5},3{6{8,9},7}},如下图：

![树结构图](/assets/images/tree.jpg "树结构图")

如果上图依次标上数字，就成为了预排序遍历树算法模式的贮存了，看到如此结构自然想到用在PHP中可以使用如此数组来表示

```
array(1,2,array(4,5),3,array(6,array(8,9),7));
```

于是尝试了一下如下的测试

```
function unlimitedSortTest()
{
    $arrSort = array(
        array(1,0),
        array(2,0),
        array(3,0),
        array(4,2),
        array(5,2),
        array(6,3),
        array(7,3),
        array(8,6),
        array(9,6)
    );
    $arrResult = array(
        1,2,array(4,5),3,array(6,array(8,9),7)
    );
    $this-&gt;unit-&gt;run($this-&gt;_listToTree($arrSort),$arrResult,'分类数据生成测试');
}
```

依然是使用CI的单元测试类，实现如此算法甚是折腾，一共使用了三个函数，一个作为借口函数调入数据，一个函数通过递归方式遍历所有节点，把数据与数据的深度标识依次存入一个数组中，最后一个函数获取子节点的元素，如果子节点函数返回书为空，递归循环退出。这种方式实现得非常简陋，于数据库结合，需要首先把数据库数据直接提取，然后存入一个数组。事实上，这返回的数据也无法得到上面测试想要得到的结果，数据的展示只需要可以明显看出数据之间的继承关系就可以。最后得到一个如下图的options

<select  name="tests">
<option value="0">1</option>
<option value="10">　11</option>
<option value="11">　12</option>
<option value="20">　　21</option>
<option value="30">　　　31</option>
<option value="40">　　　　41</option>
<option value="41">　　　　42</option>
<option value="42">　　　　43</option>
<option value="32">　　　32</option>
<option value="33">　　　33</option>
<option value="34">　　　34</option>
<option value="22">　　22</option>
<option value="23">　　23</option>
<option value="13">　13</option>
<option value="14">　14</option>
<option value="2">2</option>
<option value="3">3</option>
<option value="4">4</option>
</select>

使用空白缩进深度作为树结构的描述方式，看来还是非常不爽，展示得毕竟非常勉强。后来无意中看到另外一种更合适的，真正的树结构：

<select id="role_parents" class="valid" name="role_parents"> <option disabled="disabled" selected="selected" value="label">父级组</option> <option value="网站管理员">网站管理员</option> <option value="测试者11"> ├ 测试者11</option> <option value="测试者21"> ├ 测试者21</option> <option value="测试者2"> │ └ 测试者2</option> <option value="测试者32"> ├ 测试者32</option> <option value="测试者12"> │ ├ 测试者12</option> <option value="测试者1"> │ └ 测试者1</option> <option value="测试者"> └ 测试者</option> </select>

于是又探索起来这种形式的生成过程。最终还是使用了循环查询方法，并且实现了从任意节点获取树的方法getTree，并且还有一个比较关系是否合适的函数_notChildOf。这里只有一个属性db为继承自CI的Model类，转换为其他数据库操作类也非常容易。Ok，就这样啦，最后，除了可以生成上面的选择器，还可以借助比如jQuery的table-tree插件，生成表格数据的树结构。上面Category类中，getTree函数得到的数组中，数组key值就是用来表示数据在整个书中的关系(密码在getChild中的$j中)。

```
/**
 *
 * 分类模型父类
 * 在数据库中结构为
 * id       childName       parentName
 * -----------------------------------
 *  1       栏目1           root
 *  2       栏目2           栏目1
 *
 */

class CategoryModel extends Model{

    /**
     *
     * 父节点名，对应为数据库中字段
     *
     * @var string
     */
    protected $_parent;

    /**
     *
     * 子节点名，对应为数据库中字段
     *
     * @var string
     */
    protected $_child;

    /**
     *
     * 更节点名
     *
     * @var string
     */
    protected $_root;

    //整体树结构缓存
    protected $_tree;

    public $table;

    /**
     *
     * 重载Datamapper构造函数
     *
     */
    function __construct()
    {
        parent::__construct();
    }

    /**
     *
     * 获取树结构，以数组形式展现
     * @param $root string 树起点名
     * @return array
     */
    public function getTree($root = NULL)
    {
        $tree = $this-&gt;getChild($root);
        $treeNode = array();

        if(!empty($tree))
        {
            $parents = array_keys($tree);
            $children = array_values($tree);

            foreach($children as $key =&gt; $child)
            {
                $parent = explode('-',$parents[$key]);
                $parentNext = isset($parents[$key + 1])?explode('-',$parents[$key+1]):NULL;
                $level = $parent[1];
                $levelNext = $parentNext ? $parentNext[1]:NULL;

                if($level == 0)
                {
                    $treeNode[$child] = $child;
                }
                elseif($level &gt; 0 AND $levelNext AND $levelNext &gt;= $level)
                {
                    $treeNode[$child] = '&amp;nbsp;'.str_repeat('│&amp;nbsp;',$level-1).'├&amp;nbsp;'.$child;
                }
                else
                {
                    $treeNode[$child] = '&amp;nbsp;'.str_repeat('│&amp;nbsp;',$level-1).'└&amp;nbsp;'.$child;
                }
            }
        }

        return $treeNode;
    }

    public function getParent($target = '',&amp;$nodeTree = array())
    {
        if($target == $this-&gt;_root)
        {
            $nodeTree[] = $target;
        }
        else
        {
            $this-&gt;db-&gt;select($this-&gt;_parent.','.$this-&gt;_child);

            $query = $this-&gt;db-&gt;where($this-&gt;_child,$target)-&gt;get($this-&gt;table);
            if($query-&gt;num_rows() &gt; 0)
            {
                $row = $query-&gt;row();
                $nodeTree[] = $target;
                $this-&gt;getParent($row-&gt;{$this-&gt;_parent},&amp;$nodeTree);
            }
        }

        return $nodeTree;
    }

    public function getChild($target = NULL, &amp;$nodeTree = array(),&amp;$j = -1)
    {
        $target = ! $target?$this-&gt;_root:$target;

        $this-&gt;select($this-&gt;table.'.'.$this-&gt;_parent.','.$this-&gt;table.'.'.$this-&gt;_child);

        $query = $this-&gt;db-&gt;where($this-&gt;table.'.'.$this-&gt;_parent,$target)-&gt;get($this-&gt;table);

        if($query-&gt;num_rows &gt; 0)
        {
            //$i的作用仅仅在于为每一个child提供不同的parent键值
            $i = 1;
            foreach($query-&gt;result() as $childSlibing)
            {
                $searchNode = $childSlibing-&gt;{$this-&gt;_child};
                $j++;

                $nodeTree[$target.'-'.$j.'-'.$i] = $searchNode;

                $this-&gt;getChild($has_maney,$searchNode,&amp;$nodeTree,&amp;$j);

                $j--;
                $i++;
            }
        }

        return $nodeTree;
    }

    /**
     *
     * 判断两者是否相互包涵，即规定改变
     */
    protected function _notChildOf($field)
    {
        if(empty($this-&gt;{$field}))
        {
            return FALSE;
        }
        else
        {
            $parents = $this-&gt;getParent($this-&gt;{$field});
            if(in_array($this-&gt;{$this-&gt;_child},$parents))
            {
                return FALSE;
            }
            else
            {
                return TRUE;
            }

        }
    }
}
```
