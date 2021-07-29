<?php
namespace ChrisGruen\RealtyManager\ViewHelpers\PaginationAjax;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use ChrisGruen\RealtyManager\Configuration\ConfigurationObject;

class PerPage {
    public $perpage;
    
    function __construct() {
        $settings = GeneralUtility::makeInstance(ConfigurationObject::class);        
        $this->perpage = $settings->getPaginateItemsPerPage();
    }
    
    function getAllPageLinks($count,$href) {
        $output = '<ul id="ajax-paging" class="f3-widget-paginator">';
        if(!isset($_GET["page"])) $_GET["page"] = 1;

        if($this->perpage != 0)
            $pages  = ceil($count/$this->perpage);
            if($pages>1) {
                if($_GET["page"] == 1) {
                    $output .= '<li><span class="link first disabled">&#8810;</span></li>';
                    $output .= '<li><span class="link disabled">&#60;</span></li>';
                } else {
                    $output .= '<li><a class="link first" onclick="getresult(\'' . $href . (1) . '\')" >&#8810;</a></li>';
                    $output .= '<li><a class="link" onclick="getresult(\'' . $href . ($_GET["page"]-1) . '\')" >&#60;</a></li>';
                }
                                                                 
                if(($_GET["page"]-3) > 0) {
                    if($_GET["page"] == 1)
                        $output .= '<li class="current"><span id=1 class="link current">1</span></li>';
                    else
                        $output .= '<li><a class="link" onclick="getresult(\'' . $href . '1\')" >1</a></li>';
                }
                
                if(($_GET["page"]-3) > 1) {
                    $output = $output . '<li><span class="dot">...</span></li>';
                }
                    
                for($i=($_GET["page"]-2); $i<=($_GET["page"]+2); $i++)	{
                    
                    if($i<1) continue;
                    if($i>$pages) break;
                    
                    if($_GET["page"] == $i)
                        $output .=  '<li class="current"><span id='.$i.' class="link current">'.$i.'</span></li>';
                    else
                        $output .= '<li><a class="link" onclick="getresult(\'' . $href . $i . '\')" >'.$i.'</a></li>';
                }
                    
                if(($pages-($_GET["page"]+2)) > 1) {
                    $output .= '<li><span class="dot">...</span></li>';
                }
                
                if(($pages-($_GET["page"]+2)) > 0) {
                    if($_GET["page"] == $pages)
                        $output .= '<li class="current"><span id=' . ($pages) .' class="link current">' . ($pages) .'</span></li>';
                    else
                        $output .= '<li><a class="link" onclick="getresult(\'' . $href .  ($pages) .'\')" >' . ($pages) .'</a></li>';
                }
                
                if($_GET["page"] < $pages) {
                    $output .= '<li><a  class="link" onclick="getresult(\'' . $href . ($_GET["page"]+1) . '\')" >></a></li>';
                    $output .= '<li><a  class="link" onclick="getresult(\'' . $href . ($pages) . '\')" >&#8811;</a></li>';
                }
                else
                    $output .='<li><span class="link disabled">></span><span class="link disabled">&#8811;</span></li>';
                                                                
            }
            $output .= '</ul>';
            return $output;
    }
    function getPrevNext($count,$href) {
        $output = '';
        if(!isset($_GET["page"])) $_GET["page"] = 1;
        if($this->perpage != 0)
            $pages  = ceil($count/$this->perpage);
            if($pages>1) {
                if($_GET["page"] == 1)
                    $output = $output . '<span class="link disabled first">Prev</span>';
                    else
                        $output = $output . '<a class="link first" onclick="getresult(\'' . $href . ($_GET["page"]-1) . '\')" >Prev</a>';
                        
                        if($_GET["page"] < $pages)
                            $output = $output . '<a  class="link" onclick="getresult(\'' . $href . ($_GET["page"]+1) . '\')" >Next</a>';
                            else
                                $output = $output . '<span class="link disabled">Next</span>';
                                
                                
            }
            return $output;
    }
}