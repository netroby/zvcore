<?php 
class syntaxhl {
    public function __construct() {
        include ('./geshi/geshi.php');
    }
    public function hl_string($string) {
        $string = str_replace("<?php", "&lt?php", $string);
        $string = str_replace("?>", '?&gt', $string);
        $hl_arr = array('php', 'java', 'cpp', 'python', 'javascript', 'css');
        foreach ($hl_arr as $hl) {
            if (stripos($string,"[{$hl}]")) {
                preg_match("/\[".$hl."\](.+?)\[\/".$hl."\]/eis", $string, $nova);
                $prs = $this->do_hl($nova[1], $hl);
                preg_replace("/\[".$hl."\](.+?)\[\/".$hl."\]/eis", $prs, $string);
            }
        }
        return $string;
    }
    public function do_hl($string, $language) {
        $geshi = new geshi($string, $language);
        return $geshi->parse_code();
    }
}
