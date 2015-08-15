<?php
namespace netroby\zvcore;

class zrss
{
    public $rssOut;

    public function __construct($channelTitle, $channelLink, $channelDescription)
    {
        header("Content-Type:text/xml;");
        if (null == $channelTitle) {
            throw new Exception("请输入频道标题！~");
        } elseif (null == $channelLink) {
            throw new Exception("请输入频道链接！~");
        } elseif (null == $channelDescription) {
            throw new Exception("请输入频道说明！~");
        }
        $this->buildHeader($channelTitle, $channelLink, $channelDescription);
    }

    public function buildHeader($channelTitle, $channelLink, $channelDescription)
    {
        $this->rssOut = '<?xml version="1.0" encoding="utf-8"?>';
        $this->rssOut .= '<rss version="2.0">';
        $this->rssOut .= '<channel>';
        $this->rssOut .= '<title>' . $channelTitle . '</title>';
        $this->rssOut .= '<link>' . $channelLink . '</link>';
        $this->rssOut .= '<description>' . $channelDescription . '</description>';
        $this->rssOut .= '<pubDate>' . date('D, d M Y H:i:s', time()) . ' GMT</pubDate>';
        $this->rssOut .= '<language>zh-CN</language>';
    }

    public function buildItem($itemTitle, $itemAuthor, $itemLink, $itemDescription, $itemTime)
    {
        $this->rssOut .= '<item>';
        $this->rssOut .= '<title>' . $itemTitle . '</title>';
        $this->rssOut .= '<author>' . $itemAuthor . '</author>';
        $this->rssOut .= '<link>' . $itemLink . '</link>';
        $this->rssOut .= '<description><![CDATA[' . $itemDescription . ']]></description>';
        $this->rssOut .= '<pubDate>' . date('D, d M Y H:i:s', $itemTime) . ' GMT</pubDate>';
        $this->rssOut .= '</item>';
    }

    public function showRss()
    {
        $this->rssOut .= '</channel>';
        $this->rssOut .= '</rss>';
        echo $this->rssOut;
    }
}
