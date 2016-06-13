<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Settings block
 *
 * @package    block_settings
 * @copyright  2010 Sam Hemelryk
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class block_settings_renderer extends plugin_renderer_base {

    public function settings_tree(settings_navigation $navigation) {
        $count = 0;
        foreach ($navigation->children as &$child) {
            $child->preceedwithhr = ($count!==0);
            if ($child->display) {
                $count++;
            }
        }
        $navigationattrs = array(
            'class' => 'block_tree list',
            'role' => 'tree',
            'data-ajax-loader' => 'block_navigation/site_admin_loader');
        $content = $this->navigation_node($navigation, $navigationattrs);
        if (isset($navigation->id) && !is_numeric($navigation->id) && !empty($content)) {
            $content = $this->output->box($content, 'block_tree_box', $navigation->id);
        }
        return $content;
    }

    /**
     * Build the navigation node.
     *
     * @param navigation_node $node the navigation node object.
     * @param array $attrs list of attributes.
     * @param int $depth the depth, default to 1.
     * @return string the navigation node code.
     */
    protected function navigation_node(navigation_node $node, $attrs=array(), $depth = 1) {
        $items = $node->children;

        // exit if empty, we don't want an empty ul element
        if ($items->count()==0) {
            return '';
        }

        // array of nested li elements
        $lis = array();
        $number = 0;
        foreach ($items as $item) {
            $number++;
            if (!$item->display) {
                continue;
            }

            $isbranch = ($item->children->count()>0  || $item->nodetype==navigation_node::NODETYPE_BRANCH);
            $hasicon = (!$isbranch && $item->icon instanceof renderable);

            if ($isbranch) {
                $item->hideicon = true;
            }
            $content = $this->output->render($item);

            // this applies to the li item which contains all child lists too
            $liclasses = array($item->get_css_type());
            $liexpandable = array();
            if ($isbranch) {
                $liclasses[] = 'contains_branch';
                if (!$item->forceopen || (!$item->forceopen && $item->collapse) || ($item->children->count() == 0
                        && $item->nodetype == navigation_node::NODETYPE_BRANCH)) {
                    $liexpandable = array('aria-expanded' => 'false');
                } else {
                    $liexpandable = array('aria-expanded' => 'true');
                }
                if ($item->requiresajaxloading) {
                    $liexpandable['data-requires-ajax'] = 'true';
                    $liexpandable['data-loaded'] = 'false';
                }

            } else if ($hasicon) {
                $liclasses[] = 'item_with_icon';
            }
            if ($item->isactive === true) {
                $liclasses[] = 'current_branch';
            }
            $nodetextid = 'label_' . $depth . '_' . $number;
            $liattr = array('class' => join(' ', $liclasses), 'tabindex' => '-1', 'role' => 'treeitem') + $liexpandable;
            // class attribute on the div item which only contains the item content
            $divclasses = array('tree_item');
            if ($isbranch) {
                $divclasses[] = 'branch';
            } else {
                $divclasses[] = 'leaf';
            }
            if (!empty($item->classes) && count($item->classes)>0) {
                $divclasses[] = join(' ', $item->classes);
            }
            $divattr = array('class'=>join(' ', $divclasses));
            if (!empty($item->id)) {
                $divattr['id'] = $item->id;
            }
            $content = html_writer::tag('p', $content, $divattr) . $this->navigation_node($item, array(), $depth + 1);
            if (!empty($item->preceedwithhr) && $item->preceedwithhr===true) {
                $content = html_writer::empty_tag('hr') . $content;
            }
            $liattr['aria-labelledby'] = $nodetextid;
            $content = html_writer::tag('li', $content, $liattr);
            $lis[] = $content;
        }

        if (count($lis)) {
            if (empty($attrs['role'])) {
                $attrs['role'] = 'group';
            }
            return html_writer::tag('ul', implode("\n", $lis), $attrs);
        } else {
            return '';
        }
    }

    public function search_form(moodle_url $formtarget, $searchvalue) {
        $content = html_writer::start_tag('form', array('class'=>'adminsearchform', 'method'=>'get', 'action'=>$formtarget, 'role' => 'search'));
        $content .= html_writer::start_tag('div');
        $content .= html_writer::tag('label', s(get_string('searchinsettings', 'admin')), array('for'=>'adminsearchquery', 'class'=>'accesshide'));
        $content .= html_writer::empty_tag('input', array('id'=>'adminsearchquery', 'type'=>'text', 'name'=>'query', 'value'=>s($searchvalue)));
        $content .= html_writer::empty_tag('input', array('type'=>'submit', 'value'=>s(get_string('search'))));
        $content .= html_writer::end_tag('div');
        $content .= html_writer::end_tag('form');
        return $content;
    }

}