<?php
namespace netroby\zvcore;

class pager
{
    /**
     * url的前缀
     * index-index
     * @var string URL前缀;
     */
    public $urlPrefix;
    /**
     * 总记录数
     * 可通过查询数据表或其他位置取到总记录条数
     * @var integer 总记录数
     */
    public $totalRows;
    /**
     * 每页显示的记录数
     * 数据查询分页的限制
     * @var integer 每页显示记录数
     */
    public $perPage;
    /**
     * 总页数
     * 总记录数除以每页显示记录数
     * @var integer 总页数;
     */
    public $totalPages;
    /**
     * 当前真实页面数
     *  等于当前页面数-1
     * @var integer 当前真实页面数 等于当前页面数-1;
     */
    public $pageTrue;
    /**
     * 当前页面数
     * 通过url请求参数传递过来的当前页面数。
     * @var integer 请求参数传递过来的当前的页面数
     */
    public $pageNow;
    /**
     * 前一页
     * 当前页的前一页，最低等于起始页
     * @var integer 前一页
     */
    public $pagePrev;
    /**
     * 下一页
     * 当前页的后一页，最大不超过最后一页
     * @var integer 下一页
     */
    public $pageNext;
    /**
     * 第一页
     * @var integer 第一页
     */
    public $pageFirst;
    /**
     * 最后一页
     * 不超过最大页数
     * @var integer 最后一页
     */
    public $pageEnd;
    /**
     * 分页开始的记录数
     * 数据查询开始的记录条数
     * @var integer 分页开始记录条数
     */
    public $rowStart;
    /**
     * 分页导航 存储变量
     * 生成的分页导航HTML
     * @var string 分页导航HTML
     */
    public $show;

    /**
     * 初始化
     * 设置变量，提示信息
     * @param object $urlPrefix 默认url
     * @param object $totalRows 总记录数
     * @param object $perPage 每页数据行数
     */
    public function __construct($totalRows, $perPage = 20, $urlPrefix = null)
    {
        if (empty($totalRows)) {
            throw new Exception('请指定总记录条数!');
        } else {
            //设定总记录条数
            $this->totalRows = $totalRows;
        }

        if (empty($urlPrefix)) {
            //取当前请求的URL地址
            $this->urlPrefix = $_REQUEST['controller'] . "-" . $_REQUEST['action'];
        } else {
            $this->urlPrefix = $urlPrefix;
        }

        //每页显示条数
        $this->perPage = $perPage;

        //开始分页
        $this->setPager();

    }

    /**
     * 分页计算
     * 限定最大页数，总页数等
     */
    public function setPager()
    {
        //计算总页数
        $this->totalPages = ceil($this->totalRows / $this->perPage);
        //获取当前页面数;
        if (empty($_REQUEST['page'])) {
            $pageNow = 1;
        } else {
            $pageNow = intval($_REQUEST['page']);
        }
        //检验页面数是否超出最大值范围和最小值范围
        $this->pageNow = $this->limitCheck($pageNow, 1, $this->totalPages);


        //当前真实的页面数
        $pageTrue = ($this->pageNow) - 1;
        //计算最大许可数目
        $maxPageTrue = ($this->totalPages) - 1;
        //检查真实页面数是否超出最大值
        $this->pageTrue = $this->limitCheck($pageTrue, 0, $maxPageTrue);


        // 前一页
        $pagePrev = ($this->pageNow) - 1;
        $this->pagePrev = $this->limitCheck($pagePrev, 1, $this->totalPages);

        //后一页
        $pageNext = ($this->pageNow) + 1;
        $this->pageNext = $this->limitCheck($pageNext, 1, $this->totalPages);

        //第一页
        $this->pageFirst = 1;
        //末页
        $this->pageEnd = $this->totalPages;

        //设定分页记录开始记录行数;
        $this->rowStart = $this->pageTrue * $this->perPage;
        //显示分页html代码
        $this->showPager();
    }

    /**
     * 分页导航
     * 生成分页html代码
     */
    public function showPager()
    {
        //分页显示起始页计算为当前页减2；
        $page_nav_start_num = $this->pageNow - 2;

        //如果起始页+5大于最大许可页数，起始页置为最大许可页数减4
        if (($page_nav_start_num + 5) > ($this->totalPages)) {
            $page_nav_start_num = $this->totalPages - 4;
        }
        //如果起始页小于1，置为1
        if ($page_nav_start_num < 1) {
            $page_nav_start_num = 1;
        }
        //分页显示结束设置，如果当前页小于3，则结束页=5；
        if ($this->pageNow < 3) {
            $page_nav_end_num = 5;
        } else {
            //分页显示结束等于当前页+2；
            $page_nav_end_num = $this->pageNow + 2;
        }
        //如果分页显示页大于最大许可页数，则置为最大显示页数
        if ($page_nav_end_num > ($this->totalPages)) {
            $page_nav_end_num = $this->totalPages;
        }
        //构建显示开始
        $pager = '<ul class="pager">';
        $pager .= '<li><a href="' . $this->urlPrefix . '-page-' . $this->pageFirst . '.html">|&lt;</a></li>';
        $pager .= '<li><a href="' . $this->urlPrefix . '-page-' . $this->pagePrev . '.html">&lt;&lt;</a></li>';
        //构建分页显示
        for ($i = $page_nav_start_num; $i <= $page_nav_end_num; $i++) {
            if ($i == $this->pageNow) {
                $pager .= '<li><a href="' . $this->urlPrefix . '-page-' . $i . '.html" class="pageNow">' . $i . '</a></li>';
            } else {
                $pager .= '<li><a href="' . $this->urlPrefix . '-page-' . $i . '.html">' . $i . '</a></li>';
            }
        }
        $pager .= '<li><a href="' . $this->urlPrefix . '-page-' . $this->pageNext . '.html">&gt;&gt;</a></li>';
        $pager .= '<li><a href="' . $this->urlPrefix . '-page-' . $this->pageEnd . '.html">&gt;|</a></li>';
        $pager .= '<li><a href="#"><span style="color:red">' . $this->pageNow . '</span>/' . $this->totalPages . '</a></li></ul>';
        $this->show = $pager;
    }

    /**
     * 限定最大值和最小值
     * 最大不超过最大值，最小不低于最小值
     * @return 限定后的值
     * @param object $val 需要限定的值
     * @param object $min 最小值
     * @param object $max 最大值
     */
    public function limitCheck($val, $min, $max)
    {
        if ($val < $min) {
            return $val = $min;

        } elseif ($val > $max) {
            return $val = $max;
        } else {
            return $val = $val;
        }
    }
}
