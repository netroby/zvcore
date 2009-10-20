<?php 
class pager {
    /**
     * url��ǰ׺
     * index-index
     * @var string URLǰ׺;
     */
    public $urlPrefix;
    /**
     * �ܼ�¼��
     * ��ͨ����ѯ���ݱ������λ��ȡ���ܼ�¼����
     * @var integer �ܼ�¼��
     */
    public $totalRows;
    /**
     * ÿҳ��ʾ�ļ�¼��
     * ���ݲ�ѯ��ҳ������
     * @var integer ÿҳ��ʾ��¼��
     */
    public $perPage;
    /**
     * ��ҳ��
     * �ܼ�¼������ÿҳ��ʾ��¼��
     * @var integer ��ҳ��;
     */
    public $totalPages;
    /**
     * ��ǰ��ʵҳ����
     *  ���ڵ�ǰҳ����-1
     * @var integer ��ǰ��ʵҳ���� ���ڵ�ǰҳ����-1;
     */
    public $pageTrue;
    /**
     * ��ǰҳ����
     * ͨ��url����������ݹ����ĵ�ǰҳ������
     * @var integer ����������ݹ����ĵ�ǰ��ҳ����
     */
    public $pageNow;
    /**
     * ǰһҳ
     * ��ǰҳ��ǰһҳ����͵�����ʼҳ
     * @var integer ǰһҳ
     */
    public $pagePrev;
    /**
     * ��һҳ
     * ��ǰҳ�ĺ�һҳ����󲻳������һҳ
     * @var integer ��һҳ
     */
    public $pageNext;
    /**
     * ��һҳ
     * @var integer ��һҳ
     */
    public $pageFirst;
    /**
     * ���һҳ
     * ���������ҳ��
     * @var integer ���һҳ
     */
    public $pageEnd;
    /**
     * ��ҳ��ʼ�ļ�¼��
     * ���ݲ�ѯ��ʼ�ļ�¼����
     * @var integer ��ҳ��ʼ��¼����
     */
    public $rowStart;
    /**
     * ��ҳ���� �洢����
     * ���ɵķ�ҳ����HTML
     * @var string ��ҳ����HTML
     */
    public $show;
    
    /**
     * ��ʼ��
     * ���ñ�������ʾ��Ϣ
     * @param object $urlPrefix Ĭ��url
     * @param object $totalRows �ܼ�¼��
     * @param object $perPage ÿҳ��������
     */
    public function __construct($totalRows, $perPage = 20, $urlPrefix = null) {
        if ( empty($totalRows)) {
            throw new Exception('��ָ���ܼ�¼����!');
        } else {
            //�趨�ܼ�¼����
            $this->totalRows = $totalRows;
        }
        
        if ( empty($urlPrefix)) {
            //ȡ��ǰ�����URL��ַ
            $this->urlPrefix = $_REQUEST['controller']."-".$_REQUEST['action'];
        } else {
            $this->urlPrefix = $urlPrefix;
        }
        
        //ÿҳ��ʾ����
        $this->perPage = $perPage;
        
        //��ʼ��ҳ
        $this->setPager();
        
    }
    /**
     * ��ҳ����
     * �޶����ҳ������ҳ����
     */
    public function setPager() {
        //������ҳ��
        $this->totalPages = ceil($this->totalRows / $this->perPage);
        //��ȡ��ǰҳ����;
        if ( empty($_REQUEST['page'])) {
            $pageNow = 1;
        } else {
            $pageNow = intval($_REQUEST['page']);
        }
        //����ҳ�����Ƿ񳬳����ֵ��Χ����Сֵ��Χ
        $this->pageNow = $this->limitCheck($pageNow, 1, $this->totalPages);

        
        //��ǰ��ʵ��ҳ����
        $pageTrue = ($this->pageNow) - 1;
        //������������Ŀ
        $maxPageTrue = ($this->totalPages) - 1;
        //�����ʵҳ�����Ƿ񳬳����ֵ
        $this->pageTrue = $this->limitCheck($pageTrue, 0, $maxPageTrue);

        
        // ǰһҳ
        $pagePrev = ($this->pageNow) - 1;
        $this->pagePrev = $this->limitCheck($pagePrev, 1, $this->totalPages);
        
        //��һҳ
        $pageNext = ($this->pageNow) + 1;
        $this->pageNext = $this->limitCheck($pageNext, 1, $this->totalPages);
        
        //��һҳ
        $this->pageFirst = 1;
        //ĩҳ
        $this->pageEnd = $this->totalPages;
        
        //�趨��ҳ��¼��ʼ��¼����;
        $this->rowStart = $this->pageTrue * $this->perPage;
        //��ʾ��ҳhtml����
        $this->showPager();
    }
    /**
     * ��ҳ����
     * ���ɷ�ҳhtml����
     */
    public function showPager() {
        //��ҳ��ʾ��ʼҳ����Ϊ��ǰҳ��2��
        $page_nav_start_num = $this->pageNow - 2;

        //�����ʼҳ+5����������ҳ������ʼҳ��Ϊ������ҳ����4
        if (($page_nav_start_num + 5) > ($this->totalPages)) {
            $page_nav_start_num = $this->totalPages - 4;
        }
                //�����ʼҳС��1����Ϊ1
        if ($page_nav_start_num < 1) {
            $page_nav_start_num = 1;
        }
        //��ҳ��ʾ�������ã������ǰҳС��3�������ҳ=5��
        if ($this->pageNow < 3) {
            $page_nav_end_num = 5;
        } else {
            //��ҳ��ʾ�������ڵ�ǰҳ+2��
            $page_nav_end_num = $this->pageNow + 2;
        }
        //�����ҳ��ʾҳ����������ҳ��������Ϊ�����ʾҳ��
        if ($page_nav_end_num > ($this->totalPages)) {
            $page_nav_end_num = $this->totalPages;
        }
        //������ʾ��ʼ
        $pager = '<ul class="pager">';
        $pager .= '<li><a href="'.$this->urlPrefix.'-page-'.$this->pageFirst.'.html">|&lt;</a></li>';
        $pager .= '<li><a href="'.$this->urlPrefix.'-page-'.$this->pagePrev.'.html">&lt;&lt;</a></li>';
        //������ҳ��ʾ
        for ($i = $page_nav_start_num; $i <= $page_nav_end_num; $i++) {
            if ($i == $this->pageNow) {
                $pager .= '<li><a href="'.$this->urlPrefix.'-page-'.$i.'.html" class="pageNow">'.$i.'</a></li>';
            } else {
                $pager .= '<li><a href="'.$this->urlPrefix.'-page-'.$i.'.html">'.$i.'</a></li>';
            }
        }
        $pager .= '<li><a href="'.$this->urlPrefix.'-page-'.$this->pageNext.'.html">&gt;&gt;</a></li>';
        $pager .= '<li><a href="'.$this->urlPrefix.'-page-'.$this->pageEnd.'.html">&gt;|</a></li>';
        $pager .= '<li><a href="#"><span style="color:red">'.$this->pageNow.'</span>/'.$this->totalPages.'</a></li></ul>';
        $this->show = $pager;
    }
    /**
     * �޶����ֵ����Сֵ
     * ��󲻳������ֵ����С��������Сֵ
     * @return �޶����ֵ
     * @param object $val ��Ҫ�޶���ֵ
     * @param object $min ��Сֵ
     * @param object $max ���ֵ
     */
    public function limitCheck($val, $min, $max) {
        if ($val < $min) {
            return $val = $min;
            
        } elseif ($val > $max) {
            return $val = $max;
        } else {
            return $val = $val;
        }
    }
}
