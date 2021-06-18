<?php
namespace ChrisGruen\RealtyManager\ViewHelpers;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Class FeatureViewHelper
 */
class ImageListViewHelper extends AbstractViewHelper
{
    
    /**
     * Initialize arguments
     */
    public function initializeArguments()
    {
        $this->registerArgument('object', 'int', true);
    }
    
    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
        ) {
            
            $uid_obj = $arguments['object'];
            
            if ($uid_obj > 0) {
                
                $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('sys_file_reference');
                
                $images = $queryBuilder
                    ->select('*')
                    ->from('sys_file')
                    ->join(
                        'sys_file',
                        'sys_file_reference',
                        'fr',
                        $queryBuilder->expr()->eq('fr.uid_local', $queryBuilder->quoteIdentifier('sys_file.uid'))
                    )
                    ->join(
                        'sys_file',
                        'sys_file_metadata',
                        'fm',
                        $queryBuilder->expr()->eq('fm.file', $queryBuilder->quoteIdentifier('sys_file.uid'))
                        )
                    ->where($queryBuilder->expr()->eq('uid_foreign', $uid_obj, \PDO::PARAM_INT))
                    ->orderby('sorting_foreign')
                    ->setMaxResults(2)
                    ->execute()
                    ->fetchAll();
                
                    $html_output = '';
                    foreach($images as $key => $image) { 
                        $img_path = '/fileadmin/'.$image['identifier'];
                        $img_alt = $image['alternative'];
                        $img_title = $image['title'];
                        $html_output .= "<div class='list-img'>";
                        $html_output .= "<img class='img-fluid' src='".$img_path."' alt='".$img_alt."' title='".$img_title."' />";
                        $html_output .= "</div>";
                    }
                    
                    return $html_output;
            }
    }
}
