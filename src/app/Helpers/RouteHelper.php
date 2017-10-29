<?php

namespace Marble\Admin\App\Helpers;

use Config;
use Cache;
use App\Http\Controllers\FrontController;
use Marble\Admin\App\Models\Language;
use Marble\Admin\App\Models\Node;

class RouteHelper
{
    public static function generate()
    {
        $languages = Cache::rememberForever('languages', function () {
            return Language::all();
        });

        $controller = new FrontController();

        foreach ($languages as $language) {
            if (Config::get('app.uri_locale_prefix')) {
                $prefix = Config::get('app.uri_locale_prefix') ? '/'.$language->id.'/' : '/';

                \Route::get($prefix, function () use ($controller, $language) {
                    return $controller->viewIndexForLocale($language);
                });
            }
        }

        $routes = Cache::rememberForever('routes', function () use ($languages, $controller) {
            $routes = array();

            $pageNode = NodeHelper::getSystemNode('pages');
            $pageNodes = false;
            $menuNode = NodeHelper::getSystemNode('menu');
            $menuNodes = false;

            if ($pageNode) {
                $pageNodes = self::getChildren($pageNode->id);
            }

            if ($menuNode) {
                $menuNodes = self::getChildren($menuNode->id);
            }

            foreach ($languages as $language) {
                $prefix = Config::get('app.uri_locale_prefix') ? '/'.$language->id.'/' : '/';

                if ($pageNodes) {
                    self::generatePageRoutes($routes, $prefix, $pageNodes, $language);
                }

                if ($menuNodes) {
                    self::generateMenuRoutes($routes, $prefix, $menuNodes, $language);
                }
            }

            return $routes;
        });

        foreach ($routes as $route => $routeInfo) {
            \Route::get($route, function () use ($controller, $routeInfo) {
                return $controller->viewNode($routeInfo->nodeId, $routeInfo->languageId, func_get_args());
            });
        }
    }

    private static function generatePageRoutes(&$routes, $prefix, $nodes, $language)
    {
        foreach ($nodes as $node) {
            if (!isset($node->attributes->slug) || !$node->attributes->slug->value[$language->id]) {
                continue;
            }

            $route = $prefix;
            $route .= $node->attributes->slug->value[$language->id];
            $routes[$route] = (object) array(
                'nodeId' => $node->id,
                'languageId' => $language->id,
            );

            self::generatePageRoutes($routes, $route.'/', $node->children, $language);
        }
    }

    private static function generateMenuRoutes(&$routes, $prefix, $nodes, $language)
    {
        foreach ($nodes as $node) {
            if (
                ! isset($node->attributes->slug) 
                || ! $node->attributes->slug->value[$language->id] 
                || ! isset($node->attributes->node)
                ) {
                    
                continue;
            }
            
            $targetNode = Node::find($node->attributes->node->value[$language->id]);

            if (!$targetNode) {
                continue;
            }

            $route = $prefix;
            $route .= $node->attributes->slug->value[$language->id];
            $routes[$route] = (object) array(
                'nodeId' => $targetNode->id,
                'languageId' => $language->id,
            );

            self::generateMenuRoutes($routes, $route.'/', $node->children, $language);
        }
    }

    private static function getChildren($parentId)
    {
        $children = Node::where(array('parentId' => $parentId))->get();

        foreach ($children as &$child) {
            $child->children = self::getChildren($child->id);
        }

        return $children;
    }
}
