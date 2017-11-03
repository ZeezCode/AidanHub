<?php

class Image
{
    public $img;

    function __construct($img)
    {
        $this->img = $img;
    }

    function getIID() {
        return $this->img['iid'];
    }

    function getUUID() {
        return $this->img['uuid'];
    }

    function getTitle() {
        return $this->img['title'];
    }

    function getDescription() {
        return $this->img['description'];
    }

    function getUpvotes() {
        return $this->img['upvotes'];
    }

    function getDownvotes() {
        return $this->img['downvotes'];
    }

    function isPrivate() {
        return $this->img['private'];
    }

    function getTimestamp() {
        return $this->img['timestamp'];
    }

    function getIP() {
        return $this->img['ip'];
    }

    function getDirectURL() {
        return "http://aidanmurphey.com/hub/img?id=" . $this->getIID();
    }

    function getPostURL() {
        return "http://aidanmurphey.com/hub/post?id=" . $this->getIID();
    }

}