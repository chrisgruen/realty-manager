<?php

namespace ChrisGruen\RealtyManager\ViewHelpers\Widget;

/**
 * This file is part of the "news" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;

/**
 * This ViewHelper renders a Pagination of objects.
 *
 * = Examples =
 *
 * <code title="required arguments">
 * <f:widget.paginate objects="{blogs}" as="paginatedBlogs">
 *   // use {paginatedBlogs} as you used {blogs} before, most certainly inside
 *   // a <f:for> loop.
 * </f:widget.paginate>
 * </code>
 *
 */
class PaginateViewHelper extends \TYPO3\CMS\Fluid\Core\Widget\AbstractWidgetViewHelper
{
    /**
     * @var \ChrisGruen\RealtyManager\ViewHelpers\Widget\Controller\PaginateController
     */
    protected $controller;

    /**
     * Inject controller
     *
     * @param \ChrisGruen\RealtyManager\ViewHelpers\Widget\Controller\PaginateController $controller
     */
    public function injectController(\ChrisGruen\RealtyManager\ViewHelpers\Widget\Controller\PaginateController $controller)
    {
        $this->controller = $controller;
    }

    /**
     * Initialize arguments.
     *
     * @throws \TYPO3Fluid\Fluid\Core\ViewHelper\Exception
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('objects', 'mixed', 'Object', true);
        $this->registerArgument('as', 'string', 'as', true);
        $this->registerArgument('configuration', 'array', 'configuration', false, ['itemsPerPage' => 10, 'insertAbove' => false, 'insertBelow' => true, 'maximumNumberOfLinks' => 99]);
    }

    /**
     * @return string
     * @throws \UnexpectedValueException
     */
    public function render()
    {
        $objects = $this->arguments['objects'];

        if (!($objects instanceof QueryResultInterface || $objects instanceof ObjectStorage || is_array($objects))) {
            throw new \UnexpectedValueException('Supplied file object type ' . get_class($objects) . ' must be QueryResultInterface or ObjectStorage or be an array.', 1454510731);
        }
        return $this->initiateSubRequest();
    }
}
