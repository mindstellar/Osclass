<?php
/*
 * Osclass - software for creating and publishing online classified advertising platforms
 * Maintained and supported by Mindstellar Community
 * https://github.com/mindstellar/Osclass
 * Copyright (c) 2021.  Mindstellar
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 *                     GNU GENERAL PUBLIC LICENSE
 *                        Version 3, 29 June 2007
 *
 *  Copyright (C) 2007 Free Software Foundation, Inc. <http://fsf.org/>
 *  Everyone is permitted to copy and distribute verbatim copies
 *  of this license document, but changing it is not allowed.
 *
 *  You should have received a copy of the GNU Affero General Public
 *  License along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 */

/**
 * Enqueued dependiences class.
 *
 * @since 3.1.1
 */
class Dependencies
{

    public $registered;
    public $queue;

    public $resolved = array();
    public $unresolved = array();
    public $error = array();

    public function __construct()
    {
        $this->registered = array();
        $this->queue      = array();
    }

    /**
     * Register url to be loaded
     *
     * @param $id
     * @param $url
     * @param $dependencies mixed, it could be an array or a string
     */
    public function register($id, $url, $dependencies)
    {
        if ($id && $url) {
            $this->registered[$id] = array(
                'key'          => $id,
                'url'          => $url,
                'dependencies' => $dependencies
            );
        }
    }

    /**
     * Remove url to not be loaded
     *
     * @param $id
     */
    public function unregister($id)
    {
        unset($this->registered[$id]);
    }

    /**
     * Try to order all script having in mind their dependencies
     */
    public function order()
    {
        $this->resolved   = array();
        $this->unresolved = array();
        $this->error      = array();

        foreach ($this->queue as $queue) {
            if (isset($this->registered[$queue])) {
                $node = $this->registered[$queue];
                if ($node['dependencies'] == null) {
                    $this->resolved[$node['key']] = $node['key'];
                } else {
                    $this->solveDeps($node);
                }
            } else {
                $this->error[$queue] = $queue;
            }
        }
        if (!empty($this->error)) {
            echo sprintf(__('ERROR: Some dependencies could not be loaded (%s)'), implode(', ', $this->error));
        }
    }

    /**
     * Algorithm to solve the dependencies of the scripts
     *
     * @param $node
     */
    private function solveDeps($node)
    {
        $error = false;
        if (!isset($this->resolved[$node['key']])) {
            $this->unresolved[$node['key']] = $node['key'];
            if ($node['dependencies'] != null) {
                if (is_array($node['dependencies'])) {
                    foreach ($node['dependencies'] as $dep) {
                        if (!in_array($dep, $this->resolved)) {
                            if (in_array($dep, $this->unresolved)) {
                                $this->error[$dep] = $dep;
                                $error             = true;
                            } elseif (isset($this->registered[$dep])) {
                                $this->solveDeps($this->registered[$dep]);
                            } else {
                                $this->error[$dep] = $dep;
                            }
                        }
                    }
                } elseif (!in_array($node['dependencies'], $this->resolved)) {
                    if (in_array($node['dependencies'], $this->unresolved)) {
                        $this->error[$node['dependencies']] = $node['dependencies'];
                        $error                              = true;
                    } elseif (isset($this->registered[$node['dependencies']])) {
                        $this->solveDeps($this->registered[$node['dependencies']]);
                    } else {
                        $this->error[$node['dependencies']] = $node['dependencies'];
                    }
                }
            }
            if (!$error) {
                $this->resolved[$node['key']] = $node['key'];
                unset($this->unresolved[$node['key']]);
            }
        }
    }
}
