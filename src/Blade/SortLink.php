<?php

namespace srgafanhoto\PatternRepository\Blade;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;

class SortLink
{

    /**
     * @param array $parameters
     *
     * @return string
     */
    public static function render(array $parameters)
    {
        list($sortColumn, $sortParameter, $title, $typeIcon, $queryParameters) = self::parseParameters($parameters);

        $title = self::applyFormatting($title);

        if ($mergeTitleAs = Config::get('sortablelink.inject_title_as', null)) {
            Request::merge([$mergeTitleAs => $title]);
        }

        list($icon, $direction) = self::determineDirection($sortColumn, $sortParameter, $typeIcon);

        $trailingTag = self::formTrailingTag($icon);

        $anchorClass = self::getAnchorClass();

        $queryString = self::buildQueryString($queryParameters, $sortParameter, $direction);

        return '<a'.$anchorClass.' href="'.url(Request::path().'?'.$queryString).'"'.'>'.htmlentities($title).$trailingTag;
    }


    /**
     * @param array $parameters
     *
     * @return array
     */
    public static function parseParameters(array $parameters)
    {
        //TODO: let 2nd parameter be both title, or default query parameters
        //TODO: needs some checks before determining $title
        $explodeResult = self::explodeSortParameter($parameters[0]);
        $sortColumn = (empty($explodeResult)) ? $parameters[0] : $explodeResult[1];
        $title = (count($parameters) === 1) ? $sortColumn : $parameters[1];
        $typeIcon = (count($parameters) === 2) ? '' : $parameters[2];
        $queryParameters = (isset($parameters[3]) && is_array($parameters[3])) ? $parameters[3] : [];

        return [$sortColumn, $parameters[0], $title, $typeIcon, $queryParameters];
    }


    /**
     * Explodes parameter if possible and returns array [relation, column]
     * Empty array is returned if explode could not run eg: separator was not found.
     *
     * @param $parameter
     *
     * @return array
     *
     * @throws \Kyslik\ColumnSortable\Exceptions\ColumnSortableException when explode does not produce array of size two
     */
    public static function explodeSortParameter($parameter)
    {
        $separator = Config::get('sortablelink.uri_relation_column_separator', '.');

        if (Str::contains($parameter, $separator)) {
            $oneToOneSort = explode($separator, $parameter);
            if (count($oneToOneSort) !== 2) {
                throw new ColumnSortableException();
            }

            return $oneToOneSort;
        }

        //TODO: should return ['column', 'relation']
        return [];
    }


    /**
     * @param string $title
     *
     * @return string
     */
    private static function applyFormatting($title)
    {
        $formatting_function = Config::get('sortablelink.formatting_function', null);
        if ( ! is_null($formatting_function) && function_exists($formatting_function)) {
            $title = call_user_func($formatting_function, $title);
        }

        return $title;
    }


    /**
     * @param $sortColumn
     * @param $sortParameter
     *
     * @return array
     */
    private static function determineDirection($sortColumn, $sortParameter, $typeIcon)
    {
        
        $icon = self::selectIcon($sortColumn, $typeIcon);
        $orderBy = Config::get('sortablelink.request.orderBy', 'orderBy');
        $sortedBy = Config::get('sortablelink.request.sortedBy', 'sortedBy');

        if (Request::get($orderBy) == $sortParameter && in_array(Request::get($sortedBy), ['asc', 'desc'])) {
            $icon .= (Request::get($sortedBy) === 'asc' ? Config::get('sortablelink.asc_suffix',
                '-asc') : Config::get('sortablelink.desc_suffix', '-desc'));
            $direction = Request::get($sortedBy) === 'desc' ? 'asc' : 'desc';

            return [$icon, $direction];
        } else {
            $icon = Config::get('sortablelink.sortable_icon');
            $direction = Config::get('sortablelink.default_direction_unsorted', 'asc');

            return [$icon, $direction];
        }
    }


    /**
     * @param $sortColumn
     *
     * @return string
     */
    private static function selectIcon($sortColumn, $typeIcon)
    {
        
        $icon = Config::get('sortablelink.default_icon_set');
        
        foreach (Config::get('sortablelink.columns', []) as $key=>$value) {
            if($key == $typeIcon) {
                $icon = $value['class'];
                break;
            } elseif (in_array($sortColumn, $value['rows'])) {
                $icon = $value['class'];
                break;
            }
        }
        
        return $icon;
    }


    /**
     * @param $icon
     *
     * @return string
     */
    private static function formTrailingTag($icon)
    {
        $iconAndTextSeparator = Config::get('sortablelink.icon_text_separator', '');

        $clickableIcon = Config::get('sortablelink.clickable_icon', false);
        $trailingTag = $iconAndTextSeparator.'<i class="'.$icon.'"></i>'.'</a>';
        if ($clickableIcon === false) {
            $trailingTag = '</a>'.$iconAndTextSeparator.'<i class="'.$icon.'"></i>';

            return $trailingTag;
        }

        return $trailingTag;
    }


    /**
     * @return string
     */
    private static function getAnchorClass()
    {
        $anchorClass = Config::get('sortablelink.anchor_class', null);
        if ($anchorClass !== null) {
            return ' class="'.$anchorClass.'"';
        }

        return '';
    }


    /**
     * @param $queryParameters
     * @param $sortParameter
     * @param $direction
     *
     * @return string
     */
    private static function buildQueryString($queryParameters, $sortParameter, $direction)
    {
        $checkStrlenOrArray = function ($element) {
            return is_array($element) ? $element : strlen($element);
        };
        
        $orderBy = Config::get('sortablelink.request.orderBy', 'orderBy');
        $sortedBy = Config::get('sortablelink.request.sortedBy', 'sortedBy');
        
        $persistParameters = array_filter(Request::except($orderBy, $sortedBy, 'page'), $checkStrlenOrArray);
        $queryString = http_build_query(array_merge($queryParameters, $persistParameters, [
            $orderBy  => $sortParameter,
            $sortedBy => $direction,
        ]));

        return $queryString;
    }
}
