<?php
class Queue {
    private $capacity;
    private $size;
    private $head;
    private $tail;
    private $arr;

    function Queue($capacity) {
        $this->capacity = $capacity;
        $this->size = 0;
        $this->head = 0;
        $this->tail = 0;
    }

    function getSize() {
        return $this->size;
    }

    function clear() {
        $this->size = 0;
        $this->head = 0;
        $this->tail = 0;
    }

    function push($value) {
        if($this->size >= $this->capacity) return;
        $this->arr[$this->tail] = $value;
        $this->tail = ($this->tail + 1) % $this->capacity;
        $this->size++;
    }

    function pop() {
        if($this->size <= 0) return null;
        $value = $this->arr[$this->head];
        $this->head = ($this->head + 1) % $this->capacity;
        $this->size--;
        return $value;
    }
}
?>
