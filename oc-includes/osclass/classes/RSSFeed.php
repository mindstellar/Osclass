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
 * This class takes items descriptions and generates a RSS feed from that information.
 *
 * @author Osclass
 */
class RSSFeed
{
    private $title;
    private $link;
    private $description;
    private $items;

    public function __construct()
    {
        $this->items = array();
    }

    /**
     * @param $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @param $link
     */
    public function setLink($link)
    {
        $this->link = $link;
    }

    /**
     * @param $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @param $item
     */
    public function addItem($item)
    {
        $this->items[] = $item;
    }

    public function dumpXML()
    {
        echo '<?xml version="1.0" encoding="UTF-8"?>', PHP_EOL;
        echo '<rss version="2.0">', PHP_EOL;
        echo '<channel>', PHP_EOL;
        echo '<title>', $this->title, '</title>', PHP_EOL;
        echo '<link>', $this->link, '</link>', PHP_EOL;
        echo '<description>', $this->description, '</description>', PHP_EOL;
        foreach ($this->items as $item) {
            echo '<item>', PHP_EOL;
            echo '<title><![CDATA[', $item['title'], ']]></title>', PHP_EOL;
            echo '<link>', $item['link'], '</link>', PHP_EOL;
            echo '<guid>', $item['link'], '</guid>', PHP_EOL;

            echo '<description><![CDATA[';
            if (isset($item['image']) && !empty($item['image'])) {
                echo '<a href="' . $item['image']['link'] . '" title="' . $item['image']['title'] . '" rel="nofollow">';
                echo '<img style="float:left;border:0px;" src="' . $item['image']['url'] . '" alt="'
                    . $item['image']['title'] . '"/> </a>';
            }
            echo $item['description'], ']]>';
            echo '</description>', PHP_EOL;

            echo '<country><![CDATA[', $item['country'], ']]></country>', PHP_EOL;
            echo '<region><![CDATA[', $item['region'], ']]></region>', PHP_EOL;
            echo '<city><![CDATA[', $item['city'], ']]></city>', PHP_EOL;
            echo '<cityArea><![CDATA[', $item['city_area'], ']]></cityArea>', PHP_EOL;
            echo '<category><![CDATA[', $item['category'], ']]></category>', PHP_EOL;

            echo '<pubDate>', date('r', strtotime($item['dt_pub_date'])), '</pubDate>', PHP_EOL;

            echo '</item>', PHP_EOL;
        }
        echo '</channel>', PHP_EOL;
        echo '</rss>', PHP_EOL;
    }
}
