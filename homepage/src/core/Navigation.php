<?php
/**
 * Builds Navigation Structures (like Breadcrumbs & Sidebar trees).
 * 
 * Navigation builds a tree (consisting of NavigationElements) of the entire
 * page structure.
 * Breadcrumbs flattens the branch of the current page given its identifier
 * with the from($page) function, resulting in an array needed to render & show
 * the breadcrumbs.
 * Sidebar doesn't change the tree by much, but it flattens the topmost level.
 */
abstract class Navigation {
    protected $config;
    private $pages_dir;

    public function __construct($config, $pages_dir) {
        $this->config = $config;
        $this->pages_dir = $pages_dir;
    }

    private function create_element($page, $parent) {
        $exceptions = $this->config['exceptions'][$page] ?? [];
        return new NavigationElement($page, $parent, $exceptions);
    }

    private function build_tree() {
        $tree = $this->create_element('', null);

        $stack = [$tree];
        while (!empty($stack)) {
            $node = array_pop($stack);

            $dir = $this->pages_dir . '/' . $node->page;
            if (!is_dir($this->pages_dir . '/' . $node->page))
                continue;

            $children = get_dir_items($dir, drop_extensions: true);

            foreach ($children as $child) {
                $child_page = ltrim($node->page . '/' . $child, '/');
                $child_node = $this->create_element($child_page, $node);
                $node->add_child($child_node);
                $stack[] = $child_node;
            }
        }
        return $tree;
    }

    abstract protected function text_exception($page);

    private function resolve_text($page) {
        return $this->text_exception($page)
            ?? App::lang()->page($page)['title']
            ?? 'no title';
    }

    private function apply_config($tree, $navtype) {
        $stack = [$tree];
        while (!empty($stack)) {
            $node = array_pop($stack);

            $defaults = [
                'order' => null,
                'hidden' => false,
                'keep_children' => true,

                'icon' => null,
                'text' => $this->resolve_text($node->page),
                'url' => '/' . $node->page,
            ];
            $node->apply_config($navtype, $defaults);

            foreach ($node->children as $child)
                $stack[] = $child;
        }
        return $tree;
    }

    private function sort_children($node) {
        usort($node->children, function ($a, $b) {
            if (!is_null($a->order) && !is_null($b->order))
                return $a->order <=> $b->order;

            if (!is_null($a->order))
                return -1;

            if (!is_null($b->order))
                return 1;

            if (!empty($a->children) && empty($b->children))
                return -1;

            if (empty($a->children) && !empty($b->children))
                return 1;

            // Case-insensitive natural page ordering, case-sensitive fallback
            $comp = strnatcasecmp($a->page, $b->page);
            if ($comp !== 0) return $comp;
            return strnatcmp($a->page, $b->page);
        });
    }

    private function apply_transformation($tree) {
        $stack = [$tree];
        while (!empty($stack)) {
            $node = array_pop($stack);

            if (is_null($node->parent) && $node->hidden) {
                // Edge case: root hidden
                if (!$node->keep_children) return null;
                else {
                    // Rely on implementation to check if the root is hidden
                    // and handle it specifically
                    foreach ($node->children as $child)
                        $stack[] = $child;
                    continue;
                }
            }

            if (!$node->hidden && $node->keep_children) {
                foreach ($node->children as $child)
                    $stack[] = $child;
            } else if (!$node->hidden && !$node->keep_children) {
                $node->children = [];
            } else if ($node->hidden && $node->keep_children) {
                $node->parent->remove_child($node);
                foreach ($node->children as $child) {
                    $child->parent = $node->parent;
                    $node->parent->add_child($child);
                    $stack[] = $child;
                }
            } else if ($node->hidden && !$node->keep_children) {
                $node->parent->remove_child($node);
            }
        }

        $stack = [$tree];
        while (!empty($stack)) {
            $node = array_pop($stack);
            $this->sort_children($node);
            foreach ($node->children as $child)
                $stack[] = $child;
        }

        return $tree;
    }

    protected function final_tree($navtype) {
        $tree = $this->build_tree();
        $tree = $this->apply_config($tree, $navtype);
        $tree = $this->apply_transformation($tree);
        return $tree;
    }
}

class Breadcrumbs extends Navigation {
    protected function text_exception($page) {
        return App::lang()->breadcrumb_text($page);
    }

    private function find_path($node, $page, &$path = []) {
        $path[] = $node;
        if ($node->page === $page) return $path;
        foreach ($node->children as $child) {
            $result = $this->find_path($child, $page, $path);
            if (!is_null($result)) return $result;
        }
        array_pop($path);
        return null;
    }

    public function from($page) {
        $root = $this->final_tree('breadcrumbs');
        
        $result = $this->find_path($root, $page);

        if ($result[0]->hidden) array_shift($result);

        // Last element should not be clickable
        $result[array_key_last($result)]->url = null;

        return $result;
    }
}

class Sidebar extends Navigation {
    protected function text_exception($page) {
        return App::lang()->sidebar_text($page);
    }

    public function from($page) {
        $root = $this->final_tree('sidebar');
        $trees = $root->children;
        if (!$root->hidden) {
            $root->children = [];
            array_unshift($trees, $root);
        }
        return $trees;
    }
}

class NavigationElement {
    // Tree structure
    public $page;
    public $parent;
    public $children;

    // Configuration
    public $exceptions;

    // Transformation
    public $order;
    public $hidden;
    public $keep_children;

    // Rendering
    public $icon;
    public $text;
    public $url;

    public function __construct($page, $parent, $exceptions) {
        $this->page = $page;
        $this->parent = $parent;
        $this->children = [];

        $this->exceptions = $exceptions;
    }

    public function add_child($child) {
        $this->children[] = $child;
    }

    public function remove_child($child) {
        foreach ($this->children as $key => $c) {
            if ($c === $child) {
                unset($this->children[$key]);
                break;
            }
        }
    }

    public function apply_config($navtype, $defaults) {
        $array = $defaults;

        if (isset($this->exceptions['all']))
            $array = array_replace($array, $this->exceptions['all']);

        if (isset($this->exceptions[$navtype]))
            $array = array_replace($array, $this->exceptions[$navtype]);

        $this->order = $array['order'];
        $this->hidden = $array['hidden'];
        $this->keep_children = $array['keep_children'];

        $this->icon = $array['icon'];
        $this->text = $array['text'];
        $this->url = $array['url'];
    }
}
?>
